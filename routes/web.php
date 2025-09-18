<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Contentcontroller;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Front\FrontController;
use App\Http\Controllers\Admin\QrcodeController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\MyProfileController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ReturnOrderController;
use App\Http\Controllers\Admin\SubscribeController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', function () {
    return redirect('/admin/login');
});

Route::any('/app-deletion', [SettingController::class, 'appdeletion'])->name('appdeletion');


// admin routes
Route::prefix('admin')->group(function () {
    Route::get('/login', [LoginController::class, 'loginForm'])->name('adminloginget');
    Route::post('/login', [LoginController::class, 'login'])->name('adminlogin');
});

Route::prefix('admin')->middleware(['auth:admin'])->group(function () {
    Route::get('/dashboard', [HomeController::class, 'adminDashboard'])->name('admin.home');
    Route::post('logout', [LoginController::class, 'adminLogout'])->name('admin.logout');

    // my proflie
    Route::get('/edit-profile', [MyProfileController::class, 'editProfile'])->name('admin.edit.profile');
    Route::post('/update-profile', [MyProfileController::class, 'updateProfile'])->name('admin.update.profile');

    // setiting
    Route::get('/store-setting', [SettingController::class, 'create'])->name('store.setting.form');
    Route::post('/store-setting-update', [SettingController::class, 'store'])->name('store.setting.update');

    // shop page setting
    Route::get('/shop-page-settings', [SettingController::class, 'shopPageSettings'])->name('store.setting.shoppage');
    Route::post('/shop-settings-update', [SettingController::class, 'shopPageSettingsStore'])->name('shoppage.setting.update');

    // authentication setting
    Route::get('/authentication-settings', [SettingController::class, 'authenticationSettings'])->name('authentication.setting');
    Route::post('/authentication-settings-update', [SettingController::class, 'storeAuthenticationSettings'])->name('authentication.setting.update');

    // category routes
    Route::resource('category', CategoryController::class);

    // FAQ routes
    Route::resource('faq', FaqController::class);

    // Email Templates routes
    Route::resource('email-templates', EmailTemplateController::class);

    // ckeditor image upload and remove routes
    Route::post('ckeditor/imageupload', [EmailTemplateController::class, 'ckEditorImageUpload'])->name('ckeditor.image');
    Route::post('ckeditor/imageRemove', [EmailTemplateController::class, 'ckEditorImageRemove'])->name('ckeditor.imageremove');

    // review routes
    Route::resource('review', ReviewController::class);
    Route::delete('delete-review-image/{id}', [ReviewController::class, 'reviewImageDelete'])->name('delete-review-image');

    Route::resource('qrcode', QrcodeController::class);
    Route::get('download-qr-image/{id}', [QrcodeController::class, 'downloadQrImage'])->name('downloadQrImage');

    // order
    Route::resource('orders', OrderController::class);
    Route::get('orders-restore/{id}', [OrderController::class, 'restoreOrder'])->name('orders.restore');

    // return order
    Route::resource('return-orders', ReturnOrderController::class);
    Route::get('send-return-request-in-merchmake/{id}', [ReturnOrderController::class, 'sendRequestInMerchmake'])->name('sendRequestInMerchmake');
    Route::get('refund-request/{id}', [ReturnOrderController::class, 'refundRequest'])->name('refundRequest');

    Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');

    // user
    Route::resource('users', UserController::class)->only(['index', 'show']);

    // subscribe
    Route::resource('subscribers', SubscribeController::class)->only([
        'index',
        'destroy'
    ]);

    // testimonial
    Route::resource('testimonials', TestimonialController::class);
});


Route::get('/content/{user_id}/{order_id}/{order_item_id}', [Contentcontroller::class, 'qrContent'])->name('qrContent');


Route::get('/thank-you', [Contentcontroller::class, 'ThankYou']);
Route::get('/cancel', [Contentcontroller::class, 'cancel']);
