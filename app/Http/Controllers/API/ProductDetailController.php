<?php

namespace App\Http\Controllers\API;

use App\Models\Qrcodes;
use App\Services\MerchMake;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\QrcodesResurce;
use App\Http\Resources\SampleQrResource;
use App\Http\Controllers\API\BaseController;

class ProductDetailController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/product-detail/{id}",
     *     summary="Get product details by product ID",
     *     description="Retrieve product details along with its variations by the product ID.",
     *     operationId="getProductDetail",
     *     tags={"Product Detail Page"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the product",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product data retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="product",
     *                 type="object",
     *                 description="Product details",
     *                 example={
     *                     "id": 1,
     *                     "title": "Product title",
     *                 }
     *             ),
     *             @OA\Property(
     *                 property="variationArr",
     *                 type="array",
     *                 description="Product variations",
     *                 @OA\Items(
     *                     type="object",
     *                     example={
     *                         "size": "L",
     *                         "color": "Red",
     *                     }
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid product ID provided",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Product ID is required."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No product found with the given ID",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="No product found in merchmake with this id."
     *             )
     *         )
     *     )
     * )
     */

    public function getProductDetail(Request $request, $id)
    {
        if (empty($id)) {
            return $this->sendResponse([], 'Product ID is required.');
        }

        $merchMake = new MerchMake();
        $merchmake_product = $merchMake->getSingleProduct($id);

        if ($merchmake_product === false) {
            return $this->sendError('No product found in merchmake with this id.');
        }

        $product_variations = getSingleProductVariations($merchmake_product);

        if ($merchmake_product) {
            $data['product'] = $merchmake_product;
            $data['variationArr'] = $product_variations;
            return $this->sendResponse($data, 'Product data retrieved successfully!');
        } else {
            return $this->sendError('No product found in merchmake with this id.');
        }
    }

    /**
     * @OA\Get(
     *     path="/api/get_variation_price/{id}",
     *     summary="Get product variation price based on size and color",
     *     description="Retrieve the price of a product variation based on selected size and color.",
     *     operationId="getVariationPrice",
     *     tags={"Product Detail Page"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the product",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="size_name",
     *         in="query",
     *         required=true,
     *         description="The size of the product variation (e.g., 'S', 'M', 'L')",
     *         @OA\Schema(
     *             type="string",
     *             example="M"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="color_name",
     *         in="query",
     *         required=true,
     *         description="The color of the product variation (e.g., 'Red', 'Blue')",
     *         @OA\Schema(
     *             type="string",
     *             example="Red"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Variation data retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="price",
     *                 type="number",
     *                 format="float",
     *                 description="The price of the selected variation",
     *                 example=120.00
     *             ),
     *             @OA\Property(
     *                 property="size",
     *                 type="string",
     *                 description="The selected size",
     *                 example="M"
     *             ),
     *             @OA\Property(
     *                 property="color",
     *                 type="string",
     *                 description="The selected color",
     *                 example="Red"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Size and color are required to get the variation data",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Size and color are required to get the variation data"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No variation found with the provided size and color",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="No variation found in merchmake with this data."
     *             )
     *         )
     *     )
     * )
     */

    public function getVariationPrice(Request $request, $id)
    {

        try {
            if (!empty($request->size_name) && !empty($request->color_name)) {
                $variation_data = getSelectedVariationPrice($id, $request->size_name, $request->color_name);

                if (!empty($variation_data)) {
                    return $this->sendResponse($variation_data, 'Variation data retrieved successfully!');
                } else {
                    return $this->sendError('No variation found in merchmake with this data.');
                }
            } else {
                return $this->sendError('size_name and color_name are required to get the variation data');
            }
        } catch (\Exception $e) {
            return $this->sendError('Error retrieving variation data.', $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/qr-codes",
     *     summary="Retrieve all generated QR codes",
     *     description="Get a list of all generated QR codes from the system.",
     *     operationId="getQrCodes",
     *     tags={"Product Detail Page"},
     *     @OA\Response(
     *         response=200,
     *         description="QR codes retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         description="ID of the QR code",
     *                         example=1
     *                     ),
     *                     @OA\Property(
     *                         property="qr_image",
     *                         type="string",
     *                         description="The generated QR code value",
     *                         example="qr_code_1742292115.png"
     *                     ),
     *                       @OA\Property(
     *                         property="qr_image_path",
     *                         type="string",
     *                         description="The generated QR code path",
     *                         example="http://localhost:8000/storage/qrcodes/qr_code_1742292115.png"
     *                     ),
     *                      @OA\Property(
     *                         property="qr_data",
     *                         type="string",
     *                         description="data value used to generate the qr",
     *                         example="https://shareascan.com"
     *                     ),
     *                     @OA\Property(
     *                         property="created_at",
     *                         type="string",
     *                         format="date-time",
     *                         description="The date and time when the QR code was generated",
     *                         example="2025-03-18T15:30:00Z"
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="No QR codes found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="No QR codes found."
     *             )
     *         )
     *     )
     * )
     */

    public function getQrCodes(Request $request)
    {
        try {
            $qrcodes = Qrcodes::get();

            if ($qrcodes->count() > 0) {
                return $this->sendResponse(SampleQrResource::collection($qrcodes), 'Qrcodes retrieved successfully!');
            } else {
                return $this->sendError('No qrcodes found.');
            }
        } catch (\Exception $e) {
            return $this->sendError('Error retrieving QR codes.', $e->getMessage());
        }
    }
}
