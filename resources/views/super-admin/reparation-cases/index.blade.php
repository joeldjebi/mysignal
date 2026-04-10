@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Dossiers contentieux')
@section('page-title', 'Dossiers contentieux')
@section('page-description', 'Suivi des dossiers ouverts manuellement contre les organisations pour constat, procedure et dedommagement.')

@section('content')
    <section class="panel-card">
        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label class="form-label small text-secondary">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Reference dossier, signalement, usager...">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-secondary">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        @foreach (['submitted' => 'Soumis', 'under_review' => 'En analyse', 'awaiting_documents' => 'Pieces requises', 'sent_to_organization' => 'Transmis', 'organization_responded' => 'Reponse recue', 'approved' => 'Valide', 'rejected' => 'Rejete', 'compensated' => 'Compense', 'closed' => 'Clos'] as $status => $label)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-dark w-100">Filtrer</button>
                    <a href="{{ route('super-admin.reparation-cases.index') }}" class="btn btn-outline-secondary">RAZ</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-modern align-middle mb-0">
                <thead>
                    <tr>
                        <th>Dossier</th>
                        <th>Signalement</th>
                        <th>Usager</th>
                        <th>Organisation</th>
                        <th>Motif</th>
                        <th>Type</th>
                        <th>Huissier / Avocat</th>
                        <th>Statut</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reparationCases as $reparationCase)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $reparationCase->reference }}</div>
                                <div class="small text-secondary">{{ $reparationCase->opened_at?->format('d/m/Y H:i') ?: '-' }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $reparationCase->incidentReport?->reference ?: '-' }}</div>
                                <div class="small text-secondary">{{ $reparationCase->incidentReport?->signal_label ?: $reparationCase->incidentReport?->signal_code ?: '-' }}</div>
                            </td>
                            <td>{{ trim(($reparationCase->publicUser?->first_name ?? '').' '.($reparationCase->publicUser?->last_name ?? '')) ?: ($reparationCase->publicUser?->phone ?: '-') }}</td>
                            <td>{{ $reparationCase->organization?->name ?: '-' }}</td>
                            <td><span class="status-chip">{{ $reparationCase->eligibility_reason }}</span></td>
                            <td>
                                <div class="fw-semibold">{{ $reparationCase->case_type }}</div>
                                <div class="small text-secondary">Priorite {{ $reparationCase->priority }}</div>
                            </td>
                            <td>
                                <div class="small"><strong>Huissier :</strong> {{ $reparationCase->bailiff?->name ?: '-' }}</div>
                                <div class="small"><strong>Avocat :</strong> {{ $reparationCase->lawyer?->name ?: '-' }}</div>
                            </td>
                            <td><span class="status-chip">{{ $reparationCase->status }}</span></td>
                            <td class="text-end"><a href="{{ route('super-admin.reparation-cases.show', $reparationCase) }}" class="btn btn-sm btn-outline-dark">Ouvrir</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-secondary">Aucun dossier contentieux ouvert.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $reparationCases->currentPage() }} sur {{ $reparationCases->lastPage() }}</div>
            {{ $reparationCases->links() }}
        </div>
    </section>
@endsection
