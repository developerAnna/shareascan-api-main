<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\API\BaseController;
use App\Http\Resources\ShopPageCategoryResource;

class ShopPageController extends BaseController
{

    /**
     * @OA\Get(
     *     path="/api/shop-page-data",
     *     operationId="getShopPageData",
     *     tags={"Shop Page"},
     *     summary="Get shop page data with products and categories",
     *     description="Retrieve shop page data including products based on various filters like category, color, size, sorting, and search.(it will return category list, filtered products and those products color and size and if no any params has set it will return default new arrivals data)",
     *     @OA\Parameter(
     *         name="search_product_title",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", example="Shirt"),
     *         description="Search term for filtering products by title."
     *     ),
     *     @OA\Parameter(
     *         name="category_type",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", example="new_arrival"),
     *         description="Filter products by category type (e.g., 'new_arrival', 'bestseller', or category ID)."
     *     ),
     *     @OA\Parameter(
     *         name="color",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", example="Red"),
     *         description="Filter products by selected color."
     *     ),
     *     @OA\Parameter(
     *         name="size",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", example="M"),
     *         description="Filter products by selected size."
     *     ),
     *     @OA\Parameter(
     *         name="sorting",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", example="a_to_z"),
     *         description="Sort products by the specified sorting method (e.g., 'a_to_z', 'z_to_a')."
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Shop page data fetched successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="categories", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Electronics")
     *                 )
     *             ),
     *             @OA\Property(property="products", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=101),
     *                     @OA\Property(property="name", type="string", example="Smartphone"),
     *                     @OA\Property(property="price", type="number", example=199.99),
     *                     @OA\Property(property="color", type="string", example="Red"),
     *                     @OA\Property(property="size", type="string", example="M")
     *                 )
     *             ),
     *             @OA\Property(property="colorArr", type="array",
     *                 @OA\Items(type="string", example="Red")
     *             ),
     *             @OA\Property(property="sizeArr", type="array",
     *                 @OA\Items(type="string", example="M")
     *             ),
     *             @OA\Property(property="message", type="string", example="Data Fetched Successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No products found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No Products")
     *         )
     *     ),
     * )
     */


    public function getShopPageData(Request $request)
    {
        $categories = getShopPageCategories();

        $get_products = null;
        $selected_category = '';

        if ($request->search_product_title && !empty($request->search_product_title)) {
            $searchTerm = $request->search_product_title;
            $get_products = searchProduct($searchTerm);
        } else if ($request->category_type && !empty($request->category_type)) {

            $category = trim($request->category_type);
            $selected_category = $category;
            if ($category == "new_arrival") {
                $get_products = getNewArrivalProducts();
            } elseif ($category == "bestseller") {
                $get_products = getBestSellerProducts();
            } else {
                $get_products = getProductsFromCategoryId($category);
            }
        } else {
            $get_products = getNewArrivalProducts();
            $selected_category = 'new_arrival';
        }

        if ($request->color) {
            $get_products = getProductWithSelectedColor($get_products, $request->color);
        }

        if ($request->size) {
            $get_products = getProductWithSelectedSize($get_products, $request->size);
        }

        if ($request->sorting && !empty($request->sorting)) {
            $get_products = sortProducts($get_products, $request->sorting);
        }

        $product_variations = getVariations($get_products);

        $get_products = paginateArray($get_products, 16);

        if (!empty($get_products)) {
            return $this->sendResponse([
                'categories' => ShopPageCategoryResource::collection($categories),
                'selected_category' => $selected_category,
                'products' => $get_products,
                'colorArr' => $product_variations['color_array'] ?? [],
                'sizeArr' => $product_variations['size_array'] ?? [],
            ], 'Data Fetched Successfully!');
        } else {
            return $this->sendError('No Products');
        }
    }

    /**
     * @OA\Get(
     *     path="/api/product-price/{id}",
     *     operationId="getShopPageProductPrice",
     *     tags={"Shop Page"},
     *     summary="Get product price range by product ID",
     *     description="Retrieve the highest and lowest price of a product based on its ID, considering all variations.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1),
     *         description="The ID of the product to retrieve the price range for."
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product price range retrieved successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="max_price", type="number", format="float", example=99.99),
     *             @OA\Property(property="min_price", type="number", format="float", example=29.99),
     *             @OA\Property(property="message", type="string", example="Product price retrieved successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product price not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product price not found.")
     *         )
     *     ),
     * )
     */

    public function getShopPageProductPrice(Request $request, $id)
    {
        try {
            $product_price = getProductPrice($id);

            if (!empty($product_price['max_price']) && !empty($product_price['min_price'])) {
                return $this->sendResponse($product_price, 'Product price retrieved successfully!');
            } else {
                return $this->sendError('Product price or product not found.');
            }
        } catch (\Exception $e) {
            return $this->sendError('Error retrieving product price.', $e->getMessage());
        }
    }
}
