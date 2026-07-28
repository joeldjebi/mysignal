<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use App\Models\Organization;
use App\Models\User;
use App\Models\UserType;
use App\Services\SmsService;
use App\Services\TopTeaserEmailService;
use App\Support\Audit\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class InstitutionAdminController extends Controller
{
    public function index(): View
    {
        $perPage = min(max((int) request()->integer('per_page', 12), 1), 100);
        $query = User::query()
            ->with(['organization.application.features', 'organization.featureOverrides', 'features', 'userType'])
            ->where('is_super_admin', false)
            ->where('user_type_id', UserType::idFor(UserType::INSTITUTION_ADMIN))
            ->whereNotNull('organization_id')
            ->whereHas('organization', fn ($organizationQuery) => $this->onlyInstitutionOrganizations($organizationQuery));

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

        if (filled(request('organization_id'))) {
            $query->where('organization_id', request('organization_id'));
        }

        return view('super-admin.institution-admins.index', [
            'admins' => $query->latest()->paginate($perPage)->withQueryString(),
            'organizations' => $this->institutionOrganizationsQuery()
                ->with(['application.features', 'featureOverrides'])
                ->orderBy('name')
                ->get(),
            'features' => Feature::query()->where('status', 'active')->orderBy('name')->get(),
            'orphanedAdminsCount' => $this->orphanedInstitutionAdminsQuery()->count(),
        ]);
    }

    public function orphaned(Request $request): View
    {
        $perPage = min(max((int) $request->integer('per_page', 12), 1), 100);
        $query = $this->orphanedInstitutionAdminsQuery()
            ->with(['creator', 'userType']);

        if (filled($request->input('search'))) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%');
            });
        }

        if (filled($request->input('status'))) {
            $query->where('status', $request->input('status'));
        }

        return view('super-admin.institution-admins.orphaned', [
            'admins' => $query->latest()->paginate($perPage)->withQueryString(),
        ]);
    }

    public function store(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $attributes = $this->validateRequest($request);
        $organization = $this->institutionOrganizationsQuery()
            ->with(['application.features', 'featureOverrides'])
            ->findOrFail($attributes['organization_id']);

        $admin = User::query()->create([
            'user_type_id' => UserType::idFor(UserType::INSTITUTION_ADMIN),
            'organization_id' => $attributes['organization_id'],
            'name' => $attributes['name'],
            'email' => $attributes['email'],
            'phone' => $attributes['phone'] ?? null,
            'password' => Hash::make($attributes['password']),
            'is_super_admin' => false,
            'status' => 'active',
            'created_by' => $request->user()->id,
        ]);

        $this->syncInstitutionAdminFeatures($admin, $organization, $attributes['feature_ids'] ?? []);

        $activityLogger->log(
            'institution_admin.created',
            'Création d’un admin institutionnel.',
            $admin->fresh('features'),
            [
                'organization_id' => $admin->organization_id,
                'feature_ids' => $attributes['feature_ids'] ?? [],
            ],
            $request,
            $request->user(),
        );

        return redirect()->route('super-admin.institution-admins.index')
            ->with('success', 'L’admin institutionnel a été créé.');
    }

    public function edit(User $institutionAdmin): View
    {
        $this->abortIfNotInstitutionAdmin($institutionAdmin);

        return view('super-admin.institution-admins.edit', [
            'institutionAdmin' => $institutionAdmin->load(['organization.application.features', 'organization.featureOverrides', 'features']),
            'organizations' => $this->institutionOrganizationsQuery()
                ->with(['application.features', 'featureOverrides'])
                ->orderBy('name')
                ->get(),
            'features' => Feature::query()->where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $institutionAdmin, ActivityLogger $activityLogger): RedirectResponse
    {
        $this->abortIfNotInstitutionAdmin($institutionAdmin);

        $attributes = $this->validateRequest($request, $institutionAdmin);
        $organization = $this->institutionOrganizationsQuery()
            ->with(['application.features', 'featureOverrides'])
            ->findOrFail($attributes['organization_id']);
        $before = [
            'organization_id' => $institutionAdmin->organization_id,
            'name' => $institutionAdmin->name,
            'email' => $institutionAdmin->email,
            'phone' => $institutionAdmin->phone,
            'feature_ids' => $institutionAdmin->features()->pluck('features.id')->all(),
        ];

        $payload = [
            'organization_id' => $attributes['organization_id'],
            'name' => $attributes['name'],
            'email' => $attributes['email'],
            'phone' => $attributes['phone'] ?? null,
        ];

        if (filled($attributes['password'] ?? null)) {
            $payload['password'] = Hash::make($attributes['password']);
        }

        $institutionAdmin->update($payload);
        $this->syncInstitutionAdminFeatures($institutionAdmin, $organization, $attributes['feature_ids'] ?? []);

        $activityLogger->log(
            'institution_admin.updated',
            'Mise à jour d’un admin institutionnel.',
            $institutionAdmin->fresh('features'),
            [
                'before' => $before,
                'after' => [
                    'organization_id' => $institutionAdmin->organization_id,
                    'name' => $institutionAdmin->name,
                    'email' => $institutionAdmin->email,
                    'phone' => $institutionAdmin->phone,
                    'feature_ids' => $institutionAdmin->features()->pluck('features.id')->all(),
                ],
            ],
            $request,
            $request->user(),
        );

        return redirect()->route('super-admin.institution-admins.index')
            ->with('success', 'L’admin institutionnel a été mis à jour.');
    }

    public function destroy(Request $request, User $institutionAdmin, ActivityLogger $activityLogger): RedirectResponse
    {
        $this->abortIfNotInstitutionAdmin($institutionAdmin);

        $activityLogger->log(
            'institution_admin.deleted',
            'Suppression d’un admin institutionnel.',
            $institutionAdmin,
            [
                'organization_id' => $institutionAdmin->organization_id,
                'email' => $institutionAdmin->email,
            ],
            $request,
            $request->user(),
        );
        $institutionAdmin->delete();

        return redirect()->route('super-admin.institution-admins.index')
            ->with('success', 'L’admin institutionnel a été supprimé.');
    }

    public function toggleStatus(Request $request, User $institutionAdmin, ActivityLogger $activityLogger): RedirectResponse
    {
        $this->abortIfNotInstitutionAdmin($institutionAdmin);

        $previousStatus = $institutionAdmin->status;

        $institutionAdmin->update([
            'status' => $institutionAdmin->status === 'active' ? 'inactive' : 'active',
        ]);

        $activityLogger->log(
            'institution_admin.status_toggled',
            'Changement de statut d’un admin institutionnel.',
            $institutionAdmin,
            [
                'before' => $previousStatus,
                'after' => $institutionAdmin->status,
            ],
            $request,
            $request->user(),
        );

        return back()->with('success', 'Le statut de l’admin institutionnel a été mis à jour.');
    }

    public function sendAccess(Request $request, User $institutionAdmin, SmsService $smsService, TopTeaserEmailService $emailService, ActivityLogger $activityLogger): RedirectResponse
    {
        $this->abortIfNotInstitutionAdmin($institutionAdmin);

        if (blank($institutionAdmin->phone) && blank($institutionAdmin->email)) {
            return back()->withErrors([
                'access' => 'Le compte doit avoir un numéro de téléphone ou une adresse email avant l’envoi des accès.',
            ]);
        }

        $password = $this->temporaryPassword();
        $loginUrl = route('institution.login');
        $previousPasswordHash = $institutionAdmin->password;
        $previousStatus = $institutionAdmin->status;

        $institutionAdmin->update([
            'password' => Hash::make($password),
            'status' => 'active',
        ]);

        $message = "My-Signal: accès portail institutionnel. Lien: {$loginUrl} Identifiant: {$institutionAdmin->email} Mot de passe temporaire: {$password}";
        $smsSent = false;
        $mailSent = false;
        $errors = [];

        if (filled($institutionAdmin->phone)) {
            try {
                $smsService->sendSmsMtarget($message, (string) $institutionAdmin->phone);
                $smsSent = true;
            } catch (Throwable $exception) {
                $errors[] = 'SMS: '.$exception->getMessage();
            }
        }

        if (filled($institutionAdmin->email)) {
            try {
                $emailService->send(
                    (string) $institutionAdmin->email,
                    'Vos accès au portail institutionnel My-Signal',
                    $this->accessEmailHtml($institutionAdmin, $loginUrl, $password),
                    $this->accessEmailText($institutionAdmin, $loginUrl, $password),
                );
                $mailSent = true;
            } catch (Throwable $exception) {
                $errors[] = 'Email: '.$exception->getMessage();
            }
        }

        if (! $smsSent && ! $mailSent) {
            $institutionAdmin->forceFill([
                'password' => $previousPasswordHash,
                'status' => $previousStatus,
            ])->save();

            $activityLogger->log(
                'institution_admin.access_send_failed',
                'Échec d’envoi des accès admin institutionnel.',
                $institutionAdmin,
                [
                    'phone' => $institutionAdmin->phone,
                    'email' => $institutionAdmin->email,
                    'errors' => $errors,
                ],
                $request,
                $request->user(),
            );

            return back()->withErrors([
                'access' => 'L’envoi des accès a échoué. Le mot de passe précédent a été conservé. Cause: '.implode(' | ', $errors),
            ]);
        }

        $activityLogger->log(
            'institution_admin.access_sent',
            'Envoi des accès admin institutionnel.',
            $institutionAdmin,
            [
                'phone' => $institutionAdmin->phone,
                'email' => $institutionAdmin->email,
                'login_url' => $loginUrl,
                'sms_sent' => $smsSent,
                'mail_sent' => $mailSent,
                'errors' => $errors,
            ],
            $request,
            $request->user(),
        );

        $channels = collect([
            $smsSent ? 'SMS' : null,
            $mailSent ? 'email' : null,
        ])->filter()->implode(' et ');

        $message = 'Les accès ont été envoyés par '.$channels.'.';

        if ($errors !== []) {
            $message .= ' Un canal n’a pas abouti. Cause: '.implode(' | ', $errors);
        }

        return back()->with('success', $message);
    }

    private function validateRequest(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'organization_id' => ['required', 'exists:organizations,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8'],
            'feature_ids' => ['nullable', 'array'],
            'feature_ids.*' => ['integer', 'exists:features,id'],
        ]);
    }

    private function allowedFeatureIdsForOrganization(Organization $organization, array $featureIds): array
    {
        $allowedFeatureIds = collect($organization->resolvedFeatureIds())
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $selectedFeatureIds = collect($featureIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($allowedFeatureIds->isEmpty()) {
            return $selectedFeatureIds->all();
        }

        return $selectedFeatureIds
            ->filter(fn ($id) => $allowedFeatureIds->contains($id))
            ->values()
            ->all();
    }

    private function syncInstitutionAdminFeatures(User $institutionAdmin, Organization $organization, array $featureIds): void
    {
        $allowedFeatureIds = collect($organization->resolvedFeatureIds())
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $selectedFeatureIds = collect($this->allowedFeatureIdsForOrganization($organization, $featureIds))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $inheritsAllOrganizationFeatures = $allowedFeatureIds->isNotEmpty()
            && $selectedFeatureIds->count() === $allowedFeatureIds->count()
            && $selectedFeatureIds->diff($allowedFeatureIds)->isEmpty();

        // No direct assignment means the root AI inherits the organization's
        // full feature perimeter, including future app/org feature updates.
        $institutionAdmin->features()->sync($inheritsAllOrganizationFeatures ? [] : $selectedFeatureIds->all());
    }

    private function temporaryPassword(): string
    {
        return 'MS-'.Str::upper(Str::random(4)).'-'.random_int(1000, 9999);
    }

    private function accessEmailHtml(User $institutionAdmin, string $loginUrl, string $password): string
    {
        $name = e($institutionAdmin->name ?: 'Admin institutionnel');
        $email = e($institutionAdmin->email);
        $url = e($loginUrl);
        $temporaryPassword = e($password);

        return <<<HTML
<div style="font-family:Arial,sans-serif;color:#152536;line-height:1.55">
  <h2 style="margin:0 0 12px">Vos accès My-Signal</h2>
  <p>Bonjour {$name},</p>
  <p>Votre compte administrateur institutionnel est prêt.</p>
  <p><strong>Lien de connexion :</strong> <a href="{$url}">{$url}</a><br>
  <strong>Identifiant :</strong> {$email}<br>
  <strong>Mot de passe temporaire :</strong> {$temporaryPassword}</p>
  <p>Pour des raisons de sécurité, veuillez modifier ce mot de passe après votre première connexion.</p>
</div>
HTML;
    }

    private function accessEmailText(User $institutionAdmin, string $loginUrl, string $password): string
    {
        $name = $institutionAdmin->name ?: 'Admin institutionnel';

        return "Bonjour {$name},\n\nVotre compte administrateur institutionnel est prêt.\nLien de connexion: {$loginUrl}\nIdentifiant: {$institutionAdmin->email}\nMot de passe temporaire: {$password}\n\nVeuillez modifier ce mot de passe après votre première connexion.";
    }

    private function institutionOrganizationsQuery()
    {
        return Organization::query()
            ->where('status', 'active')
            ->where(fn ($query) => $this->onlyInstitutionOrganizations($query));
    }

    private function onlyInstitutionOrganizations($query)
    {
        return $query->whereDoesntHave('organizationType', fn ($typeQuery) => $typeQuery->where('code', 'PARTNER_ESTABLISHMENT'));
    }

    private function abortIfNotInstitutionAdmin(User $user): void
    {
        $user->loadMissing('organization.organizationType');

        abort_if(
            $user->is_super_admin
                || (int) $user->user_type_id !== (int) UserType::idFor(UserType::INSTITUTION_ADMIN)
                || $user->organization_id === null
                || $user->organization?->organizationType?->code === 'PARTNER_ESTABLISHMENT',
            404
        );
    }

    private function orphanedInstitutionAdminsQuery()
    {
        return User::query()
            ->where('is_super_admin', false)
            ->where('user_type_id', UserType::idFor(UserType::INSTITUTION_ADMIN))
            ->whereNull('organization_id');
    }
}
