@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Plans abonnements UP')
@section('page-title', 'Plans abonnements UP')
@section('page-description', 'Parametrer le plan annuel des usagers publics.')

@section('header-badges')
    <span class="badge-soft">{{ $subscriptionPlans->total() }} plans</span>
    <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#createSubscriptionPlanModal">
        Nouveau plan
    </button>
@endsection

@section('content')
    <section class="panel-card">
        <div class="fw-bold mb-3">Catalogue des plans d abonnement</div>
        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-7">
                    <label class="form-label small text-secondary">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Code, nom, description">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-secondary">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="active" @selected(request('status') === 'active')>Actif</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactif</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-dark w-100">Filtrer</button>
                    <a href="{{ route('super-admin.subscription-plans.index') }}" class="btn btn-outline-secondary">RAZ</a>
                </div>
            </div>
        </form>

        <div class="table-responsive mt-3">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Plan</th>
                        <th>Duree</th>
                        <th>Prix</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($subscriptionPlans as $plan)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $plan->name }}</div>
                                <div class="small text-secondary">{{ $plan->code }}</div>
                                <div class="small text-secondary">{{ $plan->description ?: '-' }}</div>
                            </td>
                            <td>{{ $plan->duration_months }} mois</td>
                            <td>{{ number_format($plan->price, 0, ',', ' ') }} {{ $plan->currency }}</td>
                            <td><span class="status-chip">{{ $plan->is_active ? 'active' : 'inactive' }}</span></td>
                            <td class="text-end">
                                <div class="actions-wrap">
                                    <a href="{{ route('super-admin.subscription-plans.edit', $plan) }}" class="btn btn-sm btn-outline-dark">Modifier</a>
                                    <form method="POST" action="{{ route('super-admin.subscription-plans.toggle-status', $plan) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-outline-warning">{{ $plan->is_active ? 'Desactiver' : 'Activer' }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-secondary">Aucun plan d abonnement enregistre.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $subscriptionPlans->currentPage() }} sur {{ $subscriptionPlans->lastPage() }}</div>
            {{ $subscriptionPlans->links() }}
        </div>
    </section>

    <div class="modal fade" id="createSubscriptionPlanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Nouveau plan d abonnement</h5>
                        <div class="small text-secondary">Creez le plan annuel applique aux usagers publics.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <form method="POST" action="{{ route('super-admin.subscription-plans.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4"><label class="form-label">Code</label><input class="form-control" name="code" value="{{ old('code', 'UP_ANNUAL') }}" required></div>
                            <div class="col-md-8"><label class="form-label">Nom</label><input class="form-control" name="name" value="{{ old('name', 'Abonnement annuel UP') }}" required></div>
                            <div class="col-md-4"><label class="form-label">Duree en mois</label><input class="form-control" type="number" min="1" max="120" name="duration_months" value="{{ old('duration_months', 12) }}" required></div>
                            <div class="col-md-4"><label class="form-label">Prix</label><input class="form-control" type="number" min="0" name="price" value="{{ old('price', 0) }}" required></div>
                            <div class="col-md-4"><label class="form-label">Devise</label><input class="form-control" name="currency" value="{{ old('currency', 'FCFA') }}" required></div>
                            <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" rows="3" name="description">{{ old('description') }}</textarea></div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActivePlan" @checked(old('is_active', '1'))>
                                    <label class="form-check-label" for="isActivePlan">Plan actif</label>
                                </div>
                                <div class="small text-secondary">Un seul plan actif est autorise pour cette version.</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-dark">Creer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @if ($errors->any() && old('code'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('createSubscriptionPlanModal')).show();
            });
        </script>
    @endif
@endpush
