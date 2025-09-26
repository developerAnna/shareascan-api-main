<?php

namespace App\Http\Controllers\API;

use Stripe\Stripe;
use App\Models\Cart;
use App\Models\Order;
use Stripe\PaymentIntent;
use App\Models\OrderItems;
use App\Models\ReturnOrder;
use App\Models\Transaction;
use App\Services\MerchMake;
use App\Models\StoreWebhook;
use Illuminate\Http\Request;
use App\Models\CartItemQrCodes;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Stripe\Exception\ApiErrorException;
use App\Services\OrderInMerchmakeService;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\BaseController;

class PaymentController extends BaseController
{

    /**
     * @OA\Post(
     *     path="/api/create-payment-intent",
     *     operationId="createPaymentIntent",
     *     tags={"Payment"},
     *     summary="Create a Stripe PaymentIntent",
     *     description="Generates a Stripe PaymentIntent for an order and returns the client secret.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"order_id"},
     *             @OA\Property(property="order_id", type="integer", example=1, description="The ID of the order for which the payment intent is created.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment Intent generated successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="ClientSecret", type="string", example="your-client-secret-here", description="The client secret for the payment intent.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation Error."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed for the provided data.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation Error."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to create payment intent for order.")
     *         )
     *     ),
     *     security={{"X-Access-Token": {}}}
     * )
     */

    public function createPaymentIntent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        // Set Stripe secret key
        $stripe_secret = get_options('stripe_client_secret');
        Stripe::setApiKey($stripe_secret);

        $order_id = $request->order_id;
        $order = Order::where('id', $order_id)->first();
        if ($order && $order->status == 1 && $order->payment_method != '') {
            return $this->sendError('Error.', 'Payment already done for this order');
        }
        $order_total = $order->orderItems->sum('total');

