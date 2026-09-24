<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\MarketController as AdminMarketController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\SmsCreditController as AdminSmsCreditController;
use App\Http\Controllers\Admin\PlanController as AdminPlanController;
use App\Http\Controllers\Admin\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\SubscriptionExpiredController;
use App\Http\Controllers\MarketOwner\DashboardController as MarketOwnerDashboard;
use App\Http\Controllers\MarketOwner\ShopController as MarketOwnerShopController;
use App\Http\Controllers\MarketOwner\StaffController;
use App\Http\Controllers\MarketOwner\ShopOwnerController;
use App\Http\Controllers\MarketOwner\InvoiceController as MarketOwnerInvoiceController;
use App\Http\Controllers\MarketOwner\PaymentController as MarketOwnerPaymentController;
use App\Http\Controllers\MarketOwner\ComplaintController as MarketOwnerComplaintController;
use App\Http\Controllers\MarketOwner\NoticeController as MarketOwnerNoticeController;
use App\Http\Controllers\MarketOwner\ReportController;
use App\Http\Controllers\MarketOwner\SettingsController;
use App\Http\Controllers\Collector\DashboardController as CollectorDashboard;
use App\Http\Controllers\Collector\ShopController as CollectorShopController;
use App\Http\Controllers\Collector\PaymentController as CollectorPaymentController;
use App\Http\Controllers\ShopOwner\DashboardController as ShopOwnerDashboard;
use App\Http\Controllers\ShopOwner\InvoiceController as ShopOwnerInvoiceController;
use App\Http\Controllers\ShopOwner\ComplaintController as ShopOwnerComplaintController;
use Illuminate\Support\Facades\Route;

// Welcome page - redirect to login or dashboard
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Language switch
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

// Dashboard redirect based on role
Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user->isSuperAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    if ($user->isMarketOwner()) {
        return redirect()->route('market-owner.dashboard');
    }

    if ($user->isCollector()) {
        return redirect()->route('collector.dashboard');
    }

    if ($user->isShopOwner()) {
        return redirect()->route('shop-owner.dashboard');
    }

    return view('dashboard');
})->middleware(['auth', 'verified', 'market.active'])->name('dashboard');

// Shown to market users whose subscription has lapsed
Route::get('/subscription-expired', SubscriptionExpiredController::class)
    ->middleware('auth')
    ->name('subscription.expired');

// Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Market Owner Routes
Route::middleware(['auth', 'verified', 'market.active', 'role:market_owner'])
    ->prefix('market-owner')
    ->name('market-owner.')
    ->group(function () {
        Route::get('/dashboard', [MarketOwnerDashboard::class, 'index'])->name('dashboard');

        // Shops
        Route::resource('shops', MarketOwnerShopController::class);
        Route::post('shops/{shop}/assign-collector', [MarketOwnerShopController::class, 'assignCollector'])->name('shops.assign-collector');
        Route::post('shops/{shop}/assign-owner', [MarketOwnerShopController::class, 'assignOwner'])->name('shops.assign-owner');

        // Shop owners (tenant logins)
        Route::resource('shop-owners', ShopOwnerController::class)->except(['show'])->parameters(['shop-owners' => 'shopOwner']);
        Route::post('shop-owners/{shopOwner}/toggle-status', [ShopOwnerController::class, 'toggleStatus'])->name('shop-owners.toggle-status');

        // Staff
        Route::resource('staff', StaffController::class);
        Route::post('staff/{user}/toggle-status', [StaffController::class, 'toggleStatus'])->name('staff.toggle-status');

        // Invoices
        Route::resource('invoices', MarketOwnerInvoiceController::class);
        Route::post('invoices/generate-bulk', [MarketOwnerInvoiceController::class, 'generateBulk'])->name('invoices.generate-bulk');
        Route::post('invoices/{invoice}/send-reminder', [MarketOwnerInvoiceController::class, 'sendReminder'])->name('invoices.send-reminder');
        Route::get('invoices/{invoice}/pdf', [MarketOwnerInvoiceController::class, 'downloadPdf'])->name('invoices.pdf');

        // Payments
        Route::resource('payments', MarketOwnerPaymentController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
        Route::get('payments/{payment}/receipt', [MarketOwnerPaymentController::class, 'receipt'])->name('payments.receipt');

        // Complaints
        Route::resource('complaints', MarketOwnerComplaintController::class)->only(['index', 'show', 'update']);
        Route::post('complaints/{complaint}/assign', [MarketOwnerComplaintController::class, 'assign'])->name('complaints.assign');
        Route::post('complaints/{complaint}/resolve', [MarketOwnerComplaintController::class, 'resolve'])->name('complaints.resolve');

        // Notices
        Route::resource('notices', MarketOwnerNoticeController::class);

        // Reports
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/collection', [ReportController::class, 'monthlyCollection'])->name('reports.collection');
        Route::get('reports/due', [ReportController::class, 'dueReport'])->name('reports.due');
        Route::get('reports/shops', [ReportController::class, 'shopReport'])->name('reports.shops');
        Route::get('reports/monthly', [ReportController::class, 'monthlySummary'])->name('reports.monthly');
        Route::get('reports/staff', [ReportController::class, 'staffPerformance'])->name('reports.staff');
        Route::get('reports/invoices', [ReportController::class, 'invoiceReport'])->name('reports.invoices');
        Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');

        // Settings
        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::put('settings/sms', [SettingsController::class, 'updateSms'])->name('settings.sms');
        Route::put('settings/invoice', [SettingsController::class, 'updateInvoice'])->name('settings.invoice');
        Route::put('settings/permissions', [SettingsController::class, 'updatePermissions'])->name('settings.permissions');
        Route::post('settings/test-sms', [SettingsController::class, 'testSms'])->name('settings.test-sms');
        Route::get('settings/sms-credits', [SettingsController::class, 'smsCredits'])->name('settings.sms-credits');
        Route::get('settings/export', [SettingsController::class, 'export'])->name('settings.export');
        Route::get('settings/destroy', [SettingsController::class, 'destroy'])->name('settings.destroy');
    });

// Collector Routes
Route::middleware(['auth', 'verified', 'market.active', 'role:collector'])
    ->prefix('collector')
    ->name('collector.')
    ->group(function () {
        Route::get('/dashboard', [CollectorDashboard::class, 'index'])->name('dashboard');

        // Assigned Shops
        Route::get('shops', [CollectorShopController::class, 'index'])->name('shops.index');
        Route::get('shops/{shop}', [CollectorShopController::class, 'show'])->name('shops.show');
        Route::post('shops', [CollectorShopController::class, 'store'])->name('shops.store');

        // Payments
        Route::get('payments', [CollectorPaymentController::class, 'index'])->name('payments.index');
        Route::get('payments/create/{invoice?}', [CollectorPaymentController::class, 'create'])->name('payments.create');
        Route::post('payments', [CollectorPaymentController::class, 'store'])->name('payments.store');
        Route::get('payments/{payment}', [CollectorPaymentController::class, 'show'])->name('payments.show');
        Route::get('payments/{payment}/receipt', [CollectorPaymentController::class, 'receipt'])->name('payments.receipt');
    });

// Super Admin Routes
Route::middleware(['auth', 'verified', 'role:super_admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

        // Markets Management
        Route::resource('markets', AdminMarketController::class);

        // SMS Credits (prepaid balance sold to markets)
        Route::get('sms-credits', [AdminSmsCreditController::class, 'index'])->name('sms-credits.index');
        Route::get('markets/{market}/sms-credits', [AdminSmsCreditController::class, 'show'])->name('markets.sms-credits');
        Route::post('markets/{market}/sms-credits', [AdminSmsCreditController::class, 'store'])->name('markets.sms-credits.store');

        // Plans & Subscriptions
        Route::resource('plans', AdminPlanController::class)->except(['show']);
        Route::get('subscriptions', [AdminSubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::get('markets/{market}/subscription', [AdminSubscriptionController::class, 'show'])->name('markets.subscription');
        Route::post('markets/{market}/subscription', [AdminSubscriptionController::class, 'store'])->name('markets.subscription.store');
        Route::post('markets/{market}/subscription/trial', [AdminSubscriptionController::class, 'trial'])->name('markets.subscription.trial');
        Route::post('markets/{market}/subscription/cancel', [AdminSubscriptionController::class, 'cancel'])->name('markets.subscription.cancel');

        // Users Management
        Route::resource('users', AdminUserController::class);
        Route::post('users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');
    });

// Shop Owner Routes
Route::middleware(['auth', 'verified', 'market.active', 'role:shop_owner'])
    ->prefix('shop-owner')
    ->name('shop-owner.')
    ->group(function () {
        Route::get('/dashboard', [ShopOwnerDashboard::class, 'index'])->name('dashboard');

        // Invoices
        Route::get('invoices', [ShopOwnerInvoiceController::class, 'index'])->name('invoices.index');
        Route::get('invoices/{invoice}', [ShopOwnerInvoiceController::class, 'show'])->name('invoices.show');
        Route::get('invoices/{invoice}/pdf', [ShopOwnerInvoiceController::class, 'downloadPdf'])->name('invoices.pdf');

        // Complaints
        Route::get('complaints', [ShopOwnerComplaintController::class, 'index'])->name('complaints.index');
        Route::get('complaints/create', [ShopOwnerComplaintController::class, 'create'])->name('complaints.create');
        Route::post('complaints', [ShopOwnerComplaintController::class, 'store'])->name('complaints.store');
        Route::get('complaints/{complaint}', [ShopOwnerComplaintController::class, 'show'])->name('complaints.show');
        Route::post('complaints/{complaint}/feedback', [ShopOwnerComplaintController::class, 'feedback'])->name('complaints.feedback');

        // Notices
        Route::get('notices', [ShopOwnerDashboard::class, 'notices'])->name('notices.index');
    });

require __DIR__.'/auth.php';
