<?php

use App\Http\Controllers\Web\Public\PublicPortalController;
use App\Http\Controllers\Web\MapTileController;
use App\Http\Controllers\Web\Backoffice\DashboardController as BackofficeDashboardController;
use App\Http\Controllers\Web\Backoffice\LegalCaseController as BackofficeLegalCaseController;
use App\Http\Controllers\Web\Institution\AuthController as InstitutionAuthController;
use App\Http\Controllers\Web\Institution\ActivityLogController as InstitutionActivityLogController;
use App\Http\Controllers\Web\Institution\DashboardController as InstitutionDashboardController;
use App\Http\Controllers\Web\Institution\DeviceTokenController as InstitutionDeviceTokenController;
use App\Http\Controllers\Web\Institution\DamageController as InstitutionDamageController;
use App\Http\Controllers\Web\Institution\MeterController as InstitutionMeterController;
use App\Http\Controllers\Web\Institution\NotificationController as InstitutionNotificationController;
use App\Http\Controllers\Web\Institution\PermissionController as InstitutionPermissionController;
use App\Http\Controllers\Web\Institution\ProfileController as InstitutionProfileController;
use App\Http\Controllers\Web\Institution\ReparationCaseController as InstitutionReparationCaseController;
use App\Http\Controllers\Web\Institution\ReporterUserController as InstitutionReporterUserController;
use App\Http\Controllers\Web\Institution\ReportController as InstitutionReportController;
use App\Http\Controllers\Web\Institution\RoleController as InstitutionRoleController;
use App\Http\Controllers\Web\Institution\SlaController as InstitutionSlaController;
use App\Http\Controllers\Web\Institution\SignalTypeController as InstitutionSignalTypeController;
use App\Http\Controllers\Web\Institution\StatisticController as InstitutionStatisticController;
use App\Http\Controllers\Web\Institution\UserController as InstitutionUserController;
use App\Http\Controllers\Web\Partner\AuthController as PartnerAuthController;
use App\Http\Controllers\Web\Partner\DashboardController as PartnerDashboardController;
use App\Http\Controllers\Web\Partner\DiscountOfferController as PartnerDiscountOfferController;
use App\Http\Controllers\Web\Partner\DiscountTransactionController as PartnerDiscountTransactionController;
use App\Http\Controllers\Web\Partner\UserController as PartnerUserController;
use App\Http\Controllers\Web\SuperAdmin\CityController;
use App\Http\Controllers\Web\SuperAdmin\CommuneController;
use App\Http\Controllers\Web\SuperAdmin\CountryController;
use App\Http\Controllers\Web\SuperAdmin\BusinessSectorController;
use App\Http\Controllers\Web\SuperAdmin\DeviceTokenController as SuperAdminDeviceTokenController;
use App\Http\Controllers\Web\SuperAdmin\DiscountCardController;
use App\Http\Controllers\Web\SuperAdmin\DiscountTransactionController;
use App\Http\Controllers\Web\SuperAdmin\FeatureController;
use App\Http\Controllers\Web\SuperAdmin\ActivityLogController as SuperAdminActivityLogController;
use App\Http\Controllers\Web\SuperAdmin\InstitutionAdminController;
use App\Http\Controllers\Web\SuperAdmin\InternalAccessController;
use App\Http\Controllers\Web\SuperAdmin\InternalHomeController;
use App\Http\Controllers\Web\SuperAdmin\LandingPageController;
use App\Http\Controllers\Web\SuperAdmin\LandingPageContactController;
use App\Http\Controllers\Web\SuperAdmin\ApplicationController;
use App\Http\Controllers\Web\SuperAdmin\MaintenanceCleanupController;
use App\Http\Controllers\Web\SuperAdmin\NeighborhoodController;
use App\Http\Controllers\Web\SuperAdmin\OrganizationController;
use App\Http\Controllers\Web\SuperAdmin\OrganizationTypeSignalSlaController;
use App\Http\Controllers\Web\SuperAdmin\OrganizationTypeController;
use App\Http\Controllers\Web\SuperAdmin\PermissionController;
use App\Http\Controllers\Web\SuperAdmin\PaymentController;
use App\Http\Controllers\Web\SuperAdmin\PricingRuleController;
use App\Http\Controllers\Web\SuperAdmin\PrivilegeCardTypeController;
use App\Http\Controllers\Web\SuperAdmin\PublicIncidentReportController;
use App\Http\Controllers\Web\SuperAdmin\PublicUserPushNotificationController;
use App\Http\Controllers\Web\SuperAdmin\PublicUserController;
use App\Http\Controllers\Web\SuperAdmin\PublicUserTypeController;
use App\Http\Controllers\Web\SuperAdmin\ReparationCaseController;
use App\Http\Controllers\Web\SuperAdmin\RexFeedbackController;
use App\Http\Controllers\Web\SuperAdmin\RoleController;
use App\Http\Controllers\Web\SuperAdmin\SignalTypeController;
use App\Http\Controllers\Web\SuperAdmin\ScopedRoleController;
use App\Http\Controllers\Web\SuperAdmin\ScopedUserController;
use App\Http\Controllers\Web\SuperAdmin\SubscriptionPlanController;
use App\Http\Controllers\Web\SuperAdmin\SubNeighborhoodController;
use App\Http\Controllers\Web\SuperAdmin\UserAccessController;
use App\Http\Controllers\Web\SuperAdmin\SystemUserController;
use App\Http\Controllers\Web\SuperAdmin\UpSubscriptionController;
use App\Http\Controllers\Web\SuperAdmin\AuthController as SuperAdminAuthController;
use App\Http\Controllers\Web\SuperAdmin\DashboardController as SuperAdminDashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Internal\MetricsController;

Route::get('/internal/metrics', MetricsController::class)
    ->name('internal.metrics');
Route::get('/map-tiles/{server}/{zoom}/{x}/{y}.png', MapTileController::class)
    ->where([
        'server' => '[abc]',
        'zoom' => '[0-9]+',
        'x' => '[0-9]+',
        'y' => '[0-9]+',
    ])
    ->name('map-tiles.show');
