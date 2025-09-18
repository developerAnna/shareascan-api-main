<?php

namespace App\Http\Controllers\API;

use App\Models\Review;
use Illuminate\Support\Str;
use App\Models\ReviewImages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ReviewResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\BaseController;

class ReviewController extends BaseController
{
    /**
     * @OA\Post(
     *     path="/api/review",
     *     summary="Add a review for a product",
     *     security={{"X-Access-Token": {}}},
     *     description="Allows users to add a review for a product, including rating, content, and images.",
     *     operationId="addReview",
     *     tags={"Reviews"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"product_id", "user_id", "rating", "content"},
     *                 @OA\Property(
     *                     property="product_id",
     *                     type="integer",
     *                     description="ID of the product being reviewed",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="user_id",
     *                     type="integer",
     *                     description="ID of the user leaving the review",
     *                     example=5
     *                 ),
     *                 @OA\Property(
     *                     property="rating",
     *                     type="integer",
     *                     description="Rating for the product (1-5)",
     *                     example=4
     *                 ),
     *                 @OA\Property(
     *                     property="content",
     *                     type="string",
     *                     description="Content of the review",
     *                     example="This product is amazing!"
     *                 ),
     *                 @OA\Property(
     *                     property="image",
     *                     type="array",
     *                     description="List of images (Base64 encoded strings or file uploads)",
     *                     @OA\Items(
     *                         type="string",
     *                         description="Base64 encoded image data or file",
     *                         example="data:image/jpeg;base64,/9j/4AAQSkZJRgABA..."
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Review added successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Review added successfully!"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example=18
     *                     ),
     *                     @OA\Property(
     *                         property="product_id",
     *                         type="integer",
     *                         example=1
     *                     ),
     *                     @OA\Property(
     *                         property="user_id",
     *                         type="integer",
     *                         example=5
     *                     ),
     *                     @OA\Property(
     *                         property="ratings",
     *                         type="integer",
     *                         example=4
     *                     ),
     *                     @OA\Property(
     *                         property="content",
     *                         type="string",
     *                         example="This product is amazing!"
     *                     ),
     *                     @OA\Property(
     *                         property="status",
     *                         type="integer",
     *                         example=0
     *                     ),
     *                     @OA\Property(
     *                         property="created_at",
     *                         type="string",
     *                         format="date-time",
     *                         example="2025-03-11T12:19:17.000000Z"
     *                     ),
     *                     @OA\Property(
     *                         property="updated_at",
     *                         type="string",
     *                         format="date-time",
     *                         example="2025-03-11T12:19:17.000000Z"
     *                     ),
     *                     @OA\Property(
     *                         property="images",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(
     *                                 property="filename",
     *                                 type="string",
     *                                 example="1741695557-BsLkvkN6kr.jpg"
     *                             ),
     *                             @OA\Property(
     *                                 property="file_path",
     *                                 type="string",
     *                                 example="http://example.com/storage/reviews/1741695557-BsLkvkN6kr.jpg"
     *                             ),

     *                         ),
     *                         description="List of images associated with the review"
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request - Validation failed",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="error"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Validation Error."
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="product_id",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string",
     *                         example="The product_id field is required."
     *                     )
     *                 )
     *             ),
     *             example={
     *                 "product_id": {"The product_id field is required."}
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="error"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Product not found."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Conflict - User has already reviewed this product",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="error"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="You have already reviewed this product."
     *             )
     *         )
     *     )
     * )
     */



