@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Modifier '.$systemUser->name)
@section('page-title', 'Modifier un utilisateur interne')
@section('page-description', 'Mettre à jour le compte, ses rôles et ses droits de consultation des activités.')

@section('content')
    @php
        $partnerAccess = $systemUser->accesses
            ->first(fn ($access) => $access->portal === 'partner' && $access->status === 'active');
        $inheritedPermissionGroups = $systemUser->roles
            ->flatMap(fn ($role) => $role->permissions)
            ->unique('id')
            ->sortBy('name')
            ->groupBy(function ($permission) {
                $segments = explode('_', (string) $permission->code);

                if (($segments[0] ?? null) === 'SA') {
                    return match ($segments[1] ?? null) {
                        'ACCESS' => 'Accès au portail',
                        'DASHBOARD' => 'Tableau de bord',
                        'PUBLIC' => 'Usagers publics et signalements',
                        'PAYMENTS' => 'Paiements',
                        'ACTIVITY' => 'Journaux d’activité',
                        'INSTITUTION' => 'Admins institutionnels',
                        'SYSTEM' => 'Utilisateurs internes',
                        'ROLES' => 'Rôles',
                        'PERMISSIONS' => 'Permissions',
                        'REPARATION' => 'Dossiers contentieux',
                        'APPLICATIONS' => 'Applications',
                        'FEATURES' => 'Fonctionnalités',
                        'SLA' => 'SLA',
                        'ORGANIZATIONS' => 'Organisations',
                        'ORGANIZATION' => 'Sous catégories',
                        'PRICING' => 'Tarification',
                        'COUNTRIES', 'CITIES', 'COMMUNES' => 'Géographie',
                        'BUSINESS' => 'Secteurs',
                        default => 'Autres permissions SA',
                    };
                }

                return 'Autres permissions';
            })
            ->sortKeys();

    @endphp

    <section class="panel-card">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-between gap-3 mb-4">
            <div>
                <div class="fw-bold">Modification de {{ $systemUser->name }}</div>
                <div class="text-secondary small">Ajuste le profil, les rôles et la visibilité des activités sans perdre en lisibilité.</div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <span class="badge-soft">{{ $systemUser->status === 'active' ? 'Compte actif' : 'Compte inactif' }}</span>
                <span class="badge-soft">{{ $systemUser->roles->count() }} rôle{{ $systemUser->roles->count() > 1 ? 's' : '' }}</span>
            </div>
        </div>

        <div class="alert alert-light border rounded-4 mb-4">
            <div class="fw-bold mb-2">Comment gérer les droits de cet utilisateur</div>
            <div class="small text-secondary mb-1">Les rôles doivent rester la base du profil, car ils regroupent un ensemble cohérent de permissions.</div>
            <div class="small text-secondary mb-1">Les permissions des rôles sont héritées automatiquement par le compte.</div>
            <div class="small text-secondary mb-1">Les permissions directes ne sont plus utilisées pour les utilisateurs internes créés par le super admin.</div>
            <div class="small text-secondary mb-0">Le mot de passe est optionnel ici : laissez le champ vide pour conserver l’accès actuel.</div>
        </div>

        <div class="alert alert-info border rounded-4 mb-4">
            <div class="fw-bold mb-2">Permissions héritées des rôles</div>
            @if ($inheritedPermissionGroups->isNotEmpty())
                <div class="small text-secondary mb-3">Ces permissions viennent des rôles sélectionnés pour ce compte. Elles sont déjà actives via les rôles attribués.</div>
                <div class="row g-3">
                    @foreach ($inheritedPermissionGroups as $groupLabel => $groupPermissions)
                        <div class="col-lg-6">
                            <div class="border rounded-3 p-3 h-100 bg-white">
                                <div class="fw-bold small text-uppercase text-secondary mb-2">{{ $groupLabel }}</div>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ($groupPermissions as $permission)
                                        <span class="badge text-bg-light border">{{ $permission->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="small text-secondary mb-0">Aucune permission n’est actuellement héritée, car aucun rôle attribué ne porte encore de permission.</div>
            @endif
        </div>

        <form method="POST" action="{{ route('super-admin.system-users.update', $systemUser) }}" class="row g-4">
            @csrf
            @method('PUT')
            <div class="col-md-6">
                <label class="form-label">Nom complet</label>
                <input type="text" name="name" value="{{ old('name', $systemUser->name) }}" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email', $systemUser->email) }}" class="form-control" required>
            </div>
            <div class="col-md-6">
                @include('partials.phone-field', ['value' => old('phone', $systemUser->phone), 'placeholder' => '0700000000'])
            </div>
            <div class="col-md-6">
                <label class="form-label">Nouveau mot de passe</label>
                <input type="password" name="password" class="form-control" placeholder="Laisser vide pour conserver l’actuel">
                <div class="small text-secondary mt-1">Utilise ce champ seulement si tu veux remplacer le mot de passe existant.</div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Rôles</label>
                <div class="small text-secondary mb-2">Les rôles regroupent plusieurs permissions. C’est désormais la seule méthode d’attribution des droits.</div>
                <div class="border rounded-3 p-3" style="max-height: 460px; overflow:auto;">
                    @if ($roles->isNotEmpty())
                        @foreach ($roles as $role)
                        <div class="border rounded-3 p-2 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="{{ $role->id }}" name="role_ids[]" id="edit-role-{{ $role->id }}" @checked(in_array($role->id, old('role_ids', $systemUser->roles->pluck('id')->all())))>
                                <label class="form-check-label w-100" for="edit-role-{{ $role->id }}">
                                    <div class="fw-semibold">{{ $role->name }}</div>
                                    <div class="small text-secondary">{{ $role->code }}</div>
                                    <div class="small text-secondary">{{ $role->description ?: 'Aucune description renseignée.' }}</div>
                                </label>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-secondary small">Aucun rôle actif disponible.</div>
                    @endif
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Institution partenaire</label>
                <select name="partner_organization_id" class="form-select">
                    <option value="">Aucune</option>
                    @foreach ($partnerOrganizations as $organization)
                        <option value="{{ $organization->id }}" @selected((string) old('partner_organization_id', $partnerAccess?->organization_id) === (string) $organization->id)>{{ $organization->name }}</option>
                    @endforeach
                </select>
                <div class="small text-secondary mt-1">Obligatoire si ce compte a un rôle partenaire. Le profil d’accès partenaire sera créé ou mis à jour automatiquement.</div>
            </div>
            <div class="col-12">
                <label class="form-label">Utilisateurs internes dont l’activité est visible</label>
                <div class="small text-secondary mb-1">Ce paramètre sert seulement si ce compte reçoit le droit de voir les activités des utilisateurs internes.</div>
                <div class="small text-secondary mb-2">Le rôle attribué doit inclure <span class="fw-semibold">Voir les activités des utilisateurs internes</span> dans la catégorie <span class="fw-semibold">Journaux d’activité</span>.</div>
                <div class="border rounded-3 p-3" style="max-height: 240px; overflow:auto;">
                    @if ($visibleActivityUsers->isNotEmpty())
                        @foreach ($visibleActivityUsers as $visibleUser)
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" value="{{ $visibleUser->id }}" name="activity_visible_user_ids[]" id="activity-visible-user-{{ $visibleUser->id }}" @checked(in_array($visibleUser->id, old('activity_visible_user_ids', $systemUser->activityLogVisibleUsers->pluck('id')->all())))>
                            <label class="form-check-label" for="activity-visible-user-{{ $visibleUser->id }}">
                                <span class="d-block fw-semibold">{{ $visibleUser->name }}</span>
                                <span class="small text-secondary">{{ $visibleUser->email }}</span>
                            </label>
                        </div>
                        @endforeach
                    @else
                        <div class="text-secondary small">Aucun autre utilisateur interne disponible.</div>
                    @endif
                </div>
            </div>
            <div class="col-12 d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-dark">Enregistrer</button>
                <a href="{{ route('super-admin.system-users.index') }}" class="btn btn-outline-secondary">Retour</a>
            </div>
        </form>
    </section>
@endsection
