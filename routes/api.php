<?php

use App\Http\Controllers\Api\V1\Public\Auth\AuthenticatedPublicUserController;
use App\Http\Controllers\Api\V1\Public\Auth\PublicAuthController;
use App\Http\Controllers\Api\V1\Public\Households\PublicHouseholdController;
use App\Http\Controllers\Api\V1\Public\Locations\PublicLocationController;
use App\Http\Controllers\Api\V1\Public\Meters\PublicMeterController;
use App\Http\Controllers\Api\V1\Public\Payments\PublicReportPaymentController;
use App\Http\Controllers\Api\V1\Public\Profile\PublicProfileController;
use App\Http\Controllers\Api\V1\Public\ReparationCases\PublicReparationCaseController;
use App\Http\Controllers\Api\V1\Public\Reports\PublicIncidentReportController;
use App\Http\Controllers\Api\V1\Public\Signals\PublicSignalTypeController;
use App\Http\Controllers\Api\V1\Public\Subscriptions\PublicUpSubscriptionController;
use App\Http\Controllers\Api\V1\Public\Subscriptions\PublicUpSubscriptionPaymentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/public')->group(function (): void {
    Route::get('locations', [PublicLocationController::class, 'index']);
    Route::get('signal-types', [PublicSignalTypeController::class, 'index']);

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
    });
});
