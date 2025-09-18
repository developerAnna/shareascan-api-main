<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Cart;
use App\Models\Order;
use App\Mail\GeneralMail;
use App\Models\OrderItems;
use App\Services\MerchMake;
use Illuminate\Support\Str;
use App\Utilities\Overrider;
use Illuminate\Http\Request;
use App\Models\EmailTemplate;
use App\Models\ProductSetting;
use App\Models\RecentlyViewed;
use App\Models\CartItemQrCodes;
use App\Models\ShippingAddress;
use App\Models\OrderItemQrCodes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use App\Http\Resources\OrderResource;
use App\Http\Resources\HotItemsResource;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\BaseController;
use App\Http\Resources\RecentlyViewedResource;

class OrderController extends BaseController
{

    /**
     * @OA\Post(
     *     path="/api/create-order",
     *     tags={"Order"},
     *     summary="Create a new order",
     *     description="Create a new order based on cart items and customer shipping details.",
     *     operationId="createOrder",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="first_name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="phone_number", type="string", example="+12345678901"),
     *             @OA\Property(property="address_line_1", type="string", example="123 Main St"),
     *             @OA\Property(property="address_line_2", type="string", example="Apt 4B"),
     *             @OA\Property(property="country_code", type="string", example="US"),
     *             @OA\Property(property="state", type="string", example="CA"),
     *             @OA\Property(property="city", type="string", example="Los Angeles"),
     *             @OA\Property(property="zipcode", type="string", example="90001"),
     *             @OA\Property(property="note", type="string", example="Leave at the front door.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order placed successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Order placed successfully!"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=123),
     *                 @OA\Property(property="order_total", type="number", format="float", example=150.75),
     *                 @OA\Property(
     *                     property="order_items",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="product_id", type="integer", example=1),
     *                         @OA\Property(property="product_title", type="string", example="Product Name"),
     *                         @OA\Property(property="product_variation_id", type="integer", example=1001),
     *                         @OA\Property(property="variation_color", type="string", example="Red"),
     *                         @OA\Property(property="variation_size", type="string", example="M"),
     *                         @OA\Property(property="qty", type="integer", example=2),
     *                         @OA\Property(property="price", type="number", format="float", example=75.00)
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="shipping_address",
     *                     type="object",
     *                     @OA\Property(property="address_line_1", type="string", example="123 Main St"),
     *                     @OA\Property(property="address_line_2", type="string", example="Apt 4B"),
     *                     @OA\Property(property="country_code", type="string", example="US"),
     *                     @OA\Property(property="state", type="string", example="CA"),
     *                     @OA\Property(property="city", type="string", example="Los Angeles"),
     *                     @OA\Property(property="zipcode", type="string", example="90001")
     *                 ),
     *                 @OA\Property(property="note", type="string", example="Leave at the front door."),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-20T10:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-20T10:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation Error."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 example={"email": {"The email field is required."}}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to create an order.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Failed to create an order.")
     *         )
     *     ),
     *     security={{"X-Access-Token": {}}}
     * )
     */


