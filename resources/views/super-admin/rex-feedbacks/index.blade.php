@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Retours d experience')
@section('page-title', 'Retours d experience')
@section('page-description', 'Parametrer le module REX et consulter les retours des usagers publics.')

@section('header-badges')
    <span class="badge-soft">{{ $feedbacks->total() }} REX</span>
@endsection

@section('content')
    <section class="panel-card mb-4">
        <div class="fw-bold mb-3">Parametrage du module REX</div>
        <form method="POST" action="{{ route('super-admin.rex-feedbacks.settings') }}" class="row g-3">
            @csrf
            @method('PUT')
            <div class="col-md-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_enabled" value="1" id="rexEnabled" @checked($setting->is_enabled)>
                    <label class="form-check-label" for="rexEnabled">Module actif</label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="incident_report_enabled" value="1" id="rexIncident" @checked($setting->incident_report_enabled)>
                    <label class="form-check-label" for="rexIncident">Signalements</label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="damage_enabled" value="1" id="rexDamage" @checked($setting->damage_enabled)>
                    <label class="form-check-label" for="rexDamage">Dommages</label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="reparation_case_enabled" value="1" id="rexCase" @checked($setting->reparation_case_enabled)>
                    <label class="form-check-label" for="rexCase">Dossiers</label>
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Delai de disponibilite</label>
                <input class="form-control" type="number" min="1" max="365" name="available_days" value="{{ old('available_days', $setting->available_days) }}">
                <div class="small text-secondary">En jours apres resolution ou cloture.</div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Modification autorisee</label>
                <input class="form-control" type="number" min="0" max="720" name="editable_hours" value="{{ old('editable_hours', $setting->editable_hours) }}">
                <div class="small text-secondary">En heures apres soumission.</div>
            </div>
            <div class="col-12">
                <button class="btn btn-dark">Enregistrer</button>
            </div>
        </form>
    </section>

    <section class="panel-card">
        <div class="fw-bold mb-3">Historique des REX</div>
        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-secondary">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="UP, signalement, commentaire">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Contexte</label>
                    <select name="context_type" class="form-select">
                        <option value="">Tous</option>
                        <option value="incident_report" @selected(request('context_type') === 'incident_report')>Signalement</option>
                        <option value="damage_declaration" @selected(request('context_type') === 'damage_declaration')>Dommage</option>
                        <option value="reparation_case" @selected(request('context_type') === 'reparation_case')>Dossier</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Note</label>
                    <select name="rating" class="form-select">
                        <option value="">Toutes</option>
                        @for ($rating = 5; $rating >= 1; $rating--)
                            <option value="{{ $rating }}" @selected((string) request('rating') === (string) $rating)>{{ $rating }}/5</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Application</label>
                    <select name="application_id" class="form-select">
                        <option value="">Toutes</option>
                        @foreach ($applications as $application)
                            <option value="{{ $application->id }}" @selected((string) request('application_id') === (string) $application->id)>{{ $application->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Organisation</label>
                    <select name="organization_id" class="form-select">
                        <option value="">Toutes</option>
                        @foreach ($organizations as $organization)
                            <option value="{{ $organization->id }}" @selected((string) request('organization_id') === (string) $organization->id)>{{ $organization->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 d-flex gap-2">
                    <button class="btn btn-dark w-100">Filtrer</button>
                </div>
            </div>
        </form>

        <div class="table-responsive mt-3">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>UP</th>
                        <th>Contexte</th>
                        <th>Note</th>
                        <th>Details</th>
                        <th>Commentaire</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($feedbacks as $feedback)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ trim(($feedback->publicUser?->first_name ?? '').' '.($feedback->publicUser?->last_name ?? '')) ?: '-' }}</div>
                                <div class="small text-secondary">{{ $feedback->publicUser?->phone ?: '-' }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ ['incident_report' => 'Signalement', 'damage_declaration' => 'Dommage', 'reparation_case' => 'Dossier'][$feedback->context_type] ?? $feedback->context_type }}</div>
                                <div class="small text-secondary">{{ $feedback->incidentReport?->reference ?: '-' }}</div>
                                <div class="small text-secondary">{{ $feedback->application?->name ?: '-' }} / {{ $feedback->organization?->name ?: '-' }}</div>
                            </td>
                            <td><span class="status-chip">{{ $feedback->rating }}/5</span></td>
                            <td>
                                <div class="small text-secondary">Delai: {{ $feedback->response_time_rating ?: '-' }}/5</div>
                                <div class="small text-secondary">Communication: {{ $feedback->communication_rating ?: '-' }}/5</div>
                                <div class="small text-secondary">Qualite: {{ $feedback->quality_rating ?: '-' }}/5</div>
                                <div class="small text-secondary">Equite: {{ $feedback->fairness_rating ?: '-' }}/5</div>
                            </td>
                            <td>{{ $feedback->comment ?: '-' }}</td>
                            <td>{{ $feedback->submitted_at?->format('d/m/Y H:i') ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-secondary">Aucun REX trouve.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $feedbacks->currentPage() }} sur {{ $feedbacks->lastPage() }}</div>
            {{ $feedbacks->links() }}
        </div>
    </section>
@endsection
