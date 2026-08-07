<?php

use App\Http\Controllers\Api\V1\Notifications\DeviceTokenController;
use App\Http\Controllers\Api\V1\Notifications\UserNotificationController;
use App\Http\Controllers\Api\V1\Partner\Auth\AuthenticatedPartnerUserController;
use App\Http\Controllers\Api\V1\Partner\Auth\PartnerAuthController;
use App\Http\Controllers\Api\V1\Partner\Auth\PartnerPasswordResetController;
use App\Http\Controllers\Api\V1\Partner\Discounts\PartnerDiscountCardController;
use App\Http\Controllers\Api\V1\Partner\Discounts\PartnerDiscountOfferController;
use App\Http\Controllers\Api\V1\Partner\Discounts\PartnerDiscountTransactionController;
use App\Http\Controllers\Api\V1\Partner\Profile\PartnerPasswordController;
use App\Http\Controllers\Api\V1\Partner\Profile\PartnerProfileController;
use App\Http\Controllers\Api\V1\Public\AppUpdateController;
use App\Http\Controllers\Api\V1\Public\Auth\AuthenticatedPublicUserController;
use App\Http\Controllers\Api\V1\Public\Auth\PublicAuthController;
use App\Http\Controllers\Api\V1\Public\Catalogs\PublicCatalogController;
use App\Http\Controllers\Api\V1\Public\Discounts\PublicDiscountCardController;
use App\Http\Controllers\Api\V1\Public\Households\PublicHouseholdController;
use App\Http\Controllers\Api\V1\Public\Locations\PublicLocationController;
use App\Http\Controllers\Api\V1\Public\Meters\PublicMeterController;
use App\Http\Controllers\Api\V1\Public\Payments\FineoPayCallbackController;
use App\Http\Controllers\Api\V1\Public\Payments\PublicIncidentReportPaymentSessionController;
use App\Http\Controllers\Api\V1\Public\Payments\PublicReportPaymentController;
use App\Http\Controllers\Api\V1\Public\PrivilegeCards\PrivilegeCardFineoPayCallbackController;
use App\Http\Controllers\Api\V1\Public\PrivilegeCards\PrivilegeCardWalletPassController;
use App\Http\Controllers\Api\V1\Public\PrivilegeCards\PublicPrivilegeCardController;
use App\Http\Controllers\Api\V1\Public\Profile\PublicProfileController;
use App\Http\Controllers\Api\V1\Public\PurchaseReceipts\PublicPurchaseReceiptController;
use App\Http\Controllers\Api\V1\Public\ReparationCases\PublicReparationCaseController;
use App\Http\Controllers\Api\V1\Public\Reports\PublicIncidentReportController;
use App\Http\Controllers\Api\V1\Public\Rex\PublicRexFeedbackController;
use App\Http\Controllers\Api\V1\Public\Signals\PublicSignalTypeController;
use App\Http\Controllers\Api\V1\Public\Subscriptions\PublicUpSubscriptionController;
use App\Http\Controllers\Api\V1\Public\Subscriptions\PublicUpSubscriptionPaymentController;
use App\Http\Controllers\Api\V1\Public\UserTypes\PublicUserTypeController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/public')->group(function (): void {
    Route::post('payments/fineopay/callback', FineoPayCallbackController::class)
        ->name('api.public.payments.fineopay.callback');
    Route::post('privilege-card-payments/fineopay/callback', PrivilegeCardFineoPayCallbackController::class)
        ->name('api.public.privilege-card-payments.fineopay.callback');
    Route::get('privilege-cards/{card}/pass.pkpass', [PrivilegeCardWalletPassController::class, 'downloadApplePass'])
        ->name('api.public.privilege-cards.pass.apple');

    Route::get('applications', [PublicCatalogController::class, 'applications']);
    Route::get('application-types', [PublicCatalogController::class, 'applicationTypes']);
    Route::get('organization-types', [PublicCatalogController::class, 'organizationTypes']);
    Route::get('organizations', [PublicCatalogController::class, 'organizations']);
    Route::get('countries', [PublicCatalogController::class, 'countries']);
    Route::get('cities', [PublicCatalogController::class, 'cities']);
    Route::get('business-sectors', [PublicCatalogController::class, 'businessSectors']);
    Route::get('service-categories', [PublicCatalogController::class, 'serviceCategories']);
    Route::get('locations', [PublicLocationController::class, 'index']);
    Route::get('communes', [PublicLocationController::class, 'communes']);
    Route::get('signal-types', [PublicSignalTypeController::class, 'index']);
    Route::get('user-types', [PublicUserTypeController::class, 'index']);
    Route::get('app-update', [AppUpdateController::class, 'show']);

    Route::prefix('auth')->group(function (): void {
        Route::post('request-otp', [PublicAuthController::class, 'requestOtp'])
            ->middleware('throttle:public-auth-otp');

        Route::post('verify-otp', [PublicAuthController::class, 'verifyOtp'])
            ->middleware('throttle:public-auth-otp');

        Route::post('register', [PublicAuthController::class, 'register'])
            ->middleware('throttle:public-auth-register');

        Route::post('login', [PublicAuthController::class, 'login'])
            ->middleware('throttle:public-auth-login');

        Route::post('forgot-password/request-otp', [PublicAuthController::class, 'requestPasswordResetOtp'])
            ->middleware('throttle:public-auth-otp');

        Route::post('forgot-password/verify-otp', [PublicAuthController::class, 'verifyPasswordResetOtp'])
            ->middleware('throttle:public-auth-otp');

        Route::post('forgot-password/reset-password', [PublicAuthController::class, 'resetPassword'])
            ->middleware('throttle:public-auth-register');
    });

    Route::middleware('auth:public_api')->group(function (): void {
        Route::get('me', AuthenticatedPublicUserController::class);
        Route::get('profile', [PublicProfileController::class, 'show']);
        Route::put('profile', [PublicProfileController::class, 'update']);
        Route::post('profile/photo', [PublicProfileController::class, 'updatePhoto']);
        Route::put('profile/password', [PublicProfileController::class, 'updatePassword']);
        Route::post('push-tokens', [DeviceTokenController::class, 'storePublic']);
        Route::delete('push-tokens', [DeviceTokenController::class, 'destroyPublic']);
        Route::get('notifications', [UserNotificationController::class, 'publicIndex']);
        Route::post('notifications/read-all', [UserNotificationController::class, 'publicMarkAllAsRead']);
        Route::post('notifications/{notification}/read', [UserNotificationController::class, 'publicMarkAsRead']);

        Route::get('meters', [PublicMeterController::class, 'index']);
        Route::post('meters', [PublicMeterController::class, 'store']);
        Route::get('meters/{meter}', [PublicMeterController::class, 'show']);
        Route::post('meters/{meter}', [PublicMeterController::class, 'update']);
        Route::patch('meters/{meter}', [PublicMeterController::class, 'update']);
        Route::delete('meters/{meter}', [PublicMeterController::class, 'destroy']);

        Route::get('purchase-receipts', [PublicPurchaseReceiptController::class, 'index']);
        Route::post('purchase-receipts', [PublicPurchaseReceiptController::class, 'store']);
        Route::get('purchase-receipts/{purchaseReceipt}', [PublicPurchaseReceiptController::class, 'show']);
        Route::patch('purchase-receipts/{purchaseReceipt}', [PublicPurchaseReceiptController::class, 'update']);
        Route::delete('purchase-receipts/{purchaseReceipt}', [PublicPurchaseReceiptController::class, 'destroy']);

        Route::post('households', [PublicHouseholdController::class, 'store']);
        Route::get('households/me', [PublicHouseholdController::class, 'showMine']);
        Route::delete('households/{household}', [PublicHouseholdController::class, 'destroy']);
        Route::get('households/invitations/pending', [PublicHouseholdController::class, 'pendingInvitations']);
        Route::post('households/{household}/invitations', [PublicHouseholdController::class, 'invite']);
        Route::delete('households/{household}/members/{member}', [PublicHouseholdController::class, 'removeMember']);
        Route::delete('households/invitations/{invitation}', [PublicHouseholdController::class, 'cancelInvitation']);
        Route::post('households/invitations/accept', [PublicHouseholdController::class, 'accept']);
        Route::post('households/invitations/decline', [PublicHouseholdController::class, 'decline']);

        Route::get('reports', [PublicIncidentReportController::class, 'index']);
        Route::get('reports/monthly-category-stats', [PublicIncidentReportController::class, 'monthlyCategoryStats']);
        Route::get('reports/monthly-category-stats/categories/{application}/reports', [PublicIncidentReportController::class, 'monthlyCategoryReports']);
        Route::post('reports', [PublicIncidentReportController::class, 'store']);
        Route::get('reports/{report}', [PublicIncidentReportController::class, 'show']);
        Route::post('reports/{report}/confirm-resolution', [PublicIncidentReportController::class, 'confirmResolution']);
        Route::post('reports/{report}/damages', [PublicIncidentReportController::class, 'storeDamage']);
        Route::patch('reports/{report}/damages', [PublicIncidentReportController::class, 'updateDamage']);
        Route::get('damages', [PublicIncidentReportController::class, 'damages']);
        Route::post('damages', [PublicIncidentReportController::class, 'storeDamageFromBody']);
        Route::patch('damages/{report}', [PublicIncidentReportController::class, 'updateDamage']);
        Route::get('reparation-cases', [PublicReparationCaseController::class, 'index']);
        Route::get('rex-feedbacks', [PublicRexFeedbackController::class, 'index']);
        Route::post('rex-feedbacks', [PublicRexFeedbackController::class, 'store']);
        Route::get('payments', [PublicReportPaymentController::class, 'index']);
        Route::get('payment-sessions/{syncRef}', [PublicIncidentReportPaymentSessionController::class, 'show']);
        Route::post('reports/{report}/payments', [PublicReportPaymentController::class, 'store']);
        Route::post('payments/{payment}/confirm', [PublicReportPaymentController::class, 'confirm']);
        Route::get('payments/{payment}/receipt', [PublicReportPaymentController::class, 'receipt']);

        Route::get('subscriptions', [PublicUpSubscriptionController::class, 'index']);
        Route::get('subscription', [PublicUpSubscriptionController::class, 'show']);
        Route::post('subscription', [PublicUpSubscriptionController::class, 'store']);
        Route::get('subscription/payments', [PublicUpSubscriptionPaymentController::class, 'index']);
        Route::post('subscription/payments', [PublicUpSubscriptionPaymentController::class, 'store']);
        Route::post('subscription/payments/{payment}/confirm', [PublicUpSubscriptionPaymentController::class, 'confirm']);
        Route::get('discount-card', [PublicDiscountCardController::class, 'show']);
        Route::get('privilege-cards', [PublicPrivilegeCardController::class, 'index']);
        Route::get('privilege-card', [PublicPrivilegeCardController::class, 'mine']);
        Route::get('privilege-cards/{card}/wallet-pass', [PrivilegeCardWalletPassController::class, 'issueUrl']);
        Route::post('privilege-cards/{type}/payments', [PublicPrivilegeCardController::class, 'purchase']);
        Route::get('privilege-card-payment-sessions', [PublicPrivilegeCardController::class, 'sessions']);
        Route::get('privilege-card-payment-sessions/{syncRef}', [PublicPrivilegeCardController::class, 'session']);
    });
});

