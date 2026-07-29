@extends('institution.layouts.app')

@section('title', config('app.name').' | Délais cibles')
@section('page-title', 'Délais cibles')
@section('page-description', 'Délais de traitement attendus pour votre institution.')

@section('header-badges')
    <span class="badge-soft">{{ $slaPolicies->total() }} règle{{ $slaPolicies->total() > 1 ? 's' : '' }}</span>
    <span class="badge-soft">{{ $organization?->organizationType?->name ?? 'Sous-catégorie non définie' }}</span>
@endsection

@section('content')
    @php
        $label = \App\Support\Ui\InstitutionLabel::class;
    @endphp

    <section class="panel-card">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
            <div>
                <div class="fw-bold">Référentiel des délais</div>
                <div class="text-secondary small">Délais attendus par type de signalement.</div>
            </div>
            <button class="btn btn-dark" type="button" data-bs-toggle="modal" data-bs-target="#createInstitutionSlaModal" @disabled($signalTypes->isEmpty())>
                Ajouter
            </button>
        </div>

        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-secondary">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="active" @selected(request('status') === 'active')>Actif</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactif</option>
                    </select>
                </div>
                <div class="col-md-8 d-flex gap-2">
                    <button class="btn btn-dark">Filtrer</button>
                    <a href="{{ route('institution.sla.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                </div>
            </div>
        </form>

        <div class="table-toolbar">
            <div class="table-meta">{{ $slaPolicies->total() }} résultat{{ $slaPolicies->total() > 1 ? 's' : '' }}</div>
        </div>

        <div class="table-card">
            <div class="table-responsive">
                <table class="table table-modern align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Catégorie</th>
                            <th>Signal</th>
                            <th>Délai</th>
                            <th>Description</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($slaPolicies as $slaPolicy)
                            <tr>
                                <td>{{ $label::humanize($slaPolicy->network_type) }}</td>
                                <td>{{ $slaPolicy->signal_label ?: 'Signalement' }}</td>
                                <td><span class="status-chip">{{ $slaPolicy->sla_hours }} h</span></td>
                                <td>{{ $slaPolicy->description ?: '-' }}</td>
                                <td><span class="status-chip">{{ $label::status($slaPolicy->status) }}</span></td>
                                <td class="text-end">
                                    <div class="actions-wrap">
                                        <a href="{{ route('institution.sla.edit', $slaPolicy) }}" class="btn btn-sm btn-outline-dark">Modifier</a>
                                        <form method="POST" action="{{ route('institution.sla.toggle-status', $slaPolicy) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-outline-warning">{{ $slaPolicy->status === 'active' ? 'Désactiver' : 'Activer' }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-secondary">Aucune règle de délai disponible.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $slaPolicies->currentPage() }} sur {{ $slaPolicies->lastPage() }}</div>
            {{ $slaPolicies->links() }}
        </div>
    </section>

    <div class="modal fade" id="createInstitutionSlaModal" tabindex="-1" aria-labelledby="createInstitutionSlaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('institution.sla.store') }}">
                    @csrf
                    <input type="hidden" name="_form" value="create_sla">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createInstitutionSlaModalLabel">Nouvelle règle de délai</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Signalement</label>
                                <select name="signal_code" class="form-select" required>
                                    <option value="">Sélectionner</option>
                                    @foreach ($signalTypes as $signalType)
                                        <option value="{{ $signalType->code }}" @selected(old('signal_code') === $signalType->code)>
                                            {{ $signalType->label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Délai cible (heures)</label>
                                <input type="number" min="1" max="999" name="sla_hours" value="{{ old('sla_hours') }}" class="form-control" placeholder="4" required>
                            </div>
                            <div class="col-md-7">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-dark" @disabled($signalTypes->isEmpty())>Créer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @if ($errors->any() && old('_form') === 'create_sla')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('createInstitutionSlaModal')).show();
            });
        </script>
    @endif
@endsection
