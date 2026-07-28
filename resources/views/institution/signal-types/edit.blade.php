@extends('institution.layouts.app')

@section('title', config('app.name').' | Modifier type de signal')
@section('page-title', 'Modifier un type de signal')
@section('page-description', 'Modifier les informations visibles dans le parcours public.')

@section('content')
    @php
        $label = \App\Support\Ui\InstitutionLabel::class;
    @endphp
    <div class="row g-4">
        <div class="col-lg-4">
            <section class="panel-card h-100">
                <div class="small text-secondary fw-semibold mb-2">Type de signal</div>
                <div class="h5 fw-bold mb-1">{{ $signalType->label }}</div>
                <div class="text-secondary small mb-4">{{ $signalType->application?->name ?: '-' }} / {{ $signalType->organization?->name ?: '-' }}</div>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="status-chip">{{ $label::status($signalType->status) }}</span>
                    <span class="status-chip">{{ $signalType->default_sla_hours ? $signalType->default_sla_hours.' h' : 'Sans délai défini' }}</span>
                </div>
                <div class="text-secondary small">{{ $signalType->description ?: 'Aucune description pour le moment.' }}</div>
            </section>
        </div>
        <div class="col-lg-8">
            <section class="panel-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <div class="fw-bold">Édition du type de signal</div>
                        <div class="text-secondary small">Cette mise à jour concerne uniquement votre institution.</div>
                    </div>
                    <span class="status-chip">{{ $signalType->application?->name ?: '-' }} / {{ $signalType->organization?->name ?: '-' }}</span>
                </div>

                <form method="POST" action="{{ route('institution.signal-types.update', $signalType) }}" class="row g-3">
                    @csrf
                    @method('PUT')
                    <div class="col-md-6">
                        <label class="form-label">Délai par défaut (heures)</label>
                        <input type="number" min="1" max="999" name="default_sla_hours" value="{{ old('default_sla_hours', $signalType->default_sla_hours) }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Libellé</label>
                        <input type="text" name="label" value="{{ old('label', $signalType->label) }}" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4">{{ old('description', $signalType->description) }}</textarea>
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-dark">Enregistrer</button>
                        <a href="{{ route('institution.signal-types.index') }}" class="btn btn-outline-secondary">Retour</a>
                    </div>
                </form>
            </section>
        </div>
    </div>
@endsection