Route::prefix('v1/partner')->group(function (): void {
    Route::prefix('auth')->group(function (): void {
        Route::post('login', [PartnerAuthController::class, 'login']);
        Route::post('forgot-password/request-otp', [PartnerPasswordResetController::class, 'requestOtp'])
            ->middleware('throttle:partner-auth-otp');
        Route::post('forgot-password/verify-otp', [PartnerPasswordResetController::class, 'verifyOtp'])
            ->middleware('throttle:partner-auth-otp');
        Route::post('forgot-password/reset-password', [PartnerPasswordResetController::class, 'resetPassword'])
            ->middleware('throttle:partner-auth-password-reset');
    });

    Route::middleware(['auth:partner_api', 'partner_user'])->group(function (): void {
        Route::get('me', AuthenticatedPartnerUserController::class);
        Route::post('auth/logout', [PartnerAuthController::class, 'logout']);
        Route::put('profile', [PartnerProfileController::class, 'update']);
        Route::put('profile/password', [PartnerPasswordController::class, 'update']);
        Route::post('push-tokens', [DeviceTokenController::class, 'storePartner']);
        Route::delete('push-tokens', [DeviceTokenController::class, 'destroyPartner']);
        Route::get('notifications', [UserNotificationController::class, 'partnerIndex']);
        Route::post('notifications/read-all', [UserNotificationController::class, 'partnerMarkAllAsRead']);
        Route::post('notifications/{notification}/read', [UserNotificationController::class, 'partnerMarkAsRead']);

        Route::get('discount-offers', [PartnerDiscountOfferController::class, 'index'])
            ->middleware('partner_permission:PARTNER_DISCOUNT_HISTORY_VIEW');
        Route::post('discount-offers', [PartnerDiscountOfferController::class, 'store'])
            ->middleware('partner_permission:PARTNER_DISCOUNT_OFFERS_MANAGE');
        Route::put('discount-offers/{offer}', [PartnerDiscountOfferController::class, 'update'])
            ->middleware('partner_permission:PARTNER_DISCOUNT_OFFERS_MANAGE');
        Route::patch('discount-offers/{offer}/toggle-status', [PartnerDiscountOfferController::class, 'toggleStatus'])
            ->middleware('partner_permission:PARTNER_DISCOUNT_OFFERS_MANAGE');
        Route::post('discount-cards/verify', [PartnerDiscountCardController::class, 'verify'])
            ->middleware('partner_permission:PARTNER_DISCOUNT_SCAN');
        Route::get('discount-transactions', [PartnerDiscountTransactionController::class, 'index'])
            ->middleware('partner_permission:PARTNER_DISCOUNT_HISTORY_VIEW');
        Route::post('discount-transactions', [PartnerDiscountTransactionController::class, 'store'])
            ->middleware('partner_permission:PARTNER_DISCOUNT_APPLY');
        Route::get('mobile/history', [PartnerDiscountTransactionController::class, 'mobileHistory'])
            ->middleware('partner_permission:PARTNER_DISCOUNT_HISTORY_VIEW');
        Route::get('mobile/stats', [PartnerDiscountTransactionController::class, 'mobileStats'])
            ->middleware('partner_permission:PARTNER_DISCOUNT_HISTORY_VIEW');
    });
});
