@extends('super-admin.layouts.app')

@section('title', config('app.name').' | SLA cibles')
@section('page-title', 'SLA cibles')
@section('page-description', 'Programmer les SLA cibles selon le type d organisation et le type de signal.')

@section('header-badges')
    <span class="badge-soft">{{ $slaPolicies->total() }} regles SLA</span>
    <button
        type="button"
        class="btn btn-dark"
        data-bs-toggle="modal"
        data-bs-target="#slaPolicyCreateModal"
    >
        Nouvelle regle SLA
    </button>
@endsection

@section('content')
    <section class="panel-card">
        <div class="fw-bold mb-3">Liste des SLA cibles</div>
        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-secondary">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Code, libelle, reseau">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-secondary">Type d'organisation</label>
                    <select name="organization_type_id" class="form-select">
                        <option value="">Tous</option>
                        @foreach ($organizationTypes as $organizationType)
                            <option value="{{ $organizationType->id }}" @selected((string) request('organization_type_id') === (string) $organizationType->id)>{{ $organizationType->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Canal / organisation</label>
                    <select name="network_type" class="form-select">
                        <option value="">Tous</option>
                        @foreach ($networkTypes as $networkType)
                            <option value="{{ $networkType }}" @selected(request('network_type') === $networkType)>{{ $networkType }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="active" @selected(request('status') === 'active')>Actif</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactif</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex gap-2">
                    <button class="btn btn-dark w-100">OK</button>
                </div>
            </div>
        </form>
        <div class="table-toolbar">
            <div class="table-meta">{{ $slaPolicies->total() }} resultat{{ $slaPolicies->total() > 1 ? 's' : '' }}</div>
            <a href="{{ route('super-admin.sla-policies.index') }}" class="btn btn-outline-secondary btn-sm">RAZ</a>
        </div>
        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Canal</th>
                        <th>Signal</th>
                        <th>SLA</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($slaPolicies as $slaPolicy)
                        <tr>
                            <td>{{ $slaPolicy->organizationType?->name }}</td>
                            <td>{{ $slaPolicy->network_type }}</td>
                            <td>
                                <div>{{ $slaPolicy->signal_label }}</div>
                                <div class="small text-secondary">{{ $slaPolicy->signal_code }}</div>
                            </td>
                            <td><span class="status-chip">{{ $slaPolicy->sla_hours }} h</span></td>
                            <td><span class="status-chip">{{ $slaPolicy->status }}</span></td>
                            <td class="text-end">
                                <div class="actions-wrap">
                                    <a href="{{ route('super-admin.sla-policies.edit', $slaPolicy) }}" class="btn btn-sm btn-outline-dark">Modifier</a>
                                    <form method="POST" action="{{ route('super-admin.sla-policies.toggle-status', $slaPolicy) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-outline-warning">{{ $slaPolicy->status === 'active' ? 'Desactiver' : 'Activer' }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('super-admin.sla-policies.destroy', $slaPolicy) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-secondary">Aucune regle SLA enregistree.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $slaPolicies->currentPage() }} sur {{ $slaPolicies->lastPage() }}</div>
            {{ $slaPolicies->links() }}
        </div>
    </section>

    <div class="modal fade" id="slaPolicyCreateModal" tabindex="-1" aria-labelledby="slaPolicyCreateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title" id="slaPolicyCreateModalLabel">Nouvelle regle SLA</h5>
                        <div class="text-secondary small">Programmez un delai cible selon le type de client et le type de signal.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('super-admin.sla-policies.store') }}">
                    @csrf
                    <div class="modal-body pt-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Type d'organisation</label>
                                <select name="organization_type_id" class="form-select" required>
                                    @foreach ($organizationTypes as $organizationType)
                                        <option value="{{ $organizationType->id }}" @selected((string) old('organization_type_id') === (string) $organizationType->id)>{{ $organizationType->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Canal / organisation</label>
                                <select name="network_type" class="form-select" required>
                                    @foreach ($networkTypes as $networkType)
                                        <option value="{{ $networkType }}" @selected(old('network_type') === $networkType)>{{ $networkType }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Code signal</label>
                                <input type="text" name="signal_code" class="form-control" placeholder="EL-01" value="{{ old('signal_code') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Libelle signal</label>
                                <input type="text" name="signal_label" class="form-control" placeholder="Coupure totale de courant" value="{{ old('signal_label') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">SLA cible (heures)</label>
                                <input type="number" min="1" max="999" name="sla_hours" class="form-control" placeholder="4" value="{{ old('sla_hours') }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="4" placeholder="Ajoutez un contexte ou des precisions utiles.">{{ old('description') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-dark">Creer la regle SLA</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var modalElement = document.getElementById('slaPolicyCreateModal');
                if (!modalElement || typeof bootstrap === 'undefined') {
                    return;
                }

                bootstrap.Modal.getOrCreateInstance(modalElement).show();
            });
        </script>
    @endif
@endpush
