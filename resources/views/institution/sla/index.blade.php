@extends('institution.layouts.app')

@section('title', config('app.name').' | Délais cibles')
@section('page-title', 'Délais cibles')
@section('page-description', 'Délais de traitement attendus pour votre institution.')

@section('content')
    @php
        $label = \App\Support\Ui\InstitutionLabel::class;
    @endphp
    <div class="row g-4">
        <div class="col-xl-4">
            <section class="panel-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <div class="fw-bold">Nouvelle règle de délai</div>
                        <div class="text-secondary small">Choisissez le signalement et son délai attendu.</div>
                    </div>
                    <span class="status-chip">{{ $organization?->organizationType?->name ?? 'Sous-catégorie non définie' }}</span>
                </div>

                <form method="POST" action="{{ route('institution.sla.store') }}" class="vstack gap-3">
                    @csrf
                    <div>
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
                    <div>
                        <label class="form-label">Délai cible (heures)</label>
                        <input type="number" min="1" max="999" name="sla_hours" class="form-control" placeholder="4" required>
                    </div>
                    <div>
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-dark" @disabled($signalTypes->isEmpty())>Créer</button>
                </form>
            </section>
        </div>
        <div class="col-xl-8">
            <section class="panel-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <div class="fw-bold">Référentiel des délais</div>
                        <div class="text-secondary small">Les administrateurs institutionnels peuvent créer, modifier et activer les règles.</div>
                    </div>
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
                                    <td>
                                        <div>{{ $slaPolicy->signal_label ?: 'Signalement' }}</div>
                                    </td>
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
            </section>
        </div>
    </div>
@endsection
