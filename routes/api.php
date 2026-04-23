<?php

use App\Http\Controllers\Api\V1\Partner\Auth\AuthenticatedPartnerUserController;
use App\Http\Controllers\Api\V1\Partner\Auth\PartnerAuthController;
use App\Http\Controllers\Api\V1\Partner\Auth\PartnerPasswordResetController;
use App\Http\Controllers\Api\V1\Partner\Discounts\PartnerDiscountCardController;
use App\Http\Controllers\Api\V1\Partner\Discounts\PartnerDiscountOfferController;
use App\Http\Controllers\Api\V1\Partner\Discounts\PartnerDiscountTransactionController;
use App\Http\Controllers\Api\V1\Partner\Profile\PartnerPasswordController;
use App\Http\Controllers\Api\V1\Partner\Profile\PartnerProfileController;
use App\Http\Controllers\Api\V1\Public\Auth\AuthenticatedPublicUserController;
use App\Http\Controllers\Api\V1\Public\Auth\PublicAuthController;
use App\Http\Controllers\Api\V1\Public\Discounts\PublicDiscountCardController;
use App\Http\Controllers\Api\V1\Public\Households\PublicHouseholdController;
use App\Http\Controllers\Api\V1\Public\Locations\PublicLocationController;
use App\Http\Controllers\Api\V1\Public\Meters\PublicMeterController;
use App\Http\Controllers\Api\V1\Public\Payments\PublicReportPaymentController;
use App\Http\Controllers\Api\V1\Public\Profile\PublicProfileController;
use App\Http\Controllers\Api\V1\Public\ReparationCases\PublicReparationCaseController;
use App\Http\Controllers\Api\V1\Public\Reports\PublicIncidentReportController;
use App\Http\Controllers\Api\V1\Public\Rex\PublicRexFeedbackController;
use App\Http\Controllers\Api\V1\Public\Signals\PublicSignalTypeController;
use App\Http\Controllers\Api\V1\Public\Subscriptions\PublicUpSubscriptionController;
use App\Http\Controllers\Api\V1\Public\Subscriptions\PublicUpSubscriptionPaymentController;
use App\Http\Controllers\Api\V1\Public\UserTypes\PublicUserTypeController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/public')->group(function (): void {
    Route::get('locations', [PublicLocationController::class, 'index']);
    Route::get('signal-types', [PublicSignalTypeController::class, 'index']);
    Route::get('user-types', [PublicUserTypeController::class, 'index']);

    Route::prefix('auth')->group(function (): void {
        Route::post('request-otp', [PublicAuthController::class, 'requestOtp'])
            ->middleware('throttle:public-auth-otp');

        Route::post('verify-otp', [PublicAuthController::class, 'verifyOtp'])
            ->middleware('throttle:public-auth-otp');

        Route::post('register', [PublicAuthController::class, 'register'])
            ->middleware('throttle:public-auth-register');

        Route::post('login', [PublicAuthController::class, 'login'])
            ->middleware('throttle:public-auth-login');
    });

    Route::middleware('auth:public_api')->group(function (): void {
        Route::get('me', AuthenticatedPublicUserController::class);
        Route::get('profile', [PublicProfileController::class, 'show']);
        Route::put('profile', [PublicProfileController::class, 'update']);

        Route::get('meters', [PublicMeterController::class, 'index']);
        Route::post('meters', [PublicMeterController::class, 'store']);
        Route::get('meters/{meter}', [PublicMeterController::class, 'show']);
        Route::patch('meters/{meter}', [PublicMeterController::class, 'update']);

        Route::post('households', [PublicHouseholdController::class, 'store']);
        Route::get('households/me', [PublicHouseholdController::class, 'showMine']);
        Route::get('households/invitations/pending', [PublicHouseholdController::class, 'pendingInvitations']);
        Route::post('households/{household}/invitations', [PublicHouseholdController::class, 'invite']);
        Route::post('households/invitations/accept', [PublicHouseholdController::class, 'accept']);
        Route::post('households/invitations/decline', [PublicHouseholdController::class, 'decline']);

        Route::get('reports', [PublicIncidentReportController::class, 'index']);
        Route::post('reports', [PublicIncidentReportController::class, 'store']);
        Route::get('reports/{report}', [PublicIncidentReportController::class, 'show']);
        Route::post('reports/{report}/confirm-resolution', [PublicIncidentReportController::class, 'confirmResolution']);
        Route::post('reports/{report}/damages', [PublicIncidentReportController::class, 'storeDamage']);
        Route::get('reparation-cases', [PublicReparationCaseController::class, 'index']);
        Route::get('rex-feedbacks', [PublicRexFeedbackController::class, 'index']);
        Route::post('rex-feedbacks', [PublicRexFeedbackController::class, 'store']);
        Route::get('payments', [PublicReportPaymentController::class, 'index']);
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
