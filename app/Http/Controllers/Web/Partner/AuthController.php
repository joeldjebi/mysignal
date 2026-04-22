<?php

namespace App\Http\Controllers\Web\Partner;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Audit\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user()?->loadMissing('organization.organizationType');

            if ($user?->organization?->organizationType?->code === 'PARTNER_ESTABLISHMENT') {
                return redirect()->route('partner.dashboard');
            }
        }

        return view('partner.auth.login');
    }

    public function store(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Les identifiants fournis sont invalides.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();
        $user = $request->user()?->loadMissing(['organization.organizationType', 'permissions', 'roles.permissions']);

        if (
            ! $user instanceof User ||
            $user->is_super_admin ||
            $user->status !== 'active' ||
            $user->organization_id === null ||
            $user->organization?->organizationType?->code !== 'PARTNER_ESTABLISHMENT' ||
            ! $user->permissionCodes()->contains('PARTNER_ACCESS_PORTAL')
        ) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Ce compte n a pas acces au portail partenaire.',
            ])->onlyInput('email');
        }

        $activityLogger->log(
            'partner.web.login',
            'Connexion au portail partenaire.',
            $user,
            [],
            $request,
            $user,
            'partner',
        );

        return redirect()->intended(route('partner.dashboard'));
    }

    public function destroy(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $user = $request->user();

        if ($user instanceof User) {
            $activityLogger->log(
                'partner.web.logout',
                'Deconnexion du portail partenaire.',
                $user,
                [],
                $request,
                $user,
                'partner',
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('partner.login');
    }
}
