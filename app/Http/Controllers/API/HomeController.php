<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\OrderItemQrCodes;
use App\Http\Controllers\Controller;
use App\Http\Controllers\API\BaseController;
use App\Http\Resources\QrContentResource;

class HomeController extends BaseController
{

    /**
     * @OA\Get(
     *     path="/api/best-sellers",
     *     summary="Get Best Seller Products",
     *     description="Fetches a list of best-selling products.",
     *     tags={"Best Seller Products"},
     *     @OA\Response(
     *         response=200,
     *         description="Best Seller Products fetched successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Best Seller Products successfully fetched!"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="product_title", type="string", example="Product Title"),
     *                     @OA\Property(property="image", type="string", example="http://example.com/image.jpg"),
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No best seller products found.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No best seller products found."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Something went wrong. Please try again."),
     *         )
     *     )
     * )
     */


    public function getBestSellersProducts(Request $request)
    {
        try {
            $get_products = getBestSellerProducts();

            if (!empty($get_products)) {
                return $this->sendResponse($get_products, 'Best Seller Products successfully fetched!');
            } else {
                return $this->sendError('Error.', 'No best seller products found.');
            }
        } catch (\Exception $e) {
            return $this->sendError('Error fetching best seller products.', $e->getMessage());
        }
    }


    /**
     * @OA\Get(
     *     path="/api/content/{user_id}/{order_id}/{order_item_id}",
     *     operationId="getQrContent",
     *     tags={"QR Codes"},
     *     summary="Fetch content associated with a QR Code",
     *     description="Fetches the content linked to a specific QR code for an order item.",
     *     @OA\Parameter(
     *         name="user_id",
     *         in="path",
     *         required=true,
     *         description="ID of the user",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="order_id",
     *         in="path",
     *         required=true,
     *         description="ID of the order",
     *         @OA\Schema(type="integer", example=123)
     *     ),
     *     @OA\Parameter(
     *         name="order_item_id",
     *         in="path",
     *         required=true,
     *         description="ID of the order item",
     *         @OA\Schema(type="integer", example=100)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="QR Code content fetched successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=5),
     *                 @OA\Property(property="qr_content", type="string", example="http://127.0.0.1:8000/storage/QrcodeContentDocuments/dummy_1743148120.pdf"),
     *                 @OA\Property(property="qrcode_content_type", type="string", example="document")
     *             ),
     *             @OA\Property(property="message", type="string", example="QR Code fetched successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="QR Code content not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="QR Code not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="QR Code deactivated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="QR Code deactivated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Something went wrong. Please try again.")
     *         )
     *     )
     * )
     */


    public function getQrContent(Request $request, $user_id, $order_id, $order_item_id)
    {
        $qrcode = OrderItemQrCodes::where('order_item_id', $order_item_id)->first();

        if (!$qrcode) {
            return $this->sendError('error.', 'Not found.');
        } elseif ($qrcode->status == 0) {
            return $this->sendError('error.', 'QR Code deactivated.');
        } else {
            return $this->sendResponse(new QrContentResource($qrcode), 'QR Code fetched successfully!');
        }
    }
}
