@extends('institution.layouts.app')

@section('title', config('app.name').' | Modifier type de signal')
@section('page-title', 'Modifier un type de signal')
@section('page-description', 'Ajuster le referentiel des signaux publics sur votre reseau.')

@section('content')
    <div class="row g-4">
        <div class="col-lg-4">
            <section class="panel-card h-100">
                <div class="small text-secondary fw-semibold mb-2">Type de signal</div>
                <div class="h5 fw-bold mb-1">{{ $signalType->label }}</div>
                <div class="text-secondary small mb-4">{{ $signalType->code }} · {{ $signalType->network_type }}</div>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="status-chip">{{ $signalType->status }}</span>
                    <span class="status-chip">{{ $signalType->default_sla_hours ? $signalType->default_sla_hours.' h' : 'Sans TCM defaut' }}</span>
                </div>
                <div class="text-secondary small">{{ $signalType->description ?: 'Aucune description detaillee pour le moment.' }}</div>
            </section>
        </div>
        <div class="col-lg-8">
            <section class="panel-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <div class="fw-bold">Edition du type de signal</div>
                        <div class="text-secondary small">Cette mise a jour restera limitee a votre reseau institutionnel.</div>
                    </div>
                    <span class="status-chip">{{ $signalType->network_type }}</span>
                </div>

                <form method="POST" action="{{ route('institution.signal-types.update', $signalType) }}" class="row g-3">
                    @csrf
                    @method('PUT')
                    <div class="col-md-4">
                        <label class="form-label">Code signal</label>
                        <input type="text" class="form-control" value="{{ $signalType->code }}" disabled>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">SLA par defaut (heures)</label>
                        <input type="number" min="1" max="999" name="default_sla_hours" value="{{ old('default_sla_hours', $signalType->default_sla_hours) }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Reseau</label>
                        <input type="text" class="form-control" value="{{ $signalType->network_type }}" disabled>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Libelle</label>
                        <input type="text" name="label" value="{{ old('label', $signalType->label) }}" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4">{{ old('description', $signalType->description) }}</textarea>
                    </div>
                    <div class="col-12">
                        @include('partials.signal-type-field-builder', ['builderId' => 'institution-signal-type-edit', 'fields' => old() ? collect(old('field_keys', []))->map(function ($key, $index) {
                            return [
                                'key' => $key,
                                'label' => old('field_labels.'.$index),
                                'type' => old('field_types.'.$index, 'text'),
                                'options' => collect(preg_split('/\r\n|\r|\n/', (string) old('field_options.'.$index)))
                                    ->map(fn ($option) => trim((string) $option))
                                    ->filter()
                                    ->values()
                                    ->all(),
                                'required' => in_array((string) $index, array_map('strval', old('field_required', [])), true),
                            ];
                        })->all() : ($signalType->data_fields ?? [])])
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