    public function addReview(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'user_id' => 'required',
            'rating' => 'required|integer|between:1,5',
            'content' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        if ($request->user_id != Auth::user()->id) {
            return $this->sendError('Invalid User Id.', [], 422);
        }

        DB::beginTransaction();

        try {

            $checkProductExitsRes = checkProductExits($request->product_id);
            if (!$checkProductExitsRes['productExists']) {
                return $this->sendError('Product not found.');
            }

            $checkuserreview = Review::where('user_id', Auth::user()->id)->where('product_id', $request->product_id)->first();
            if (!empty($checkuserreview)) {
                return $this->sendError('You have already reviewed this product.');
            }

            $review = Review::create([
                'product_id' => $request->product_id,
                'user_id' => Auth::user()->id,
                'star_count' => $request->rating,
                'content' => $request->content,
                'status' => 0,
                'product_title' => $checkProductExitsRes['product_title']
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
                        $filePath = 'reviews/' . $fileName;

                        Storage::disk('public')->put($filePath, $imageData);

                        $imageUrls[] = asset('storage/' . $filePath);

                        ReviewImages::create([
                            'review_id' => $review->id,
                            'file_name' => $fileName,
                            'file_path' => $filePath,
                            'file_url' => asset('storage/' . $filePath),
                        ]);
                    } else {
                        if (isset($request->file('image')[$key])) {
                            $file = $request->file('image')[$key];
                            $originalFileName = $file->getClientOriginalName();
                            $extension = $file->getClientOriginalExtension();

                            $fileName = pathinfo($originalFileName, PATHINFO_FILENAME) . '_' . time() . '.' . $extension;
                            $path = $file->storeAs('reviews', $fileName, 'public');

                            $imageUrls[] = asset('storage/' . $path);

                            ReviewImages::create([
                                'review_id' => $review->id,
                                'filename' => $fileName,
                                'file_path' => $path,
                                'file_url' => asset('storage/' . $path),
                            ]);
                        } else {
                            $img_data = $request['image'][$key];

                            $decoded_image = base64_decode($img_data);

                            if ($decoded_image === false) {
                                return response()->json(['status' => 'error', 'message' => 'Invalid Base64 image data']);
                            }

                            $fileName = time() . '-' . Str::random(10) . '.' . 'jpg';
                            $filePath = 'reviews/' . $fileName;

                            Storage::disk('public')->put($filePath, $decoded_image);

                            $imageUrls[] = asset('storage/' . $filePath);
                            ReviewImages::create([
                                'review_id' => $review->id,
                                'filename' => $fileName,
                                'file_path' => $filePath,
                                'file_url' => asset('storage/' . $filePath),
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return $this->sendResponse(ReviewResource::collection([$review]), 'Review added successfully!');
        } catch (\Exception $e) {
            // dd($e->getMessage());
            DB::rollBack();
            Log::error('Error while adding review: ' . $e->getMessage());
            return $this->sendError('error.', 'Failed to add the review. Please try again.');
        }
    }

    /**
     * @OA\Get(
     *     path="/api/get-reviews/{id}",
     *     summary="Get reviews for a product",
     *     description="Fetch all approved reviews for a given product.",
     *     operationId="getReviews",
     *     tags={"Reviews"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the product to fetch reviews for",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reviews fetched successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Reviews fetched successfully."
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example=18
     *                     ),
     *                     @OA\Property(
     *                         property="product_id",
     *                         type="integer",
     *                         example=1
     *                     ),
     *                     @OA\Property(
     *                         property="user_id",
     *                         type="integer",
     *                         example=5
     *                     ),
     *                     @OA\Property(
     *                         property="ratings",
     *                         type="integer",
     *                         example=4
     *                     ),
     *                     @OA\Property(
     *                         property="content",
     *                         type="string",
     *                         example="This product is amazing!"
     *                     ),
     *                      @OA\Property(
     *                         property="user_name",
     *                         type="string",
     *                         example="John Smith"
     *                     ),
     *                     @OA\Property(
     *                         property="status",
     *                         type="integer",
     *                         example=1
     *                     ),
     *                     @OA\Property(
     *                         property="created_at",
     *                         type="string",
     *                         format="date-time",
     *                         example="2025-03-11T12:19:17.000000Z"
     *                     ),
     *                     @OA\Property(
     *                         property="updated_at",
     *                         type="string",
     *                         format="date-time",
     *                         example="2025-03-11T12:19:17.000000Z"
     *                     ),
     *                     @OA\Property(
     *                         property="images",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(
     *                                 property="filename",
     *                                 type="string",
     *                                 example="1741695557-BsLkvkN6kr.jpg"
     *                             ),
     *                             @OA\Property(
     *                                 property="file_path",
     *                                 type="string",
     *                                 example="http://example.com/storage/reviews/1741695557-BsLkvkN6kr.jpg"
     *                             ),
     *                         ),
     *                         description="List of images associated with the review"
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Reviews not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="error"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Reviews not found."
     *             )
     *         )
     *     )
     * )
     */

    public function getReviews(Request $request, $id)
    {

        try {
            $reviews = Review::where('product_id', $id)->where('status', 1)->get();

            if ($reviews && $reviews->count() > 0) {
                return $this->sendResponse(ReviewResource::collection($reviews), 'Reviews fetched successfully.');
            } else {
                return $this->sendError('Error.', 'Reviews not found.');
            }
        } catch (\Exception $e) {
            return $this->sendError('Error fetching reviews.', $e->getMessage());
        }
    }
}
