@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Modifier une ville')
@section('page-title', 'Modifier une ville')
@section('page-description', 'Mettre a jour une ville et son rattachement pays.')

@section('content')
    <section class="panel-card">
        <div class="fw-bold mb-3">Edition de {{ $city->name }}</div>
        <form method="POST" action="{{ route('super-admin.cities.update', $city) }}" class="row g-3">
            @csrf
            @method('PUT')
            <div class="col-md-4">
                <label class="form-label">Pays</label>
                <select name="country_id" class="form-select" required>
                    @foreach ($countries as $country)
                        <option value="{{ $country->id }}" @selected(old('country_id', $city->country_id) == $country->id)>{{ $country->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label">Nom</label>
                <input type="text" name="name" value="{{ old('name', $city->name) }}" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Code</label>
                <input type="text" name="code" value="{{ old('code', $city->code) }}" class="form-control" required>
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-dark">Enregistrer</button>
                <a href="{{ route('super-admin.cities.index') }}" class="btn btn-outline-secondary">Retour</a>
            </div>
        </form>
    </section>
@endsection