        try {
            // Create a PaymentIntent with the specified amount and currency
            $paymentIntent = PaymentIntent::create([
                'amount' => $order_total * 100,  // Amount in cents
                'currency' => 'USD',  // For India, use INR as the currency
                'description' => 'Payment for Order #' . $order->id,
                'metadata' => [
                    'integration_check' => 'accept_a_payment',
                    'customer_name' => $order->shippingAddress->first_name . ' ' . $order->shippingAddress->last_name,
                    'customer_email' => $order->shippingAddress->email,
                    'order_id' => $order->id
                ],
                'receipt_email' => $order->shippingAddress->email,
                'shipping' => [
                    'name' => $order->shippingAddress->first_name . ' ' . $order->shippingAddress->last_name,
                    'address' => [
                        'line1' => $order->shippingAddress->address_1,
                        'line2' => $order->shippingAddress->address_2 ?? null,
                        'city' => $order->shippingAddress->city,
                        'state' => $order->shippingAddress->state,
                        'country' => $order->shippingAddress->country_code,
                        'postal_code' => $order->shippingAddress->zip,
                    ],
                ],
                'payment_method_types' => ['card'],
            ]);

            $exits = Transaction::where('order_id', $order_id)->first();
            if ($exits) {
                $exits->delete();
            }
            $transaction = new Transaction();
            $transaction->order_id = $order_id;
            $transaction->payment_method = 'Stripe';
            $transaction->save();

            $order->update(['payment_method' => 'Stripe']);

            return $this->sendResponse(['ClientSecret' => $paymentIntent->client_secret], 'Payment Intent generated successfully!');
        } catch (ApiErrorException $e) {
            Log::error('Error in creating payment intent for order: ' . $e->getMessage());
            return $this->sendError('Error.', 'Failed to create payment intent for order. ' . $e->getMessage());
        }
    }

    public function handleStripeWebhook(Request $request)
    {
        // Read the raw payload and decode it for logging or DB storage
        $payload = file_get_contents('php://input');
        $parsedPayload = json_decode($payload, true);

        try {
            $event = \Stripe\Event::constructFrom($parsedPayload);
        } catch (\UnexpectedValueException $e) {
            Log::channel('stripe_webhook')->error('Stripe webhook error: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        // Log the event type for debugging purposes
        Log::channel('stripe_webhook')->info('Stripe Webhook event: ' . $event->type);
        Log::channel('stripe_webhook')->info('Event payload: ', $parsedPayload);

        $object = $event->data->object;

        $orderId = $object->metadata->order_id ?? null;

        // Helper function to find the order by ID
        $findOrder = function ($id) {
            return Order::find((int) $id);
        };

        // Helper function to update a transaction record by order_id
        // Pass an associative array for columns to update
        $updateTransaction = function ($id, array $data) {
            Transaction::where('order_id', $id)->update($data);
        };

        switch ($event->type) {
            /**
             * payment_intent.created
             * Triggered when a PaymentIntent is first created.
             * You can update your transaction record with partial info.
             */
            case 'payment_intent.created':
                if ($orderId) {
                    $order = $findOrder($orderId);
                    if ($order) {
                        $updateTransaction($order->id, [
                            'amount'         => ($object->amount / 100),
                            'stripe_response' => json_encode($parsedPayload, true),
                        ]);
                    }
                }
                break;

            /**
                 * payment_intent.succeeded
                 * Payment has been successfully processed (may or may not be captured).
                 * Often followed by charge.succeeded.
                 */
            case 'payment_intent.succeeded':
                if ($orderId) {
                    $order = $findOrder($orderId);
                    if ($order) {
                        $order->update(['payment_status' => 'Pending']);
                        $updateTransaction($order->id, [
                            'stripe_response' => json_encode($parsedPayload, true),
                        ]);
                    }
                }
                break;

            /**
                 * payment_intent.requires_action
                 * Additional authentication (e.g., 3D Secure) is needed.
                 */
            case 'payment_intent.requires_action':
                Log::channel('stripe_webhook')->info("PaymentIntent requires further action: {$object->id}");
                break;

            /**
                 * payment_intent.canceled
                 * The PaymentIntent was canceled (by you or automatically by Stripe).
                 */
            case 'payment_intent.canceled':
                if ($orderId) {
                    $order = $findOrder($orderId);
                    if ($order) {
                        $order->update(['payment_status' => 'Canceled']);
                        $updateTransaction($order->id, [
                            'transaction_id' => $object->id,
                        ]);
                    }
                }
                break;

            /**
                 * charge.succeeded
                 * The charge was successfully captured and the funds are transferred.
                 * This typically confirms the final payment success.
                 */
            case 'charge.succeeded':
                if ($orderId) {
                    $order = $findOrder($orderId);
                    if ($order) {
                        $order->update(['payment_status' => 'Completed', 'status' => 1]);
                        $updateTransaction($order->id, [
                            'transaction_id'    => $object->id,
                            'charge_id'    => $object->id,
                        ]);

                        $merchMakeService = new OrderInMerchmakeService();
                        $merchMakeService->handlePaymentSucceed($order);
                    }
                }
                break;

            /**
                 * charge.failed
                 * The charge failed (insufficient funds, card declined, etc.).
                 */
            case 'charge.failed':
                if ($orderId) {
                    $order = $findOrder($orderId);
                    if ($order) {
                        $order->update(['payment_status' => 'Failed']);
                        $updateTransaction($order->id, [
                            'transaction_id' => $object->id,
                        ]);
                    }
                }
                break;

            /**
                 * charge.refunded
                 * A refund has been issued for this charge.
                 */
            case 'charge.refunded':
                if ($orderId) {
                    $order = $findOrder($orderId);
                    if ($order) {
                        $order->update(['payment_status' => 'Refunded']);
                        ReturnOrder::where('order_id', $order->id)->update(['return_status' => 'Refunded']);
                        $updateTransaction($order->id, [
                            'transaction_id' => $object->id,
                        ]);

                        $merchMakeService = new OrderInMerchmakeService();
                        $merchMakeService->handlePaymentRefund($order);
                    }
                }
                break;

            /**
                 * payment_method.attached
                 * A payment method was attached to a customer object.
                 */
            case 'payment_method.attached':
                Log::channel('stripe_webhook')->info("Payment method attached: {$object->id}");
                break;

            default:
                Log::channel('stripe_webhook')->warning('Received unknown event type: ' . $event->type);
                break;
        }

        if ($orderId) {

            $order = $findOrder($orderId);

            StoreWebhook::create([
                'order_id' => $order->id,
                'hoook_id' => $event->data->object->id,
                'hook_type' => $event->type,
                'hook_data' =>  json_encode($event),
                'hook_status' => $event->data->object->status,
                'event_id' => $event->id,
                'payment_method' => "Stripe",
            ]);
        }
    }
}
