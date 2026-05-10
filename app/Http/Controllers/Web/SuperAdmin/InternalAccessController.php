<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Audit\ActivityLogger;
use App\Support\Auth\SuperAdminAccessResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InternalAccessController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::check() && $this->isInternalPortalUser(Auth::user())) {
            return redirect()->route($this->resolveRedirectRoute(Auth::user()));
        }

        return view('super-admin.auth.internal-login');
    }

    public function store(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors([
                    'email' => 'Les identifiants fournis sont invalides.',
                ])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = $request->user();

        if (! $this->isInternalPortalUser($user)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors([
                    'email' => 'Ce compte n a pas acces au portail backoffice.',
                ])
                ->onlyInput('email');
        }

        $activityLogger->log(
            'backoffice.login',
            'Connexion au portail backoffice.',
            $user,
            [],
            $request,
            $user,
            'backoffice',
        );

        return redirect()->intended(route($this->resolveRedirectRoute($user)));
    }

    public function destroy(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $user = $request->user();

        if ($user instanceof User) {
            $activityLogger->log(
                'backoffice.logout',
                'Deconnexion du portail backoffice.',
                $user,
                [],
                $request,
                $user,
                'backoffice',
            );
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('backoffice.login');
    }

    private function isInternalPortalUser(?User $user): bool
    {
        if ($user === null || $user->is_super_admin || $user->status !== 'active') {
            return false;
        }

        $access = app(SuperAdminAccessResolver::class)->resolve($user);

        if ($access !== null) {
            app(SuperAdminAccessResolver::class)->apply($user, $access);
        }

        return $access !== null
            && in_array($access->portal, ['backoffice', 'huissier', 'aoda', 'avocat'], true)
            && $user->hasEffectivePermissionCode('SA_ACCESS_PORTAL');
    }

    private function resolveRedirectRoute(?User $user): string
    {
        if (! $user) {
            return 'backoffice.home';
        }

        foreach ([
            'BO_REPARATION_CASES_HUISSIER' => 'backoffice.dashboard',
            'BO_REPARATION_CASES_AODA' => 'backoffice.dashboard',
            'BO_REPARATION_CASES_AVOCAT' => 'backoffice.dashboard',
            'SA_DASHBOARD_VIEW' => 'super-admin.dashboard',
            'SA_LANDING_PAGE_MANAGE' => 'super-admin.landing-page.edit',
            'SA_SYSTEM_USERS_MANAGE' => 'super-admin.system-users.index',
            'SA_REPARATION_CASES_MANAGE' => 'super-admin.reparation-cases.index',
            'SA_PAYMENTS_VIEW' => 'super-admin.payments.index',
            'SA_DISCOUNT_CARDS_VIEW' => 'super-admin.discount-cards.index',
            'SA_DISCOUNT_TRANSACTIONS_VIEW' => 'super-admin.discount-transactions.index',
            'SA_ACTIVITY_LOGS_VIEW_SELF' => 'super-admin.activity-logs.index',
            'SA_ACTIVITY_LOGS_VIEW_INSTITUTION' => 'super-admin.activity-logs.index',
            'SA_ACTIVITY_LOGS_VIEW_PUBLIC' => 'super-admin.activity-logs.index',
            'SA_ACTIVITY_LOGS_VIEW_INTERNAL' => 'super-admin.activity-logs.index',
            'SA_PUBLIC_USERS_MANAGE' => 'super-admin.public-users.index',
            'SA_PUBLIC_REPORTS_VIEW' => 'super-admin.public-reports.index',
            'SA_ORGANIZATIONS_MANAGE' => 'super-admin.organizations.index',
            'SA_APPLICATIONS_MANAGE' => 'super-admin.applications.index',
            'SA_ROLES_MANAGE' => 'super-admin.roles.index',
            'SA_PERMISSIONS_MANAGE' => 'super-admin.permissions.index',
        ] as $permissionCode => $routeName) {
            if ($user->hasEffectivePermissionCode($permissionCode)) {
                return $routeName;
            }
        }

        return 'backoffice.home';
    }
}
