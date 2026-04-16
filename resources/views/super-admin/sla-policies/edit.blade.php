@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Modifier TCM')
@section('page-title', 'Modifier une regle TCM')
@section('page-description', 'Ajuster le TCM cible d un type d organisation pour un signal donne.')

@section('content')
    <section class="panel-card" style="max-width: 860px;">
        <form method="POST" action="{{ route('super-admin.sla-policies.update', $slaPolicy) }}" class="row g-3">
            @csrf
            @method('PUT')
            <div class="col-md-6">
                <label class="form-label">Type d'organisation</label>
                <select name="organization_type_id" class="form-select" required>
                    @foreach ($organizationTypes as $organizationType)
                        <option value="{{ $organizationType->id }}" @selected(old('organization_type_id', $slaPolicy->organization_type_id) == $organizationType->id)>{{ $organizationType->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Canal / organisation</label>
                <select name="network_type" class="form-select" required>
                    @foreach ($networkTypes as $networkType)
                        <option value="{{ $networkType }}" @selected(old('network_type', $slaPolicy->network_type) === $networkType)>{{ $networkType }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Code signal</label>
                <input type="text" name="signal_code" value="{{ old('signal_code', $slaPolicy->signal_code) }}" class="form-control" required>
            </div>
            <div class="col-md-5">
                <label class="form-label">Libelle signal</label>
                <input type="text" name="signal_label" value="{{ old('signal_label', $slaPolicy->signal_label) }}" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">SLA cible (heures)</label>
                <input type="number" min="1" max="999" name="sla_hours" value="{{ old('sla_hours', $slaPolicy->sla_hours) }}" class="form-control" required>
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4">{{ old('description', $slaPolicy->description) }}</textarea>
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-dark">Enregistrer</button>
                <a href="{{ route('super-admin.sla-policies.index') }}" class="btn btn-outline-secondary">Retour</a>
            </div>
        </form>
    </section>
@endsection
