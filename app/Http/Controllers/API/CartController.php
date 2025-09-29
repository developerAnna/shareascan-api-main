<?php

namespace App\Http\Controllers\API;

use App\Models\Cart;
use App\Services\MerchMake;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\BaseController;
use App\Models\Qrcodes;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx\Rels;

class CartController extends BaseController
{

    /**
     * @OA\Post(
     *     path="/api/add-to-cart",
     *     operationId="addCart",
     *     tags={"Cart"},
     *     summary="Add product to cart (Requires token)",
     *     description="Add a product and its variation to the cart, including validation for art files.",
     *     security={{"X-Access-Token": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="user_id", type="integer", example=1, description="ID of the user."),
     *             @OA\Property(property="product_id", type="integer", example=101, description="ID of the product."),
     *             @OA\Property(property="product_variation_id", type="integer", example=202, description="ID of the product variation."),
     *             @OA\Property(property="qty", type="integer", example=2, description="Quantity of the product to add."),
     *             @OA\Property(property="price", type="number", format="float", example=99.99, description="Price of the product."),
     *             @OA\Property(property="art_files", type="object", description="Object of art file positions as keys and QR code IDs as values.",
     *                 @OA\Property(property="Front", type="integer", example=501, description="ID of the QR code for the 'Front' position."),
     *                 @OA\Property(property="Back", type="integer", example=502, description="ID of the QR code for the 'Back' position.")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product added to cart successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1, description="Cart entry ID."),
     *                 @OA\Property(property="user_id", type="integer", example=1, description="ID of the user."),
     *                 @OA\Property(property="product_id", type="integer", example=101, description="ID of the product."),
     *                 @OA\Property(property="qty", type="integer", example=2, description="Quantity of the product added."),
     *                 @OA\Property(property="price", type="number", format="float", example=99.99, description="Price of the product."),
     *                 @OA\Property(property="product_variation_id", type="integer", example=202, description="ID of the product variation."),
     *                 @OA\Property(property="product_title", type="string", example="T-shirt", description="Title of the product."),
     *                 @OA\Property(property="variation_color", type="string", example="Red", description="Color of the product variation."),
     *                 @OA\Property(property="variation_size", type="string", example="M", description="Size of the product variation."),
     *                 @OA\Property(property="art_files", type="object", description="List of art files and positions associated with the cart item.",
     *                     @OA\Property(property="Front", type="integer", example=501, description="ID of the associated QR code for the 'Front' position."),
     *                     @OA\Property(property="Back", type="integer", example=502, description="ID of the associated QR code for the 'Back' position.")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Product added to cart successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation Error."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="user_id", type="array", @OA\Items(type="string", example="The user_id field is required.")),
     *                 @OA\Property(property="product_id", type="array", @OA\Items(type="string", example="The product_id field is required.")),
     *                 @OA\Property(property="qty", type="array", @OA\Items(type="string", example="The qty must be numeric.")),
     *                 @OA\Property(property="price", type="array", @OA\Items(type="string", example="The price must be numeric.")),
     *                 @OA\Property(property="product_variation_id", type="array", @OA\Items(type="string", example="The product_variation_id field is required.")),
     *                 @OA\Property(property="art_files", type="array", @OA\Items(type="string", example="The art_files field is required."))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to add the product to the cart.")
     *         )
     *     )
     * )
     */



    public function addCart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'product_id' => 'required',
            'qty' => 'required|numeric',
            'price' => 'required|numeric',
            'product_variation_id' => 'required',
            'art_files' => 'required|array', // Ensure art_files is an array
            'art_files.*' => 'required|distinct',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        if ($request->user_id != Auth::user()->id) {
            return $this->sendError('Invalid User Id.', [], 422);
        }

        DB::beginTransaction();

