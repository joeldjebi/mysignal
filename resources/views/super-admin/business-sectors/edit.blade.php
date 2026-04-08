@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Modifier un secteur d activite')
@section('page-title', 'Modifier un secteur d activite')
@section('page-description', 'Ajuster le libelle propose aux usagers publics entreprise.')

@section('content')
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="panel-card h-100">
                <div class="badge-soft mb-3">Referentiel entreprise</div>
                <div class="fw-bold fs-4 mb-2">{{ $businessSector->name }}</div>
                <div class="text-secondary mb-3">{{ $businessSector->description ?: 'Aucune description pour le moment.' }}</div>
                <div class="vstack gap-3">
                    <div>
                        <div class="small text-uppercase text-secondary fw-bold">Code</div>
                        <div class="fw-semibold">{{ $businessSector->code }}</div>
                    </div>
                    <div>
                        <div class="small text-uppercase text-secondary fw-bold">Ordre</div>
                        <div class="fw-semibold">{{ $businessSector->sort_order }}</div>
                    </div>
                    <div>
                        <div class="small text-uppercase text-secondary fw-bold">Statut</div>
                        <div><span class="status-chip">{{ $businessSector->status }}</span></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="panel-card">
                <form method="POST" action="{{ route('super-admin.business-sectors.update', $businessSector) }}" class="row g-3">
                    @csrf
                    @method('PUT')
                    <div class="col-md-4">
                        <label class="form-label">Code</label>
                        <input type="text" name="code" value="{{ old('code', $businessSector->code) }}" class="form-control" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Libelle</label>
                        <input type="text" name="name" value="{{ old('name', $businessSector->name) }}" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Ordre</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $businessSector->sort_order) }}" class="form-control" min="0">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="5">{{ old('description', $businessSector->description) }}</textarea>
                    </div>
                    <div class="col-12 d-flex justify-content-between align-items-center pt-2">
                        <a href="{{ route('super-admin.business-sectors.index') }}" class="btn btn-outline-secondary">Retour</a>
                        <button class="btn btn-dark px-4">Mettre a jour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
