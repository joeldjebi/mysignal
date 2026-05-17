@extends('institution.layouts.app')

@section('title', config('app.name').' | Dossiers')
@section('page-title', 'Dossiers de traitement')
@section('page-description', 'Historique des dossiers lies aux signalements de votre organisation.')

@section('content')
    @php
        $statusLabel = fn (?string $status) => [
            'submitted' => 'Soumis',
            'under_review' => 'Analyse',
            'awaiting_documents' => 'Pieces attendues',
            'sent_to_organization' => 'Transmis organisation',
            'organization_responded' => 'Reponse organisation',
            'approved' => 'Approuve',
            'rejected' => 'Rejete',
            'compensated' => 'Dedommage',
            'closed' => 'Clos',
        ][$status] ?? ($status ?: '-');
        $statusClass = fn (?string $status) => match ($status) {
            'approved', 'compensated', 'closed' => 'chip-success',
            'rejected' => 'chip-danger',
            'under_review', 'awaiting_documents', 'sent_to_organization', 'organization_responded' => 'chip-warning',
            default => 'chip-neutral',
        };
    @endphp

    <section class="panel-card">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
            <div>
                <div class="fw-bold">Dossiers</div>
                <div class="text-secondary small">Seuls les dossiers rattachés à votre application et à votre organisation sont visibles ici.</div>
            </div>
            <span class="status-chip">{{ $reparationCases->total() }} dossier(s)</span>
        </div>

        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small text-secondary">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Reference dossier, signalement, usager">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-secondary">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        @foreach (['submitted', 'under_review', 'awaiting_documents', 'sent_to_organization', 'organization_responded', 'approved', 'rejected', 'compensated', 'closed'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $statusLabel($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Type</label>
                    <select name="case_type" class="form-select">
                        <option value="">Tous</option>
                        <option value="precontentieux" @selected(request('case_type') === 'precontentieux')>Precontentieux</option>
                        <option value="judiciaire" @selected(request('case_type') === 'judiciaire')>Judiciaire</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-dark w-100">Filtrer</button>
                    <a href="{{ route('institution.reparation-cases.index') }}" class="btn btn-outline-secondary">RAZ</a>
                </div>
            </div>
        </form>

        <div class="table-card">
            <div class="table-responsive">
                <table class="table table-modern align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Dossier</th>
                            <th>Perimetre</th>
                            <th>Signalement</th>
                            <th>Usager</th>
                            <th>Statut</th>
                            <th>Intervenants</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reparationCases as $case)
                            <tr>
                                <td>
                                    <div class="meta-stack">
                                        <span class="meta-title">{{ $case->reference }}</span>
                                        <span class="meta-subtitle">{{ $case->opened_at?->format('d/m/Y H:i') ?: $case->created_at?->format('d/m/Y H:i') }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="meta-stack">
                                        <span class="meta-title">{{ $case->application?->name ?: '-' }}</span>
                                        <span class="meta-subtitle">{{ $case->organization?->name ?: '-' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="meta-stack">
                                        <span class="meta-title">{{ $case->incidentReport?->reference ?: '-' }}</span>
                                        <span class="meta-subtitle">{{ $case->incidentReport?->signal_label ?: $case->incidentReport?->signal_code }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="meta-stack">
                                        <span class="meta-title">{{ trim(($case->publicUser?->first_name ?? '').' '.($case->publicUser?->last_name ?? '')) ?: '-' }}</span>
                                        <span class="meta-subtitle">{{ $case->publicUser?->phone ?: '-' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-chip {{ $statusClass($case->status) }}">{{ $statusLabel($case->status) }}</span>
                                </td>
                                <td>
                                    <div class="meta-stack">
                                        <span class="meta-subtitle">Responsable: {{ $case->assignedTo?->name ?: '-' }}</span>
                                        <span class="meta-subtitle">Huissier: {{ $case->bailiff?->name ?: '-' }}</span>
                                        <span class="meta-subtitle">Avocat: {{ $case->lawyer?->name ?: '-' }}</span>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('institution.reparation-cases.show', $case) }}" class="btn btn-sm btn-outline-dark">Details</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-secondary py-4">Aucun dossier disponible.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $reparationCases->currentPage() }} sur {{ $reparationCases->lastPage() }}</div>
            {{ $reparationCases->links() }}
        </div>
    </section>
@endsection
