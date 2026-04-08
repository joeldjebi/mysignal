@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Modifier une permission')
@section('page-title', 'Modifier une permission')
@section('page-description', 'Mettre a jour une permission de la plateforme.')

@section('content')
    <section class="panel-card">
        <div class="fw-bold mb-3">Edition de {{ $permission->name }}</div>
        <form method="POST" action="{{ route('super-admin.permissions.update', $permission) }}" class="row g-3">
            @csrf
            @method('PUT')
            <div class="col-md-3">
                <label class="form-label">Code</label>
                <input type="text" name="code" value="{{ old('code', $permission->code) }}" class="form-control" required>
            </div>
            <div class="col-md-5">
                <label class="form-label">Nom</label>
                <input type="text" name="name" value="{{ old('name', $permission->name) }}" class="form-control" required>
            </div>
            <div class="col-md-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $permission->description) }}</textarea>
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-dark">Enregistrer</button>
                <a href="{{ route('super-admin.permissions.index') }}" class="btn btn-outline-secondary">Retour</a>
            </div>
        </form>
    </section>
@endsection
