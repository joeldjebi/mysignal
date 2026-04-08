@extends('institution.layouts.app')

@section('title', config('app.name').' | Permissions')
@section('page-title', 'Permissions')
@section('page-description', 'Fonctions autorisees pour cette institution par le super admin et reutilisables dans les roles et users.')

@section('header-badges')
    <span class="badge-soft">{{ $permissions->count() }} permissions</span>
@endsection

@section('content')
    <section class="panel-card">
        <div class="fw-bold mb-3">Catalogue des permissions disponibles</div>
        <div class="text-secondary small mb-4">
            Ces permissions correspondent aux fonctionnalites que le super admin a activees pour votre institution.
            Vous pouvez les affecter a vos roles locaux ou directement a vos users.
        </div>

        @forelse ($groupedPermissions as $groupLabel => $groupPermissions)
            <div class="mb-4">
                <div class="fw-bold mb-3">{{ $groupLabel }}</div>
                <div class="row g-3">
                    @foreach ($groupPermissions as $permission)
                        <div class="col-md-6 col-xl-4">
                            <div class="surface-soft h-100">
                                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                    <div class="meta-title">{{ $permission->name }}</div>
                                    <span class="status-chip">{{ $permission->status }}</span>
                                </div>
                                <div class="meta-subtitle mb-2">{{ $permission->code }}</div>
                                <div class="small text-secondary mb-3">{{ $permission->description ?: 'Permission disponible pour l institution.' }}</div>
                                <div class="d-flex gap-2 flex-wrap">
                                    <span class="badge-soft">{{ $permission->institution_roles_count }} role(s)</span>
                                    <span class="badge-soft">{{ $permission->institution_users_count }} user(s)</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="text-center text-secondary py-4">
                Aucune permission disponible pour le moment. Verifiez les fonctionnalites activees par le super admin.
            </div>
        @endforelse
    </section>
@endsection
