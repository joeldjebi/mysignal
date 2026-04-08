@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Modifier une commune')
@section('page-title', 'Modifier une commune')
@section('page-description', 'Mettre a jour une commune et sa ville de rattachement.')

@section('content')
    <section class="panel-card">
        <div class="fw-bold mb-3">Edition de {{ $commune->name }}</div>
        <form method="POST" action="{{ route('super-admin.communes.update', $commune) }}" class="row g-3">
            @csrf
            @method('PUT')
            <div class="col-md-5">
                <label class="form-label">Ville</label>
                <select name="city_id" class="form-select" required>
                    @foreach ($cities as $city)
                        <option value="{{ $city->id }}" @selected(old('city_id', $commune->city_id) == $city->id)>{{ $city->name }} · {{ $city->country?->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Nom</label>
                <input type="text" name="name" value="{{ old('name', $commune->name) }}" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Code</label>
                <input type="text" name="code" value="{{ old('code', $commune->code) }}" class="form-control" required>
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-dark">Enregistrer</button>
                <a href="{{ route('super-admin.communes.index') }}" class="btn btn-outline-secondary">Retour</a>
            </div>
        </form>
    </section>
@endsection
