<?php

namespace App\Http\Controllers\API;

use App\Models\Subscribe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\BaseController;

class SubscribeController extends BaseController
{

    /**
     * @OA\Post(
     *     path="/api/subscribe",
     *     operationId="",
     *     tags={"subscribe"},
     *     summary="Subscribe with email",
     *     description="Subscribes a user by saving their email in the subscribers list.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Subscription successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="email", type="string", example="user@example.com"),
     *                 @OA\Property(property="created_at", type="string", example="2025-03-28T12:34:56.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-03-28T12:34:56.000000Z")
     *             ),
     *             @OA\Property(property="message", type="string", example="Subscribed successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation Error."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="email", type="array",
     *                     @OA\Items(type="string", example="The email has already been taken.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to subscribe. Please try again.")
     *         )
     *     )
     * )
     */

    public function subscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|unique:subscribes,email',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        DB::beginTransaction();

        try {

            $subscribe = Subscribe::create($request->all());
            DB::commit();

            return $this->sendResponse($subscribe, 'Subscribed successfully!');
        } catch (\Exception $e) {
            // dd($e->getMessage());
            DB::rollBack();
            Log::error('Error while subscribing: ' . $e->getMessage());
            return $this->sendError('error.', 'Failed to subscribe. Please try again.');
        }
    }
}
