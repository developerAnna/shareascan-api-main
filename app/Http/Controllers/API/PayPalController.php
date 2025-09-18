<?php

namespace App\Http\Controllers\API;


use App\Models\Cart;

use App\Models\Order;

use PayPal\Api\Payer;
use PayPal\Api\Amount;
use PayPal\Api\Address;

use PayPal\Api\Payment;
use PayPal\Api\ItemList;
use PayPal\Api\PayerInfo;
use App\Models\OrderItems;
use App\Models\ReturnOrder;
use App\Services\MerchMake;
use PayPal\Api\Transaction;
use PayPal\Rest\ApiContext;
use App\Models\StoreWebhook;
use Illuminate\Http\Request;
use PayPal\Api\RedirectUrls;
use App\Models\CartItemQrCodes;
use PayPal\Api\ShippingAddress;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\VerifyWebhookSignature;
use App\Services\OrderInMerchmakeService;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\BaseController;
use PayPal\Exception\PayPalConnectionException;
use App\Models\Transaction as ModelsTransaction;

class PayPalController extends BaseController
{
    /**
     * @OA\Post(
     *     path="/api/paypal-payment",
     *     summary="Generate PayPal payment URL",
     *     description="Creates a PayPal payment and returns the approval URL.",
     *     security={{"X-Access-Token": {}}},
     *     tags={"Payment"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"order_id"},
     *             @OA\Property(property="order_id", type="integer", example=123, description="Order ID")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment URL generated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="approval_url", type="string", example="https://www.paypal.com/checkoutnow?token=EC-123456789")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error or missing shipping address",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Shipping address is missing for this order.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="object", example={"order_id": {"The order_id field is required."}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An error occurred while generating the payment URL.")
     *         )
     *     ),
     * )
     */

    public function paypalPaymentUrl(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        try {

            $currency = "USD";

            $order = Order::where('id', $request->order_id)->first();

            if ($order && $order->status == 1 && $order->payment_method != '') {
                return $this->sendError('Error.', 'Payment already done for this order');
            }

            $totalAmount = $order->orderItems->sum('total');

            try {
                // Create a new payment instance
                $payment = new Payment();
                $payment->setIntent('sale');

                // Set the payer details
                $payer = new Payer();
                $payer->setPaymentMethod('paypal')
                    ->setPayerInfo(new \PayPal\Api\PayerInfo());

                $payment->setPayer($payer);

                // Set the amount
                $amount = new Amount();
                $amount->setTotal($totalAmount);
                $amount->setCurrency($currency);

                $transaction = new Transaction();
                $transaction->setAmount($amount);

                // Ensure shipping address exists
                if (!$order->shippingAddress) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Shipping address is missing for this order.'
                    ], 400);
                }

                $shippingAddress = new Address();
                $shippingAddress->setLine1($order->shippingAddress->address_1)
                    ->setLine2($order->shippingAddress->address_2 ?? '')
                    ->setCity($order->shippingAddress->city)
                    ->setState($order->shippingAddress->state)
                    ->setPostalCode($order->shippingAddress->zip)
                    ->setCountryCode($order->shippingAddress->country_code);

                // Create PayerInfo object and set the shipping address
                $payerInfo = new PayerInfo();
                $payerInfo->setShippingAddress($shippingAddress);
                $payer->setPayerInfo($payerInfo);

                $payment->setTransactions([$transaction]);

                // Set the redirect URLs
                $redirectUrls = new RedirectUrls();
                $redirectUrls->setReturnUrl(env('FRONT_URL') . '/thank-you');
                $redirectUrls->setCancelUrl(env('FRONT_URL') . '/cancel');
                $payment->setRedirectUrls($redirectUrls);

                // Get PayPal credentials from config
                $client_id = get_options('paypal_client_id');
                $client_secret = get_options('paypal_client_secret');

                // Initialize the API context
                $apiContext = new ApiContext(
                    new OAuthTokenCredential($client_id, $client_secret)
                );

