@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Modifier un quartier')
@section('page-title', 'Modifier un quartier')
@section('page-description', 'Mettre a jour un quartier et sa commune de rattachement.')

@section('content')
    <section class="panel-card">
        <div class="fw-bold mb-3">Edition de {{ $neighborhood->name }}</div>
        <form method="POST" action="{{ route('super-admin.neighborhoods.update', $neighborhood) }}" class="row g-3">
            @csrf
            @method('PUT')
            <div class="col-md-5">
                <label class="form-label">Commune</label>
                <select name="commune_id" class="form-select" required>
                    @foreach ($communes as $commune)
                        <option value="{{ $commune->id }}" @selected(old('commune_id', $neighborhood->commune_id) == $commune->id)>{{ $commune->name }} · {{ $commune->city?->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Nom</label>
                <input type="text" name="name" value="{{ old('name', $neighborhood->name) }}" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Code</label>
                <input type="text" name="code" value="{{ old('code', $neighborhood->code) }}" class="form-control" required>
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-dark">Enregistrer</button>
                <a href="{{ route('super-admin.neighborhoods.index') }}" class="btn btn-outline-secondary">Retour</a>
            </div>
        </form>
    </section>
@endsection
