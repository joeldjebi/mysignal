@extends('institution.layouts.app')

@section('title', config('app.name').' | Mes activites')
@section('page-title', 'Mes activites')
@section('page-description', 'Consulter l historique des actions effectuees avec ce compte institutionnel.')

@section('content')
    <section class="panel-card">
        <div class="fw-bold mb-3">Historique de mes activites</div>
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
                    <a href="{{ route('institution.activity-logs.index') }}" class="btn btn-outline-secondary">RAZ</a>
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
                        <th>Sujet</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td>{{ $log->created_at?->format('d/m/Y H:i:s') ?: '-' }}</td>
                            <td>
                                <div class="fw-semibold">{{ $log->action }}</div>
                                <div class="small text-secondary">{{ $log->ip_address ?: '-' }}</div>
                            </td>
                            <td>{{ $log->description ?: '-' }}</td>
                            <td>
                                @if ($log->subject)
                                    <div class="fw-semibold">{{ class_basename($log->subject_type) }}</div>
                                    <div class="small text-secondary">#{{ $log->subject_id }}</div>
                                @else
                                    <span class="text-secondary">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-secondary">Aucune activite enregistree.</td></tr>
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
