@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Modifier '.$systemUser->name)
@section('page-title', 'Modifier un utilisateur interne')
@section('page-description', 'Mettre a jour le compte, ses roles et ses permissions directes.')

@section('content')
    @php
        $permissionGroups = $permissions
            ->groupBy(function ($permission) {
                $segments = explode('_', (string) $permission->code);

                if (($segments[0] ?? null) === 'SA') {
                    return match ($segments[1] ?? null) {
                        'ACCESS' => 'Acces au portail',
                        'DASHBOARD' => 'Dashboard',
                        'PUBLIC' => 'Usagers publics et signalements',
                        'PAYMENTS' => 'Paiements',
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
                <div class="text-secondary small">Ajuste le profil, les roles et les permissions directes sans perdre en lisibilite.</div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <span class="badge-soft">{{ $systemUser->status === 'active' ? 'Compte actif' : 'Compte inactif' }}</span>
                <span class="badge-soft">{{ $systemUser->roles->count() }} role{{ $systemUser->roles->count() > 1 ? 's' : '' }}</span>
                <span class="badge-soft">{{ $systemUser->permissions->count() }} permission{{ $systemUser->permissions->count() > 1 ? 's' : '' }} directe{{ $systemUser->permissions->count() > 1 ? 's' : '' }}</span>
            </div>
        </div>

        <div class="alert alert-light border rounded-4 mb-4">
            <div class="fw-bold mb-2">Comment gerer les droits de cet utilisateur</div>
            <div class="small text-secondary mb-1">Les roles doivent rester la base du profil, car ils regroupent un ensemble coherent de permissions.</div>
            <div class="small text-secondary mb-1">Les permissions directes servent surtout a affiner un besoin ponctuel, sans reconstruire un role complet.</div>
            <div class="small text-secondary mb-0">Le mot de passe est optionnel ici : laisse le champ vide si tu veux conserver l acces actuel.</div>
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
                <div class="small text-secondary mb-2">Les roles regroupent plusieurs permissions. C est la methode recommandee.</div>
                <div class="border rounded-3 p-3" style="max-height: 360px; overflow:auto;">
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
            <div class="col-md-6">
                <label class="form-label">Permissions directes</label>
                <div class="small text-secondary mb-2">A utiliser pour un ajustement fin, permission par permission.</div>
                <div class="border rounded-3 p-3" style="max-height: 360px; overflow:auto;">
                    @forelse ($permissionGroups as $groupLabel => $groupPermissions)
                        <div class="mb-3">
                            <div class="fw-bold small text-uppercase text-secondary mb-2">{{ $groupLabel }}</div>
                            <div class="vstack gap-2">
                                @foreach ($groupPermissions as $permission)
                                    <div class="border rounded-3 p-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="{{ $permission->id }}" name="permission_ids[]" id="edit-permission-{{ $permission->id }}" @checked(in_array($permission->id, old('permission_ids', $systemUser->permissions->pluck('id')->all())))>
                                            <label class="form-check-label w-100" for="edit-permission-{{ $permission->id }}">
                                                <div class="fw-semibold">{{ $permission->name }}</div>
                                                <div class="small text-secondary">{{ $permission->code }}</div>
                                                <div class="small text-secondary">{{ $permission->description ?: 'Aucune description renseignee.' }}</div>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="text-secondary small">Aucune permission active disponible.</div>
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
