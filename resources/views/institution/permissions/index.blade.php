@extends('institution.layouts.app')

@section('title', config('app.name').' | Permissions')
@section('page-title', 'Droits disponibles')
@section('page-description', 'Fonctions autorisées pour cette institution.')

@section('header-badges')
    <span class="badge-soft">{{ $permissions->count() }} permissions</span>
@endsection

@section('content')
    @php
        $label = \App\Support\Ui\InstitutionLabel::class;
    @endphp
    <section class="panel-card">
        <div class="fw-bold mb-3">Droits disponibles</div>
        <div class="text-secondary small mb-4">
            Ces droits correspondent aux fonctions actives pour votre institution.
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
                                    <span class="status-chip">{{ $label::status($permission->status) }}</span>
                                </div>
                                <div class="small text-secondary mb-3">{{ $permission->description ?: 'Droit disponible pour l’institution.' }}</div>
                                <div class="d-flex gap-2 flex-wrap">
                                    <span class="badge-soft">{{ $permission->institution_roles_count }} rôle(s)</span>
                                    <span class="badge-soft">{{ $permission->institution_users_count }} collaborateur(s)</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="text-center text-secondary py-4">
                Aucun droit disponible pour le moment. Vérifiez les fonctions actives auprès du super administrateur.
            </div>
        @endforelse
    </section>
@endsection