        try {
            $merchMake = new MerchMake();
            $merchmake_product = $merchMake->getSingleProduct($request->product_id);
            $color =  $size = $price =  null;

            if ($merchmake_product) {
                foreach ($merchmake_product['variations'] as $variation) {
                    if ($variation['id'] == $request->product_variation_id) {
                        $color = $variation['color_name'];
                        $size = $variation['size_name'];
                        $price = $variation['price'];
                        break;
                    }
                }

                if (!$color || !$size || !$price) {
                    return $this->sendError('error.', 'Product variation not found.');
                }

                // Validate art files before creating the cart
                if ($request->art_files) {
                    // Get the merchmake product art file positions
                    $merchmake_product_art_files = [];
                    if (isset($merchmake_product['art_files'])) {
                        foreach ($merchmake_product['art_files'] as $artFile) {
                            $merchmake_product_art_files[] = $artFile['name'];
                        }
                    }

                    // Check each art file before creating the cart
                    foreach ($request['art_files'] as $key => $qrcode_id) {
                        if (empty($key) || empty($qrcode_id) || !in_array($key, $merchmake_product_art_files)) {
                            return $this->sendError('error.', 'Invalid art file position.');
                        }
                        if (empty(Qrcodes::where('id', $qrcode_id)->first())) {
                            return $this->sendError('error.', 'Invalid QR code.');
                        }
                    }
                }

                // Now that all validations are passed, create the cart
                $cart = Cart::create([
                    'user_id' => Auth::user()->id,
                    'product_id' => $request->product_id,
                    'qty' => $request->qty,
                    'price' => $price,
                    'total' => $price * $request->qty,
                    'product_variation_id' => $request->product_variation_id,
                    'product_title' => $merchmake_product['title'],
                    'variation_color' => $color,
                    'variation_size' => $size,
                ]);

                // Add art files to cart if present
                foreach ($request['art_files'] as $key => $qrcode_id) {
                    $cart->cartItmesQrCodes()->create([
                        'qrcode_id' => $qrcode_id,
                        'position' => $key
                    ]);
                }
            } else {
                return $this->sendError('error.', 'Product not found in merchmake.');
            }

            DB::commit();
            return $this->sendResponse(CartResource::collection([$cart]), 'Product added to cart successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in adding the product into cart: ' . $e->getMessage());
            return $this->sendError('error.', 'Failed to add the product into cart.');
        }
    }



    /**
     * @OA\Get(
     *     path="/api/view-cart",
     *     operationId="viewCart",
     *     tags={"Cart"},
     *     summary="View the user's cart (Require token)",
     *     security={{"X-Access-Token": {}}},
     *     description="Fetches the cart items for the authenticated user, including product images, and the total of the cart.",
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             type="object",
     *             description="This endpoint does not require a body, it's for viewing the user's cart"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cart retrieved successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="car_items", type="array", @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="product_id", type="integer", example=123),
     *                 @OA\Property(property="product_variation_id", type="integer", example=456),
     *                 @OA\Property(property="images", type="array", @OA\Items(
     *                     type="string", example="https://example.com/product-image.jpg"
     *                 )),
     *                 @OA\Property(property="qty", type="integer", example=2),
     *                 @OA\Property(property="total", type="number", format="float", example=29.99)
     *             )),
     *             @OA\Property(property="cart_sub_total", type="number", format="float", example=59.98)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request. The request is invalid.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Bad Request."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized. User must be authenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not authenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found. Resource could not be found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Resource not found.")
     *         )
     *     ),
     *
     * )
     */



    public function viewCart(Request $request)
    {
        if (Auth::check()) {
            $cart_items = Cart::where('user_id', Auth::id())->get();
          
            if ($cart_items->count() > 0) {
                // $cart_total = Cart::where('user_id', Auth::id())->sum('total');
                $cart_total = Cart::where('user_id', Auth::id())
                            ->selectRaw('SUM(total::numeric) as cart_total')
                            ->value('cart_total');
                return $this->sendResponse(['car_items' => CartResource::collection($cart_items), 'cart_sub_total' => $cart_total], 'Cart retrieved successfully!');
            } else {
                return $this->sendResponse([], 'Cart is empty.');
            }
        } else {
            return $this->sendError('error.', 'User not authenticated.');
        }
    }


    /**
     * @OA\Delete(
     *     path="/api/remove-cart-item/{id}",
     *     operationId="removeCartItem",
     *     tags={"Cart"},
     *     summary="Remove an item from the user's cart (Require token)",
     *     security={{"X-Access-Token": {}}},
     *     description="Removes a cart item for the authenticated user by its cart ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the cart item to be removed",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cart item removed successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Cart item removed successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request. The request is invalid or malformed.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Bad Request.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized. User must be authenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not authenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found. Cart item could not be found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cart item not found.")
     *         )
     *     ),
     *
     * )
     */

    public function removeCartItem(Request $request, $id)
    {
        $cart_item = Cart::where('user_id', Auth::id())->find($id);

        if (!$cart_item) {
            return $this->sendError('Cart item not found.', 'The cart item you are trying to remove does not exist or does not belong to the authenticated user.');
        }

        try {
            $cart_item->delete();
            return $this->sendResponse([], 'Cart item removed successfully!');
        } catch (\Exception $e) {
            return $this->sendError('Error occurred while removing cart item.', $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/update-cart",
     *     operationId="updateCart",
     *     tags={"Cart"},
     *     summary="Update the quantity of items in the user's cart (Require token)",
     *     security={{"X-Access-Token": {}}},
     *     description="Updates the quantity of cart items for the authenticated user, recalculates the total for each item, and saves the updated cart.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"cart_items"},
     *             @OA\Property(property="cart_items", type="array", @OA\Items(
     *                 type="object",
     *                 required={"cart_id", "qty"},
     *                 @OA\Property(property="cart_id", type="integer", example=1),
     *                 @OA\Property(property="qty", type="integer", example=3)
     *             )),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cart updated successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Cart updated successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request. No cart items provided or the request is invalid.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Bad Request."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized. User must be authenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not authenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found. Cart item not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cart item not found.")
     *         )
     *     ),
     * )
     */


    public function updateCart(Request $request)
    {
        if (isset($request->cart_items) && !empty($request->cart_items)) {
            DB::beginTransaction();

            try {
                foreach ($request->cart_items as $cart_item) {
                    $cart = Cart::where('id', $cart_item['cart_id'])
                        ->where('user_id', Auth::id())
                        ->first();

                    if ($cart) {
                        $cart->qty = $cart_item['qty'];
                        $cart->total = $cart_item['qty'] * $cart->price;
                        $cart->save();
                    }
                }

                $cart = Cart::where('user_id', Auth::id())->get();

                DB::commit();

                return $this->sendResponse(CartResource::collection($cart), 'Cart updated successfully!');
            } catch (\Exception $e) {
                DB::rollBack();

                return $this->sendError('Error updating cart.', $e->getMessage());
            }
        } else {
            return $this->sendError('Error.', 'No cart items provided.');
        }
    }
}
