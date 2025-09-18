<?php

namespace App\Http\Controllers\API;

use App\Models\Wishlist;
use Faker\Provider\Base;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\WishlistResource;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\BaseController;

class WishlistController extends BaseController
{

    /**
     * @OA\Get(
     *     path="/api/wishlist",
     *     operationId="getWishlistData",
     *     tags={"Wishlist"},
     *     summary="Fetch user wishlist data",
     *     security={{"X-Access-Token": {}}},
     *     description="Retrieve wishlist data for a user based on their authentication.",
     *     @OA\Response(
     *         response=200,
     *         description="Wishlist data retrieved successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="wishlists", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=101),
     *                     @OA\Property(property="name", type="string", example="Smartphone"),
     *                     @OA\Property(property="description", type="string", example="A high-end smartphone.")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Wishlist data retrieved successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="User is not authenticated or access token is missing.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User id is not provided.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Wishlist is empty.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Wishlist is empty.")
     *         )
     *     ),
     * )
     */

    public function getWishlistData(Request $request)
    {
        // try {
        //     $user = Auth::user();

        //     if (!empty($user)) {
        //         $product_ids = Wishlist::where('user_id', $user->id)->pluck('product_id')->toArray();

        //         $wishlists = getWishListProducts($product_ids);
        //         // dd($wishlists);




        //         // Step 4: Map by product ID
        //         $productMap = collect($wishlists)->keyBy('id');

        //         // Step 5: Transform using resource
        //         $wishlistWithDetails = $wishlistItems->map(function ($item) use ($productMap) {
        //             $details = $productMap->get($item->product_id, []);
        //             return new WishlistResource($item, $details);
        //         });

        //         return $this->sendResponse($wishlistWithDetails, 'Wishlist data retrieved successfully!');

        try {
            $user = Auth::user();

            if (empty($user)) {
                return $this->sendError('User is not authenticated.');
            }

            $wishlistItems = Wishlist::where('user_id', $user->id)->get();

            if ($wishlistItems->isEmpty()) {
                return $this->sendError('Wishlist is empty.');
            }

            $product_ids = $wishlistItems->pluck('product_id')->toArray();

            $wishlists = getWishListProducts($product_ids);

            $productMap = collect($wishlists)->keyBy('id');

            $wishlistWithDetails = $wishlistItems->map(function ($item) use ($productMap) {
                $details = $productMap->get($item->product_id, []);
                return new WishlistResource($item, $details);
            });

                return $this->sendResponse($wishlistWithDetails, 'Wishlist data retrieved successfully!');

                // if ($wishlists) {
                //     return $this->sendResponse($wishlists, 'Wishlist data retrieved successfully!');
                // } else {
                //     return $this->sendError('Wishlist is empty.');
                // }
            // } else {
            //     return $this->sendError('User id is not provided.');
            // }
        } catch (\Exception $e) {
            return $this->sendError('Error retrieving wishlist data.', $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/add-to-wishlist",
     *     operationId="addToWishlist",
     *     tags={"Wishlist"},
     *     summary="Add a product to the wishlist",
     *     security={{"X-Access-Token": {}}},
     *     description="Adds a product to the user's wishlist. If the product is already in the wishlist, it will return an error message.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "product_id"},
     *             @OA\Property(property="user_id", type="integer", example=1, description="ID of the user"),
     *             @OA\Property(property="product_id", type="integer", example=101, description="ID of the product to add to the wishlist")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product added to wishlist successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="wishlist", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="product_id", type="integer", example=101)
     *             ),
     *             @OA\Property(property="message", type="string", example="Product added to wishlist successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Product already in wishlist.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Already added to wishlist.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation Error."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="user_id", type="array", @OA\Items(type="string", example="The user id field is required.")),
     *                 @OA\Property(property="product_id", type="array", @OA\Items(type="string", example="The product id field is required."))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to add the product into wishlist.")
     *         )
     *     ),
     * )
     */

    public function addToWishlist(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'product_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        DB::beginTransaction();

        try {
            $product_exits = Wishlist::where('user_id', Auth::user()->id)->where('product_id', $request->product_id)->first();
            if (empty($product_exits)) {
                $wishlist = Wishlist::create(['user_id' => Auth::user()->id, 'product_id' => $request->product_id]);
            } else {
                return $this->sendError('Already added to wishlist.');
            }
            DB::commit();
            return $this->sendResponse($wishlist, 'Product added to wishlist successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in adding the product into wishlist: ' . $e->getMessage());
            return $this->sendError('error.', 'Failed to add the product into wishlist.');
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/remove-wishlist-product/{id}",
     *     operationId="removeWishlistProduct",
     *     tags={"Wishlist"},
     *     summary="Remove a product from the user's wishlist",
     *     security={{"X-Access-Token": {}}},
     *     description="Remove a product from the user's wishlist by providing the wishlist ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1),
     *         description="ID of the wishlist to be removed from the wishlist."
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product removed from the wishlist successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product removed from the wishlist successfully!"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid wishlist ID or user is not authorized to remove the product.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid wishlist id."),
     *             @OA\Property(property="error", type="string", example="error.")
     *         )
     *     ),
     * )
     */



    public function removeWishlistProduct(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $wishlist = Wishlist::where('id', $id)->where('user_id', Auth::user()->id)->first();

            if ($wishlist) {
                $wishlist->delete();

                DB::commit();

                return $this->sendResponse([], 'Product removed from the wishlist successfully!');
            } else {
                DB::rollBack();

                return $this->sendError('Error.', 'Invalid wishlist id.');
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->sendError('Error removing product from wishlist.', $e->getMessage());
        }
    }
}