    public function createOrder(Request $request)
    {
        ini_set('max_execution_time', '900');

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'first_name' => 'required',
            'last_name' => 'required',
            'phone_number' => [
                'required',
                'regex:/^\+?\d{10,11}$/',
            ],
            'address_line_1' => 'required',
            'country_code' => [
                'required',
                'in:US',
            ],
            'state' => 'required',
            'city' => 'required',
            'zipcode' => [
                'required',
                'regex:/^\+?\d{5,6}$/',
            ],
        ], [
            'country_code.required' => 'The country code is required and should be "US".',  // Custom message for required rule
            'country_code.in' => 'The country code must be "US".' // Custom message for the "in" rule
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        DB::beginTransaction();

        try {
            $cart_items = Cart::where('user_id', Auth::user()->id)->get();
            $cart_ids = Cart::where('user_id', Auth::user()->id)->pluck('id')->toArray();

            if ($cart_items->count() > 0) {

                $check_order = Order::where('user_id', Auth::user()->id)
                    ->where('status', 0)
                    ->whereNull('payment_method')
                    ->whereNull('payment_status')
                    ->first();

                if ($check_order) {
                    $check_order_items = OrderItems::whereIn('cart_id', $cart_ids)
                        ->where('order_id', $check_order->id)
                        ->get();

                    if ($check_order_items->count() > 0) {
                        foreach ($check_order_items as $check_order_item) {
                            $check_qr_code = OrderItemQrCodes::where('order_item_id', $check_order_item->id)->first();

                            if ($check_qr_code) {
                                $path = storage_path('app/public/' . $check_qr_code->qr_image_path);

                                if (file_exists($path)) {
                                    File::delete($path);
                                }

                                $check_qr_code->delete();
                            }
                        }

                        $check_order_items->each(function ($item) {
                            $item->delete();
                        });
                    }

                    $check_order->delete();
                }


                $order = new Order();
                $order->user_id = Auth::user()->id;
                $order->note = $request->note;
                $order->total = $cart_items->sum('total');
                // $order->sub_total = $cart_items->sum('total');
                $order->order_status = 'Pending';
                $order->status = 0;
                $order->save();

                $shipping_address = new ShippingAddress;
                $shipping_address->order_id = $order->id;
                $shipping_address->address_type = 1;
                $shipping_address->first_name = $request->first_name;
                $shipping_address->last_name = $request->last_name;
                $shipping_address->address_1 = $request->address_line_1;
                $shipping_address->address_2 = $request->address_line_2;
                $shipping_address->city = $request->city;
                $shipping_address->state = $request->state;
                $shipping_address->phone = $request->phone_number;
                $shipping_address->country_code = $request->country_code;
                $shipping_address->zip = $request->zipcode;
                $shipping_address->email = $request->email;
                $shipping_address->save();

                foreach ($cart_items as $key => $cart_item) {

                    $dummyText = "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.";

                    // Loop through quantity to create separate OrderItems for each unit
                    for ($i = 0; $i < $cart_item->qty; $i++) {
                        $order_uid = generateUniqueCode(10); // Generate a new 10-digit unique code for each item

                        $order_item = new OrderItems();
                        $order_item->order_id = $order->id;
                        $order_item->product_id = $cart_item->product_id;
                        $order_item->cart_id = $cart_item->id;
                        $order_item->uuid = $order_uid;
                        $order_item->product_title = $cart_item->product_title;
                        $order_item->product_variation_id = $cart_item->product_variation_id;
                        $order_item->qty = 1; // Set qty to 1 for each individual item entry
                        $order_item->price = $cart_item->price;
                        $order_item->total = $cart_item->price; // Assuming total is based on unit price
                        $order_item->variation_color = $cart_item->variation_color;
                        $order_item->variation_size = $cart_item->variation_size;
                        $order_item->save();

                        // Check if the item has QR codes and generate a unique QR code for each item
                        if (isset($cart_item->cartItmesQrCodes)) {
                            foreach ($cart_item->cartItmesQrCodes as $cartItemQrCode) {
                                $rgbValue = json_decode($cartItemQrCode->qrcode->rgb_color);

                                $generateURL = env('FRONT_URL') . "/content/" . $cart_item->user_id . "/" . $cart_item->product_id . "/" .  $order_item->id;
                                $generate_qr = generateQR($cartItemQrCode->qrcode->hexa_color, $rgbValue, $generateURL, $source = 'user');

                                if (!empty($generate_qr['filename']) && !empty($generate_qr['filepath'])) {
                                    $order_qr_code = new OrderItemQrCodes();
                                    $order_qr_code->order_item_id = $order_item->id;
                                    $order_qr_code->position = $cartItemQrCode->position;
                                    $order_qr_code->hex_color = $cartItemQrCode->qrcode->hexa_color;
                                    $order_qr_code->qr_content = $dummyText;
                                    $order_qr_code->qrcode_content_type = 'text';
                                    $order_qr_code->qr_image = $generate_qr['filename'];
                                    $order_qr_code->qr_image_path = $generate_qr['filepath'];
                                    $order_qr_code->status = 1;
                                    $order_qr_code->save();
                                }
                            }
                        }
                    }
                }
            } else {
                return $this->sendResponse([], 'No items in the cart.');
            }


            // Overrider::load("Settings");

            // //Replace paremeter
            // $replace = array(
            //     '{order_id}'          => $order->id,
            //     '{name}'              => $request->first_name . ' ' . $request->last_name,
            //     '{order_status}'              => $order->order_status,
            // );

            // //Send contact email
            // $template = EmailTemplate::where('slug', 'order-placed')->first();
            // $template->body = process_string($replace, $template->body);

            // Mail::to($request->email)->send(new GeneralMail($template));

            DB::commit();
            return $this->sendResponse(OrderResource::collection([$order]), 'Order placed successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in creating order: ' . $e->getMessage());
            return $this->sendError('error.', 'Failed to create an order.');
        }
    }


