<?php

use App\Http\Controllers\Payments\EazypayCallbackController;
use App\Http\Controllers\Storefront\StorefrontAuthController;
use App\Http\Controllers\Storefront\StorefrontCartController;
use App\Http\Controllers\Storefront\StorefrontCategoryController;
use App\Http\Controllers\Storefront\StorefrontCheckoutController;
use App\Http\Controllers\Storefront\StorefrontContactController;
use App\Http\Controllers\Storefront\StorefrontDeliveryLocationController;
use App\Http\Controllers\Storefront\StorefrontLanguageController;
use App\Http\Controllers\Storefront\StorefrontPageController;
use App\Http\Controllers\Storefront\StorefrontPreviewController;
use App\Http\Controllers\Storefront\StorefrontProductController;
use App\Http\Controllers\Storefront\StorefrontWishlistController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/admin.php';

Route::get('/', [StorefrontPageController::class, 'home'])->name('store.home');
Route::get('/invoice-preview/{template}', [StorefrontPreviewController::class, 'invoice'])->name('store.invoice-preview');
Route::get('/language/{locale}', [StorefrontLanguageController::class, 'update'])->name('store.language');
Route::post('/delivery-location', [StorefrontDeliveryLocationController::class, 'update'])->name('store.delivery-location');
Route::get('/email-preview/{template}', [StorefrontPreviewController::class, 'email'])->name('store.email-preview');
Route::get('/category/{category:slug}', [StorefrontCategoryController::class, 'show'])->name('store.category');
Route::get('/product/{product}', [StorefrontProductController::class, 'show'])->name('store.product');

Route::post('/store/login', [StorefrontAuthController::class, 'login'])->middleware('throttle:login')->name('store.auth.login');
Route::post('/store/register', [StorefrontAuthController::class, 'register'])->middleware('throttle:login')->name('store.auth.register');
Route::post('/store/logout', [StorefrontAuthController::class, 'logout'])->name('store.auth.logout');
Route::post('/cart/add', [StorefrontCartController::class, 'add'])->name('store.cart.add');
Route::post('/cart/update', [StorefrontCartController::class, 'update'])->name('store.cart.update');
Route::post('/cart/remove/{lineKey}', [StorefrontCartController::class, 'remove'])->where('lineKey', '[0-9:]+')->name('store.cart.remove');
Route::post('/cart/clear', [StorefrontCartController::class, 'clear'])->name('store.cart.clear');
Route::post('/wishlist/add', [StorefrontWishlistController::class, 'add'])->name('store.wishlist.add');
Route::post('/wishlist/remove/{productId}', [StorefrontWishlistController::class, 'remove'])->name('store.wishlist.remove');
Route::post('/wishlist/toggle', [StorefrontWishlistController::class, 'toggle'])->name('store.wishlist.toggle');
Route::post('/checkout/place-order', [StorefrontCheckoutController::class, 'placeOrder'])->name('store.checkout.place-order');
Route::post('/contact', [StorefrontContactController::class, 'store'])->middleware('throttle:5,1')->name('store.contact');

// The bank sends the payer back here. Registered before the /{page} catch-all
// so a GET return is not swallowed by the storefront page route, and exempt
// from CSRF because the POST originates on the bank domain.
Route::match(['get', 'post'], '/payments/eazypay/callback', [EazypayCallbackController::class, 'handle'])
    ->middleware('throttle:api')
    ->name('payments.eazypay.callback');

Route::get('/{page}', [StorefrontPageController::class, 'show'])->name('store.page');
