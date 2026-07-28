@extends('institution.layouts.app')

@section('title', config('app.name').' | Modifier un délai')
@section('page-title', 'Modifier un délai')
@section('page-description', 'Ajuster le délai applicable à votre institution.')

@section('content')
    @php
        $label = \App\Support\Ui\InstitutionLabel::class;
    @endphp
    <section class="panel-card" style="max-width: 920px;">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <div class="fw-bold">Édition du délai</div>
                <div class="text-secondary small">Cette mise à jour concerne uniquement votre institution.</div>
            </div>
            <span class="status-chip">{{ $label::humanize($slaPolicy->network_type) }}</span>
        </div>

        <form method="POST" action="{{ route('institution.sla.update', $slaPolicy) }}" class="row g-3">
            @csrf
            @method('PUT')
            <div class="col-md-8">
                <label class="form-label">Libellé du signalement</label>
                <input type="text" name="signal_label" value="{{ old('signal_label', $slaPolicy->signal_label) }}" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Délai cible (heures)</label>
                <input type="number" min="1" max="999" name="sla_hours" value="{{ old('sla_hours', $slaPolicy->sla_hours) }}" class="form-control" required>
            </div>
            <div class="col-md-8">
                <label class="form-label">Description</label>
                <input type="text" name="description" value="{{ old('description', $slaPolicy->description) }}" class="form-control">
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-dark">Enregistrer</button>
                <a href="{{ route('institution.sla.index') }}" class="btn btn-outline-secondary">Retour</a>
            </div>
        </form>
    </section>
@endsection
