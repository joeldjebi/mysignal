<?php

namespace App\Http\Controllers\Web\Partner;

use App\Domain\Partners\Actions\CreatePartnerUserAction;
use App\Domain\Partners\Actions\TogglePartnerUserStatusAction;
use App\Domain\Partners\Actions\UpdatePartnerUserAction;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Partner\Concerns\InteractsWithPartnerContext;
use App\Models\User;
use App\Support\Audit\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    use InteractsWithPartnerContext;

    public function index(): View
    {
        $context = $this->partnerContext();
        $organization = $context['organization'];
        abort_if($organization === null, 403);

        $query = User::query()
            ->with(['roles', 'permissions'])
            ->where('organization_id', $organization->id)
            ->where('is_super_admin', false);

        if (filled(request('search'))) {
            $search = trim((string) request('search'));
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%');
            });
        }

        if (filled(request('status'))) {
            $query->where('status', request('status'));
        }

        if (filled(request('role_code'))) {
            $query->whereHas('roles', fn ($builder) => $builder->where('roles.code', request('role_code')));
        }

        return view('partner.users.index', [
            'organization' => $organization,
            'activeNav' => 'users',
            'authorization' => $this->partnerAuthorizationFlags(),
            'users' => $query->latest('id')->paginate(12)->withQueryString(),
            'roles' => $this->partnerRoles(),
        ]);
    }

    public function store(Request $request, CreatePartnerUserAction $action, ActivityLogger $activityLogger): RedirectResponse
    {
        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8'],
            'role_code' => ['required', Rule::in(['PARTNER_ADMIN', 'PARTNER_MANAGER', 'PARTNER_AGENT'])],
        ]);

        $user = $action->handle($request->user(), $attributes);

        $activityLogger->log(
            'partner.web.user.created',
            'Creation d un utilisateur partenaire depuis le dashboard web.',
            $user,
            [
                'organization_id' => $user->organization_id,
                'role_codes' => $user->roles->pluck('code')->all(),
            ],
            $request,
            $request->user(),
            'partner',
        );

        return redirect()->route('partner.users.index')->with('success', 'L utilisateur partenaire a ete cree.');
    }

    public function edit(User $user): View
    {
        $context = $this->partnerContext();
        $organization = $context['organization'];
        abort_if($organization === null || $user->organization_id !== $organization->id || $user->is_super_admin, 404);

        return view('partner.users.edit', [
            'organization' => $organization,
            'activeNav' => 'users',
            'authorization' => $this->partnerAuthorizationFlags(),
            'userAccount' => $user->load(['roles', 'permissions']),
            'roles' => $this->partnerRoles(),
        ]);
    }

    public function update(Request $request, User $user, UpdatePartnerUserAction $action, ActivityLogger $activityLogger): RedirectResponse
    {
        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'string', 'min:8'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'role_code' => ['required', Rule::in(['PARTNER_ADMIN', 'PARTNER_MANAGER', 'PARTNER_AGENT'])],
        ]);

        $updatedUser = $action->handle($request->user(), $user, $attributes);

        $activityLogger->log(
            'partner.web.user.updated',
            'Mise a jour d un utilisateur partenaire depuis le dashboard web.',
            $updatedUser,
            [
                'organization_id' => $updatedUser->organization_id,
                'role_codes' => $updatedUser->roles->pluck('code')->all(),
                'status' => $updatedUser->status,
            ],
            $request,
            $request->user(),
            'partner',
        );

        return redirect()->route('partner.users.index')->with('success', 'L utilisateur partenaire a ete mis a jour.');
    }

    public function toggleStatus(Request $request, User $user, TogglePartnerUserStatusAction $action, ActivityLogger $activityLogger): RedirectResponse
    {
        $updatedUser = $action->handle($request->user(), $user);

        $activityLogger->log(
            'partner.web.user.status_toggled',
            'Changement de statut d un utilisateur partenaire.',
            $updatedUser,
            [
                'organization_id' => $updatedUser->organization_id,
                'status' => $updatedUser->status,
            ],
            $request,
            $request->user(),
            'partner',
        );

        return back()->with('success', 'Le statut de l utilisateur partenaire a ete mis a jour.');
    }
}