Route::get('/', [PublicPortalController::class, 'landing'])->name('public.landing');
Route::get('/qui-sommes-nous', [PublicPortalController::class, 'landingPage'])->defaults('pageKey', 'page_about')->name('public.pages.about');
Route::get('/my-signal-tv', [PublicPortalController::class, 'landingPage'])->defaults('pageKey', 'page_tv')->name('public.pages.tv');
Route::get('/signalements', [PublicPortalController::class, 'reports'])->name('public.reports');
Route::get('/faq', [PublicPortalController::class, 'landingPage'])->defaults('pageKey', 'page_faq')->name('public.pages.faq');
Route::get('/contactez-nous', [PublicPortalController::class, 'landingPage'])->defaults('pageKey', 'page_contact')->name('public.pages.contact');
Route::post('/contactez-nous', [PublicPortalController::class, 'storeContact'])->name('public.pages.contact.store');
Route::get('/conditions-generales-utilisation', [PublicPortalController::class, 'landingPage'])->defaults('pageKey', 'page_terms')->name('public.pages.terms');
Route::get('/politique-confidentialite', [PublicPortalController::class, 'landingPage'])->defaults('pageKey', 'page_privacy')->name('public.pages.privacy');
Route::get('/auth', [PublicPortalController::class, 'auth'])->name('public.auth');
Route::get('/dashboard', [PublicPortalController::class, 'dashboard'])->name('public.dashboard');
Route::get('/firebase-messaging-sw.js', function () {
    $javascript = <<<'JS'
function normalizePayload(event) {
    if (!event.data) {
        return {};
    }

    try {
        return event.data.json();
    } catch (error) {
        return { notification: { title: 'MYSIGNAL', body: event.data.text() } };
    }
}

self.addEventListener('push', (event) => {
    const payload = normalizePayload(event);
    let wrappedPayload = payload;

    if (payload.data?.FCM_MSG) {
        try {
            wrappedPayload = JSON.parse(payload.data.FCM_MSG);
        } catch (error) {
            wrappedPayload = payload;
        }
    }

    const notification = wrappedPayload.notification || {};
    const data = wrappedPayload.data || payload.data || {};
    const title = notification.title || data.title || 'MYSIGNAL';
    const options = {
        body: notification.body || data.body || '',
        icon: notification.icon || '/favicon.ico',
        badge: notification.badge || '/favicon.ico',
        tag: data.notification_id ? `mysignal-${data.notification_id}` : `mysignal-${Date.now()}`,
        renotify: true,
        requireInteraction: true,
        timestamp: Date.now(),
        data,
    };

    console.log('[MYSIGNAL] Firebase background payload', wrappedPayload);
    event.waitUntil(
        self.registration.showNotification(title, options)
            .then(() => {
                console.log('[MYSIGNAL] Notification affichee', { title, options });
            })
            .catch((error) => {
                console.error('[MYSIGNAL] Echec affichage notification', error);
            })
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const data = event.notification.data || {};
    if (data.url) {
        try {
            const target = new URL(data.url, self.location.origin);

            if (target.origin === self.location.origin) {
                event.waitUntil(clients.openWindow(target.href));
                return;
            }
        } catch (error) {
            console.warn('[MYSIGNAL] URL notification invalide', error);
        }
    }

    const screen = data.screen === 'dashboard' ? 'notifications' : (data.screen || 'notifications');
    const targetUrl = new URL('/dashboard', self.location.origin);

    if (screen) {
        targetUrl.hash = screen;
    }

    event.waitUntil((async () => {
        const clientList = await clients.matchAll({ type: 'window', includeUncontrolled: true });
        const existingClient = clientList.find((client) => client.url.startsWith(targetUrl.origin));

        if (existingClient) {
            await existingClient.focus();
            existingClient.postMessage({ type: 'MYSIGNAL_NOTIFICATION_CLICK', data });
            return;
        }

        await clients.openWindow(targetUrl.href);
    })());
});

self.addEventListener('install', (event) => {
    event.waitUntil(self.skipWaiting());
});

self.addEventListener('activate', (event) => {
    event.waitUntil(self.clients.claim());
});
JS;

    return response($javascript, 200)
        ->header('Content-Type', 'application/javascript; charset=UTF-8')
        ->header('Service-Worker-Allowed', '/')
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
})->name('firebase.messaging-sw');
Route::redirect('/admin', '/sa/login');
Route::redirect('/admin/login', '/sa/login');
Route::redirect('/backoffice', '/backoffice/login');
Route::redirect('/partenaire', '/partner/login');

Route::prefix('institution')->name('institution.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('login', [InstitutionAuthController::class, 'create'])->name('login');
        Route::post('login', [InstitutionAuthController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth', 'institution_admin'])->group(function (): void {
        Route::get('dashboard', InstitutionDashboardController::class)->name('dashboard');
        Route::post('push-tokens', [InstitutionDeviceTokenController::class, 'store'])->name('push-tokens.store');
        Route::get('notifications', [InstitutionNotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications/read-all', [InstitutionNotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
        Route::post('notifications/{notification}/read', [InstitutionNotificationController::class, 'markAsRead'])->name('notifications.read');
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
        Route::get('reparation-cases', [InstitutionReparationCaseController::class, 'index'])
            ->middleware('institution_feature:PUBLIC_REPORTS')
            ->name('reparation-cases.index');
        Route::get('reparation-cases/{reparationCase}', [InstitutionReparationCaseController::class, 'show'])
            ->middleware('institution_feature:PUBLIC_REPORTS')
            ->name('reparation-cases.show');
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
        Route::post('push-tokens', [SuperAdminDeviceTokenController::class, 'store'])->name('push-tokens.store');
        Route::post('logout', [SuperAdminAuthController::class, 'destroy'])->name('logout');

        Route::get('landing-page', [LandingPageController::class, 'edit'])->middleware('super_admin_permission:SA_LANDING_PAGE_MANAGE')->name('landing-page.edit');
        Route::put('landing-page', [LandingPageController::class, 'update'])->middleware('super_admin_permission:SA_LANDING_PAGE_MANAGE')->name('landing-page.update');
        Route::get('landing-page/contacts', [LandingPageContactController::class, 'index'])->middleware('super_admin_permission:SA_LANDING_PAGE_MANAGE')->name('landing-page.contacts.index');
        Route::patch('landing-page/contacts/{contactSubmission}/read', [LandingPageContactController::class, 'markAsRead'])->middleware('super_admin_permission:SA_LANDING_PAGE_MANAGE')->name('landing-page.contacts.read');

        Route::get('countries', [CountryController::class, 'index'])->middleware('super_admin_permission:SA_COUNTRIES_VIEW,SA_COUNTRIES_MANAGE')->name('countries.index');
        Route::post('countries', [CountryController::class, 'store'])->middleware('super_admin_permission:SA_COUNTRIES_CREATE,SA_COUNTRIES_MANAGE')->name('countries.store');
        Route::get('countries/{country}/edit', [CountryController::class, 'edit'])->middleware('super_admin_permission:SA_COUNTRIES_UPDATE,SA_COUNTRIES_MANAGE')->name('countries.edit');
        Route::put('countries/{country}', [CountryController::class, 'update'])->middleware('super_admin_permission:SA_COUNTRIES_UPDATE,SA_COUNTRIES_MANAGE')->name('countries.update');
        Route::delete('countries/{country}', [CountryController::class, 'destroy'])->middleware('super_admin_permission:SA_COUNTRIES_DELETE,SA_COUNTRIES_MANAGE')->name('countries.destroy');
        Route::patch('countries/{country}/toggle-status', [CountryController::class, 'toggleStatus'])->middleware('super_admin_permission:SA_COUNTRIES_TOGGLE_STATUS,SA_COUNTRIES_MANAGE')->name('countries.toggle-status');

        Route::get('cities', [CityController::class, 'index'])->middleware('super_admin_permission:SA_CITIES_VIEW,SA_CITIES_MANAGE')->name('cities.index');
        Route::post('cities', [CityController::class, 'store'])->middleware('super_admin_permission:SA_CITIES_CREATE,SA_CITIES_MANAGE')->name('cities.store');
        Route::get('cities/{city}/edit', [CityController::class, 'edit'])->middleware('super_admin_permission:SA_CITIES_UPDATE,SA_CITIES_MANAGE')->name('cities.edit');
        Route::put('cities/{city}', [CityController::class, 'update'])->middleware('super_admin_permission:SA_CITIES_UPDATE,SA_CITIES_MANAGE')->name('cities.update');
        Route::delete('cities/{city}', [CityController::class, 'destroy'])->middleware('super_admin_permission:SA_CITIES_DELETE,SA_CITIES_MANAGE')->name('cities.destroy');
        Route::patch('cities/{city}/toggle-status', [CityController::class, 'toggleStatus'])->middleware('super_admin_permission:SA_CITIES_TOGGLE_STATUS,SA_CITIES_MANAGE')->name('cities.toggle-status');

        Route::get('communes', [CommuneController::class, 'index'])->middleware('super_admin_permission:SA_COMMUNES_VIEW,SA_COMMUNES_MANAGE')->name('communes.index');
        Route::post('communes', [CommuneController::class, 'store'])->middleware('super_admin_permission:SA_COMMUNES_CREATE,SA_COMMUNES_MANAGE')->name('communes.store');
        Route::get('communes/{commune}/edit', [CommuneController::class, 'edit'])->middleware('super_admin_permission:SA_COMMUNES_UPDATE,SA_COMMUNES_MANAGE')->name('communes.edit');
        Route::put('communes/{commune}', [CommuneController::class, 'update'])->middleware('super_admin_permission:SA_COMMUNES_UPDATE,SA_COMMUNES_MANAGE')->name('communes.update');
        Route::delete('communes/{commune}', [CommuneController::class, 'destroy'])->middleware('super_admin_permission:SA_COMMUNES_DELETE,SA_COMMUNES_MANAGE')->name('communes.destroy');
        Route::patch('communes/{commune}/toggle-status', [CommuneController::class, 'toggleStatus'])->middleware('super_admin_permission:SA_COMMUNES_TOGGLE_STATUS,SA_COMMUNES_MANAGE')->name('communes.toggle-status');

        Route::get('neighborhoods', [NeighborhoodController::class, 'index'])->middleware('super_admin_permission:SA_NEIGHBORHOODS_VIEW,SA_NEIGHBORHOODS_MANAGE')->name('neighborhoods.index');
        Route::post('neighborhoods', [NeighborhoodController::class, 'store'])->middleware('super_admin_permission:SA_NEIGHBORHOODS_CREATE,SA_NEIGHBORHOODS_MANAGE')->name('neighborhoods.store');
        Route::get('neighborhoods/{neighborhood}/edit', [NeighborhoodController::class, 'edit'])->middleware('super_admin_permission:SA_NEIGHBORHOODS_UPDATE,SA_NEIGHBORHOODS_MANAGE')->name('neighborhoods.edit');
        Route::put('neighborhoods/{neighborhood}', [NeighborhoodController::class, 'update'])->middleware('super_admin_permission:SA_NEIGHBORHOODS_UPDATE,SA_NEIGHBORHOODS_MANAGE')->name('neighborhoods.update');
        Route::delete('neighborhoods/{neighborhood}', [NeighborhoodController::class, 'destroy'])->middleware('super_admin_permission:SA_NEIGHBORHOODS_DELETE,SA_NEIGHBORHOODS_MANAGE')->name('neighborhoods.destroy');
        Route::patch('neighborhoods/{neighborhood}/toggle-status', [NeighborhoodController::class, 'toggleStatus'])->middleware('super_admin_permission:SA_NEIGHBORHOODS_TOGGLE_STATUS,SA_NEIGHBORHOODS_MANAGE')->name('neighborhoods.toggle-status');

        Route::get('sub-neighborhoods', [SubNeighborhoodController::class, 'index'])->middleware('super_admin_permission:SA_SUB_NEIGHBORHOODS_VIEW,SA_SUB_NEIGHBORHOODS_MANAGE')->name('sub-neighborhoods.index');
        Route::post('sub-neighborhoods', [SubNeighborhoodController::class, 'store'])->middleware('super_admin_permission:SA_SUB_NEIGHBORHOODS_CREATE,SA_SUB_NEIGHBORHOODS_MANAGE')->name('sub-neighborhoods.store');
        Route::get('sub-neighborhoods/{subNeighborhood}/edit', [SubNeighborhoodController::class, 'edit'])->middleware('super_admin_permission:SA_SUB_NEIGHBORHOODS_UPDATE,SA_SUB_NEIGHBORHOODS_MANAGE')->name('sub-neighborhoods.edit');
        Route::put('sub-neighborhoods/{subNeighborhood}', [SubNeighborhoodController::class, 'update'])->middleware('super_admin_permission:SA_SUB_NEIGHBORHOODS_UPDATE,SA_SUB_NEIGHBORHOODS_MANAGE')->name('sub-neighborhoods.update');
        Route::delete('sub-neighborhoods/{subNeighborhood}', [SubNeighborhoodController::class, 'destroy'])->middleware('super_admin_permission:SA_SUB_NEIGHBORHOODS_DELETE,SA_SUB_NEIGHBORHOODS_MANAGE')->name('sub-neighborhoods.destroy');
        Route::patch('sub-neighborhoods/{subNeighborhood}/toggle-status', [SubNeighborhoodController::class, 'toggleStatus'])->middleware('super_admin_permission:SA_SUB_NEIGHBORHOODS_TOGGLE_STATUS,SA_SUB_NEIGHBORHOODS_MANAGE')->name('sub-neighborhoods.toggle-status');

        Route::get('business-sectors', [BusinessSectorController::class, 'index'])->middleware('super_admin_permission:SA_BUSINESS_SECTORS_VIEW,SA_BUSINESS_SECTORS_MANAGE')->name('business-sectors.index');
        Route::post('business-sectors', [BusinessSectorController::class, 'store'])->middleware('super_admin_permission:SA_BUSINESS_SECTORS_CREATE,SA_BUSINESS_SECTORS_MANAGE')->name('business-sectors.store');
        Route::get('business-sectors/{businessSector}/edit', [BusinessSectorController::class, 'edit'])->middleware('super_admin_permission:SA_BUSINESS_SECTORS_UPDATE,SA_BUSINESS_SECTORS_MANAGE')->name('business-sectors.edit');
        Route::put('business-sectors/{businessSector}', [BusinessSectorController::class, 'update'])->middleware('super_admin_permission:SA_BUSINESS_SECTORS_UPDATE,SA_BUSINESS_SECTORS_MANAGE')->name('business-sectors.update');
        Route::delete('business-sectors/{businessSector}', [BusinessSectorController::class, 'destroy'])->middleware('super_admin_permission:SA_BUSINESS_SECTORS_DELETE,SA_BUSINESS_SECTORS_MANAGE')->name('business-sectors.destroy');
        Route::patch('business-sectors/{businessSector}/toggle-status', [BusinessSectorController::class, 'toggleStatus'])->middleware('super_admin_permission:SA_BUSINESS_SECTORS_TOGGLE_STATUS,SA_BUSINESS_SECTORS_MANAGE')->name('business-sectors.toggle-status');

        Route::get('client-types', [OrganizationTypeController::class, 'index'])->middleware('super_admin_permission:SA_ORGANIZATION_TYPES_VIEW,SA_ORGANIZATION_TYPES_MANAGE')->name('client-types.index');
        Route::post('client-types', [OrganizationTypeController::class, 'store'])->middleware('super_admin_permission:SA_ORGANIZATION_TYPES_CREATE,SA_ORGANIZATION_TYPES_MANAGE')->name('client-types.store');
        Route::get('client-types/{clientType}/edit', [OrganizationTypeController::class, 'edit'])->middleware('super_admin_permission:SA_ORGANIZATION_TYPES_UPDATE,SA_ORGANIZATION_TYPES_MANAGE')->name('client-types.edit');
        Route::put('client-types/{clientType}', [OrganizationTypeController::class, 'update'])->middleware('super_admin_permission:SA_ORGANIZATION_TYPES_UPDATE,SA_ORGANIZATION_TYPES_MANAGE')->name('client-types.update');
        Route::delete('client-types/{clientType}', [OrganizationTypeController::class, 'destroy'])->middleware('super_admin_permission:SA_ORGANIZATION_TYPES_DELETE,SA_ORGANIZATION_TYPES_MANAGE')->name('client-types.destroy');
        Route::patch('client-types/{clientType}/toggle-status', [OrganizationTypeController::class, 'toggleStatus'])->middleware('super_admin_permission:SA_ORGANIZATION_TYPES_TOGGLE_STATUS,SA_ORGANIZATION_TYPES_MANAGE')->name('client-types.toggle-status');

        Route::get('features', [FeatureController::class, 'index'])->middleware('super_admin_permission:SA_FEATURES_VIEW,SA_FEATURES_MANAGE')->name('features.index');
        Route::post('features', [FeatureController::class, 'store'])->middleware('super_admin_permission:SA_FEATURES_CREATE,SA_FEATURES_MANAGE')->name('features.store');
        Route::get('features/{feature}/edit', [FeatureController::class, 'edit'])->middleware('super_admin_permission:SA_FEATURES_UPDATE,SA_FEATURES_MANAGE')->name('features.edit');
        Route::put('features/{feature}', [FeatureController::class, 'update'])->middleware('super_admin_permission:SA_FEATURES_UPDATE,SA_FEATURES_MANAGE')->name('features.update');
        Route::delete('features/{feature}', [FeatureController::class, 'destroy'])->middleware('super_admin_permission:SA_FEATURES_DELETE,SA_FEATURES_MANAGE')->name('features.destroy');
        Route::patch('features/{feature}/toggle-status', [FeatureController::class, 'toggleStatus'])->middleware('super_admin_permission:SA_FEATURES_TOGGLE_STATUS,SA_FEATURES_MANAGE')->name('features.toggle-status');
        Route::get('applications', [ApplicationController::class, 'index'])->middleware('super_admin_permission:SA_APPLICATIONS_VIEW,SA_APPLICATIONS_MANAGE')->name('applications.index');
        Route::post('applications', [ApplicationController::class, 'store'])->middleware('super_admin_permission:SA_APPLICATIONS_CREATE,SA_APPLICATIONS_MANAGE')->name('applications.store');
        Route::get('applications/{application}/edit', [ApplicationController::class, 'edit'])->middleware('super_admin_permission:SA_APPLICATIONS_UPDATE,SA_APPLICATIONS_MANAGE')->name('applications.edit');
        Route::put('applications/{application}', [ApplicationController::class, 'update'])->middleware('super_admin_permission:SA_APPLICATIONS_UPDATE,SA_APPLICATIONS_MANAGE')->name('applications.update');
        Route::delete('applications/{application}', [ApplicationController::class, 'destroy'])->middleware('super_admin_permission:SA_APPLICATIONS_DELETE,SA_APPLICATIONS_MANAGE')->name('applications.destroy');
        Route::patch('applications/{application}/toggle-status', [ApplicationController::class, 'toggleStatus'])->middleware('super_admin_permission:SA_APPLICATIONS_TOGGLE_STATUS,SA_APPLICATIONS_MANAGE')->name('applications.toggle-status');
        Route::get('signal-types', [SignalTypeController::class, 'index'])->middleware('super_admin_permission:SA_SIGNAL_TYPES_VIEW,SA_SIGNAL_TYPES_MANAGE')->name('signal-types.index');
        Route::post('signal-types', [SignalTypeController::class, 'store'])->middleware('super_admin_permission:SA_SIGNAL_TYPES_CREATE,SA_SIGNAL_TYPES_MANAGE')->name('signal-types.store');
        Route::post('signal-types/import', [SignalTypeController::class, 'import'])->middleware('super_admin_permission:SA_SIGNAL_TYPES_CREATE,SA_SIGNAL_TYPES_MANAGE')->name('signal-types.import');
        Route::get('signal-types/import-template', [SignalTypeController::class, 'downloadImportTemplate'])->middleware('super_admin_permission:SA_SIGNAL_TYPES_VIEW,SA_SIGNAL_TYPES_MANAGE')->name('signal-types.import-template');
        Route::delete('signal-types/clear', [SignalTypeController::class, 'destroyAll'])->middleware('super_admin_permission:SA_SIGNAL_TYPES_DELETE,SA_SIGNAL_TYPES_MANAGE')->name('signal-types.clear');
        Route::delete('signal-types/selected', [SignalTypeController::class, 'destroySelected'])->middleware('super_admin_permission:SA_SIGNAL_TYPES_DELETE,SA_SIGNAL_TYPES_MANAGE')->name('signal-types.destroy-selected');
        Route::get('signal-types/{signalType}/edit', [SignalTypeController::class, 'edit'])->middleware('super_admin_permission:SA_SIGNAL_TYPES_UPDATE,SA_SIGNAL_TYPES_MANAGE')->name('signal-types.edit');
        Route::put('signal-types/{signalType}', [SignalTypeController::class, 'update'])->middleware('super_admin_permission:SA_SIGNAL_TYPES_UPDATE,SA_SIGNAL_TYPES_MANAGE')->name('signal-types.update');
        Route::delete('signal-types/{signalType}', [SignalTypeController::class, 'destroy'])->middleware('super_admin_permission:SA_SIGNAL_TYPES_DELETE,SA_SIGNAL_TYPES_MANAGE')->name('signal-types.destroy');
        Route::patch('signal-types/{signalType}/toggle-status', [SignalTypeController::class, 'toggleStatus'])->middleware('super_admin_permission:SA_SIGNAL_TYPES_TOGGLE_STATUS,SA_SIGNAL_TYPES_MANAGE')->name('signal-types.toggle-status');
        Route::get('signal-sub-types', [SignalTypeController::class, 'subTypesIndex'])->middleware('super_admin_permission:SA_SIGNAL_TYPES_VIEW,SA_SIGNAL_TYPES_MANAGE')->name('signal-sub-types.index');
        Route::post('signal-sub-types', [SignalTypeController::class, 'storeSubTypeFromIndex'])->middleware('super_admin_permission:SA_SIGNAL_TYPES_UPDATE,SA_SIGNAL_TYPES_MANAGE')->name('signal-sub-types.store');
        Route::post('signal-sub-types/import', [SignalTypeController::class, 'importSubTypes'])->middleware('super_admin_permission:SA_SIGNAL_TYPES_UPDATE,SA_SIGNAL_TYPES_MANAGE')->name('signal-sub-types.import');
        Route::get('signal-sub-types/import-template', [SignalTypeController::class, 'downloadSubTypeImportTemplate'])->middleware('super_admin_permission:SA_SIGNAL_TYPES_VIEW,SA_SIGNAL_TYPES_MANAGE')->name('signal-sub-types.import-template');
        Route::post('signal-types/{signalType}/sub-types', [SignalTypeController::class, 'storeSubType'])->middleware('super_admin_permission:SA_SIGNAL_TYPES_UPDATE,SA_SIGNAL_TYPES_MANAGE')->name('signal-types.sub-types.store');
        Route::put('signal-types/{signalType}/sub-types/{subType}', [SignalTypeController::class, 'updateSubType'])->middleware('super_admin_permission:SA_SIGNAL_TYPES_UPDATE,SA_SIGNAL_TYPES_MANAGE')->name('signal-types.sub-types.update');
        Route::patch('signal-types/{signalType}/sub-types/{subType}/toggle-status', [SignalTypeController::class, 'toggleSubTypeStatus'])->middleware('super_admin_permission:SA_SIGNAL_TYPES_UPDATE,SA_SIGNAL_TYPES_MANAGE')->name('signal-types.sub-types.toggle-status');
        Route::delete('signal-types/{signalType}/sub-types/{subType}', [SignalTypeController::class, 'destroySubType'])->middleware('super_admin_permission:SA_SIGNAL_TYPES_UPDATE,SA_SIGNAL_TYPES_MANAGE')->name('signal-types.sub-types.destroy');
        Route::get('sla-policies', [OrganizationTypeSignalSlaController::class, 'index'])->middleware('super_admin_permission:SA_SLA_POLICIES_VIEW,SA_SLA_POLICIES_MANAGE')->name('sla-policies.index');
        Route::post('sla-policies', [OrganizationTypeSignalSlaController::class, 'store'])->middleware('super_admin_permission:SA_SLA_POLICIES_CREATE,SA_SLA_POLICIES_MANAGE')->name('sla-policies.store');
        Route::get('sla-policies/{slaPolicy}/edit', [OrganizationTypeSignalSlaController::class, 'edit'])->middleware('super_admin_permission:SA_SLA_POLICIES_UPDATE,SA_SLA_POLICIES_MANAGE')->name('sla-policies.edit');
        Route::put('sla-policies/{slaPolicy}', [OrganizationTypeSignalSlaController::class, 'update'])->middleware('super_admin_permission:SA_SLA_POLICIES_UPDATE,SA_SLA_POLICIES_MANAGE')->name('sla-policies.update');
        Route::delete('sla-policies/{slaPolicy}', [OrganizationTypeSignalSlaController::class, 'destroy'])->middleware('super_admin_permission:SA_SLA_POLICIES_DELETE,SA_SLA_POLICIES_MANAGE')->name('sla-policies.destroy');
        Route::patch('sla-policies/{slaPolicy}/toggle-status', [OrganizationTypeSignalSlaController::class, 'toggleStatus'])->middleware('super_admin_permission:SA_SLA_POLICIES_TOGGLE_STATUS,SA_SLA_POLICIES_MANAGE')->name('sla-policies.toggle-status');

        Route::get('organizations', [OrganizationController::class, 'index'])->middleware('super_admin_permission:SA_ORGANIZATIONS_VIEW,SA_ORGANIZATIONS_MANAGE')->name('organizations.index');
        Route::post('organizations', [OrganizationController::class, 'store'])->middleware('super_admin_permission:SA_ORGANIZATIONS_CREATE,SA_ORGANIZATIONS_MANAGE')->name('organizations.store');
        Route::post('organizations/import', [OrganizationController::class, 'import'])->middleware('super_admin_permission:SA_ORGANIZATIONS_CREATE,SA_ORGANIZATIONS_MANAGE')->name('organizations.import');
        Route::get('organizations/import-template/{template}', [OrganizationController::class, 'downloadImportTemplate'])->middleware('super_admin_permission:SA_ORGANIZATIONS_VIEW,SA_ORGANIZATIONS_MANAGE')->name('organizations.import-template');
        Route::delete('organizations/clear', [OrganizationController::class, 'destroyAll'])->middleware('super_admin_permission:SA_ORGANIZATIONS_DELETE,SA_ORGANIZATIONS_MANAGE')->name('organizations.clear');
        Route::get('organizations/{organization}', [OrganizationController::class, 'show'])->middleware('super_admin_permission:SA_ORGANIZATIONS_VIEW,SA_ORGANIZATIONS_MANAGE')->name('organizations.show');
        Route::get('organizations/{organization}/edit', [OrganizationController::class, 'edit'])->middleware('super_admin_permission:SA_ORGANIZATIONS_UPDATE,SA_ORGANIZATIONS_MANAGE')->name('organizations.edit');
        Route::put('organizations/{organization}', [OrganizationController::class, 'update'])->middleware('super_admin_permission:SA_ORGANIZATIONS_UPDATE,SA_ORGANIZATIONS_MANAGE')->name('organizations.update');
        Route::delete('organizations/{organization}', [OrganizationController::class, 'destroy'])->middleware('super_admin_permission:SA_ORGANIZATIONS_DELETE,SA_ORGANIZATIONS_MANAGE')->name('organizations.destroy');
        Route::patch('organizations/{organization}/toggle-status', [OrganizationController::class, 'toggleStatus'])->middleware('super_admin_permission:SA_ORGANIZATIONS_TOGGLE_STATUS,SA_ORGANIZATIONS_MANAGE')->name('organizations.toggle-status');

        Route::get('institution-admins', [InstitutionAdminController::class, 'index'])->middleware('super_admin_permission:SA_INSTITUTION_ADMINS_VIEW,SA_INSTITUTION_ADMINS_MANAGE')->name('institution-admins.index');
        Route::post('institution-admins', [InstitutionAdminController::class, 'store'])->middleware('super_admin_permission:SA_INSTITUTION_ADMINS_CREATE,SA_INSTITUTION_ADMINS_MANAGE')->name('institution-admins.store');
        Route::get('institution-admins/{institutionAdmin}/edit', [InstitutionAdminController::class, 'edit'])->middleware('super_admin_permission:SA_INSTITUTION_ADMINS_UPDATE,SA_INSTITUTION_ADMINS_MANAGE')->name('institution-admins.edit');
        Route::put('institution-admins/{institutionAdmin}', [InstitutionAdminController::class, 'update'])->middleware('super_admin_permission:SA_INSTITUTION_ADMINS_UPDATE,SA_INSTITUTION_ADMINS_MANAGE')->name('institution-admins.update');
        Route::patch('institution-admins/{institutionAdmin}', [InstitutionAdminController::class, 'update'])->middleware('super_admin_permission:SA_INSTITUTION_ADMINS_UPDATE,SA_INSTITUTION_ADMINS_MANAGE');
        Route::delete('institution-admins/{institutionAdmin}', [InstitutionAdminController::class, 'destroy'])->middleware('super_admin_permission:SA_INSTITUTION_ADMINS_DELETE,SA_INSTITUTION_ADMINS_MANAGE')->name('institution-admins.destroy');
        Route::patch('institution-admins/{institutionAdmin}/toggle-status', [InstitutionAdminController::class, 'toggleStatus'])->middleware('super_admin_permission:SA_INSTITUTION_ADMINS_TOGGLE_STATUS,SA_INSTITUTION_ADMINS_MANAGE')->name('institution-admins.toggle-status');

        Route::get('pricing', [PricingRuleController::class, 'edit'])->middleware('super_admin_permission:SA_PRICING_MANAGE')->name('pricing.edit');
        Route::put('pricing', [PricingRuleController::class, 'update'])->middleware('super_admin_permission:SA_PRICING_MANAGE')->name('pricing.update');
        Route::delete('pricing', [PricingRuleController::class, 'destroy'])->middleware('super_admin_permission:SA_PRICING_MANAGE')->name('pricing.destroy');
        Route::patch('pricing/toggle-status', [PricingRuleController::class, 'toggleStatus'])->middleware('super_admin_permission:SA_PRICING_MANAGE')->name('pricing.toggle-status');
        Route::get('subscription-plans', [SubscriptionPlanController::class, 'index'])->middleware('super_admin_permission:SA_SUBSCRIPTION_PLANS_VIEW,SA_SUBSCRIPTION_PLANS_MANAGE')->name('subscription-plans.index');
        Route::post('subscription-plans', [SubscriptionPlanController::class, 'store'])->middleware('super_admin_permission:SA_SUBSCRIPTION_PLANS_CREATE,SA_SUBSCRIPTION_PLANS_MANAGE')->name('subscription-plans.store');
        Route::get('subscription-plans/{subscriptionPlan}/edit', [SubscriptionPlanController::class, 'edit'])->middleware('super_admin_permission:SA_SUBSCRIPTION_PLANS_UPDATE,SA_SUBSCRIPTION_PLANS_MANAGE')->name('subscription-plans.edit');
        Route::put('subscription-plans/{subscriptionPlan}', [SubscriptionPlanController::class, 'update'])->middleware('super_admin_permission:SA_SUBSCRIPTION_PLANS_UPDATE,SA_SUBSCRIPTION_PLANS_MANAGE')->name('subscription-plans.update');
        Route::delete('subscription-plans/{subscriptionPlan}', [SubscriptionPlanController::class, 'destroy'])->middleware('super_admin_permission:SA_SUBSCRIPTION_PLANS_DELETE,SA_SUBSCRIPTION_PLANS_MANAGE')->name('subscription-plans.destroy');
        Route::patch('subscription-plans/{subscriptionPlan}/toggle-status', [SubscriptionPlanController::class, 'toggleStatus'])->middleware('super_admin_permission:SA_SUBSCRIPTION_PLANS_TOGGLE_STATUS,SA_SUBSCRIPTION_PLANS_MANAGE')->name('subscription-plans.toggle-status');
        Route::get('privilege-card-types', [PrivilegeCardTypeController::class, 'index'])->middleware('super_admin_permission:SA_PRIVILEGE_CARD_TYPES_VIEW,SA_PRIVILEGE_CARD_TYPES_MANAGE,SA_SUBSCRIPTION_PLANS_VIEW,SA_SUBSCRIPTION_PLANS_MANAGE')->name('privilege-card-types.index');
        Route::get('privilege-cards/issued', [PrivilegeCardTypeController::class, 'issuedCards'])->middleware('super_admin_permission:SA_PRIVILEGE_CARD_TYPES_VIEW,SA_PRIVILEGE_CARD_TYPES_MANAGE,SA_SUBSCRIPTION_PLANS_VIEW,SA_SUBSCRIPTION_PLANS_MANAGE')->name('privilege-card-types.issued-cards');
        Route::get('privilege-cards/purchases', [PrivilegeCardTypeController::class, 'purchases'])->middleware('super_admin_permission:SA_PRIVILEGE_CARD_TYPES_VIEW,SA_PRIVILEGE_CARD_TYPES_MANAGE,SA_SUBSCRIPTION_PLANS_VIEW,SA_SUBSCRIPTION_PLANS_MANAGE')->name('privilege-card-types.purchases');
        Route::get('privilege-cards/scans', [PrivilegeCardTypeController::class, 'scans'])->middleware('super_admin_permission:SA_PRIVILEGE_CARD_TYPES_VIEW,SA_PRIVILEGE_CARD_TYPES_MANAGE,SA_SUBSCRIPTION_PLANS_VIEW,SA_SUBSCRIPTION_PLANS_MANAGE')->name('privilege-card-types.scans');
        Route::post('privilege-card-types', [PrivilegeCardTypeController::class, 'store'])->middleware('super_admin_permission:SA_PRIVILEGE_CARD_TYPES_CREATE,SA_PRIVILEGE_CARD_TYPES_MANAGE,SA_SUBSCRIPTION_PLANS_CREATE,SA_SUBSCRIPTION_PLANS_MANAGE')->name('privilege-card-types.store');
        Route::post('privilege-card-types/issue-card', [PrivilegeCardTypeController::class, 'issueCard'])->middleware('super_admin_permission:SA_PRIVILEGE_CARD_TYPES_CREATE,SA_PRIVILEGE_CARD_TYPES_MANAGE,SA_SUBSCRIPTION_PLANS_CREATE,SA_SUBSCRIPTION_PLANS_MANAGE')->name('privilege-card-types.issue-card');
        Route::put('privilege-card-types/{privilegeCardType}', [PrivilegeCardTypeController::class, 'update'])->middleware('super_admin_permission:SA_PRIVILEGE_CARD_TYPES_UPDATE,SA_PRIVILEGE_CARD_TYPES_MANAGE,SA_SUBSCRIPTION_PLANS_UPDATE,SA_SUBSCRIPTION_PLANS_MANAGE')->name('privilege-card-types.update');
        Route::delete('privilege-card-types/{privilegeCardType}', [PrivilegeCardTypeController::class, 'destroy'])->middleware('super_admin_permission:SA_PRIVILEGE_CARD_TYPES_DELETE,SA_PRIVILEGE_CARD_TYPES_MANAGE,SA_SUBSCRIPTION_PLANS_DELETE,SA_SUBSCRIPTION_PLANS_MANAGE')->name('privilege-card-types.destroy');
        Route::patch('privilege-card-types/{privilegeCardType}/toggle-status', [PrivilegeCardTypeController::class, 'toggleStatus'])->middleware('super_admin_permission:SA_PRIVILEGE_CARD_TYPES_TOGGLE_STATUS,SA_PRIVILEGE_CARD_TYPES_MANAGE,SA_SUBSCRIPTION_PLANS_TOGGLE_STATUS,SA_SUBSCRIPTION_PLANS_MANAGE')->name('privilege-card-types.toggle-status');
        Route::get('up-subscriptions', [UpSubscriptionController::class, 'index'])->middleware('super_admin_permission:SA_UP_SUBSCRIPTIONS_VIEW')->name('up-subscriptions.index');
        Route::get('public-user-types', [PublicUserTypeController::class, 'index'])->middleware('super_admin_permission:SA_PUBLIC_USER_TYPES_VIEW,SA_PUBLIC_USER_TYPES_MANAGE')->name('public-user-types.index');
        Route::post('public-user-types', [PublicUserTypeController::class, 'store'])->middleware('super_admin_permission:SA_PUBLIC_USER_TYPES_CREATE,SA_PUBLIC_USER_TYPES_MANAGE')->name('public-user-types.store');
        Route::get('public-user-types/{publicUserType}/edit', [PublicUserTypeController::class, 'edit'])->middleware('super_admin_permission:SA_PUBLIC_USER_TYPES_UPDATE,SA_PUBLIC_USER_TYPES_MANAGE')->name('public-user-types.edit');
        Route::put('public-user-types/{publicUserType}', [PublicUserTypeController::class, 'update'])->middleware('super_admin_permission:SA_PUBLIC_USER_TYPES_UPDATE,SA_PUBLIC_USER_TYPES_MANAGE')->name('public-user-types.update');
        Route::patch('public-user-types/{publicUserType}', [PublicUserTypeController::class, 'update'])->middleware('super_admin_permission:SA_PUBLIC_USER_TYPES_UPDATE,SA_PUBLIC_USER_TYPES_MANAGE');
        Route::delete('public-user-types/{publicUserType}', [PublicUserTypeController::class, 'destroy'])->middleware('super_admin_permission:SA_PUBLIC_USER_TYPES_DELETE,SA_PUBLIC_USER_TYPES_MANAGE')->name('public-user-types.destroy');
        Route::patch('public-user-types/{publicUserType}/toggle-status', [PublicUserTypeController::class, 'toggleStatus'])->middleware('super_admin_permission:SA_PUBLIC_USER_TYPES_TOGGLE_STATUS,SA_PUBLIC_USER_TYPES_MANAGE')->name('public-user-types.toggle-status');
        Route::get('public-users/push-notifications', [PublicUserPushNotificationController::class, 'index'])->middleware('super_admin_permission:SA_PUBLIC_USERS_MANAGE')->name('public-users.push-notifications.index');
        Route::post('public-users/push-notifications', [PublicUserPushNotificationController::class, 'store'])->middleware('super_admin_permission:SA_PUBLIC_USERS_MANAGE')->name('public-users.push-notifications.store');
        Route::get('public-users', [PublicUserController::class, 'index'])->middleware('super_admin_permission:SA_PUBLIC_USERS_VIEW,SA_PUBLIC_USERS_MANAGE')->name('public-users.index');
        Route::get('public-users/create', [PublicUserController::class, 'create'])->middleware('super_admin_permission:SA_PUBLIC_USERS_CREATE,SA_PUBLIC_USERS_MANAGE')->name('public-users.create');
        Route::post('public-users', [PublicUserController::class, 'store'])->middleware('super_admin_permission:SA_PUBLIC_USERS_CREATE,SA_PUBLIC_USERS_MANAGE')->name('public-users.store');
        Route::get('public-users/{publicUser}', [PublicUserController::class, 'show'])->middleware('super_admin_permission:SA_PUBLIC_USERS_VIEW,SA_PUBLIC_USERS_MANAGE')->name('public-users.show');
        Route::get('public-users/{publicUser}/edit', [PublicUserController::class, 'edit'])->middleware('super_admin_permission:SA_PUBLIC_USERS_UPDATE,SA_PUBLIC_USERS_MANAGE')->name('public-users.edit');
        Route::put('public-users/{publicUser}', [PublicUserController::class, 'update'])->middleware('super_admin_permission:SA_PUBLIC_USERS_UPDATE,SA_PUBLIC_USERS_MANAGE')->name('public-users.update');
        Route::delete('public-users/{publicUser}', [PublicUserController::class, 'destroy'])->middleware('super_admin_permission:SA_PUBLIC_USERS_DELETE,SA_PUBLIC_USERS_MANAGE')->name('public-users.destroy');
        Route::patch('public-users/{publicUser}/toggle-status', [PublicUserController::class, 'toggleStatus'])->middleware('super_admin_permission:SA_PUBLIC_USERS_TOGGLE_STATUS,SA_PUBLIC_USERS_MANAGE')->name('public-users.toggle-status');
        Route::get('public-reports', [PublicIncidentReportController::class, 'index'])->middleware('super_admin_permission:SA_PUBLIC_REPORTS_VIEW')->name('public-reports.index');
        Route::get('payments', [PaymentController::class, 'index'])->middleware('super_admin_permission:SA_PAYMENTS_VIEW')->name('payments.index');
        Route::post('payments/sessions/{paymentSession}/validate', [PaymentController::class, 'validateSession'])->middleware('super_admin_permission:SA_PAYMENTS_MANUAL_VALIDATE')->name('payments.sessions.validate');
        Route::get('maintenance/cleanup', [MaintenanceCleanupController::class, 'index'])->middleware('super_admin_permission:SA_MAINTENANCE_CLEANUP')->name('maintenance.cleanup.index');
        Route::patch('maintenance/nearby-report-notifications', [MaintenanceCleanupController::class, 'toggleNearbyReportNotifications'])->middleware('super_admin_permission:SA_MAINTENANCE_CLEANUP')->name('maintenance.nearby-report-notifications.toggle');
        Route::delete('maintenance/cleanup', [MaintenanceCleanupController::class, 'destroy'])->middleware('super_admin_permission:SA_MAINTENANCE_CLEANUP')->name('maintenance.cleanup.destroy');
        Route::delete('maintenance/cleanup/table', [MaintenanceCleanupController::class, 'destroyTable'])->middleware('super_admin_permission:SA_MAINTENANCE_CLEANUP')->name('maintenance.cleanup.table.destroy');
        Route::get('discount-cards', [DiscountCardController::class, 'index'])->middleware('super_admin_permission:SA_DISCOUNT_CARDS_VIEW')->name('discount-cards.index');
        Route::get('discount-transactions', [DiscountTransactionController::class, 'index'])->middleware('super_admin_permission:SA_DISCOUNT_TRANSACTIONS_VIEW')->name('discount-transactions.index');
        Route::get('activity-logs', [SuperAdminActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::get('rex-feedbacks', [RexFeedbackController::class, 'index'])->middleware('super_admin_permission:SA_REX_FEEDBACKS_VIEW')->name('rex-feedbacks.index');
        Route::put('rex-feedbacks/settings', [RexFeedbackController::class, 'updateSettings'])->middleware('super_admin_permission:SA_REX_FEEDBACKS_MANAGE')->name('rex-feedbacks.settings');
        Route::get('reparation-damages', [ReparationCaseController::class, 'pendingDamages'])->middleware('super_admin_permission:SA_REPARATION_CASES_MANAGE')->name('reparation-damages.index');
        Route::get('reparation-cases', [ReparationCaseController::class, 'index'])->middleware('super_admin_permission:SA_REPARATION_CASES_MANAGE')->name('reparation-cases.index');
        Route::post('reparation-cases', [ReparationCaseController::class, 'store'])->middleware('super_admin_permission:SA_REPARATION_CASES_MANAGE')->name('reparation-cases.store');
        Route::get('reparation-cases/{reparationCase}', [ReparationCaseController::class, 'show'])->middleware('super_admin_permission:SA_REPARATION_CASES_MANAGE')->name('reparation-cases.show');
        Route::put('reparation-cases/{reparationCase}', [ReparationCaseController::class, 'update'])->middleware('super_admin_permission:SA_REPARATION_CASES_MANAGE')->name('reparation-cases.update');
        Route::post('reparation-cases/{reparationCase}/steps', [ReparationCaseController::class, 'storeStep'])->middleware('super_admin_permission:SA_REPARATION_CASES_MANAGE')->name('reparation-cases.steps.store');

        Route::get('my-roles', [ScopedRoleController::class, 'index'])->middleware('super_admin_permission:SA_SCOPED_ROLES_MANAGE')->name('scoped-roles.index');
        Route::post('my-roles', [ScopedRoleController::class, 'store'])->middleware('super_admin_permission:SA_SCOPED_ROLES_MANAGE')->name('scoped-roles.store');
        Route::get('my-roles/{role}/edit', [ScopedRoleController::class, 'edit'])->middleware('super_admin_permission:SA_SCOPED_ROLES_MANAGE')->name('scoped-roles.edit');
        Route::put('my-roles/{role}', [ScopedRoleController::class, 'update'])->middleware('super_admin_permission:SA_SCOPED_ROLES_MANAGE')->name('scoped-roles.update');
        Route::delete('my-roles/{role}', [ScopedRoleController::class, 'destroy'])->middleware('super_admin_permission:SA_SCOPED_ROLES_MANAGE')->name('scoped-roles.destroy');

        Route::get('my-users', [ScopedUserController::class, 'index'])->middleware('super_admin_permission:SA_SCOPED_USERS_MANAGE')->name('scoped-users.index');
        Route::post('my-users', [ScopedUserController::class, 'store'])->middleware('super_admin_permission:SA_SCOPED_USERS_MANAGE')->name('scoped-users.store');
        Route::get('my-users/{scopedUser}/edit', [ScopedUserController::class, 'edit'])->middleware('super_admin_permission:SA_SCOPED_USERS_MANAGE')->name('scoped-users.edit');
        Route::put('my-users/{scopedUser}', [ScopedUserController::class, 'update'])->middleware('super_admin_permission:SA_SCOPED_USERS_MANAGE')->name('scoped-users.update');
        Route::delete('my-users/{scopedUser}', [ScopedUserController::class, 'destroy'])->middleware('super_admin_permission:SA_SCOPED_USERS_MANAGE')->name('scoped-users.destroy');

        Route::get('roles', [RoleController::class, 'index'])->middleware('super_admin_permission:SA_ROLES_VIEW,SA_ROLES_MANAGE')->name('roles.index');
        Route::post('roles', [RoleController::class, 'store'])->middleware('super_admin_permission:SA_ROLES_MANAGE')->name('roles.store');
        Route::get('roles/{role}', [RoleController::class, 'show'])->middleware('super_admin_permission:SA_ROLES_VIEW,SA_ROLES_MANAGE')->name('roles.show');
        Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->middleware('super_admin_permission:SA_ROLES_MANAGE')->name('roles.edit');
        Route::put('roles/{role}', [RoleController::class, 'update'])->middleware('super_admin_permission:SA_ROLES_MANAGE')->name('roles.update');
        Route::patch('roles/{role}', [RoleController::class, 'update'])->middleware('super_admin_permission:SA_ROLES_MANAGE');
        Route::delete('roles/{role}', [RoleController::class, 'destroy'])->middleware('super_admin_permission:SA_ROLES_MANAGE')->name('roles.destroy');
        Route::patch('roles/{role}/toggle-status', [RoleController::class, 'toggleStatus'])->middleware('super_admin_permission:SA_ROLES_MANAGE')->name('roles.toggle-status');

        Route::get('permissions', [PermissionController::class, 'index'])->middleware('super_admin_permission:SA_PERMISSIONS_VIEW,SA_PERMISSIONS_MANAGE')->name('permissions.index');
        Route::post('permissions', [PermissionController::class, 'store'])->middleware('super_admin_permission:SA_PERMISSIONS_MANAGE')->name('permissions.store');
        Route::get('permissions/{permission}/edit', [PermissionController::class, 'edit'])->middleware('super_admin_permission:SA_PERMISSIONS_MANAGE')->name('permissions.edit');
        Route::put('permissions/{permission}', [PermissionController::class, 'update'])->middleware('super_admin_permission:SA_PERMISSIONS_MANAGE')->name('permissions.update');
        Route::patch('permissions/{permission}', [PermissionController::class, 'update'])->middleware('super_admin_permission:SA_PERMISSIONS_MANAGE');
        Route::delete('permissions/{permission}', [PermissionController::class, 'destroy'])->middleware('super_admin_permission:SA_PERMISSIONS_MANAGE')->name('permissions.destroy');
        Route::patch('permissions/{permission}/toggle-status', [PermissionController::class, 'toggleStatus'])->middleware('super_admin_permission:SA_PERMISSIONS_MANAGE')->name('permissions.toggle-status');
        Route::get('system-users', [SystemUserController::class, 'index'])->middleware('super_admin_permission:SA_SYSTEM_USERS_VIEW,SA_SYSTEM_USERS_MANAGE')->name('system-users.index');
        Route::post('system-users', [SystemUserController::class, 'store'])->middleware('super_admin_permission:SA_SYSTEM_USERS_MANAGE')->name('system-users.store');
        Route::get('system-users/{systemUser}', [SystemUserController::class, 'show'])->middleware('super_admin_permission:SA_SYSTEM_USERS_VIEW,SA_SYSTEM_USERS_MANAGE')->name('system-users.show');
        Route::get('system-users/{systemUser}/edit', [SystemUserController::class, 'edit'])->middleware('super_admin_permission:SA_SYSTEM_USERS_MANAGE')->name('system-users.edit');
        Route::put('system-users/{systemUser}', [SystemUserController::class, 'update'])->middleware('super_admin_permission:SA_SYSTEM_USERS_MANAGE')->name('system-users.update');
        Route::patch('system-users/{systemUser}', [SystemUserController::class, 'update'])->middleware('super_admin_permission:SA_SYSTEM_USERS_MANAGE');
        Route::delete('system-users/{systemUser}', [SystemUserController::class, 'destroy'])->middleware('super_admin_permission:SA_SYSTEM_USERS_MANAGE')->name('system-users.destroy');
        Route::post('system-users/{systemUser}/accesses', [UserAccessController::class, 'store'])->middleware('super_admin_permission:SA_SYSTEM_USERS_MANAGE')->name('system-users.accesses.store');
        Route::put('system-users/{systemUser}/accesses/{access}', [UserAccessController::class, 'update'])->middleware('super_admin_permission:SA_SYSTEM_USERS_MANAGE')->name('system-users.accesses.update');
        Route::delete('system-users/{systemUser}/accesses/{access}', [UserAccessController::class, 'destroy'])->middleware('super_admin_permission:SA_SYSTEM_USERS_MANAGE')->name('system-users.accesses.destroy');
        Route::patch('system-users/{systemUser}/toggle-status', [SystemUserController::class, 'toggleStatus'])->middleware('super_admin_permission:SA_SYSTEM_USERS_TOGGLE_STATUS')->name('system-users.toggle-status');
    });
});

Route::prefix('partner')->name('partner.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('login', [PartnerAuthController::class, 'create'])->name('login');
        Route::post('login', [PartnerAuthController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth', 'partner_access'])->group(function (): void {
        Route::get('dashboard', PartnerDashboardController::class)
            ->middleware('partner_web_permission:PARTNER_DASHBOARD_VIEW')
            ->name('dashboard');
        Route::post('logout', [PartnerAuthController::class, 'destroy'])->name('logout');

        Route::get('offers', [PartnerDiscountOfferController::class, 'index'])
            ->middleware('partner_web_permission:PARTNER_DISCOUNT_HISTORY_VIEW')
            ->name('offers.index');
        Route::get('discount-transactions', [PartnerDiscountTransactionController::class, 'index'])
            ->middleware('partner_web_permission:PARTNER_DISCOUNT_HISTORY_VIEW')
            ->name('discount-transactions.index');
        Route::post('offers', [PartnerDiscountOfferController::class, 'store'])
            ->middleware('partner_web_permission:PARTNER_DISCOUNT_OFFERS_MANAGE')
            ->name('offers.store');
        Route::get('offers/{offer}/edit', [PartnerDiscountOfferController::class, 'edit'])
            ->middleware('partner_web_permission:PARTNER_DISCOUNT_OFFERS_MANAGE')
            ->name('offers.edit');
        Route::put('offers/{offer}', [PartnerDiscountOfferController::class, 'update'])
            ->middleware('partner_web_permission:PARTNER_DISCOUNT_OFFERS_MANAGE')
            ->name('offers.update');
        Route::patch('offers/{offer}/toggle-status', [PartnerDiscountOfferController::class, 'toggleStatus'])
            ->middleware('partner_web_permission:PARTNER_DISCOUNT_OFFERS_MANAGE')
            ->name('offers.toggle-status');

        Route::get('users', [PartnerUserController::class, 'index'])
            ->middleware('partner_web_permission:PARTNER_USERS_MANAGE')
            ->name('users.index');
        Route::post('users', [PartnerUserController::class, 'store'])
            ->middleware('partner_web_permission:PARTNER_USERS_CREATE')
            ->name('users.store');
        Route::get('users/{user}/edit', [PartnerUserController::class, 'edit'])
            ->middleware('partner_web_permission:PARTNER_USERS_UPDATE')
            ->name('users.edit');
        Route::put('users/{user}', [PartnerUserController::class, 'update'])
            ->middleware('partner_web_permission:PARTNER_USERS_UPDATE')
            ->name('users.update');
        Route::patch('users/{user}/toggle-status', [PartnerUserController::class, 'toggleStatus'])
            ->middleware('partner_web_permission:PARTNER_USERS_TOGGLE_STATUS')
            ->name('users.toggle-status');
    });
});

Route::prefix('backoffice')->name('backoffice.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('login', [InternalAccessController::class, 'create'])->name('login');
        Route::post('login', [InternalAccessController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth', 'super_admin_access'])->group(function (): void {
        Route::get('home', InternalHomeController::class)->name('home');
        Route::get('dashboard', BackofficeDashboardController::class)->name('dashboard');
        Route::get('legal-cases', [BackofficeLegalCaseController::class, 'index'])->name('legal-cases.index');
        Route::get('legal-cases/{reparationCase}', [BackofficeLegalCaseController::class, 'show'])->name('legal-cases.show');
        Route::post('legal-cases/{reparationCase}/bailiff-steps', [BackofficeLegalCaseController::class, 'storeBailiffStep'])->name('legal-cases.bailiff-steps.store');
        Route::patch('legal-cases/{reparationCase}/complete-bailiff', [BackofficeLegalCaseController::class, 'completeBailiff'])->name('legal-cases.complete-bailiff');
        Route::patch('legal-cases/{reparationCase}/assign-lawyer', [BackofficeLegalCaseController::class, 'assignLawyer'])->name('legal-cases.assign-lawyer');
        Route::patch('legal-cases/{reparationCase}/conclude-aoda', [BackofficeLegalCaseController::class, 'concludeByAoda'])->name('legal-cases.conclude-aoda');
        Route::post('legal-cases/{reparationCase}/lawyer-steps', [BackofficeLegalCaseController::class, 'storeLawyerStep'])->name('legal-cases.lawyer-steps.store');
        Route::post('logout', [InternalAccessController::class, 'destroy'])->name('logout');
    });
});
