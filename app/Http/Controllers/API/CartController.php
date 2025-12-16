<?php

namespace App\Http\Controllers\API;

use App\Models\Cart;
use App\Models\Desing;
use App\Models\Qrcodes;
use App\Services\MerchMake;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\BaseController;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx\Rels;
use Illuminate\Http\Resources\Json\JsonResource;

class CartController extends BaseController
{

    /**
     * @OA\Post(
     *     path="/api/add-to-cart",
     *     operationId="addCart",
     *     tags={"Cart"},
     *     summary="Add product to cart (Requires token)",
     *     description="Add a product with variation and optional art files (QR + Design).",
     *     security={{"X-Access-Token": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="product_id", type="integer", example=20716),
     *             @OA\Property(property="product_variation_id", type="integer", example=251295),
     *             @OA\Property(property="qty", type="integer", example=1),
     *             @OA\Property(property="price", type="number", format="float", example=53.95),
     *
     *             @OA\Property(
     *                 property="art_files",
     *                 type="object",
     *                 description="Each key (Front/Back/etc.) contains qr_id and design_id.",
     *                 @OA\Property(
     *                     property="Back",
     *                     type="object",
     *                     @OA\Property(property="qr_id", type="integer", example=16, description="QR Code ID"),
     *                     @OA\Property(property="design_id", type="integer", example=20, description="Design ID")
     *                 ),
     *                 @OA\Property(
     *                     property="Front",
     *                     type="object",
     *                     @OA\Property(property="qr_id", type="integer", example=15, description="QR Code ID"),
     *                     @OA\Property(property="design_id", type="integer", example=18, description="Design ID")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Product added to cart successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="product_id", type="integer", example=20716),
     *                 @OA\Property(property="qty", type="integer", example=1),
     *                 @OA\Property(property="price", type="number", example=53.95),
     *                 @OA\Property(property="total", type="number", example=53.95),
     *                 @OA\Property(property="product_variation_id", type="integer", example=251295),
     *                 @OA\Property(property="product_title", type="string", example="Premium T-Shirt"),
     *                 @OA\Property(property="variation_color", type="string", example="Black"),
     *                 @OA\Property(property="variation_size", type="string", example="L"),
     *
     *                 @OA\Property(
     *                     property="art_files",
     *                     type="object",
     *                     description="Saved QR + Design for each position.",
     *                     @OA\Property(
     *                         property="Back",
     *                         type="object",
     *                         @OA\Property(property="qr_id", type="integer", example=16),
     *                         @OA\Property(property="design_id", type="integer", example=20)
     *                     ),
     *                     @OA\Property(
     *                         property="Front",
     *                         type="object",
     *                         @OA\Property(property="qr_id", type="integer", example=15),
     *                         @OA\Property(property="design_id", type="integer", example=18)
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Product added to cart successfully!")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation Error."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="user_id", type="array", @OA\Items(type="string", example="The user_id field is required.")),
     *                 @OA\Property(property="product_id", type="array", @OA\Items(type="string", example="The product_id field is required.")),
     *                 @OA\Property(property="qty", type="array", @OA\Items(type="string", example="The qty must be numeric.")),
     *                 @OA\Property(property="price", type="array", @OA\Items(type="string", example="The price must be numeric.")),
     *                 @OA\Property(property="art_files", type="array", @OA\Items(type="string", example="The art_files field is required.")),
     *                 @OA\Property(property="art_files.*.qr_id", type="array", @OA\Items(type="string", example="The qr_id field is required.")),
     *                 @OA\Property(property="art_files.*.design_id", type="array", @OA\Items(type="string", example="The design_id field is required."))
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to add the product into cart.")
     *         )
     *     )
     * )
     */


