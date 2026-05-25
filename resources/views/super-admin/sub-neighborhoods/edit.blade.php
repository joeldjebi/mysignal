@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Modifier un sous-quartier')
@section('page-title', 'Modifier un sous-quartier')
@section('page-description', 'Mettre a jour un sous-quartier et son quartier de rattachement.')

@section('content')
    <section class="panel-card">
        <div class="fw-bold mb-3">Edition de {{ $subNeighborhood->name }}</div>
        <form method="POST" action="{{ route('super-admin.sub-neighborhoods.update', $subNeighborhood) }}" class="row g-3">
            @csrf
            @method('PUT')
            <div class="col-md-5">
                <label class="form-label">Quartier</label>
                <select name="neighborhood_id" class="form-select" required>
                    @foreach ($neighborhoods as $neighborhood)
                        <option value="{{ $neighborhood->id }}" @selected(old('neighborhood_id', $subNeighborhood->neighborhood_id) == $neighborhood->id)>{{ $neighborhood->name }} · {{ $neighborhood->commune?->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Nom</label>
                <input type="text" name="name" value="{{ old('name', $subNeighborhood->name) }}" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Code</label>
                <input type="text" name="code" value="{{ old('code', $subNeighborhood->code) }}" class="form-control" required>
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-dark">Enregistrer</button>
                <a href="{{ route('super-admin.sub-neighborhoods.index') }}" class="btn btn-outline-secondary">Retour</a>
            </div>
        </form>
    </section>
@endsection
