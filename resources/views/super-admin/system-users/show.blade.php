@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Details '.$systemUser->name)
@section('page-title', 'Details utilisateur interne')
@section('page-description', 'Consulter le profil, les roles et les permissions heritees de cet utilisateur interne.')

@section('header-badges')
    <span class="badge-soft">{{ $systemUser->status === 'active' ? 'Compte actif' : 'Compte inactif' }}</span>
    <span class="badge-soft">{{ $systemUser->roles->count() }} role{{ $systemUser->roles->count() > 1 ? 's' : '' }}</span>
@endsection

@section('content')
    <section class="panel-card mb-4">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-between gap-3">
            <div>
                <div class="fw-bold mb-2">{{ $systemUser->name }}</div>
                <div class="text-secondary">{{ $systemUser->email }}</div>
                <div class="text-secondary">{{ $systemUser->phone ?: 'Telephone non renseigne' }}</div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('super-admin.system-users.edit', $systemUser) }}" class="btn btn-dark">Modifier</a>
                <a href="{{ route('super-admin.system-users.index') }}" class="btn btn-outline-secondary">Retour</a>
            </div>
        </div>
    </section>

    <div class="row g-4">
        <div class="col-lg-4">
            <section class="panel-card h-100">
                <div class="fw-bold mb-3">Informations du compte</div>
                <div class="small text-secondary text-uppercase fw-semibold mb-1">Statut</div>
                <div class="mb-3"><span class="status-chip">{{ $systemUser->status }}</span></div>

                <div class="small text-secondary text-uppercase fw-semibold mb-1">Cree par</div>
                <div class="mb-3">{{ $systemUser->creator?->name ?: 'Non renseigne' }}</div>

                <div class="small text-secondary text-uppercase fw-semibold mb-1">Date de creation</div>
                <div class="mb-3">{{ $systemUser->created_at?->format('d/m/Y H:i') ?: '-' }}</div>

                <div class="small text-secondary text-uppercase fw-semibold mb-1">Derniere mise a jour</div>
                <div>{{ $systemUser->updated_at?->format('d/m/Y H:i') ?: '-' }}</div>
            </section>
        </div>

        <div class="col-lg-4">
            <section class="panel-card h-100">
                <div class="fw-bold mb-3">Roles attribues</div>
                @forelse ($systemUser->roles as $role)
                    <div class="border rounded-3 p-3 mb-3">
                        <div class="fw-semibold">{{ $role->name }}</div>
                        <div class="small text-secondary">{{ $role->code }}</div>
                        <div class="small text-secondary mt-1">{{ $role->description ?: 'Aucune description renseignee.' }}</div>
                        <div class="small text-secondary mt-2">{{ $role->permissions->count() }} permission{{ $role->permissions->count() > 1 ? 's' : '' }} via ce role</div>
                    </div>
                @empty
                    <div class="text-secondary">Aucun role attribue.</div>
                @endforelse
            </section>
        </div>

        <div class="col-lg-12">
            <section class="panel-card">
                <div class="fw-bold mb-3">Permissions heritees des roles</div>
                @php
                    $inheritedPermissions = $systemUser->roles
                        ->flatMap(fn ($role) => $role->permissions)
                        ->unique('id')
                        ->sortBy('name')
                        ->values();
                @endphp
                @if ($inheritedPermissions->isNotEmpty())
                    <div class="row g-3">
                        @foreach ($inheritedPermissions as $permission)
                            <div class="col-lg-4">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="fw-semibold">{{ $permission->name }}</div>
                                    <div class="small text-secondary">{{ $permission->code }}</div>
                                    <div class="small text-secondary mt-1">{{ $permission->description ?: 'Aucune description renseignee.' }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-secondary">Aucune permission heritee. Il faut attribuer au moins un role portant des permissions.</div>
                @endif
            </section>
        </div>
        <div class="col-lg-12">
            <section class="panel-card">
                <div class="fw-bold mb-3">Visibilite sur les activites des utilisateurs internes</div>
                @if ($systemUser->activityLogVisibleUsers->isNotEmpty())
                    <div class="row g-3">
                        @foreach ($systemUser->activityLogVisibleUsers as $visibleUser)
                            <div class="col-lg-4">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="fw-semibold">{{ $visibleUser->name }}</div>
                                    <div class="small text-secondary">{{ $visibleUser->email }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-secondary">Aucun utilisateur interne specifique n est autorise pour ce compte.</div>
                @endif
            </section>
        </div>
    </div>
@endsection
