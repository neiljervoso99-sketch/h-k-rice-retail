
<?php

use App\Http\Controllers\AddressLookupController;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\BundleController;
use App\Http\Controllers\Admin\BundleController as AdminBundleController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeliveryProofController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\ManualTransactionController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProductReviewController as AdminProductReviewController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ProfitPricingController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Customer\FavoriteController;
use App\Http\Controllers\Customer\ProductReviewController as CustomerProductReviewController;
use App\Http\Controllers\Customer\ProfileController as CustomerProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Rider\OrderController as RiderOrderController;
use App\Http\Controllers\Rider\ProfileController as RiderProfileController;
use App\Http\Middleware\AuditAdminActions;
use Illuminate\Support\Facades\Route;

// Store homepage
Route::get('/', [ProductController::class, 'index'])
    ->name('home');

Route::post('/assistant/message', AssistantController::class)
    ->middleware(['auth', 'admin', 'throttle:20,1'])
    ->name('assistant.message');

Route::get('/bundles', [BundleController::class, 'index'])
    ->name('bundles.index');

Route::middleware(['auth', 'customer'])->post('/bundles/{bundle}/add-to-cart', [BundleController::class, 'addToCart'])
    ->whereNumber('bundle')
    ->name('bundles.add-to-cart');

// Guest login and registration
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.submit');

    Route::get('/register', [RegisterController::class, 'show'])
        ->name('register');

    Route::post('/register', [RegisterController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('register.submit');
});

// Logout
Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');

    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])
        ->name('notifications.read-all');

    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])
        ->whereUuid('notification')
        ->name('notifications.read');
});

// Customer cart
Route::get('/cart', [CartController::class, 'index'])
    ->name('cart.index');

Route::post('/cart/add/{product}', [CartController::class, 'add'])
    ->name('cart.add');

Route::patch('/cart/update/{product}', [CartController::class, 'update'])
    ->name('cart.update');

Route::delete('/cart/remove/{product}', [CartController::class, 'remove'])
    ->name('cart.remove');

// Customer checkout and confirmation
Route::middleware(['auth', 'customer'])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'create'])
        ->name('checkout.create');

    Route::post('/checkout', [CheckoutController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('checkout.store');

    Route::get('/orders/{order}/confirmation', [
        CheckoutController::class,
        'confirmation',
    ])
        ->whereNumber('order')
        ->name('checkout.confirmation');

    Route::post('/address-lookup', [AddressLookupController::class, 'lookup'])
        ->middleware('throttle:20,1')
        ->name('address.lookup');

    Route::post('/address-suggestions', [AddressLookupController::class, 'suggestions'])
        ->middleware('throttle:30,1')
        ->name('address.suggestions');
});

// Admin and staff dashboard
Route::middleware(['auth', 'admin-dashboard', AuditAdminActions::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/manual-transactions', [
            ManualTransactionController::class,
            'index',
        ])->name('manual-transactions.index');

        Route::get('/manual-transactions/create', [
            ManualTransactionController::class,
            'create',
        ])->name('manual-transactions.create');

        Route::post('/manual-transactions', [
            ManualTransactionController::class,
            'store',
        ])->name('manual-transactions.store');

        Route::get('/profile', [ProfileController::class, 'edit'])
            ->name('profile.edit');

        Route::patch('/profile', [ProfileController::class, 'update'])
            ->name('profile.update');

        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])
            ->name('profile.password.update');

        Route::get('/manual-transactions/{order}/receipt', [
            ManualTransactionController::class,
            'receipt',
        ])->whereNumber('order')->name('manual-transactions.receipt');
    });

