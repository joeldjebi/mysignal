@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Modifier un role')
@section('page-title', 'Modifier un role')
@section('page-description', 'Mettre a jour un role et ses permissions associees.')

@section('content')
    <section class="panel-card">
        <div class="fw-bold mb-3">Edition de {{ $role->name }}</div>
        <form method="POST" action="{{ route('super-admin.roles.update', $role) }}" class="row g-3">
            @csrf
            @method('PUT')
            <div class="col-md-3">
                <label class="form-label">Code</label>
                <input type="text" name="code" value="{{ old('code', $role->code) }}" class="form-control" required>
            </div>
            <div class="col-md-5">
                <label class="form-label">Nom</label>
                <input type="text" name="name" value="{{ old('name', $role->name) }}" class="form-control" required>
            </div>
            <div class="col-md-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="2">{{ old('description', $role->description) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Permissions</label>
                <div class="border rounded-3 p-3" style="max-height: 260px; overflow:auto;">
                    <div class="row row-cols-1 row-cols-md-2 g-2">
                        @foreach ($permissions as $permission)
                            <div class="col">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="{{ $permission->id }}" name="permission_ids[]" id="permission-edit-{{ $permission->id }}" @checked(in_array($permission->id, old('permission_ids', $role->permissions->pluck('id')->all())))>
                                    <label class="form-check-label" for="permission-edit-{{ $permission->id }}">{{ $permission->name }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-dark">Enregistrer</button>
                <a href="{{ route('super-admin.roles.index') }}" class="btn btn-outline-secondary">Retour</a>
            </div>
        </form>
    </section>
@endsection
