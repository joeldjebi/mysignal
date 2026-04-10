@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Modifier '.$systemUser->name)
@section('page-title', 'Modifier un utilisateur interne')
@section('page-description', 'Mettre a jour le compte, ses roles et ses droits de consultation des activites.')

@section('content')
    @php
        $inheritedPermissionGroups = $systemUser->roles
            ->flatMap(fn ($role) => $role->permissions)
            ->unique('id')
            ->sortBy('name')
            ->groupBy(function ($permission) {
                $segments = explode('_', (string) $permission->code);

                if (($segments[0] ?? null) === 'SA') {
                    return match ($segments[1] ?? null) {
                        'ACCESS' => 'Acces au portail',
                        'DASHBOARD' => 'Dashboard',
                        'PUBLIC' => 'Usagers publics et signalements',
                        'PAYMENTS' => 'Paiements',
                        'ACTIVITY' => 'Journaux d activite',
                        'INSTITUTION' => 'Admins institutionnels',
                        'SYSTEM' => 'Utilisateurs internes',
                        'ROLES' => 'Roles',
                        'PERMISSIONS' => 'Permissions',
                        'REPARATION' => 'Dossiers contentieux',
                        'APPLICATIONS' => 'Applications',
                        'FEATURES' => 'Fonctionnalites',
                        'SLA' => 'SLA',
                        'ORGANIZATIONS' => 'Organisations',
                        'ORGANIZATION' => 'Types d organisation',
                        'PRICING' => 'Tarification',
                        'COUNTRIES', 'CITIES', 'COMMUNES' => 'Geographie',
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
                <div class="fw-bold">Edition de {{ $systemUser->name }}</div>
                <div class="text-secondary small">Ajuste le profil, les roles et la visibilite des activites sans perdre en lisibilite.</div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <span class="badge-soft">{{ $systemUser->status === 'active' ? 'Compte actif' : 'Compte inactif' }}</span>
                <span class="badge-soft">{{ $systemUser->roles->count() }} role{{ $systemUser->roles->count() > 1 ? 's' : '' }}</span>
            </div>
        </div>

        <div class="alert alert-light border rounded-4 mb-4">
            <div class="fw-bold mb-2">Comment gerer les droits de cet utilisateur</div>
            <div class="small text-secondary mb-1">Les roles doivent rester la base du profil, car ils regroupent un ensemble coherent de permissions.</div>
            <div class="small text-secondary mb-1">Les permissions des roles sont heritees automatiquement par le compte.</div>
            <div class="small text-secondary mb-1">Les permissions directes ne sont plus utilisees pour les utilisateurs internes crees par le super admin.</div>
            <div class="small text-secondary mb-0">Le mot de passe est optionnel ici : laisse le champ vide si tu veux conserver l acces actuel.</div>
        </div>

        <div class="alert alert-info border rounded-4 mb-4">
            <div class="fw-bold mb-2">Permissions heritees des roles</div>
            @if ($inheritedPermissionGroups->isNotEmpty())
                <div class="small text-secondary mb-3">Ces permissions viennent des roles selectionnes pour ce compte. Elles sont deja actives via l heritage des roles.</div>
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
                <div class="small text-secondary mb-0">Aucune permission n est actuellement heritee, car aucun role attribue ne porte encore de permission.</div>
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
                <input type="password" name="password" class="form-control" placeholder="Laisser vide pour conserver l actuel">
                <div class="small text-secondary mt-1">Utilise ce champ seulement si tu veux remplacer le mot de passe existant.</div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Roles</label>
                <div class="small text-secondary mb-2">Les roles regroupent plusieurs permissions. C est desormais l unique methode d attribution des droits.</div>
                <div class="border rounded-3 p-3" style="max-height: 460px; overflow:auto;">
                    @forelse ($roles as $role)
                        <div class="border rounded-3 p-2 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="{{ $role->id }}" name="role_ids[]" id="edit-role-{{ $role->id }}" @checked(in_array($role->id, old('role_ids', $systemUser->roles->pluck('id')->all())))>
                                <label class="form-check-label w-100" for="edit-role-{{ $role->id }}">
                                    <div class="fw-semibold">{{ $role->name }}</div>
                                    <div class="small text-secondary">{{ $role->code }}</div>
                                    <div class="small text-secondary">{{ $role->description ?: 'Aucune description renseignee.' }}</div>
                                </label>
                            </div>
                        </div>
                    @empty
                        <div class="text-secondary small">Aucun role actif disponible.</div>
                    @endforelse
                </div>
            </div>
            <div class="col-12">
                <label class="form-label">Utilisateurs internes dont l activite est visible</label>
                <div class="small text-secondary mb-1">Ce parametre sert seulement si ce compte recoit la permission de voir les activites des utilisateurs internes.</div>
                <div class="small text-secondary mb-2">Le role attribue doit donc contenir la permission <span class="fw-semibold">Voir activites utilisateurs internes</span> (<code>SA_ACTIVITY_LOGS_VIEW_INTERNAL</code>) dans la categorie <span class="fw-semibold">Journaux d activite</span>.</div>
                <div class="border rounded-3 p-3" style="max-height: 240px; overflow:auto;">
                    @forelse ($visibleActivityUsers as $visibleUser)
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" value="{{ $visibleUser->id }}" name="activity_visible_user_ids[]" id="activity-visible-user-{{ $visibleUser->id }}" @checked(in_array($visibleUser->id, old('activity_visible_user_ids', $systemUser->activityLogVisibleUsers->pluck('id')->all())))>
                            <label class="form-check-label" for="activity-visible-user-{{ $visibleUser->id }}">
                                <span class="d-block fw-semibold">{{ $visibleUser->name }}</span>
                                <span class="small text-secondary">{{ $visibleUser->email }}</span>
                            </label>
                        </div>
                    @empty
                        <div class="text-secondary small">Aucun autre utilisateur interne disponible.</div>
                    @endforelse
                </div>
            </div>
            <div class="col-12 d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-dark">Enregistrer</button>
                <a href="{{ route('super-admin.system-users.index') }}" class="btn btn-outline-secondary">Retour</a>
            </div>
        </form>
    </section>
@endsection
