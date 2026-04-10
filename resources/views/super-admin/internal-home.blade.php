@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Backoffice')
@section('page-title', 'Accueil backoffice')
@section('page-description', 'Espace d acces pour les utilisateurs internes autorises.')

@section('header-badges')
    <span class="badge-soft">{{ $internalUser?->roles?->count() ?? 0 }} role{{ ($internalUser?->roles?->count() ?? 0) > 1 ? 's' : '' }}</span>
    <span class="badge-soft">{{ $permissionCodes->count() }} permission{{ $permissionCodes->count() > 1 ? 's' : '' }}</span>
@endsection

@section('content')
    <section class="panel-card mb-4">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-between gap-3">
            <div>
                <div class="fw-bold mb-2">Bienvenue {{ $internalUser?->name }}</div>
                <div class="text-secondary">La connexion au backoffice est bien active. Cet espace sert de point d entree pour les utilisateurs internes autorises.</div>
            </div>
            <span class="status-chip">{{ $internalUser?->status === 'active' ? 'Compte actif' : 'Compte inactif' }}</span>
        </div>
    </section>

    <section class="panel-card mb-4">
        <div class="fw-bold mb-2">Profil d acces</div>
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="border rounded-4 p-3 h-100">
                    <div class="small text-secondary text-uppercase fw-semibold mb-2">Roles attribues</div>
                    @if ($internalUser?->roles?->isNotEmpty())
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($internalUser->roles as $role)
                                <span class="badge text-bg-light border">{{ $role->name }}</span>
                            @endforeach
                        </div>
                    @else
                        <div class="text-secondary small">Aucun role n est encore attribue a ce compte.</div>
                    @endif
                </div>
            </div>
            <div class="col-lg-6">
                <div class="border rounded-4 p-3 h-100">
                    <div class="small text-secondary text-uppercase fw-semibold mb-2">Permissions effectives</div>
                    @if ($permissionCodes->isNotEmpty())
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($permissionCodes as $permissionCode)
                                <span class="badge text-bg-light border">{{ $permissionCode }}</span>
                            @endforeach
                        </div>
                    @else
                        <div class="text-secondary small">Aucune permission n est disponible pour ce compte.</div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="panel-card">
        <div class="fw-bold mb-2">Prochaine etape</div>
        <div class="text-secondary mb-3">Si tu arrives sur cette page sans autre menu utile, cela signifie que ton compte a l acces portail, mais qu il manque encore des permissions metier pour afficher des modules supplementaires.</div>
        <div class="alert alert-light border rounded-4 mb-0">
            <div class="small text-secondary mb-1">A demander au Super Admin</div>
            <div class="fw-semibold">Attribuer un role metier ou des permissions complementaires selon le module attendu.</div>
        </div>
    </section>
@endsection
