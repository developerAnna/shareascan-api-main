<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\FAQ;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController;


class FAQController extends BaseController
{

    /**
     * @OA\Get(
     *     path="/api/faq",
     *     operationId="getFAQ",
     *     tags={"FAQ"},
     *     summary="Get FAQ details",
     *     description="Retrieve a list of FAQs that are active (status=1).",
     *     @OA\Response(
     *         response=200,
     *         description="FAQ details fetched successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="question", type="string", example="What is your return policy?"),
     *                     @OA\Property(property="answer", type="string", example="Our return policy allows returns within 30 days."),
     *                     @OA\Property(property="status", type="integer", example=1)
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="FAQ details fetched successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="FAQ not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="FAQ not found.")
     *         )
     *     ),
     * )
     */

    public function getFAQ(Request $request)
    {
        try {
            $faq = FAQ::where('status', 1)->get();

            if ($faq->count() > 0) {
                return $this->sendResponse($faq, 'FAQ details fetched successfully.');
            } else {
                return $this->sendResponse([], 'No FAQ available.');
            }
        } catch (\Exception $e) {
            return $this->sendError('Error fetching FAQ.', $e->getMessage());
        }
    }
}
