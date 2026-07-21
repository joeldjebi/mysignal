@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Modifier une fonctionnalité')
@section('page-title', 'Modifier une fonctionnalité')
@section('page-description', 'Mettre à jour une fonctionnalité activable.')

@section('content')
    <section class="panel-card">
        <div class="fw-bold mb-3">Édition de {{ $feature->name }}</div>
        <form method="POST" action="{{ route('super-admin.features.update', $feature) }}" class="row g-3">
            @csrf
            @method('PUT')
            <div class="col-md-3">
                <label class="form-label">Référence interne</label>
                <input type="text" name="code" value="{{ old('code', $feature->code) }}" class="form-control" required>
            </div>
            <div class="col-md-5">
                <label class="form-label">Nom</label>
                <input type="text" name="name" value="{{ old('name', $feature->name) }}" class="form-control" required>
            </div>
            <div class="col-md-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $feature->description) }}</textarea>
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-dark">Enregistrer</button>
                <a href="{{ route('super-admin.features.index') }}" class="btn btn-outline-secondary">Retour</a>
            </div>
        </form>
    </section>
@endsection