    /**
     * @OA\Get(
     *     path="/api/get-orders",
     *     operationId="getOrder",
     *     tags={"Order"},
     *     summary="Get orders for the authenticated user",
     *     description="Retrieve a list of orders for the authenticated user. The user must be logged in.(Orders will be retrieved with payment done)",
     *     security={{"X-Access-Token": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Orders fetched successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="user_id", type="integer", example=1),
     *                     @OA\Property(property="order_total", type="number", format="float", example=100.50),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-21T12:30:00"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-21T12:30:00"),
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Orders fetched successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No orders found for the user.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No orders found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="User not authenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not found.")
     *         )
     *     ),
     * )
     */

    public function getOrder(Request $request)
    {

        try {
            $user = Auth::user();

            if (!$user) {
                return $this->sendError('Unauthorized', 'User not found.', 401);
            }

            $orders = Order::where('user_id', $user->id)
                ->where('status', 1)
                ->paginate(10);

            if ($orders->isNotEmpty()) {
                return $this->sendResponse([
                    'orders' => OrderResource::collection($orders),
                    'pagination' => [
                        'total' => $orders->total(),
                        'per_page' => $orders->perPage(),
                        'current_page' => $orders->currentPage(),
                        'last_page' => $orders->lastPage(),
                        'next_page_url' => $orders->nextPageUrl(),
                        'prev_page_url' => $orders->previousPageUrl(),
                    ],
                ], 'Orders fetched successfully!');
            } else {
                return $this->sendError('No Orders', 'No orders found.', 404);
            }
        } catch (\Exception $e) {
            return $this->sendError('Server Error', $e->getMessage(), 500);
        }
    }


    /**
     * @OA\Get(
     *     path="/api/order-detail/{id}",
     *     operationId="getOrderDetail",
     *     tags={"Order"},
     *     summary="Get details of a specific order",
     *     description="Retrieve the details of a specific order based on its ID.(Order will be retrieved with payment done)",
     *    security={{"X-Access-Token": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the order to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order details fetched successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="user_id", type="integer", example=1),
     *                     @OA\Property(property="order_total", type="number", format="float", example=100.50),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-21T12:30:00"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-21T12:30:00"),
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Order details fetched successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Order not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="User not authenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not found.")
     *         )
     *     ),
     * )
     */

    public function getOrderDetail(Request $request, $id)
    {
        try {
            $order = Order::where('id', $id)->where('status', 1)->first();

            if ($order) {
                return $this->sendResponse(OrderResource::collection([$order]), 'Order fetched successfully!');
            } else {
                return $this->sendError('Not Found', 'Order not found.', 404);
            }
        } catch (\Exception $e) {
            return $this->sendError('Server Error', $e->getMessage(), 500);
        }
    }



    /**
     * @OA\Get(
     *     path="/api/hot-items",
     *     operationId="hotItems",
     *     tags={"Order"},
     *     summary="Get hot items",
     *     description="Retrieve a list of hot items (products marked as 'hot_product').",
     *     @OA\Response(
     *         response=200,
     *         description="Hot items fetched successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="product_title", type="string", example="iPhone 13"),
     *                     @OA\Property(property="price", type="number", format="float", example=999.99),
     *                     @OA\Property(property="image_url", type="string", example="https://example.com/images/iphone-13.jpg"),
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Hot items fetched successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No hot items found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No hot items found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="User not authenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not found.")
     *         )
     *     ),
     * )
     */


