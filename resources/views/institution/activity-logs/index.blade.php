@extends('institution.layouts.app')

@section('title', config('app.name').' | Mes activités')
@section('page-title', 'Mes activités')
@section('page-description', 'Consulter l’historique des actions effectuées avec ce compte.')

@section('content')
    @php
        $label = \App\Support\Ui\InstitutionLabel::class;
    @endphp
    <section class="panel-card">
        <div class="fw-bold mb-3">Historique de mes activités</div>
        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-secondary">Du</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-secondary">Au</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button class="btn btn-dark w-100">Filtrer</button>
                    <a href="{{ route('institution.activity-logs.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                </div>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Action</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td>{{ $log->created_at?->format('d/m/Y H:i:s') ?: '-' }}</td>
                            <td>
                                <div class="fw-semibold">{{ $label::action($log->action) }}</div>
                            </td>
                            <td>{{ $log->description ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-secondary">Aucune activité enregistrée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $logs->currentPage() }} sur {{ $logs->lastPage() }}</div>
            {{ $logs->links() }}
        </div>
    </section>
@endsection
