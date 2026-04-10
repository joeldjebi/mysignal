<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Audit\ActivityLogger;
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
        return $user !== null
            && ! $user->is_super_admin
            && $user->organization_id === null
            && $user->hasPermissionCode('SA_ACCESS_PORTAL');
    }

    private function resolveRedirectRoute(?User $user): string
    {
        if (! $user) {
            return 'backoffice.home';
        }

        foreach ([
            'SA_DASHBOARD_VIEW' => 'super-admin.dashboard',
            'SA_SYSTEM_USERS_MANAGE' => 'super-admin.system-users.index',
            'SA_REPARATION_CASES_MANAGE' => 'super-admin.reparation-cases.index',
            'SA_PAYMENTS_VIEW' => 'super-admin.payments.index',
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
            if ($user->hasPermissionCode($permissionCode)) {
                return $routeName;
            }
        }

        return 'backoffice.home';
    }
}
