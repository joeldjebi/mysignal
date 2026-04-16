<?php

use App\Http\Controllers\Web\Public\PublicPortalController;
use App\Http\Controllers\Web\Institution\AuthController as InstitutionAuthController;
use App\Http\Controllers\Web\Institution\ActivityLogController as InstitutionActivityLogController;
use App\Http\Controllers\Web\Institution\DashboardController as InstitutionDashboardController;
use App\Http\Controllers\Web\Institution\DamageController as InstitutionDamageController;
use App\Http\Controllers\Web\Institution\MeterController as InstitutionMeterController;
use App\Http\Controllers\Web\Institution\PermissionController as InstitutionPermissionController;
use App\Http\Controllers\Web\Institution\ProfileController as InstitutionProfileController;
use App\Http\Controllers\Web\Institution\ReporterUserController as InstitutionReporterUserController;
use App\Http\Controllers\Web\Institution\ReportController as InstitutionReportController;
use App\Http\Controllers\Web\Institution\RoleController as InstitutionRoleController;
use App\Http\Controllers\Web\Institution\SlaController as InstitutionSlaController;
use App\Http\Controllers\Web\Institution\SignalTypeController as InstitutionSignalTypeController;
use App\Http\Controllers\Web\Institution\StatisticController as InstitutionStatisticController;
use App\Http\Controllers\Web\Institution\UserController as InstitutionUserController;
use App\Http\Controllers\Web\SuperAdmin\CityController;
use App\Http\Controllers\Web\SuperAdmin\CommuneController;
use App\Http\Controllers\Web\SuperAdmin\CountryController;
use App\Http\Controllers\Web\SuperAdmin\BusinessSectorController;
use App\Http\Controllers\Web\SuperAdmin\FeatureController;
use App\Http\Controllers\Web\SuperAdmin\ActivityLogController as SuperAdminActivityLogController;
use App\Http\Controllers\Web\SuperAdmin\InstitutionAdminController;
use App\Http\Controllers\Web\SuperAdmin\InternalAccessController;
use App\Http\Controllers\Web\SuperAdmin\InternalHomeController;
use App\Http\Controllers\Web\SuperAdmin\LandingPageController;
use App\Http\Controllers\Web\SuperAdmin\ApplicationController;
use App\Http\Controllers\Web\SuperAdmin\OrganizationController;
use App\Http\Controllers\Web\SuperAdmin\OrganizationTypeSignalSlaController;
use App\Http\Controllers\Web\SuperAdmin\OrganizationTypeController;
use App\Http\Controllers\Web\SuperAdmin\PermissionController;
use App\Http\Controllers\Web\SuperAdmin\PaymentController;
use App\Http\Controllers\Web\SuperAdmin\PricingRuleController;
use App\Http\Controllers\Web\SuperAdmin\PublicIncidentReportController;
use App\Http\Controllers\Web\SuperAdmin\PublicUserController;
use App\Http\Controllers\Web\SuperAdmin\PublicUserTypeController;
use App\Http\Controllers\Web\SuperAdmin\ReparationCaseController;
use App\Http\Controllers\Web\SuperAdmin\RexFeedbackController;
use App\Http\Controllers\Web\SuperAdmin\RoleController;
use App\Http\Controllers\Web\SuperAdmin\SignalTypeController;
use App\Http\Controllers\Web\SuperAdmin\SubscriptionPlanController;
use App\Http\Controllers\Web\SuperAdmin\SystemUserController;
use App\Http\Controllers\Web\SuperAdmin\UpSubscriptionController;
use App\Http\Controllers\Web\SuperAdmin\AuthController as SuperAdminAuthController;
use App\Http\Controllers\Web\SuperAdmin\DashboardController as SuperAdminDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicPortalController::class, 'landing'])->name('public.landing');
Route::get('/dashboard', [PublicPortalController::class, 'dashboard'])->name('public.dashboard');
Route::redirect('/admin', '/sa/login');
Route::redirect('/admin/login', '/sa/login');
Route::redirect('/backoffice', '/backoffice/login');

