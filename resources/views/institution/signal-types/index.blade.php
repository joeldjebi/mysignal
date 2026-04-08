@extends('institution.layouts.app')

@section('title', config('app.name').' | Types de signaux')
@section('page-title', 'Types de signaux')
@section('page-description', 'Referentiel des types de signaux pour votre reseau, avec champs requis et SLA par defaut.')
@section('header-badges')
    <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#createInstitutionSignalTypeModal">Nouveau type de signal</button>
@endsection

@section('content')
    <section class="panel-card">
        <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <div class="fw-bold">Catalogue du reseau</div>
                        <div class="text-secondary small">Ces types de signaux sont visibles dans le parcours public de declaration.</div>
                    </div>
                    <span class="status-chip">{{ $organization?->code ?? 'Reseau non defini' }}</span>
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
                            <a href="{{ route('institution.signal-types.index') }}" class="btn btn-outline-secondary">RAZ</a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-modern align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Signal</th>
                                <th>SLA defaut</th>
                                <th>Champs</th>
                                <th>Statut</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($signalTypes as $signalType)
                                <tr>
                                    <td>
                                        <div>{{ $signalType->label }}</div>
                                        <div class="small text-secondary">{{ $signalType->code }}</div>
                                        <div class="small text-secondary mt-1">{{ $signalType->description ?: '-' }}</div>
                                    </td>
                                    <td><span class="status-chip">{{ $signalType->default_sla_hours ? $signalType->default_sla_hours.' h' : '-' }}</span></td>
                                    <td>{{ count($signalType->data_fields ?? []) }} champ(s)</td>
                                    <td><span class="status-chip">{{ $signalType->status }}</span></td>
                                    <td class="text-end">
                                        <div class="actions-wrap">
                                            <a href="{{ route('institution.signal-types.edit', $signalType) }}" class="btn btn-sm btn-outline-dark">Modifier</a>
                                            <form method="POST" action="{{ route('institution.signal-types.toggle-status', $signalType) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-outline-warning">{{ $signalType->status === 'active' ? 'Desactiver' : 'Activer' }}</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-secondary">Aucun type de signal disponible pour ce portail.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
    </section>

    <div class="modal fade" id="createInstitutionSignalTypeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content border-0" style="border-radius: 28px; overflow: hidden;">
                <div class="modal-header px-4 py-3 border-0" style="background: linear-gradient(145deg, #0f2738, #1b4867); color: white;">
                    <div>
                        <div class="small text-white-50 fw-semibold mb-1">Nouveau type de signal</div>
                        <div class="h5 fw-bold mb-0">Creer un type de signal</div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" action="{{ route('institution.signal-types.store') }}" class="vstack gap-3">
                        @csrf
                        <div>
                            <label class="form-label">Code signal</label>
                            <input type="text" name="code" class="form-control" placeholder="EL-01" required>
                        </div>
                        <div>
                            <label class="form-label">Libelle</label>
                            <input type="text" name="label" class="form-control" placeholder="Coupure totale de courant" required>
                        </div>
                        <div>
                            <label class="form-label">SLA par defaut (heures)</label>
                            <input type="number" min="1" max="999" name="default_sla_hours" class="form-control" placeholder="4">
                        </div>
                        <div>
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        @include('partials.signal-type-field-builder', ['builderId' => 'institution-signal-type-create', 'fields' => []])
                        <button type="submit" class="btn btn-dark">Creer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('createInstitutionSignalTypeModal')).show();
            });
        </script>
    @endif
@endsection
