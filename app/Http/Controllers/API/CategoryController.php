<?php

namespace App\Http\Controllers\API;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\API\BaseController;
use App\Http\Resources\HomePageCategoryResource;


class CategoryController extends BaseController
{

    /**
     * @OA\Get(
     *     path="/api/home-page-categories",
     *     operationId="getHomePageCategories",
     *     tags={"Category"},
     *     summary="Fetch homepage categories",
     *     description="Retrieve a list of all categories for the homepage.",
     *     @OA\Response(
     *         response=200,
     *         description="Category details fetched successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="categories", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Electronics"),
     *                     @OA\Property(property="description", type="string", example="Latest electronic gadgets."),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2022-01-01T12:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2022-01-01T12:00:00Z")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Category details fetched successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No categories found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Category not found.")
     *         )
     *     ),
     * )
     */

    public function getHomePageCategories(Request $request)
    {
        try {
            $categories = Category::orderBy('id', 'desc')->get();

            if ($categories && $categories->count() > 0) {

                return $this->sendResponse(HomePageCategoryResource::collection($categories), 'Category details fetched successfully.');
            } else {
                return $this->sendResponse([], 'No categories available at the moment.');
            }
        } catch (\Exception $e) {
            return $this->sendError('Error fetching categories.', $e->getMessage());
        }
    }
}
