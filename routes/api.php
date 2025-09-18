<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\FAQController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\HomeController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\PayPalController;
use App\Http\Controllers\API\QrCodeController;
use App\Http\Controllers\API\ReviewController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\CheckoutController;
use App\Http\Controllers\API\ShopPageController;
use App\Http\Controllers\API\WishlistController;
use App\Http\Controllers\API\ContactUsController;
use App\Http\Controllers\API\SubscribeController;
use App\Http\Controllers\API\ReturnOrderController;
use App\Http\Controllers\API\SocialLoginController;
use App\Http\Controllers\API\TestimonialController;
use App\Http\Controllers\API\VerificationController;
use App\Http\Controllers\API\ProductDetailController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



// front pages
Route::get('/faq', [FAQController::class, 'getFAQ']);
Route::get('/home-page-categories', [CategoryController::class, 'getHomePageCategories']);
Route::post('/contact-us', [ContactUsController::class, 'contactUs']);

// shop pages categories
Route::get('/shop-page-data', [ShopPageController::class, 'getShopPageData']);
Route::get('/product-price/{id}', [ShopPageController::class, 'getShopPageProductPrice']);

// product detail
Route::get('/product-detail/{id}', [ProductDetailController::class, 'getProductDetail']);
Route::get('/get_variation_price/{id}', [ProductDetailController::class, 'getVariationPrice']);
Route::get('/qr-codes', [ProductDetailController::class, 'getQrCodes']);

// get reviews
Route::get('/get-reviews/{id}', [ReviewController::class, 'getReviews']);

// get best sellers products
Route::get('/best-sellers', [HomeController::class, 'getBestSellersProducts']);

// hot items
Route::get('/hot-items', [OrderController::class, 'hotItems']);

// subscribe email
Route::post('/subscribe', [SubscribeController::class, 'subscribe']);

// testimonials email
Route::get('/testimonials', [TestimonialController::class, 'getTestimonials']);

// get qr content
Route::get('/content/{user_id}/{order_id}/{order_item_id}', [HomeController::class, 'getQrContent']);

// Public post viewing routes (no authentication required)
Route::get('/public/users/{userId}/posts/{postId}', [App\Http\Controllers\API\PostController::class, 'publicShow']);
Route::get('/public/users/{userId}/posts', [App\Http\Controllers\API\PostController::class, 'publicUserPosts']);


Route::controller(AuthController::class)->group(function () {
    Route::post('register', 'register')->name('register');
    Route::post('login', 'login')->name('login');
    Route::post('forgot-password', 'forgotPassword');
    Route::post('reset-password', 'resetPassword')->name('password.reset');
});

// Route::get('login/{provider}', [SocialLoginController::class, 'redirectToProvider']);
Route::get('auth/google/callback', [SocialLoginController::class, 'handleProviderCallback']);
Route::post('auth-login', [SocialLoginController::class, 'login']);


// Email verification routes
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verifyEmail'])
    ->middleware(['signed']) // Ensure request is signed for security
    ->name('verification.verify');



