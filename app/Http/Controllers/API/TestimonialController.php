<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\API\BaseController;
use App\Models\Testimonial;

class TestimonialController extends BaseController
{

    /**
     * @OA\Get(
     *     path="/api/testimonials",
     *     operationId="getTestimonials",
     *     tags={"Testimonials"},
     *     summary="Fetch all active testimonials",
     *     description="Fetches a list of all active testimonials ordered by ID in descending order.",
     *     @OA\Response(
     *         response=200,
     *         description="Testimonials fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="testimonial", type="string", example="This is a great product."),
     *                     @OA\Property(property="status", type="integer", example=1),
     *                     @OA\Property(property="created_at", type="string", example="2025-03-28T12:34:56.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", example="2025-03-28T12:34:56.000000Z")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Testimonials fetched successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Testimonials not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Testimonials not found.")
     *         )
     *     )
     * )
     */

    public function getTestimonials(Request $request)
    {
        try {
            $testimonials = Testimonial::where('status', 1)->orderBy('id', 'desc')->get();

            if ($testimonials->count() > 0) {
                return $this->sendResponse($testimonials, 'Testimonials fetched successfully.');
            } else {
                return $this->sendError('Error.', 'Testimonials not found.');
            }
        } catch (\Exception $e) {
            return $this->sendError('Error fetching testimonials.', $e->getMessage());
        }
    }
}