// Admin-only routes
Route::middleware(['auth', 'admin', AuditAdminActions::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/users', [UserManagementController::class, 'index'])
            ->name('users.index');

        Route::get('/reviews', [AdminProductReviewController::class, 'index'])
            ->name('reviews.index');

        Route::get('/bundles', [AdminBundleController::class, 'index'])
            ->name('bundles.index');

        Route::post('/bundles', [AdminBundleController::class, 'store'])
            ->name('bundles.store');

        Route::patch('/reviews/{review}/approve', [AdminProductReviewController::class, 'approve'])
            ->whereNumber('review')
            ->name('reviews.approve');

        Route::post('/users', [UserManagementController::class, 'store'])
            ->name('users.store');

        Route::patch('/users/{user}/role', [
            UserManagementController::class,
            'updateRole',
        ])
            ->whereNumber('user')
            ->name('users.role.update');

        Route::get('/products', [AdminProductController::class, 'index'])
            ->name('products.index');

        Route::get('/products/create', [AdminProductController::class, 'create'])
            ->name('products.create');

        Route::get('/products/{product}/edit', [
            AdminProductController::class,
            'edit',
        ])->name('products.edit');

        Route::post('/products', [AdminProductController::class, 'store'])
            ->name('products.store');

        // Products
        Route::post('/products/bulk', [
            AdminProductController::class,
            'bulk',
        ])->name('products.bulk');

        Route::put('/products/{product}/qr-code', [
            AdminProductController::class,
            'updateQrCode',
        ])->name('products.qr-code.update');

        Route::patch('/products/{product}', [
            AdminProductController::class,
            'update',
        ])->name('products.update');

        Route::delete('/products/{product}', [
            AdminProductController::class,
            'destroy',
        ])->name('products.destroy');

        // Inventory
        Route::get('/inventory', [InventoryController::class, 'index'])
            ->name('inventory.index');

        Route::post('/inventory/stock-in', [
            InventoryController::class,
            'stockIn',
        ])->name('inventory.stock-in');

        Route::get('/inventory/qr-lookup', [
            InventoryController::class,
            'qrLookup',
        ])->name('inventory.qr-lookup');

        Route::post('/inventory/adjustment', [
            InventoryController::class,
            'adjustment',
        ])->name('inventory.adjustment');

        Route::get('/inventory/export', [InventoryController::class, 'export'])
            ->name('inventory.export');

        // Expenses
        Route::get('/expenses', [ExpenseController::class, 'index'])
            ->name('expenses.index');

        Route::post('/expenses', [ExpenseController::class, 'store'])
            ->name('expenses.store');

        // Profit and pricing
        Route::get('/profit-pricing', [
            ProfitPricingController::class,
            'index',
        ])->name('profit-pricing.index');

        // Reports
        Route::get('/reports', [
            ReportController::class,
            'index',
        ])->name('reports.index');

        Route::get('/reports/export', [
            ReportController::class,
            'export',
        ])->name('reports.export');

        Route::get('/reports/export/excel', [
            ReportController::class,
            'exportExcel',
        ])->name('reports.export.excel');

        Route::get('/reports/export/pdf', [
            ReportController::class,
            'exportPdf',
        ])->name('reports.export.pdf');

        // Orders
        Route::get('/orders', [OrderController::class, 'index'])
            ->name('orders.index');

        Route::get('/orders/{order}', [OrderController::class, 'show'])
            ->whereNumber('order')
            ->name('orders.show');

        Route::post('/orders/{order}/accept', [
            OrderController::class,
            'accept',
        ])
            ->whereNumber('order')
            ->name('orders.accept');

        Route::post('/orders/{order}/reject', [
            OrderController::class,
            'cancel',
        ])
            ->whereNumber('order')
            ->name('orders.reject');

        Route::post('/orders/{order}/complete', [
            OrderController::class,
            'complete',
        ])
            ->whereNumber('order')
            ->name('orders.complete');

        // Assign rider to an accepted order
        Route::post('/orders/{order}/assign-rider', [
            OrderController::class,
            'assignRider',
        ])
            ->whereNumber('order')
            ->name('orders.assign-rider');

        // Admin delivery proof
        Route::get('/orders/{order}/delivery-proof', [
            DeliveryProofController::class,
            '__invoke',
        ])
            ->whereNumber('order')
            ->name('orders.delivery-proof');

        // Payment proof
        Route::get('/orders/{order}/payments/{payment}/proof', [
            OrderController::class,
            'paymentProof',
        ])
            ->whereNumber('order')
            ->whereNumber('payment')
            ->name('orders.payments.proof');

        Route::post('/orders/{order}/payments/{payment}/verify', [
            OrderController::class,
            'verifyPayment',
        ])
            ->whereNumber('order')
            ->whereNumber('payment')
            ->name('orders.payments.verify');

        Route::post('/orders/{order}/payments/{payment}/reject', [
            OrderController::class,
            'rejectPayment',
        ])
            ->whereNumber('order')
            ->whereNumber('payment')
            ->name('orders.payments.reject');
    });

// Rider routes
Route::middleware(['auth', 'rider'])
    ->prefix('rider')
    ->name('rider.')
    ->group(function () {
        Route::get('/', [RiderOrderController::class, 'index'])
            ->name('dashboard');

        Route::get('/orders/{order}', [
            RiderOrderController::class,
            'show',
        ])
            ->whereNumber('order')
            ->name('orders.show');

        Route::post('/orders/{order}/proof', [
            RiderOrderController::class,
            'uploadProof',
        ])
            ->whereNumber('order')
            ->name('orders.proof.upload');

        Route::get('/orders/{order}/proof/view', [
            RiderOrderController::class,
            'viewProof',
        ])
            ->whereNumber('order')
            ->name('orders.proof.view');

        Route::post('/orders/{order}/complete', [
            RiderOrderController::class,
            'complete',
        ])
            ->whereNumber('order')
            ->name('orders.complete');

        // Rider profile
        Route::get('/profile', [RiderProfileController::class, 'edit'])
            ->name('profile.edit');

        Route::patch('/profile', [RiderProfileController::class, 'update'])
            ->name('profile.update');
    });

// Customer profile
Route::middleware(['auth', 'customer'])->group(function () {
    Route::get('/profile', [
        CustomerProfileController::class,
        'edit',
    ])->name('customer.profile.edit');

    Route::patch('/profile', [
        CustomerProfileController::class,
        'update',
    ])->name('customer.profile.update');
});
// Customer orders and tracking
Route::middleware(['auth', 'customer'])->group(function () {
    Route::get('/favorites', [FavoriteController::class, 'index'])
        ->name('customer.favorites.index');

    Route::post('/favorites/{product}', [FavoriteController::class, 'store'])
        ->whereNumber('product')
        ->name('customer.favorites.store');

    Route::delete('/favorites/{product}', [FavoriteController::class, 'destroy'])
        ->whereNumber('product')
        ->name('customer.favorites.destroy');

    Route::get('/my-orders', [
        CustomerOrderController::class,
        'index',
    ])->name('customer.orders.index');

    Route::get('/my-orders/{order}', [
        CustomerOrderController::class,
        'show',
    ])->whereNumber('order')->name('customer.orders.show');

    Route::post('/my-orders/{order}/reorder', [
        CustomerOrderController::class,
        'reorder',
    ])->whereNumber('order')->name('customer.orders.reorder');

    Route::post('/my-orders/{order}/reviews/{product}', [
        CustomerProductReviewController::class,
        'store',
    ])->whereNumber('order')->whereNumber('product')->name('customer.orders.reviews.store');
});
