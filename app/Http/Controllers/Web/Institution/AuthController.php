<?php

namespace App\Http\Controllers\Web\Institution;

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
            if (Auth::user()?->is_super_admin) {
                return redirect()->route('super-admin.dashboard');
            }

            if (Auth::user()?->organization_id !== null) {
                return redirect()->route('institution.dashboard');
            }
        }

        return view('institution.auth.login');
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

        if ($user?->is_super_admin || $user?->organization_id === null) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors([
                    'email' => 'Ce compte n a pas acces au portail institutionnel.',
                ])
                ->onlyInput('email');
        }

        if ($user instanceof User) {
            $activityLogger->log(
                'institution.login',
                'Connexion au portail institutionnel.',
                $user,
                [],
                $request,
                $user,
                'institution',
            );
        }

        return redirect()->intended(route('institution.dashboard'));
    }

    public function destroy(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $user = $request->user();

        if ($user instanceof User) {
            $activityLogger->log(
                'institution.logout',
                'Deconnexion du portail institutionnel.',
                $user,
                [],
                $request,
                $user,
                'institution',
            );
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('institution.login');
    }
}