    public function addCart(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'product_id' => 'required',
            'qty' => 'required|numeric',
            'price' => 'required|numeric',
            'product_variation_id' => 'required',
            'art_files' => 'required|array', // Ensure art_files is an array
            'art_files.*' => 'required|distinct',
            'design_id' => 'nullable|exists:desings,id',
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
                    // foreach ($request['art_files'] as $key => $qrcode_id) {
                    //     if (empty($key) || empty($qrcode_id) || !in_array($key, $merchmake_product_art_files)) {
                    //         return $this->sendError('error.', 'Invalid art file position.');
                    //     }
                    //     if (empty(Qrcodes::where('id', $qrcode_id)->first())) {
                    //         return $this->sendError('error.', 'Invalid QR code.');
                    //     }
                    // }

                    foreach ($request['art_files'] as $position => $values) {

                        // Validate position (Back, Front, etc.)
                        if (empty($position) || !in_array($position, $merchmake_product_art_files)) {
                            return $this->sendError('error.', 'Invalid art file position.');
                        }

                        // Validate structure: must contain file_id & design_id
                        $fileId   = $values['qr_id']   ?? null;
                        $designId = $values['design_id'] ?? null;

                        if (empty($fileId)) {
                            return $this->sendError('error.', 'QR code is missing.');
                        }

                        // Validate QR code exists
                        if (!Qrcodes::where('id', $fileId)->exists()) {
                            return $this->sendError('error.', 'Invalid QR code.');
                        }

                        // Validate Design exists
                        if (!empty($designId) && !Desing::where('id', $designId)->exists()) {
                            return $this->sendError('error.', 'Invalid design ID.');
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
                // foreach ($request['art_files'] as $key => $qrcode_id) {
                //     dd($key, $qrcode_id);
                //     $cart->cartItmesQrCodes()->create([
                //         'qrcode_id' => $qrcode_id,
                //         'position' => $key
                //     ]);
                // }

                foreach ($request['art_files'] as $side => $values) {

                    $fileId   = $values['qr_id'] ?? null;
                    $designId = $values['design_id'] ?? null;

                    $getImage = generateDesingWithQr($designId, $fileId);

                    $cart->cartItmesQrCodes()->create([
                        'qrcode_id' => $fileId,
                        'design_id' => $designId,
                        'position'  => $side,
                        'desing_with_qr' => $getImage['image_path'] ?? null  // store generated image path in DB
                    ]);
                }

                // try {

                //     if ($request->design_id) {

                //         $design = Desing::find($request->design_id);

                //         if (!$design) {
                //             return response()->json(['success' => false, 'message' => 'Design not found'], 404);
                //         }

                //         $basePath = storage_path('app/public/DesignImages/' . $design->image_name);
                //         if (!file_exists($basePath)) {
                //             return response()->json(['success' => false, 'message' => 'Base design image missing'], 500);
                //         }

                //         $base = imagecreatefromstring(file_get_contents($basePath));

                //         foreach ($request['art_files'] as $key => $qrId) {

                //             $qr = Qrcodes::find($qrId);

                //             if (!$qr || !$qr->qr_image) {
                //                 return response()->json(['success' => false, 'message' => "QR (#$qrId) image not found"], 404);
                //             }

                //             $qrFilePath = public_path('storage/' . $qr->qr_image_path);

                //             if (!file_exists($qrFilePath)) {
                //                 return response()->json(['success' => false, 'message' => "QR image missing (#$qrId)"], 500);
                //             }


                //             $qrimg = imagecreatefromstring(file_get_contents($qrFilePath));



                //             // Enable transparency on base image
                //             imagealphablending($base, true);
                //             imagesavealpha($base, true);

                //             $targetW = $design->target_width ?? 340;
                //             $targetH = $design->target_height ?? 340;

                //             // $targetW = 250;
                //             // $targetH = 250;

                //             // // Resize before merging  (same as working code)
                //             $qrResized = imagescale($qrimg, $targetW, $targetH, IMG_BILINEAR_FIXED);

                //             // $posX =  intval($design->x_axis);
                //             // $posY =  intval($design->y_axis);

                //             // $posX =  500;
                //             // $posY =  1200;
                //             $rotation =  0;

                //             // // Rotate
                //             // $qrRotated = imagerotate($qrResized, -$rotation, 0);
                //             // imagealphablending($qrRotated, true);
                //             // imagesavealpha($qrRotated, true);

                //             // // Merge resized QR
                //             // imagecopy($base, $qrResized, $posX, $posY, 0, 0, $targetW, $targetH);

                //             // // Cleanup memory
                //             // imagedestroy($qrimg);
                //             // imagedestroy($qrResized);

                //             // Positions from DB or static
                //             // $posX = 460;
                //             // $posY = 430;// up down
                //             // $rotation = $design->rotation ?? 20;   // OR static: 0 if you want

                //             // --- SMART CONDITION ---
                //             if ($rotation != 0) {

                //                 // Create transparent color
                //                 $transparent = imagecolorallocatealpha($qrResized, 0, 0, 0, 127);

                //                 // Rotate with transparent background
                //                 $qrRotated = imagerotate($qrResized, -$rotation, $transparent);

                //                 // Preserve alpha
                //                 imagealphablending($qrRotated, false);
                //                 imagesavealpha($qrRotated, true);

                //                 imagecopy($base, $qrRotated, $posX, $posY, 0, 0, imagesx($qrRotated), imagesy($qrRotated));
                //                 imagedestroy($qrRotated);
                //             } else {
                //                 imagecopy($base, $qrResized, $posX, $posY, 0, 0, $targetW, $targetH);
                //             }

                //             // Cleanup
                //             imagedestroy($qrimg);
                //             imagedestroy($qrResized);
                //         }

                //         // Save final image
                //         $folder = storage_path('app/public/CartDesignImages/');
                //         if (!file_exists($folder)) mkdir($folder, 0777, true);

                //         $finalName = 'cart_design_' . time() . '.png';
                //         $finalPath = $folder . $finalName;
                //         imagepng($base, $finalPath, 6);
                //         imagedestroy($base);

                //         // $cart->cartDesign()->create([
                //         //     'design_id' => $design->id,
                //         //     'image'     => $finalName
                //         // ]);
                //     }

                //     return response()->json([
                //         'success' => true,
                //         'message' => 'Cart created successfully',
                //         // 'total_items' => $cart->cartItmesQrCodes->count(),
                //         'design_applied' => $request->design_id ? true : false
                //     ], 200);
                // } catch (\Throwable $e) {

                //     return response()->json([
                //         'success' => false,
                //         'message' => $e->getMessage()
                //     ], 500);
                // }
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
     * @OA\Post(
     *     path="/api/desing-with-qrimage",
     *     operationId="generateNewDesingWithQR",
     *     tags={"Cart"},
     *     summary="Generate a new design with QR code",
     *     description="Generates a new design by overlaying a QR code onto a base design image, and returns the URL of the resulting image.",
     *     security={{"X-Access-Token": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="design_id", type="integer", example=101, description="ID of the base design."),
     *             @OA\Property(property="qr_code_id", type="integer", example=501, description="ID of the QR code to apply on the design.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Design generated successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="image_url", type="string", example="https://example.com/storage/CartDesignImages/cart_design_1698765432.png", description="URL of the generated design image.")
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Image generated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Design not found or QR image not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation Error."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="design_id", type="array", @OA\Items(type="string", example="The design_id field is required.")),
     *                 @OA\Property(property="qr_code_id", type="array", @OA\Items(type="string", example="The qr_code_id field is required."))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to generate image")
     *         )
     *     )
     * )
     */

    public function generateNewDesingWithQR(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'qr_code_id' => 'required|exists:qrcodes,id',
            'design_id' => 'required|exists:desings,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }


        try {

            if ($request->design_id) {

                $design = Desing::find($request->design_id);
                if (!$design) {
                    return response()->json(['success' => false, 'message' => 'Design not found'], 404);
                }

                $basePath = storage_path('app/public/DesignImages/' . $design->image_name);
                if (!file_exists($basePath)) {
                    return response()->json(['success' => false, 'message' => 'Base design image missing'], 500);
                }

                // Load base image
                $base = imagecreatefromstring(file_get_contents($basePath));
                imagesavealpha($base, true);
                imagealphablending($base, true);

                $qrId = $request->qr_code_id;
                $qr = Qrcodes::find($qrId);
                if (!$qr || !$qr->qr_image) {
                    return response()->json(['success' => false, 'message' => "QR (#$qrId) image not found"], 404);
                }

                $qrFilePath = public_path('storage/' . $qr->qr_image_path);
                if (!file_exists($qrFilePath)) {
                    return response()->json(['success' => false, 'message' => "QR image missing (#$qrId)"], 500);
                }

                // Resize QR with transparency
                $targetW = $design->target_width ?? 150;
                $targetH = $design->target_height ?? 150;
                // $targetW =  360;
                // $targetH =  360;
                $qrResized = resizeWithTransparency($qrFilePath, $targetW, $targetH);

                // Position & rotation
                $posX = intval($design->x_axis);
                $posY = intval($design->y_axis);

                // $posX = 950;
                // $posY = 420;
                $rotation = $design->rotation ?? 0;

                // Rotate if needed
                if ($rotation != 0) {
                    $transparent = imagecolorallocatealpha($qrResized, 0, 0, 0, 127);
                    $qrRotated = imagerotate($qrResized, -$rotation, $transparent);
                    imagesavealpha($qrRotated, true);
                    imagecopy($base, $qrRotated, $posX, $posY, 0, 0, imagesx($qrRotated), imagesy($qrRotated));
                    imagedestroy($qrRotated);
                } else {
                    imagecopy($base, $qrResized, $posX, $posY, 0, 0, $targetW, $targetH);
                }

                imagedestroy($qrResized);

                // Save the final image
                $folder = storage_path('app/public/CartDesignImages/');
                if (!file_exists($folder)) mkdir($folder, 0777, true);

                $finalName = 'cart_design_' . time() . '.png';
                $finalPath = $folder . $finalName;

                if (imagepng($base, $finalPath, 6)) {
                    imagedestroy($base);
                    $imageUrl = url('storage/CartDesignImages/' . $finalName);

                    // Return the image URL via a JSON Resource (full URL usable in browser)
                    return (new JsonResource(['image_url' => $imageUrl]))
                        ->additional(['success' => true, 'message' => 'Image generated successfully'])
                        ->response()
                        ->setStatusCode(200);
                } else {
                    imagedestroy($base);
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to save final image'
                    ], 500);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Cart created successfully',
                // 'total_items' => $cart->cartItmesQrCodes->count(),
                'design_applied' => $request->design_id ? true : false
            ], 200);
        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
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
     * @OA\Get(
     *     path="/api/get-cartitems-count",
     *     operationId="getCartItemsCount",
     *     tags={"Cart"},
     *     summary="Get the total number of items in the user's cart (Require token)",
     *     security={{"X-Access-Token": {}}},
     *     description="Fetches the total quantity of items in the cart for the authenticated user.",
     *     @OA\Response(
     *         response=200,
     *         description="Cart item count retrieved successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="cart_items_count", type="integer", example=3)
     *             ),
     *             @OA\Property(property="message", type="string", example="Cart items count retrieved successfully!")
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
     *     @OA\Response(
     *         response=422,
     *         description="Validation Failed. Errors with the provided data.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation failed."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */

    public function getCartCount(Request $request)
    {
        if (Auth::check()) {
            $cart_count = Cart::where('user_id', Auth::id())->sum('qty');

            if ($cart_count > 0) {
                return $this->sendResponse(['cart_items_count' => $cart_count], 'Cart items count retrieved successfully!');
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
