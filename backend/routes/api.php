<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentWebhookController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ShippingCalculatorController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\ThemeController;
use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\ImageUploadController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\Admin\SettingsController;
use App\Http\Controllers\Api\Admin\ShippingController as AdminShippingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Public store routes require tenant middleware to resolve the store.
| Admin routes require authentication + store_owner role.
| Platform routes require authentication + platform_admin role.
|
*/

// ========================================================================
// AUTH ROUTES
// ========================================================================
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

// ========================================================================
// PUBLIC STORE ROUTES (require tenant resolution)
// ========================================================================
Route::middleware('tenant')->group(function () {

    // Store config & theme
    Route::get('/store/config', [StoreController::class, 'config']);
    Route::get('/store/payment-methods', [StoreController::class, 'paymentMethods']);
    Route::get('/theme', [ThemeController::class, 'show']);

    // Products
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{slug}', [ProductController::class, 'show']);

    // Categories
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{slug}/products', [CategoryController::class, 'products']);

    // Cart
    Route::get('/cart', [CartController::class, 'show']);
    Route::post('/cart/items', [CartController::class, 'addItem']);
    Route::patch('/cart/items/{itemId}', [CartController::class, 'updateItemQuantity']);
    Route::delete('/cart/items/{itemId}', [CartController::class, 'removeItem']);

    // Checkout
    Route::post('/checkout', [CheckoutController::class, 'process']);

    // Order status (public)
    Route::get('/orders/{orderNumber}/status', [OrderController::class, 'status']);

    // Shipping calculator (public)
    Route::post('/shipping/calculate', [ShippingCalculatorController::class, 'calculate']);
});

// ========================================================================
// PAYMENT WEBHOOKS (no auth, no tenant — webhooks come from external services)
// ========================================================================
Route::prefix('webhooks')->group(function () {
    Route::post('/stripe', [PaymentWebhookController::class, 'stripe']);
    Route::post('/mercadopago', [PaymentWebhookController::class, 'mercadopago']);
});

// ========================================================================
// ADMIN ROUTES (authenticated store owner)
// ========================================================================
Route::prefix('admin')
    ->middleware(['auth:sanctum', 'role:store_owner,platform_admin'])
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index']);

        // Image Upload
        Route::post('/upload-images', [ImageUploadController::class, 'upload']);

        // Products CRUD
        Route::get('/products', [AdminProductController::class, 'index']);
        Route::post('/products', [AdminProductController::class, 'store']);
        Route::get('/products/{id}', [AdminProductController::class, 'show']);
        Route::put('/products/{id}', [AdminProductController::class, 'update']);
        Route::delete('/products/{id}', [AdminProductController::class, 'destroy']);

        // Categories CRUD
        Route::get('/categories', [AdminCategoryController::class, 'index']);
        Route::post('/categories', [AdminCategoryController::class, 'store']);
        Route::put('/categories/{id}', [AdminCategoryController::class, 'update']);
        Route::delete('/categories/{id}', [AdminCategoryController::class, 'destroy']);

        // Orders
        Route::get('/orders', [AdminOrderController::class, 'index']);
        Route::get('/orders/{id}', [AdminOrderController::class, 'show']);
        Route::patch('/orders/{id}/payment-status', [AdminOrderController::class, 'updatePaymentStatus']);
        Route::patch('/orders/{id}/delivery-status', [AdminOrderController::class, 'updateDeliveryStatus']);
        Route::patch('/orders/{id}/mark-paid', [AdminOrderController::class, 'markAsPaid']);

        // Settings
        Route::get('/settings', [SettingsController::class, 'show']);
        Route::put('/settings/store', [SettingsController::class, 'updateStore']);
        Route::put('/settings/theme', [SettingsController::class, 'updateTheme']);
        Route::put('/settings/payment', [SettingsController::class, 'updatePaymentConfig']);
        Route::put('/settings/whatsapp', [SettingsController::class, 'updateWhatsApp']);
        Route::put('/settings/domain', [SettingsController::class, 'updateDomain']);

        // Shipping options CRUD
        Route::get('/shipping', [AdminShippingController::class, 'index']);
        Route::post('/shipping', [AdminShippingController::class, 'store']);
        Route::put('/shipping/{id}', [AdminShippingController::class, 'update']);
        Route::delete('/shipping/{id}', [AdminShippingController::class, 'destroy']);
        Route::put('/settings/shipping-zip', [AdminShippingController::class, 'updateStoreZip']);
    });

// ========================================================================
// PLATFORM ADMIN ROUTES
// ========================================================================
Route::prefix('platform')
    ->middleware(['auth:sanctum', 'role:platform_admin'])
    ->group(function () {

        // List all stores
        Route::get('/stores', function () {
            $stores = \App\Models\Store::with(['theme', 'primaryDomain', 'owner'])
                ->withCount(['products', 'orders'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return response()->json(
                \App\Http\Resources\StoreResource::collection($stores)->response()->getData(true)
            );
        });

        // Create a new store + owner
        Route::post('/stores', function (\Illuminate\Http\Request $request) {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'slug' => 'required|string|max:255|unique:stores,slug|alpha_dash',
                'email' => 'nullable|email|max:255',
                'owner_name' => 'required|string|max:255',
                'owner_email' => 'required|email|unique:users,email',
                'owner_password' => 'required|string|min:6',
            ]);

            $store = \App\Models\Store::create([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'email' => $validated['email'] ?? null,
                'status' => 'active',
            ]);

            // Create default theme
            $store->theme()->create([]);

            // Create store owner
            \App\Models\User::create([
                'name' => $validated['owner_name'],
                'email' => $validated['owner_email'],
                'password' => $validated['owner_password'],
                'store_id' => $store->id,
                'role' => 'store_owner',
            ]);

            $store->load(['theme', 'owner']);
            $store->loadCount(['products', 'orders']);

            return response()->json([
                'message' => 'Loja criada com sucesso.',
                'data' => new \App\Http\Resources\StoreResource($store),
            ], 201);
        });

        // Update a store
        Route::patch('/stores/{id}', function (\Illuminate\Http\Request $request, int $id) {
            $store = \App\Models\Store::findOrFail($id);
            $store->update($request->only(['status', 'name']));

            $store->load(['theme', 'owner']);
            $store->loadCount(['products', 'orders']);

            return response()->json([
                'message' => 'Loja atualizada.',
                'data' => new \App\Http\Resources\StoreResource($store),
            ]);
        });

        // Delete a store
        Route::delete('/stores/{id}', function (int $id) {
            $store = \App\Models\Store::findOrFail($id);

            // Delete related records
            $store->users()->delete();
            $store->theme()->delete();
            $store->domains()->delete();
            $store->delete();

            return response()->json([
                'message' => 'Loja excluída com sucesso.',
            ]);
        });
    });
