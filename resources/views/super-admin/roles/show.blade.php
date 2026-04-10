@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Details role')
@section('page-title', 'Details du role')
@section('page-description', 'Consulter le profil du role et les permissions qu il transmet automatiquement a ses utilisateurs.')

@section('header-badges')
    <span class="badge-soft">{{ $role->status === 'active' ? 'Role actif' : 'Role inactif' }}</span>
    <span class="badge-soft">{{ $role->permissions->count() }} permission{{ $role->permissions->count() > 1 ? 's' : '' }}</span>
@endsection

@section('content')
    <div class="row g-4">
        <div class="col-lg-4">
            <section class="panel-card h-100">
                <div class="fw-bold mb-3">Profil du role</div>
                <div class="vstack gap-3">
                    <div>
                        <div class="small text-secondary">Code</div>
                        <div class="fw-semibold">{{ $role->code }}</div>
                    </div>
                    <div>
                        <div class="small text-secondary">Nom</div>
                        <div class="fw-semibold">{{ $role->name }}</div>
                    </div>
                    <div>
                        <div class="small text-secondary">Statut</div>
                        <div><span class="status-chip">{{ $role->status }}</span></div>
                    </div>
                    <div>
                        <div class="small text-secondary">Description</div>
                        <div>{{ $role->description ?: 'Aucune description renseignee.' }}</div>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <a href="{{ route('super-admin.roles.edit', $role) }}" class="btn btn-dark">Modifier</a>
                    <a href="{{ route('super-admin.roles.index') }}" class="btn btn-outline-secondary">Retour</a>
                </div>
            </section>
        </div>
        <div class="col-lg-8">
            <section class="panel-card">
                <div class="fw-bold mb-2">Permissions du role</div>
                <div class="small text-secondary mb-3">Ces permissions sont heritees automatiquement par les utilisateurs qui recoivent ce role.</div>
                @forelse ($groupedPermissions as $groupLabel => $groupPermissions)
                    <div class="mb-3">
                        <div class="small text-uppercase fw-bold text-secondary mb-2">{{ $groupLabel }}</div>
                        <div class="row g-2">
                            @foreach ($groupPermissions as $permission)
                                <div class="col-lg-6">
                                    <div class="border rounded-3 p-3 h-100">
                                        <div class="fw-semibold">{{ $permission->name }}</div>
                                        <div class="small text-secondary">{{ $permission->code }}</div>
                                        <div class="small text-secondary mt-1">{{ $permission->description ?: 'Aucune description renseignee.' }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="alert alert-warning border rounded-4 py-2 px-3 mb-0">
                        <div class="small mb-0">Aucune permission n est actuellement associee a ce role.</div>
                    </div>
                @endforelse
            </section>
        </div>
    </div>
@endsection