Route::prefix('institution')->name('institution.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('login', [InstitutionAuthController::class, 'create'])->name('login');
        Route::post('login', [InstitutionAuthController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth', 'institution_admin'])->group(function (): void {
        Route::get('dashboard', InstitutionDashboardController::class)->name('dashboard');
        Route::get('profile', [InstitutionProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [InstitutionProfileController::class, 'update'])->name('profile.update');
        Route::get('meters', [InstitutionMeterController::class, 'index'])
            ->middleware('institution_feature:PUBLIC_METERS')
            ->name('meters.index');
        Route::get('meters/{meter}', [InstitutionMeterController::class, 'show'])
            ->middleware('institution_feature:PUBLIC_METERS')
            ->name('meters.show');
        Route::get('reports', [InstitutionReportController::class, 'index'])
            ->middleware('institution_feature:PUBLIC_REPORTS')
            ->name('reports.index');
        Route::get('damages', [InstitutionDamageController::class, 'index'])
            ->middleware('institution_feature:INSTITUTION_REPORT_DAMAGE_ACCESS')
            ->name('damages.index');
        Route::get('reports/{report}', [InstitutionReportController::class, 'show'])
            ->middleware('institution_feature:PUBLIC_REPORTS')
            ->name('reports.show');
        Route::patch('reports/{report}/take-over', [InstitutionReportController::class, 'takeOver'])
            ->middleware('institution_feature:PUBLIC_REPORTS')
            ->name('reports.take-over');
        Route::patch('reports/{report}/resolve', [InstitutionReportController::class, 'resolve'])
            ->middleware('institution_feature:PUBLIC_REPORTS')
            ->name('reports.resolve');
        Route::patch('reports/{report}/reject', [InstitutionReportController::class, 'reject'])
            ->middleware('institution_feature:PUBLIC_REPORTS')
            ->name('reports.reject');
        Route::patch('reports/{report}/damage-resolution', [InstitutionReportController::class, 'updateDamageResolution'])
            ->middleware(['institution_feature:PUBLIC_REPORTS', 'institution_feature:INSTITUTION_REPORT_DAMAGE_ACCESS', 'institution_feature:INSTITUTION_REPORT_DAMAGE_RESOLUTION'])
            ->name('reports.damage-resolution');
        Route::get('statistics', [InstitutionStatisticController::class, 'index'])
            ->middleware('institution_feature:PUBLIC_REPORT_STATISTICS')
            ->name('statistics.index');
        Route::get('report-users', [InstitutionReporterUserController::class, 'index'])
            ->middleware('institution_feature:PUBLIC_REPORT_USERS')
            ->name('report-users.index');
        Route::get('report-users/{reportUser}', [InstitutionReporterUserController::class, 'show'])
            ->middleware('institution_feature:PUBLIC_REPORT_USERS')
            ->name('report-users.show');
        Route::get('sla', [InstitutionSlaController::class, 'index'])
            ->middleware('institution_feature:INSTITUTION_SLA_ACCESS')
            ->name('sla.index');
        Route::post('sla', [InstitutionSlaController::class, 'store'])
            ->middleware('institution_feature:INSTITUTION_SLA_ACCESS')
            ->name('sla.store');
        Route::get('sla/{sla}/edit', [InstitutionSlaController::class, 'edit'])
            ->middleware('institution_feature:INSTITUTION_SLA_ACCESS')
            ->name('sla.edit');
        Route::put('sla/{sla}', [InstitutionSlaController::class, 'update'])
            ->middleware('institution_feature:INSTITUTION_SLA_ACCESS')
            ->name('sla.update');
        Route::patch('sla/{sla}/toggle-status', [InstitutionSlaController::class, 'toggleStatus'])
            ->middleware('institution_feature:INSTITUTION_SLA_ACCESS')
            ->name('sla.toggle-status');
        Route::get('signal-types', [InstitutionSignalTypeController::class, 'index'])
            ->middleware('institution_feature:INSTITUTION_SIGNAL_TYPES_ACCESS')
            ->name('signal-types.index');
        Route::post('signal-types', [InstitutionSignalTypeController::class, 'store'])
            ->middleware('institution_feature:INSTITUTION_SIGNAL_TYPES_ACCESS')
            ->name('signal-types.store');
        Route::get('signal-types/{signalType}/edit', [InstitutionSignalTypeController::class, 'edit'])
            ->middleware('institution_feature:INSTITUTION_SIGNAL_TYPES_ACCESS')
            ->name('signal-types.edit');
        Route::put('signal-types/{signalType}', [InstitutionSignalTypeController::class, 'update'])
            ->middleware('institution_feature:INSTITUTION_SIGNAL_TYPES_ACCESS')
            ->name('signal-types.update');
        Route::patch('signal-types/{signalType}/toggle-status', [InstitutionSignalTypeController::class, 'toggleStatus'])
            ->middleware('institution_feature:INSTITUTION_SIGNAL_TYPES_ACCESS')
            ->name('signal-types.toggle-status');
        Route::middleware('institution_permission:INSTITUTION_MANAGE_USERS')->group(function (): void {
            Route::resource('users', InstitutionUserController::class)->except(['create', 'show']);
            Route::patch('users/{user}/toggle-status', [InstitutionUserController::class, 'toggleStatus'])->name('users.toggle-status');
        });
        Route::middleware('institution_permission:INSTITUTION_MANAGE_ROLES')->group(function (): void {
            Route::resource('roles', InstitutionRoleController::class)->except(['create', 'show']);
            Route::patch('roles/{role}/toggle-status', [InstitutionRoleController::class, 'toggleStatus'])->name('roles.toggle-status');
        });
        Route::get('permissions', [InstitutionPermissionController::class, 'index'])
            ->middleware('institution_permission:INSTITUTION_MANAGE_PERMISSIONS')
            ->name('permissions.index');
        Route::get('activity-logs', [InstitutionActivityLogController::class, 'index'])
            ->middleware('institution_feature:INSTITUTION_ACTIVITY_LOGS')
            ->name('activity-logs.index');
        Route::post('logout', [InstitutionAuthController::class, 'destroy'])->name('logout');
    });
});

