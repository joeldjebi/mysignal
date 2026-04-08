@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Modifier un type de client')
@section('page-title', 'Modifier un type de client')
@section('page-description', 'Mettre a jour un type d organisation.')

@section('content')
    <section class="panel-card">
        <div class="fw-bold mb-3">Edition de {{ $organizationType->name }}</div>
        <form method="POST" action="{{ route('super-admin.client-types.update', $organizationType) }}" class="row g-3">
            @csrf
            @method('PUT')
            <div class="col-md-3">
                <label class="form-label">Code</label>
                <input type="text" name="code" value="{{ old('code', $organizationType->code) }}" class="form-control" required>
            </div>
            <div class="col-md-5">
                <label class="form-label">Nom</label>
                <input type="text" name="name" value="{{ old('name', $organizationType->name) }}" class="form-control" required>
            </div>
            <div class="col-md-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $organizationType->description) }}</textarea>
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-dark">Enregistrer</button>
                <a href="{{ route('super-admin.client-types.index') }}" class="btn btn-outline-secondary">Retour</a>
            </div>
        </form>
    </section>
@endsection