                // Set the API mode (sandbox or live)
                $apiContext->setConfig([
                    'mode' => get_options('paypal_mode'),
                ]);

                // Create the payment
                $payment->create($apiContext);
                $approvalUrl = $payment->getApprovalLink();
                $paymentId = $payment->getId();

                parse_str(parse_url($approvalUrl, PHP_URL_QUERY), $queryParams);
                $paypalToken = $queryParams['token'] ?? null;  // Extract "token" from URL

                // Save payment details
                $order->paypal_id = $paymentId;
                $order->paypal_token = $paypalToken;
                $order->payment_method = "Paypal";
                $order->save();

                return response()->json([
                    'status' => true,
                    'approval_url' => $approvalUrl,
                ], 200);
            } catch (\Throwable $th) {
                return response()->json([
                    'status' => false,
                    'message' => $th->getMessage()
                ], 500);
            }
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
            Log::info($order->id);
            // Logs::addLog('Issue on generating URL -> Exception Message:' . $th->getMessage(), 'PayPal generate url', $cart->cart_id, $user->id);
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    /**
     * @OA\Post(
     *     path="/api/paypal-payment/capture",
     *     summary="Capture PayPal Payment",
     *     description="This endpoint captures a PayPal payment after the user has been redirected back from PayPal.",
     *     tags={"Payment"},
     *     security={{"X-Access-Token": {}}},
     *     operationId="paypalPaymentCapture",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"payment_id", "payer_id", "token"},
     *             @OA\Property(property="payment_id", type="string", description="The PayPal payment ID."),
     *             @OA\Property(property="payer_id", type="string", description="The PayPal payer ID."),
     *             @OA\Property(property="token", type="string", description="The PayPal token generated during the initial payment creation."),
     *         ),
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Payment captured successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="approved"),
     *             @OA\Property(property="data", type="object", description="The PayPal payment execution result.")
     *         )
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Payment capture failed or order not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Order not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Validation error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation Error."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error while capturing payment.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="PayPal API connection error")
     *         )
     *     ),
     * )
     */

    public function paypalPaymentCapture(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'payment_id' => 'required',
            'payer_id' => 'required',
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        $payment_id = $request->input('payment_id');
        $payer_id = $request->input('payer_id');
        $token = $request->input('token');

        $order = Order::where('paypal_token', $token)->first();
        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found',
            ], 401);
        }
        try {
            $client_id = get_options('paypal_client_id');
            $client_secret = get_options('paypal_client_secret');

            $apiContext = new ApiContext(
                new OAuthTokenCredential($client_id, $client_secret)
            );

            $apiContext->setConfig([
                'mode' => get_options('paypal_mode'),
            ]);

            $payment = Payment::get($payment_id, $apiContext);

            // Execute the payment
            $execution = new \PayPal\Api\PaymentExecution();
            $execution->setPayerId($payer_id);

            try {
                $result = $payment->execute($execution, $apiContext);
                Log::info($result);

                if ($result->state == 'approved') {
                    // $order->payment_status = "Completed";
                    $order->save();

                    $transactionDetails = $result->transactions[0];
                    $amount = $transactionDetails->amount->total;

                    $exits = ModelsTransaction::where('order_id', $order->id)->first();
                    if ($exits) {
                        $exits->delete();
                    }

                    // Store transaction details in the database
                    $transaction = new ModelsTransaction();
                    $transaction->order_id = $order->id;
                    $transaction->transaction_id = $result->id;
                    $transaction->payment_method = "Paypal";
                    $transaction->amount = $amount;
                    $transaction->stripe_response = json_encode($result->toArray(), true);
                    $transaction->save();

                    return response()->json(['status' => 'approved', 'data' => $result]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Failed to capture payment',
                    ], 401);
                }
            } catch (PayPalConnectionException $e) {
                Log::info($e->getMessage());
                Log::info($order->id);
                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
        } catch (PayPalConnectionException $e) {

            Log::info($e->getMessage());
            Log::info($order->id);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function handlePaypalWebhook(Request $request)
    {
        try {
            $payload = $request->getContent();
            Log::channel('paypal_webhook')->info('Received Payload: ' . $payload);

            $headers = apache_request_headers();
            // Log::info($headers);

            $webhook_id = get_options('paypal_webhook_id') ?? null;

            // get http payload
            $body = file_get_contents('php://input');

            $data =
                $headers['PAYPAL-TRANSMISSION-ID'] . '|' .
                $headers['PAYPAL-TRANSMISSION-TIME'] . '|' .
                $webhook_id . '|' . crc32($body);


            // load certificate and extract public key
            $pubKey = openssl_pkey_get_public(file_get_contents($headers['PAYPAL-CERT-URL']));
            $key = openssl_pkey_get_details($pubKey)['key'];

            // verify data against provided signature
            $result = openssl_verify(
                $data,
                base64_decode($headers['PAYPAL-TRANSMISSION-SIG']),
                $key,
                'sha256WithRSAEncryption'
            );


            if ($result == 1) {

                Log::channel('paypal_webhook')->info('webhook verified');
                // Decode JSON Webhook
                $webhook_type = json_decode($payload, true);
                if (!$webhook_type) {
                    Log::channel('paypal_webhook')->error('Invalid JSON in Webhook.');
                    return response()->json(['success' => false, 'message' => 'Invalid JSON'], 400);
                }

                $order_id = null;
                $payment_id = $webhook_type['resource']['parent_payment'] ?? ($webhook_type['resource']['id'] ?? '');

                // Get Order (if exists)
                $order_details = Order::where('paypal_id', $payment_id)->first();
                if ($order_details) {
                    $order_id = $order_details->id;
                }


                switch ($webhook_type['event_type']) {
                    case 'PAYMENTS.PAYMENT.CREATED':
                        if ($order_details) {
                            $order_details->update(['payment_status' => 'Created']);
                        }
                        break;

                    case 'PAYMENT.SALE.COMPLETED':
                        if ($order_details) {
                            $order_details->update(['payment_status' => 'Completed', 'status' => 1]);

                            $merchMakeService = new OrderInMerchmakeService();
                            $merchMakeService->handlePaymentSucceed($order_details);
                        }
                        break;

                    case 'PAYMENT.SALE.PENDING':
                        if ($order_details) {
                            $order_details->update(['payment_status' => 'Pending']);
                        }
                        break;

                    case 'PAYMENT.SALE.DENIED':
                        if ($order_details) {
                            $order_details->update(['payment_status' => 'Denied']);
                        }
                        break;

                    case 'PAYMENT.SALE.REFUNDED':
                        if ($order_details) {
                            $order_details->update(['payment_status' => 'Refunded']);
                            ReturnOrder::where('order_id', $order_id)->update(['return_status' => 'Refunded']);

                            $merchMakeService = new OrderInMerchmakeService();
                            $merchMakeService->handlePaymentRefund($order_details);
                        }
                        break;

                    default:
                        Log::channel('paypal_webhook')->warning('Unhandled Webhook Event: ' . $webhook_type['event_type']);
                        break;
                }

                // Save Webhook to Database
                StoreWebhook::create([
                    'order_id' => $order_id,
                    'hoook_id' => $payment_id,
                    'hook_type' => $webhook_type['event_type'],
                    'hook_data' => json_encode($webhook_type),
                    'hook_status' => $webhook_type['resource']['state'] ?? 'unknown',
                    'event_id' => $webhook_type['id'],
                    'payment_method' => 'Paypal'
                ]);
            } elseif ($result == 0) {
                // webhook notification is NOT verified
                Log::channel('paypal_webhook')->info('webhook not verified');
                // return false;
            } else {
                // there was an error verifying this
                Log::channel('paypal_webhook')->info('webhook verification error');
                // return false;
            }

            return response()->json(['success' => true, 'message' => 'Webhook processed']);
        } catch (\Exception $ex) {
            Log::channel('paypal_webhook')->error('Error: ' . $ex->getMessage());
            return response()->json(['success' => false, 'message' => $ex->getMessage()], 500);
        }
    }
}