// Authenticated routes that use Sanctum middleware
Route::middleware(['access-token', 'auth:sanctum'])->group(function () {

    // Resend verification email
    Route::post('/email/resend-verification', [VerificationController::class, 'resendVerificationEmail']);

    // Logout route
    Route::post('/logout', [AuthController::class, 'logout']);

    // Routes that require both authentication and verified email
    Route::middleware(['verified'])->group(function () {

        // change password
        Route::post('/change-password', [AuthController::class, 'changePassword']);

        // User
        Route::post('/update-user-details/{id}', [UserController::class, 'updateUserDetails']);
        Route::get('/user-detail/{id}', [UserController::class, 'getUserDetail']);

        // wishlist
        Route::get('/wishlist', [WishlistController::class, 'getWishlistData']);
        Route::post('/add-to-wishlist', [WishlistController::class, 'addToWishlist']);
        Route::delete('/remove-wishlist-product/{id}', [WishlistController::class, 'removeWishlistProduct']);

        // review
        Route::post('/review', [ReviewController::class, 'addReview']);

        // add to cart
        Route::post('/add-to-cart', [CartController::class, 'addCart']);
        Route::get('/view-cart', [CartController::class, 'viewCart']);
        Route::delete('/remove-cart-item/{id}', [CartController::class, 'removeCartItem']);
        Route::post('/update-cart', [CartController::class, 'updateCart']);

        Route::get('/checkout', [CheckoutController::class, 'checkout']);

        // create order
        Route::post('/create-order', [OrderController::class, 'createOrder']);
        Route::get('/get-orders', [OrderController::class, 'getOrder']);
        Route::get('/order-detail/{id}', [OrderController::class, 'getOrderDetail']);


        Route::get('/recently-viewed', [OrderController::class, 'getRecentlyViewed']);
        Route::post('/store-recently-viewed', [OrderController::class, 'storeRecentlyViewed']);


        // stripe  payment
        Route::post('/create-payment-intent', [PaymentController::class, 'createPaymentIntent']);

        // paypal payment
        Route::post('/paypal-payment', [PayPalController::class, 'paypalPaymentUrl'])->name('api.paypal-payment');
        Route::post('/paypal-payment/capture', [PayPalController::class, 'paypalPaymentCapture'])->name('api.paypal-payment-capture');

        // return product
        Route::post('/return-order', [ReturnOrderController::class, 'returnOrderRequest']);
        Route::get('/return-order/{order_id}', [ReturnOrderController::class, 'getReturnOrder']);
        Route::get('/return-orders', [ReturnOrderController::class, 'getAllReturnOrder']);

        // qr code content
        Route::get('/get-qrcode-content/{order_item_id}', [QrCodeController::class, 'getQrCodeContent']);
        Route::post('/update-qrcode-content', [QrCodeController::class, 'updateQrcodeContent']);
        Route::post('/active-deactivate-qr', [QrCodeController::class, 'deactivateQR']);

        // Post routes
        Route::get('/posts', [App\Http\Controllers\API\PostController::class, 'index']);
        Route::post('/posts', [App\Http\Controllers\API\PostController::class, 'store']);
        Route::get('/posts/{id}', [App\Http\Controllers\API\PostController::class, 'show']);
        Route::put('/posts/{id}', [App\Http\Controllers\API\PostController::class, 'update']);
        Route::delete('/posts/{id}', [App\Http\Controllers\API\PostController::class, 'destroy']);
        Route::post('/posts/{id}/like', [App\Http\Controllers\API\PostController::class, 'toggleLike']);
        Route::post('/posts/{id}/repost', [App\Http\Controllers\API\PostController::class, 'toggleRepost']);
        Route::get('/my-posts', [App\Http\Controllers\API\PostController::class, 'myPosts']);
        Route::get('/users/{userId}/posts', [App\Http\Controllers\API\PostController::class, 'userPosts']);
        Route::get('/posts/{id}/replies', [App\Http\Controllers\API\PostController::class, 'replies']);

        // Media upload routes
        Route::post('/media/upload', [App\Http\Controllers\API\MediaUploadController::class, 'upload']);
        Route::delete('/media/delete', [App\Http\Controllers\API\MediaUploadController::class, 'delete']);
        Route::get('/media/metadata', [App\Http\Controllers\API\MediaUploadController::class, 'metadata']);
        Route::get('/media/optimized-url', [App\Http\Controllers\API\MediaUploadController::class, 'optimizedUrl']);
    });
});

// Temporary: Media upload routes for testing (moved outside auth middleware)
Route::post('/media/upload', [App\Http\Controllers\API\MediaUploadController::class, 'upload']);
Route::delete('/media/delete', [App\Http\Controllers\API\MediaUploadController::class, 'delete']);
Route::get('/media/metadata', [App\Http\Controllers\API\MediaUploadController::class, 'metadata']);
Route::get('/media/optimized-url', [App\Http\Controllers\API\MediaUploadController::class, 'optimizedUrl']);

Route::post('/stripe-webhook', [PaymentController::class, 'handleStripeWebhook']);
Route::post('/paypal-webhook', [PayPalController::class, 'handlePaypalWebhook']);