Route::prefix('sa')->name('super-admin.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('login', [SuperAdminAuthController::class, 'create'])->name('login');
        Route::post('login', [SuperAdminAuthController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth', 'super_admin_access'])->group(function (): void {
        Route::get('dashboard', SuperAdminDashboardController::class)
            ->middleware('super_admin_permission:SA_DASHBOARD_VIEW')
            ->name('dashboard');
        Route::post('logout', [SuperAdminAuthController::class, 'destroy'])->name('logout');

        Route::get('landing-page', [LandingPageController::class, 'edit'])->middleware('super_admin_permission:SA_LANDING_PAGE_MANAGE')->name('landing-page.edit');
        Route::put('landing-page', [LandingPageController::class, 'update'])->middleware('super_admin_permission:SA_LANDING_PAGE_MANAGE')->name('landing-page.update');

        Route::resource('countries', CountryController::class)->except(['create', 'show'])->middleware('super_admin_permission:SA_COUNTRIES_MANAGE');
        Route::patch('countries/{country}/toggle-status', [CountryController::class, 'toggleStatus'])->middleware('super_admin_permission:SA_COUNTRIES_MANAGE')->name('countries.toggle-status');

        Route::resource('cities', CityController::class)->except(['create', 'show'])->middleware('super_admin_permission:SA_CITIES_MANAGE');
        Route::patch('cities/{city}/toggle-status', [CityController::class, 'toggleStatus'])->middleware('super_admin_permission:SA_CITIES_MANAGE')->name('cities.toggle-status');

        Route::resource('communes', CommuneController::class)->except(['create', 'show'])->middleware('super_admin_permission:SA_COMMUNES_MANAGE');
        Route::patch('communes/{commune}/toggle-status', [CommuneController::class, 'toggleStatus'])->middleware('super_admin_permission:SA_COMMUNES_MANAGE')->name('communes.toggle-status');
        Route::resource('business-sectors', BusinessSectorController::class)->except(['create', 'show'])->middleware('super_admin_permission:SA_BUSINESS_SECTORS_MANAGE');
        Route::patch('business-sectors/{businessSector}/toggle-status', [BusinessSectorController::class, 'toggleStatus'])->middleware('super_admin_permission:SA_BUSINESS_SECTORS_MANAGE')->name('business-sectors.toggle-status');

        Route::resource('client-types', OrganizationTypeController::class)->parameters(['client-types' => 'clientType'])->except(['create', 'show'])->middleware('super_admin_permission:SA_ORGANIZATION_TYPES_MANAGE');
        Route::patch('client-types/{clientType}/toggle-status', [OrganizationTypeController::class, 'toggleStatus'])->middleware('super_admin_permission:SA_ORGANIZATION_TYPES_MANAGE')->name('client-types.toggle-status');

        Route::resource('features', FeatureController::class)->except(['create', 'show'])->middleware('super_admin_permission:SA_FEATURES_MANAGE');
        Route::patch('features/{feature}/toggle-status', [FeatureController::class, 'toggleStatus'])->middleware('super_admin_permission:SA_FEATURES_MANAGE')->name('features.toggle-status');
        Route::resource('applications', ApplicationController::class)->except(['create', 'show'])->middleware('super_admin_permission:SA_APPLICATIONS_MANAGE');
        Route::patch('applications/{application}/toggle-status', [ApplicationController::class, 'toggleStatus'])->middleware('super_admin_permission:SA_APPLICATIONS_MANAGE')->name('applications.toggle-status');
        Route::resource('signal-types', SignalTypeController::class)->except(['create', 'show'])->middleware('super_admin_permission:SA_SIGNAL_TYPES_MANAGE');
        Route::patch('signal-types/{signalType}/toggle-status', [SignalTypeController::class, 'toggleStatus'])->middleware('super_admin_permission:SA_SIGNAL_TYPES_MANAGE')->name('signal-types.toggle-status');
        Route::resource('sla-policies', OrganizationTypeSignalSlaController::class)->parameters(['sla-policies' => 'slaPolicy'])->except(['create', 'show'])->middleware('super_admin_permission:SA_SLA_POLICIES_MANAGE');
        Route::patch('sla-policies/{slaPolicy}/toggle-status', [OrganizationTypeSignalSlaController::class, 'toggleStatus'])->middleware('super_admin_permission:SA_SLA_POLICIES_MANAGE')->name('sla-policies.toggle-status');

        Route::resource('organizations', OrganizationController::class)->except(['create'])->middleware('super_admin_permission:SA_ORGANIZATIONS_MANAGE');
        Route::patch('organizations/{organization}/toggle-status', [OrganizationController::class, 'toggleStatus'])->middleware('super_admin_permission:SA_ORGANIZATIONS_MANAGE')->name('organizations.toggle-status');

        Route::resource('institution-admins', InstitutionAdminController::class)->parameters(['institution-admins' => 'institutionAdmin'])->except(['create', 'show'])->middleware('super_admin_permission:SA_INSTITUTION_ADMINS_MANAGE');
        Route::patch('institution-admins/{institutionAdmin}/toggle-status', [InstitutionAdminController::class, 'toggleStatus'])->middleware('super_admin_permission:SA_INSTITUTION_ADMINS_TOGGLE_STATUS')->name('institution-admins.toggle-status');

        Route::get('pricing', [PricingRuleController::class, 'edit'])->middleware('super_admin_permission:SA_PRICING_MANAGE')->name('pricing.edit');
        Route::put('pricing', [PricingRuleController::class, 'update'])->middleware('super_admin_permission:SA_PRICING_MANAGE')->name('pricing.update');
        Route::delete('pricing', [PricingRuleController::class, 'destroy'])->middleware('super_admin_permission:SA_PRICING_MANAGE')->name('pricing.destroy');
        Route::patch('pricing/toggle-status', [PricingRuleController::class, 'toggleStatus'])->middleware('super_admin_permission:SA_PRICING_MANAGE')->name('pricing.toggle-status');
        Route::resource('subscription-plans', SubscriptionPlanController::class)->except(['create', 'show', 'destroy'])->middleware('super_admin_permission:SA_SUBSCRIPTION_PLANS_MANAGE');
        Route::patch('subscription-plans/{subscriptionPlan}/toggle-status', [SubscriptionPlanController::class, 'toggleStatus'])->middleware('super_admin_permission:SA_SUBSCRIPTION_PLANS_MANAGE')->name('subscription-plans.toggle-status');
        Route::get('up-subscriptions', [UpSubscriptionController::class, 'index'])->middleware('super_admin_permission:SA_UP_SUBSCRIPTIONS_VIEW')->name('up-subscriptions.index');
        Route::resource('public-user-types', PublicUserTypeController::class)->parameters(['public-user-types' => 'publicUserType'])->except(['create', 'show'])->middleware('super_admin_permission:SA_PUBLIC_USER_TYPES_MANAGE');
        Route::patch('public-user-types/{publicUserType}/toggle-status', [PublicUserTypeController::class, 'toggleStatus'])->middleware('super_admin_permission:SA_PUBLIC_USER_TYPES_MANAGE')->name('public-user-types.toggle-status');
        Route::resource('public-users', PublicUserController::class)->parameters(['public-users' => 'publicUser'])->middleware('super_admin_permission:SA_PUBLIC_USERS_MANAGE');
        Route::patch('public-users/{publicUser}/toggle-status', [PublicUserController::class, 'toggleStatus'])->middleware('super_admin_permission:SA_PUBLIC_USERS_TOGGLE_STATUS')->name('public-users.toggle-status');
        Route::get('public-reports', [PublicIncidentReportController::class, 'index'])->middleware('super_admin_permission:SA_PUBLIC_REPORTS_VIEW')->name('public-reports.index');
        Route::get('payments', [PaymentController::class, 'index'])->middleware('super_admin_permission:SA_PAYMENTS_VIEW')->name('payments.index');
        Route::get('activity-logs', [SuperAdminActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::get('rex-feedbacks', [RexFeedbackController::class, 'index'])->middleware('super_admin_permission:SA_REX_FEEDBACKS_VIEW')->name('rex-feedbacks.index');
        Route::put('rex-feedbacks/settings', [RexFeedbackController::class, 'updateSettings'])->middleware('super_admin_permission:SA_REX_FEEDBACKS_MANAGE')->name('rex-feedbacks.settings');
        Route::get('reparation-cases', [ReparationCaseController::class, 'index'])->middleware('super_admin_permission:SA_REPARATION_CASES_MANAGE')->name('reparation-cases.index');
        Route::post('reparation-cases', [ReparationCaseController::class, 'store'])->middleware('super_admin_permission:SA_REPARATION_CASES_MANAGE')->name('reparation-cases.store');
        Route::get('reparation-cases/{reparationCase}', [ReparationCaseController::class, 'show'])->middleware('super_admin_permission:SA_REPARATION_CASES_MANAGE')->name('reparation-cases.show');
        Route::put('reparation-cases/{reparationCase}', [ReparationCaseController::class, 'update'])->middleware('super_admin_permission:SA_REPARATION_CASES_MANAGE')->name('reparation-cases.update');
        Route::post('reparation-cases/{reparationCase}/steps', [ReparationCaseController::class, 'storeStep'])->middleware('super_admin_permission:SA_REPARATION_CASES_MANAGE')->name('reparation-cases.steps.store');

        Route::resource('roles', RoleController::class)->except(['create'])->middleware('super_admin_permission:SA_ROLES_MANAGE');
        Route::patch('roles/{role}/toggle-status', [RoleController::class, 'toggleStatus'])->middleware('super_admin_permission:SA_ROLES_MANAGE')->name('roles.toggle-status');

        Route::resource('permissions', PermissionController::class)->except(['create', 'show'])->middleware('super_admin_permission:SA_PERMISSIONS_MANAGE');
        Route::patch('permissions/{permission}/toggle-status', [PermissionController::class, 'toggleStatus'])->middleware('super_admin_permission:SA_PERMISSIONS_MANAGE')->name('permissions.toggle-status');
        Route::resource('system-users', SystemUserController::class)->parameters(['system-users' => 'systemUser'])->except(['create'])->middleware('super_admin_permission:SA_SYSTEM_USERS_MANAGE');
        Route::patch('system-users/{systemUser}/toggle-status', [SystemUserController::class, 'toggleStatus'])->middleware('super_admin_permission:SA_SYSTEM_USERS_TOGGLE_STATUS')->name('system-users.toggle-status');
    });
});

Route::prefix('backoffice')->name('backoffice.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('login', [InternalAccessController::class, 'create'])->name('login');
        Route::post('login', [InternalAccessController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth', 'super_admin_access'])->group(function (): void {
        Route::get('home', InternalHomeController::class)->name('home');
        Route::post('logout', [InternalAccessController::class, 'destroy'])->name('logout');
    });
});
