@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Modifier un pays')
@section('page-title', 'Modifier un pays')
@section('page-description', 'Mettre a jour le referentiel pays.')

@section('content')
    <section class="panel-card">
        <div class="fw-bold mb-3">Edition de {{ $country->name }}</div>
        <form method="POST" action="{{ route('super-admin.countries.update', $country) }}" class="row g-3">
            @csrf
            @method('PUT')
            <div class="col-md-8">
                <label class="form-label">Nom</label>
                <input type="text" name="name" value="{{ old('name', $country->name) }}" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Code</label>
                <input type="text" name="code" value="{{ old('code', $country->code) }}" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Flag</label>
                <input type="text" name="flag" value="{{ old('flag', $country->flag) }}" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Indicatif</label>
                <div class="input-group">
                    <span class="input-group-text">+</span>
                    <input type="text" name="dial_code" value="{{ old('dial_code', $country->dial_code) }}" class="form-control" required>
                </div>
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-dark">Enregistrer</button>
                <a href="{{ route('super-admin.countries.index') }}" class="btn btn-outline-secondary">Retour</a>
            </div>
        </form>
    </section>
@endsection
