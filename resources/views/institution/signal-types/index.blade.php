@extends('institution.layouts.app')

@section('title', config('app.name').' | Types de signaux')
@section('page-title', 'Types de signaux')
@section('page-description', 'Liste des signalements disponibles pour votre institution.')
@section('header-badges')
    <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#createInstitutionSignalTypeModal">Nouveau type de signal</button>
@endsection

@section('content')
    @php
        $label = \App\Support\Ui\InstitutionLabel::class;
    @endphp
    <section class="panel-card">
        <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <div class="fw-bold">Catalogue de l’institution</div>
                        <div class="text-secondary small">Ces signalements sont visibles dans le parcours public.</div>
                    </div>
                    <span class="status-chip">{{ $application?->name ?: '-' }} / {{ $organization?->name ?? '-' }}</span>
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
                            <a href="{{ route('institution.signal-types.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-modern align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Signal</th>
                                <th>Périmètre</th>
                                <th>Délai par défaut</th>
                                <th>Statut</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($signalTypes as $signalType)
                                <tr>
                                    <td>
                                        <div>{{ $signalType->label }}</div>
                                        <div class="small text-secondary mt-1">{{ $signalType->description ?: '-' }}</div>
                                    </td>
                                    <td>
                                        <div class="small">{{ $signalType->application?->name ?: '-' }}</div>
                                        <div class="small text-secondary">{{ $signalType->organization?->name ?: '-' }}</div>
                                    </td>
                                    <td><span class="status-chip">{{ $signalType->default_sla_hours ? $signalType->default_sla_hours.' h' : '-' }}</span></td>
                                    <td><span class="status-chip">{{ $label::status($signalType->status) }}</span></td>
                                    <td class="text-end">
                                        <div class="actions-wrap">
                                            <a href="{{ route('institution.signal-types.edit', $signalType) }}" class="btn btn-sm btn-outline-dark">Modifier</a>
                                            <form method="POST" action="{{ route('institution.signal-types.toggle-status', $signalType) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-outline-warning">{{ $signalType->status === 'active' ? 'Désactiver' : 'Activer' }}</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-secondary">Aucun type de signal disponible pour cette application et cette organisation.</td></tr>
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
                        <div class="h5 fw-bold mb-0">Créer un type de signal</div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" action="{{ route('institution.signal-types.store') }}" class="vstack gap-3">
                        @csrf
                        <div>
                            <label class="form-label">Libellé</label>
                            <input type="text" name="label" class="form-control" placeholder="Coupure totale de courant" required>
                        </div>
                        <div>
                            <label class="form-label">Délai par défaut (heures)</label>
                            <input type="number" min="1" max="999" name="default_sla_hours" class="form-control" placeholder="4">
                        </div>
                        <div>
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-dark">Créer</button>
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
