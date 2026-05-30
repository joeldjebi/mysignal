@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Sous-types de signal')
@section('page-title', 'Sous-types de signal')
@section('page-description', 'Referentiel des sous-types proposes au UP selon le type de signal selectionne.')

@section('header-badges')
    <span class="badge-soft">{{ $subTypes->total() }} sous-types</span>
@endsection

@section('content')
    <div class="row g-4">
        <div class="col-lg-4">
            <section class="panel-card sticky-form-card">
                <div class="fw-bold mb-3">Nouveau sous-type</div>
                <form method="POST" action="{{ route('super-admin.signal-sub-types.store') }}" class="vstack gap-3">
                    @csrf
                    <div>
                        <label class="form-label">Type de signal</label>
                        <select name="signal_type_id" class="form-select" required>
                            <option value="">Choisir un type de signal</option>
                            @foreach ($signalTypes as $signalType)
                                <option value="{{ $signalType->id }}" @selected((string) old('signal_type_id', request('signal_type_id')) === (string) $signalType->id)>
                                    {{ $signalType->code }} - {{ $signalType->label }}
                                    @if ($signalType->organization)
                                        ({{ $signalType->organization->name }})
                                    @elseif ($signalType->application)
                                        ({{ $signalType->application->name }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Code</label>
                        <input type="text" name="code" value="{{ old('code') }}" class="form-control" placeholder="PANNE_COMPTEUR" required>
                        <div class="small text-secondary mt-2">Le code OTHER est reserve a l option Autre visible cote UP.</div>
                    </div>
                    <div>
                        <label class="form-label">Libelle</label>
                        <input type="text" name="label" value="{{ old('label') }}" class="form-control" placeholder="Panne compteur" required>
                    </div>
                    <div>
                        <label class="form-label">Ordre</label>
                        <input type="number" min="0" max="9999" name="sort_order" value="{{ old('sort_order', 0) }}" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-dark">Enregistrer</button>
                </form>
            </section>
        </div>
        <div class="col-lg-8">
            <section class="panel-card">
                <div class="fw-bold mb-3">Liste des sous-types</div>
                <form method="GET" class="filter-bar">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label small text-secondary">Recherche</label>
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Code, libelle, type de signal">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-secondary">Type de signal</label>
                            <select name="signal_type_id" class="form-select">
                                <option value="">Tous</option>
                                @foreach ($signalTypes as $signalType)
                                    <option value="{{ $signalType->id }}" @selected((string) request('signal_type_id') === (string) $signalType->id)>{{ $signalType->code }} - {{ $signalType->label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-secondary">Statut</label>
                            <select name="status" class="form-select">
                                <option value="">Tous</option>
                                <option value="active" @selected(request('status') === 'active')>Actif</option>
                                <option value="inactive" @selected(request('status') === 'inactive')>Inactif</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <button class="btn btn-dark w-100">OK</button>
                            <a href="{{ route('super-admin.signal-sub-types.index') }}" class="btn btn-outline-secondary">RAZ</a>
                        </div>
                    </div>
                </form>

                <div class="table-toolbar">
                    <div class="table-meta">{{ $subTypes->total() }} resultat{{ $subTypes->total() > 1 ? 's' : '' }}</div>
                </div>
                <div class="table-responsive">
                    <table class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th>Type de signal</th>
                                <th>Sous-type</th>
                                <th>Ordre</th>
                                <th>Statut</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($subTypes as $subType)
                                <tr>
                                    <td>
                                        <div>{{ $subType->signalType?->label ?: '-' }}</div>
                                        <div class="small text-secondary">{{ $subType->signalType?->code ?: '-' }}</div>
                                    </td>
                                    <td>
                                        <div>{{ $subType->label }}</div>
                                        <div class="small text-secondary">{{ $subType->code }}</div>
                                        <div class="small text-secondary mt-1">{{ $subType->description ?: '-' }}</div>
                                    </td>
                                    <td><span class="status-chip">{{ $subType->sort_order }}</span></td>
                                    <td><span class="status-chip">{{ $subType->status }}</span></td>
                                    <td class="text-end">
                                        <div class="actions-wrap">
                                            <a href="{{ route('super-admin.signal-types.edit', $subType->signalType) }}" class="btn btn-sm btn-outline-dark">Modifier</a>
                                            <form method="POST" action="{{ route('super-admin.signal-types.sub-types.toggle-status', [$subType->signalType, $subType]) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-outline-warning">{{ $subType->status === 'active' ? 'Desactiver' : 'Activer' }}</button>
                                            </form>
                                            <form method="POST" action="{{ route('super-admin.signal-types.sub-types.destroy', [$subType->signalType, $subType]) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-secondary">Aucun sous-type de signal enregistre.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="table-meta">Page {{ $subTypes->currentPage() }} sur {{ $subTypes->lastPage() }}</div>
                    {{ $subTypes->links() }}
                </div>
            </section>
        </div>
    </div>
@endsection
