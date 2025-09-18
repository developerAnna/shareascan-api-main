<?php

namespace App\Http\Controllers\API;

use App\Models\Cart;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\API\BaseController;

class CheckoutController extends BaseController
{

    /**
     * @OA\Get(
     *     path="/api/checkout",
     *     operationId="checkout",
     *     summary="Process the checkout",
     *     tags={"Checkout"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="cart_id", type="integer", example=1),
     *             @OA\Property(property="payment_method", type="string", example="credit_card")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Checkout successful",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Checkout completed successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     ),
     *      security={{"X-Access-Token": {}}}
     * )
     */


    public function checkout(Request $request)
    {
        try {
            $items = Cart::where('user_id', Auth::user()->id)->get();

            if ($items->count() > 0) {
                $total_amount = Cart::where('user_id', Auth::user()->id)->sum('total');

                return $this->sendResponse(
                    ['products' => $items, 'total' => $total_amount],
                    'Checkout data fetched successfully.'
                );
            } else {
                return $this->sendError('Cart is empty.');
            }
        } catch (\Exception $e) {
            return $this->sendError('Error during checkout.', $e->getMessage());
        }
    }
}
