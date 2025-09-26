<?php

namespace App\Http\Controllers\API;

use App\Models\Order;
use App\Mail\GeneralMail;
use App\Models\ReturnOrder;
use Illuminate\Support\Str;
use App\Utilities\Overrider;
use Illuminate\Http\Request;
use App\Models\EmailTemplate;
use App\Mail\ReturnOrderAdmin;
use App\Models\ReturnOrderImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ReturnOrderResource;
use App\Http\Controllers\API\BaseController;

class ReturnOrderController extends BaseController
{

    /**
     * @OA\Post(
     *     path="/api/return-order",
     *     operationId="returnOrderRequest",
     *     tags={"Order"},
     *     summary="Create a return order request",
     *     security={{"X-Access-Token": {}}},
     *     description="This endpoint allows a user to create a return order request. The return request is validated, and images (if any) are processed before sending emails to both the user and admin.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Return order request data",
     *         @OA\JsonContent(
     *             required={"order_id", "reason"},
     *             @OA\Property(property="order_id", type="integer", description="The ID of the order to return"),
     *             @OA\Property(property="reason", type="string", description="Reason for returning the order", maxLength=225),
     *             @OA\Property(property="description", type="string", description="Additional description of the return (optional)"),
     *             @OA\Property(
     *                 property="image",
     *                 type="array",
     *                 @OA\Items(
     *                     type="string",
     *                     description="Base64-encoded image data or file",
     *                     example="data:image/png;base64,...."
     *                 ),
     *                 description="Array of images for the return order"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Return order request created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Return Order request has sent successfully!"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", description="Return order ID"),
     *                     @OA\Property(property="order_id", type="integer", description="Order ID"),
     *                     @OA\Property(property="reason", type="string", description="Reason for return"),
     *                     @OA\Property(property="description", type="string", description="Description of the return"),
     *                     @OA\Property(property="return_status", type="string", description="Return status", example="Pending"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation Error."),
     *             @OA\Property(property="errors", type="object", additionalProperties={
     *                 @OA\Property(property="order_id", type="array", @OA\Items(type="string", example="The order id field is required.")),
     *                 @OA\Property(property="reason", type="array", @OA\Items(type="string", example="The reason field is required."))
     *             })
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Failed to submit return order request. Please try again.")
     *         )
     *     )
     * )
     */

    public function returnOrderRequest(Request $request)
    {
        ini_set('max_execution_time', '900');

        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'reason' => 'required|string|max:225',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        DB::beginTransaction();

        try {

            $checkExits = ReturnOrder::where('order_id', $request->order_id)->first();

            if ($checkExits) {
                return $this->sendError('Return order request for this order already exists.');
            }

            $returnOrder = ReturnOrder::create([
                'order_id' => $request->order_id,
                'reason' => $request->reason,
                'description' => $request->description,
                'is_send_to_merchmake' => 0,
                'return_status' => 'Pending',
            ]);

            $imageUrls = [];

            if ($request->image) {
                foreach ($request['image'] as $key => $image) {
                    if (strpos($image, 'data:image') === 0) {

                        $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $image));

                        $finfo = new \finfo(FILEINFO_MIME_TYPE);
                        $mimeType = $finfo->buffer($imageData);
                        $extension = '';

                        switch ($mimeType) {
                            case 'image/jpeg':
                                $extension = 'jpg';
                                break;
                            case 'image/png':
                                $extension = 'png';
                                break;
                            case 'image/gif':
                                $extension = 'gif';
                                break;
                            default:
                                $extension = 'png';
                                break;
                        }

                        $fileName = time() . '-' . Str::random(10) . '.' . $extension;
                        $filePath = 'ReturnOrderImages/' . $fileName;

                        Storage::disk('public')->put($filePath, $imageData);

                        $imageUrls[] = asset('storage/' . $filePath);

                        ReturnOrderImage::create([
                            'return_orders_id' => $returnOrder->id,
                            'image_name' => $fileName,
                            'image_path' => $filePath,
                        ]);
                    } else {

                        if (isset($request->file('image')[$key])) {
                            $file = $request->file('image')[$key];

                            $originalFileName = $file->getClientOriginalName();
                            $extension = $file->getClientOriginalExtension();

                            $fileName = pathinfo($originalFileName, PATHINFO_FILENAME) . '_' . time() . '.' . $extension;
                            $path = $file->storeAs('ReturnOrderImages', $fileName, 'public');

                            $imageUrls[] = asset('storage/' . $path);

                            ReturnOrderImage::create([
                                'return_orders_id' => $returnOrder->id,
                                'image_name' => $fileName,
                                'image_path' => $path,
                            ]);
                        } else {

                            $img_data = $request['image'][$key];

                            $decoded_image = base64_decode($img_data);

                            if ($decoded_image === false) {
                                return response()->json(['status' => 'error', 'message' => 'Invalid Base64 image data']);
                            }

                            $fileName = time() . '-' . Str::random(10) . '.' . 'jpg';
                            $filePath = 'ReturnOrderImages/' . $fileName;

                            Storage::disk('public')->put($filePath, $decoded_image);

                            $imageUrls[] = asset('storage/' . $filePath);
                            ReturnOrderImage::create([
                                'return_orders_id' => $returnOrder->id,
                                'image_name' => $fileName,
                                'image_path' => $filePath,
                            ]);
                        }
                    }
                }
            }


