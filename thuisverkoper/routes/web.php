<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DocumentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    
    // Property routes
    Route::resource('properties', PropertyController::class);
    Route::patch('/properties/{property}/toggle-status', [PropertyController::class, 'toggleStatus'])->name('properties.toggle-status');
    Route::delete('/properties/{property}/remove-image', [PropertyController::class, 'removeImage'])->name('properties.remove-image');
    
    // Service management routes (authenticated)
    Route::resource('services', ServiceController::class)->except(['index', 'show']);
    Route::patch('/services/{service}/toggle-status', [ServiceController::class, 'toggleStatus'])->name('services.toggle-status');
    
    // Cart management routes (authenticated)
    Route::post('/services/{service}/add-to-cart', [ServiceController::class, 'addToCart'])->name('services.add-to-cart');
    Route::delete('/services/{service}/remove-from-cart', [ServiceController::class, 'removeFromCart'])->name('services.remove-from-cart');
    Route::get('/cart', [ServiceController::class, 'viewCart'])->name('services.cart');
    Route::delete('/cart/clear', [ServiceController::class, 'clearCart'])->name('services.clear-cart');
    
    // Order management routes (authenticated)
    Route::resource('orders', OrderController::class)->except(['create', 'edit']);
    Route::get('/checkout', [OrderController::class, 'checkout'])->name('orders.checkout');
    Route::get('/orders/{order}/success', [OrderController::class, 'success'])->name('orders.success');
    Route::get('/orders/{order}/cancelled', [OrderController::class, 'cancelled'])->name('orders.cancelled');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('/orders/{order}/refund', [OrderController::class, 'refund'])->name('orders.refund');
    Route::get('/orders/{order}/invoice', [OrderController::class, 'downloadInvoice'])->name('orders.invoice');
    Route::post('/orders/{order}/retry-payment', [OrderController::class, 'retryPayment'])->name('orders.retry-payment');
    
    // AJAX order routes (authenticated)
    Route::get('/api/orders/stats', [OrderController::class, 'getStats'])->name('api.orders.stats');
    Route::get('/api/orders/search', [OrderController::class, 'search'])->name('api.orders.search');
    
    // Booking management routes (authenticated)
    Route::resource('bookings', BookingController::class);
    Route::patch('/bookings/{booking}/confirm', [BookingController::class, 'confirm'])->name('bookings.confirm');
    Route::patch('/bookings/{booking}/complete', [BookingController::class, 'complete'])->name('bookings.complete');
    Route::patch('/bookings/{booking}/reschedule', [BookingController::class, 'reschedule'])->name('bookings.reschedule');
    
    // AJAX booking routes (authenticated)
    Route::get('/api/bookings/calendar-events', [BookingController::class, 'calendarEvents'])->name('api.bookings.calendar-events');
    Route::get('/api/bookings/available-slots', [BookingController::class, 'availableSlots'])->name('api.bookings.available-slots');
    Route::get('/api/bookings/stats', [BookingController::class, 'stats'])->name('api.bookings.stats');
    Route::get('/api/bookings/search', [BookingController::class, 'search'])->name('api.bookings.search');
    
    // Document management routes (authenticated)
    Route::resource('documents', DocumentController::class);
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::get('/documents/{document}/preview', [DocumentController::class, 'preview'])->name('documents.preview');
    Route::get('/documents/{document}/signature', [DocumentController::class, 'signature'])->name('documents.signature');
    Route::post('/documents/{document}/sign', [DocumentController::class, 'sign'])->name('documents.sign');
    Route::post('/documents/{document}/regenerate', [DocumentController::class, 'regenerate'])->name('documents.regenerate');
    Route::post('/documents/bulk-action', [DocumentController::class, 'bulkAction'])->name('documents.bulk-action');
    Route::get('/documents/{document}/verify', [DocumentController::class, 'verifySignature'])->name('documents.verify');
    Route::get('/documents/{document}/versions', [DocumentController::class, 'versions'])->name('documents.versions');
    
    // AJAX document routes (authenticated)
    Route::get('/api/documents/stats', [DocumentController::class, 'getStats'])->name('api.documents.stats');
    Route::get('/api/documents/search', [DocumentController::class, 'search'])->name('api.documents.search');
});

// Public property listing
Route::get('/properties', [PropertyController::class, 'index'])->name('properties.index');
Route::get('/properties/{property}', [PropertyController::class, 'show'])->name('properties.show');

// Public service routes
Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
Route::get('/services/{service}', [ServiceController::class, 'show'])->name('services.show');

// AJAX routes for services
Route::get('/api/services/category/{category}', [ServiceController::class, 'getByCategory'])->name('api.services.by-category');
Route::get('/api/services/search', [ServiceController::class, 'search'])->name('api.services.search');

// Payment webhook routes (no CSRF protection, no auth required)
Route::post('/webhooks/stripe', [PaymentController::class, 'stripeWebhook'])->name('webhooks.stripe');
Route::post('/webhooks/mollie', [PaymentController::class, 'mollieWebhook'])->name('webhooks.mollie');