    public function hotItems(Request $request)
    {
        try {
            $hot_products = ProductSetting::where('type', 'hot_product')->get();

            if ($hot_products->isNotEmpty()) {
                return $this->sendResponse(HotItemsResource::collection($hot_products), 'Hot items fetched successfully!');
            } else {
                return $this->sendError('Not Found', 'No hot items found.', 404);
            }
        } catch (\Exception $e) {
            return $this->sendError('Server Error', $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/store-recently-viewed",
     *     summary="Store a Recently Viewed Product",
     *     description="This API endpoint adds a product to the recently viewed products list for a user. If the user has already viewed 5 products, the oldest product will be deleted to accommodate the new one.",
     *     operationId="storeRecentlyViewed",
     *     tags={"Recently Viewed Products"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "product_id"},
     *             @OA\Property(property="user_id", type="integer", description="User ID", example=1),
     *             @OA\Property(property="product_id", type="integer", description="Product ID", example=123)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product added to recently viewed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Product added to recently viewed successfully!"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", description="Recently Viewed record ID", example=1),
     *                     @OA\Property(property="user_id", type="integer", description="User ID", example=1),
     *                     @OA\Property(property="product_id", type="integer", description="Product ID", example=123),
     *                     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp of when the record was created", example="2025-03-24T10:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", description="Timestamp of the last update", example="2025-03-24T10:00:00Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation Error."),
     *             @OA\Property(property="errors", type="object", additionalProperties={"type": "string"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to add the product into recently view",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to add the product into recently view."),
     *             @OA\Property(property="errors", type="string", example="Error message in case of failure")
     *         )
     *     ),
     *     security={{"X-Access-Token": {}}}
     * )
     */


    public function storeRecentlyViewed(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'product_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        if ($request->user_id != Auth::user()->id) {
            return $this->sendError('Invalid User Id.', [], 422);
        }

        DB::beginTransaction();

        try {
            $exits = RecentlyViewed::where('user_id', Auth::user()->id)->where('product_id', $request->product_id)->first();
            if (empty($exits)) {
                $getRecentCount = RecentlyViewed::where('user_id', $request->user_id)->count();
                if ($getRecentCount == 5 || $getRecentCount  > 5) {
                    $delete_first_added_product = RecentlyViewed::where('user_id', $request->user_id)->orderBy('created_at', 'asc')->first();
                    if ($delete_first_added_product) {
                        $delete_first_added_product->delete();
                    }
                }
                $RecentlyViewed = RecentlyViewed::create($request->all());
            } else {
                return $this->sendResponse([], 'Its already added to recently viewed.');
            }
            DB::commit();
            return $this->sendResponse(RecentlyViewedResource::collection([$RecentlyViewed]), 'Product added to recently viewed successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in adding the product into recently view: ' . $e->getMessage());
            return $this->sendError('error.', 'Failed to add the product into recently view.');
        }
    }


    /**
     * @OA\Get(
     *     path="/api/recently-viewed",
     *     summary="Get Recently Viewed Products",
     *     description="This API endpoint retrieves a list of recently viewed products for a user.",
     *     operationId="getRecentlyViewed",
     *     tags={"Recently Viewed Products"},
     *     security={{"X-Access-Token": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched recently viewed products",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Recently viewed products fetched successfully!"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", description="Recently viewed record ID", example=1),
     *                     @OA\Property(property="user_id", type="integer", description="User ID", example=1),
     *                     @OA\Property(property="product_id", type="integer", description="Product ID", example=123),
     *                     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the product was viewed", example="2025-03-24T10:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", description="Timestamp when the record was last updated", example="2025-03-24T10:00:00Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized access")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No recently viewed products found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No recently viewed products found.")
     *         )
     *     )
     * )
     */

    public function getRecentlyViewed(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->sendError('Unauthorized', 'User not authenticated.', 401);
            }

            $recentlyViewedProducts = RecentlyViewed::where('user_id', $user->id)
                ->orderBy('id', 'desc')
                ->get();

            if ($recentlyViewedProducts->isNotEmpty()) {
                return $this->sendResponse(
                    RecentlyViewedResource::collection($recentlyViewedProducts),
                    'Recently viewed products fetched successfully!'
                );
            } else {
                return $this->sendError('Not Found', 'No recently viewed products.', 404);
            }
        } catch (\Exception $e) {
            return $this->sendError('Server Error', $e->getMessage(), 500);
        }
    }
}