            DB::commit();

            // send mail to user
            Overrider::load("Settings");

            //Replace paremeter
            $replace = array(
                '{order_id}'          => $returnOrder->order_id,
                '{reason}'              => $returnOrder->reason,
                '{name}'              => $returnOrder->order->user->name . ' ' . $returnOrder->order->user->last_name,
            );

            //Send contact email
            $template = EmailTemplate::where('slug', 'return-order-request')->first();
            $template->body = process_string($replace, $template->body);

            Mail::to($returnOrder->order->user->email)->send(new GeneralMail($template));

            // send mail to admin

            $email = get_options('get_contact_us_email_on');

            $files = [];

            if (!empty($returnOrder->returnOrderImages)) {
                foreach ($returnOrder->returnOrderImages as $img) {
                    $files[] = storage_path('app/public/' . $img->image_path);
                }
            }

            // Prepare the mail data
            $mailData = [
                'title' => 'Return Order Request',
                'return_order' => $returnOrder,
                'files' => $files
            ];

            // Send the email
            Mail::to($email)->send(new ReturnOrderAdmin($mailData));

            return $this->sendResponse(ReturnOrderResource::collection([$returnOrder]), 'Return Order request has sent successfully!');
        } catch (\Exception $e) {
            // dd($e->getMessage());
            DB::rollBack();
            Log::error('Error while submitting return order request: ' . $e->getMessage());
            return $this->sendError('error.', 'Failed to submit return order request. Please try again.');
        }
    }

    /**
     * @OA\Get(
     *     path="/api/return-order/{order_id}",
     *     summary="Get Return Order for a specific order",
     *     description="Fetches a return order associated with a given order ID.",
     *     tags={"Order"},
     *     security={{"X-Access-Token": {}}},
     *     @OA\Parameter(
     *         name="order_id",
     *         in="path",
     *         required=true,
     *         description="The ID of the order to retrieve the return order for.",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Return Order fetched successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Return Order fetched successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1, description="The ID of the return order"),
     *                 @OA\Property(property="order_id", type="integer", example=101, description="The order ID associated with the return order"),
     *                 @OA\Property(property="status", type="string", example="pending", description="The status of the return order"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-15T10:00:00Z", description="Timestamp when the return order was created"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-16T15:00:00Z", description="Timestamp when the return order was last updated")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Return Order not found.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Return Order not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Something went wrong. Please try again.")
     *         )
     *     )
     * )
     */
    public function getReturnOrder(Request $request, $order_id)
    {
        try {
            $order = Order::where('user_id', Auth::user()->id)->where('id', $order_id)->first();
            if ($order) {
                $return_order = ReturnOrder::where('order_id', $order->id)->first();
                if ($return_order) {
                    return $this->sendResponse(new ReturnOrderResource($return_order), 'Return Order fetched successfully.');
                }
            } else {
                return $this->sendError('Error.', 'Return Order not found.');
            }
        } catch (\Exception $e) {
            return $this->sendError('Error fetching return order.', $e->getMessage());
        }
    }



    /**
     * @OA\Get(
     *     path="/api/return-orders",
     *     summary="Get All Return Orders for a specific user",
     *     description="Fetches all return orders associated with the authenticated user.",
     *     tags={"Order"},
     *     security={{"X-Access-Token": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Return Orders fetched successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Return Orders fetched successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1, description="The ID of the return order"),
     *                     @OA\Property(property="order_id", type="integer", example=101, description="The ID of the associated order"),
     *                     @OA\Property(property="status", type="string", example="pending", description="The status of the return order"),
     *                     @OA\Property(property="created_at", type="string", example="2025-01-15T10:00:00Z", description="When the return order was created"),
     *                     @OA\Property(property="updated_at", type="string", example="2025-01-16T12:30:00Z", description="When the return order was last updated")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No return orders found.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Return Order not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Something went wrong. Please try again.")
     *         )
     *     )
     * )
     */

    public function getAllReturnOrder(Request $request)
    {
        try {
            $order_ids = Order::where('user_id', Auth::user()->id)->pluck('id')->toArray();

            $return_orders = ReturnOrder::whereIn('order_id', $order_ids)->get();

            if ($return_orders->count() > 0) {
                return $this->sendResponse(ReturnOrderResource::collection($return_orders), 'Return Orders fetched successfully.');
            } else {
                return $this->sendError('Error.', 'Return Orders not found.');
            }
        } catch (\Exception $e) {
            return $this->sendError('Error fetching return orders.', $e->getMessage());
        }
    }
}
