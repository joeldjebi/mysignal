@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Dommages a ouvrir en contentieux')
@section('page-title', 'Dommages a ouvrir en contentieux')
@section('page-description', 'Signalements avec dommage declare et sans dossier contentieux ouvert.')

@section('header-badges')
    <span class="badge-soft">{{ $pendingDamageReports->total() }} dommage{{ $pendingDamageReports->total() > 1 ? 's' : '' }} a ouvrir</span>
@endsection

@section('content')
    <section class="panel-card">
        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-7">
                    <label class="form-label small text-secondary">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Reference, usager, signalement, organisation...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Par page</label>
                    <select name="per_page" class="form-select">
                        @foreach ([15, 25, 50, 100] as $perPageOption)
                            <option value="{{ $perPageOption }}" @selected((int) request('per_page', 15) === $perPageOption)>{{ $perPageOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-dark w-100">Filtrer</button>
                    <a href="{{ route('super-admin.reparation-damages.index') }}" class="btn btn-outline-secondary">RAZ</a>
                </div>
            </div>
        </form>

        <div class="table-toolbar">
            <div>
                <div class="fw-bold">Liste des dommages a ouvrir</div>
                <div class="table-meta">Ces signalements peuvent etre ouverts en dossier contentieux puis attribues a un huissier.</div>
            </div>
            <div class="table-meta">{{ $pendingDamageReports->total() }} resultat{{ $pendingDamageReports->total() > 1 ? 's' : '' }}</div>
        </div>

        <div class="table-responsive">
            <table class="table table-modern align-middle mb-0">
                <thead>
                    <tr>
                        <th>Signalement</th>
                        <th>Usager</th>
                        <th>Organisation</th>
                        <th>Dommage</th>
                        <th>Localisation</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pendingDamageReports as $report)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $report->reference }}</div>
                                <div class="small text-secondary">{{ $report->signal_label ?: $report->signal_code ?: $report->incident_type }}</div>
                                <div class="small text-secondary">{{ $report->created_at?->format('d/m/Y H:i') ?: '-' }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ trim(($report->publicUser?->first_name ?? '').' '.($report->publicUser?->last_name ?? '')) ?: '-' }}</div>
                                <div class="small text-secondary">{{ $report->publicUser?->phone ?: '-' }}</div>
                            </td>
                            <td>
                                <div>{{ $report->organization?->name ?: '-' }}</div>
                                <div class="small text-secondary">{{ $report->application?->name ?: '-' }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $report->damage_summary ?: 'Dommage declare' }}</div>
                                <div class="small text-secondary">
                                    {{ $report->damage_amount_estimated !== null ? number_format((float) $report->damage_amount_estimated, 0, ',', ' ').' FCFA' : 'Montant non renseigne' }}
                                </div>
                            </td>
                            <td>
                                <div>{{ $report->commune?->name ?: '-' }}</div>
                                <div class="small text-secondary">{{ $report->address ?: 'Adresse non renseignee' }}</div>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-dark" type="button" data-bs-toggle="modal" data-bs-target="#openReparationCaseModal-{{ $report->id }}">
                                    Ouvrir / attribuer
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-secondary">Aucun dommage en attente d ouverture de dossier.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @foreach ($pendingDamageReports as $report)
            <div class="modal fade" id="openReparationCaseModal-{{ $report->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title fw-bold mb-0">Ouvrir un dossier contentieux</h5>
                                <div class="small text-secondary">{{ $report->reference }} · {{ $report->signal_label ?: $report->signal_code ?: 'Signalement dommage' }}</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                        </div>
                        <form method="POST" action="{{ route('super-admin.reparation-cases.store') }}">
                            @csrf
                            <input type="hidden" name="incident_report_id" value="{{ $report->id }}">
                            <div class="modal-body">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Type de dossier</label>
                                        <select class="form-select" name="case_type">
                                            <option value="precontentieux">Precontentieux</option>
                                            <option value="judiciaire">Judiciaire</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Priorite</label>
                                        <select class="form-select" name="priority">
                                            <option value="normal">Normale</option>
                                            <option value="high">Haute</option>
                                            <option value="critical">Critique</option>
                                            <option value="low">Faible</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Huissier</label>
                                        <select class="form-select" name="bailiff_user_id">
                                            <option value="">Aucun huissier a l ouverture</option>
                                            @foreach ($bailiffUsers as $bailiffUser)
                                                <option value="{{ $bailiffUser->id }}">{{ $bailiffUser->name }}{{ $bailiffUser->email ? ' · '.$bailiffUser->email : '' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="border rounded-4 p-3 mb-3">
                                    <div class="small text-secondary mb-1">Dommage declare</div>
                                    <div class="fw-semibold">{{ $report->damage_summary ?: 'Dommage declare par l usager public.' }}</div>
                                    <div class="small text-secondary mt-1">
                                        Montant estime :
                                        {{ $report->damage_amount_estimated !== null ? number_format((float) $report->damage_amount_estimated, 0, ',', ' ').' FCFA' : 'Non renseigne' }}
                                    </div>
                                </div>
                                <label class="form-label">Notes d'ouverture</label>
                                <textarea class="form-control" name="opening_notes" rows="4" placeholder="Resume du contexte, points a instruire, attentes vis-a-vis de l organisation..."></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                                <button type="submit" class="btn btn-dark">Ouvrir le dossier</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $pendingDamageReports->currentPage() }} sur {{ $pendingDamageReports->lastPage() }}</div>
            {{ $pendingDamageReports->links() }}
        </div>
    </section>
@endsection
