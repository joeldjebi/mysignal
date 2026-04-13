@extends('super-admin.layouts.app')

@section('title', config('app.name').' | '.$reparationCase->reference)
@section('page-title', 'Dossier contentieux')
@section('page-description', 'Instruction du dossier avec attribution huissier, avocat et suivi des etapes de procedure.')

@section('content')
    <div class="row g-4">
        <div class="col-lg-4">
            <section class="panel-card h-100">
                <div class="small text-secondary fw-semibold mb-2">Dossier</div>
                <div class="h5 fw-bold mb-1">{{ $reparationCase->reference }}</div>
                <div class="text-secondary small mb-3">{{ $reparationCase->organization?->name ?: 'Organisation non definie' }} · {{ $reparationCase->application?->name ?: 'Application non definie' }}</div>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="status-chip">{{ $reparationCase->status }}</span>
                    <span class="status-chip">{{ $reparationCase->eligibility_reason }}</span>
                    <span class="status-chip">{{ $caseTypes[$reparationCase->case_type] ?? $reparationCase->case_type }}</span>
                    <span class="status-chip">Priorite {{ $priorities[$reparationCase->priority] ?? $reparationCase->priority }}</span>
                </div>
                <div class="small text-secondary mb-1">Signalement source</div>
                <div class="fw-semibold">{{ $reparationCase->incidentReport?->reference ?: '-' }}</div>
                <div class="small text-secondary mb-3">{{ $reparationCase->incidentReport?->signal_label ?: $reparationCase->incidentReport?->signal_code ?: '-' }}</div>
                <div class="small text-secondary mb-1">Usager</div>
                <div class="fw-semibold">{{ trim(($reparationCase->publicUser?->first_name ?? '').' '.($reparationCase->publicUser?->last_name ?? '')) ?: '-' }}</div>
                <div class="small text-secondary mb-3">{{ $reparationCase->publicUser?->phone ?: '-' }}</div>
                <div class="small text-secondary mb-1">SLA</div>
                <div class="fw-semibold">{{ $slaState['label'] }}</div>
                <div class="small text-secondary mb-3">{{ $slaState['elapsed_hours'] !== null ? $slaState['elapsed_hours'].' h ecoulees' : 'Sans valeur exploitable' }}</div>
                <div class="small text-secondary mb-1">Ouvert par</div>
                <div class="fw-semibold">{{ $reparationCase->openedBy?->name ?: '-' }}</div>
                <div class="small text-secondary">{{ $reparationCase->opened_at?->format('d/m/Y H:i') ?: '-' }}</div>
                <div class="small text-secondary mt-3 mb-1">Huissier</div>
                <div class="fw-semibold">{{ $reparationCase->bailiff?->name ?: 'Non attribue' }}</div>
                <div class="small text-secondary mt-3 mb-1">Avocat</div>
                <div class="fw-semibold">{{ $reparationCase->lawyer?->name ?: 'Non attribue' }}</div>
            </section>
        </div>
        <div class="col-lg-8">
            <section class="panel-card mb-4">
                <div class="fw-bold mb-3">Pilotage du dossier</div>
                <form method="POST" action="{{ route('super-admin.reparation-cases.update', $reparationCase) }}" class="row g-3">
                    @csrf
                    @method('PUT')
                    <div class="col-md-4">
                        <label class="form-label">Type de dossier</label>
                        <select class="form-select" name="case_type" required>
                            @foreach ($caseTypes as $caseType => $label)
                                <option value="{{ $caseType }}" @selected(old('case_type', $reparationCase->case_type) === $caseType)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Priorite</label>
                        <select class="form-select" name="priority" required>
                            @foreach ($priorities as $priority => $label)
                                <option value="{{ $priority }}" @selected(old('priority', $reparationCase->priority) === $priority)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Statut</label>
                        <select class="form-select" name="status" required>
                            @foreach (['submitted' => 'Soumis', 'under_review' => 'En analyse', 'awaiting_documents' => 'Pieces requises', 'sent_to_organization' => 'Transmis a l organisation', 'organization_responded' => 'Reponse organisation', 'approved' => 'Valide', 'rejected' => 'Rejete', 'compensated' => 'Compense', 'closed' => 'Clos'] as $status => $label)
                                <option value="{{ $status }}" @selected(old('status', $reparationCase->status) === $status)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Assigner a</label>
                        <select class="form-select" name="assigned_to_user_id">
                            <option value="">Non assigne</option>
                            @foreach ($assignableUsers as $assignableUser)
                                <option value="{{ $assignableUser->id }}" @selected((string) old('assigned_to_user_id', $reparationCase->assigned_to_user_id) === (string) $assignableUser->id)>{{ $assignableUser->name }}{{ $assignableUser->email ? ' · '.$assignableUser->email : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Huissier</label>
                        <select class="form-select" name="bailiff_user_id">
                            <option value="">Non attribue</option>
                            @foreach ($bailiffUsers as $bailiffUser)
                                <option value="{{ $bailiffUser->id }}" @selected((string) old('bailiff_user_id', $reparationCase->bailiff_user_id) === (string) $bailiffUser->id)>{{ $bailiffUser->name }}{{ $bailiffUser->email ? ' · '.$bailiffUser->email : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Avocat</label>
                        <select class="form-select" name="lawyer_user_id">
                            <option value="">Non attribue</option>
                            @foreach ($lawyerUsers as $lawyerUser)
                                <option value="{{ $lawyerUser->id }}" @selected((string) old('lawyer_user_id', $reparationCase->lawyer_user_id) === (string) $lawyerUser->id)>{{ $lawyerUser->name }}{{ $lawyerUser->email ? ' · '.$lawyerUser->email : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Montant valide</label>
                        <input type="number" min="0" step="0.01" class="form-control" name="damage_amount_validated" value="{{ old('damage_amount_validated', $reparationCase->damage_amount_validated) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Notes de traitement</label>
                        <textarea class="form-control" rows="5" name="resolution_notes">{{ old('resolution_notes', $reparationCase->resolution_notes) }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Motif de cloture</label>
                        <textarea class="form-control" rows="3" name="closure_reason">{{ old('closure_reason', $reparationCase->closure_reason) }}</textarea>
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-dark">Enregistrer</button>
                        <a href="{{ route('super-admin.reparation-cases.index') }}" class="btn btn-outline-secondary">Retour</a>
                    </div>
                </form>
            </section>

            <section class="panel-card mb-4">
                <div class="fw-bold mb-3">Contexte d ouverture</div>
                <div class="mb-2"><strong>Notes d ouverture :</strong> {{ $reparationCase->opening_notes ?: 'Aucune note.' }}</div>
                <div class="mb-2"><strong>Montant reclame :</strong> {{ $reparationCase->damage_amount_claimed !== null ? number_format((float) $reparationCase->damage_amount_claimed, 0, ',', ' ').' FCFA' : 'Non renseigne' }}</div>
                <div><strong>Resume dommage :</strong> {{ $reparationCase->damage_summary ?: 'Non renseigne' }}</div>
            </section>

            <section class="panel-card mb-4">
                <div class="fw-bold mb-3">Etapes de procedure</div>
                <form method="POST" action="{{ route('super-admin.reparation-cases.steps.store', $reparationCase) }}" class="row g-3 mb-4">
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label">Type d etape</label>
                        <select class="form-select" name="step_type" required>
                            @foreach ($stepTypes as $stepType => $label)
                                <option value="{{ $stepType }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Statut</label>
                        <select class="form-select" name="status" required>
                            @foreach ($stepStatuses as $status => $label)
                                <option value="{{ $status }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Responsable</label>
                        <select class="form-select" name="assigned_to_user_id">
                            <option value="">Aucun</option>
                            @foreach ($assignableUsers as $assignableUser)
                                <option value="{{ $assignableUser->id }}">{{ $assignableUser->name }}{{ $assignableUser->email ? ' · '.$assignableUser->email : '' }}</option>
                            @endforeach
                            @foreach ($bailiffUsers as $bailiffUser)
                                @if (! $assignableUsers->contains('id', $bailiffUser->id))
                                    <option value="{{ $bailiffUser->id }}">{{ $bailiffUser->name }}{{ $bailiffUser->email ? ' · '.$bailiffUser->email : '' }}</option>
                                @endif
                            @endforeach
                            @foreach ($lawyerUsers as $lawyerUser)
                                @if (! $assignableUsers->contains('id', $lawyerUser->id) && ! $bailiffUsers->contains('id', $lawyerUser->id))
                                    <option value="{{ $lawyerUser->id }}">{{ $lawyerUser->name }}{{ $lawyerUser->email ? ' · '.$lawyerUser->email : '' }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Titre</label>
                        <input type="text" class="form-control" name="title" required placeholder="Ex. Constat programme sur site">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Echeance</label>
                        <input type="datetime-local" class="form-control" name="due_at">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date de realisation</label>
                        <input type="datetime-local" class="form-control" name="completed_at">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Resume</label>
                        <textarea class="form-control" rows="3" name="summary" placeholder="Constat, diligence, acte transmis, audience, resultat..."></textarea>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="stepVisibleToPublic" name="is_visible_to_public" checked>
                            <label class="form-check-label" for="stepVisibleToPublic">Visible dans le dashboard de l usager public</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-dark">Ajouter l etape</button>
                    </div>
                </form>

                <div class="vstack gap-3">
                    @forelse ($reparationCase->steps as $step)
                        <div class="border rounded-4 p-3">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                                <div>
                                    <div class="fw-bold">{{ $step->title }}</div>
                                    <div class="small text-secondary">{{ $stepTypes[$step->step_type] ?? $step->step_type }} · {{ $stepStatuses[$step->status] ?? $step->status }}</div>
                                    <div class="small text-secondary">Responsable : {{ $step->assignedTo?->name ?: 'Non assigne' }}</div>
                                </div>
                                <div class="text-end small text-secondary">
                                    <div>Cree le {{ $step->created_at?->format('d/m/Y H:i') ?: '-' }}</div>
                                    <div>Echeance : {{ $step->due_at?->format('d/m/Y H:i') ?: '-' }}</div>
                                    <div>Realise le : {{ $step->completed_at?->format('d/m/Y H:i') ?: '-' }}</div>
                                    <div>{{ $step->is_visible_to_public ? 'Visible usager public' : 'Interne seulement' }}</div>
                                </div>
                            </div>
                            <div class="mt-3">{{ $step->summary ?: 'Aucun resume renseigne.' }}</div>
                        </div>
                    @empty
                        <div class="text-secondary">Aucune etape procedurale enregistree pour le moment.</div>
                    @endforelse
                </div>
            </section>

            <section class="panel-card">
                <div class="fw-bold mb-3">Signalement source</div>
                <div class="mb-2"><strong>Description :</strong> {{ $reparationCase->incidentReport?->description ?: 'Aucune description fournie.' }}</div>
                <div class="mb-2"><strong>Adresse :</strong> {{ $reparationCase->incidentReport?->address ?: '-' }}</div>
                <div class="mb-2"><strong>Commune :</strong> {{ $reparationCase->incidentReport?->commune?->name ?: '-' }}</div>
                @if (!empty($resolvedSignalPayload))
                    <div class="border-top pt-3 mt-3">
                        <div class="small text-secondary fw-semibold mb-2">Donnees complementaires</div>
                        <div class="row g-3">
                            @foreach ($resolvedSignalPayload as $key => $value)
                                <div class="col-md-6">
                                    <div class="border rounded-4 p-3 h-100">
                                        <div class="small text-secondary mb-1">{{ $key }}</div>
                                        <div class="fw-semibold">{{ is_array($value) ? ($value['name'] ?? 'Fichier') : $value }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                @if (!empty($resolvedDamageAttachment) && filled($resolvedDamageAttachment['temporary_url'] ?? null))
                    <div class="border-top pt-3 mt-3">
                        <div class="small text-secondary fw-semibold mb-2">Justificatif dommage</div>
                        @if (str_starts_with((string) ($resolvedDamageAttachment['mime_type'] ?? ''), 'image/'))
                            <img src="{{ $resolvedDamageAttachment['temporary_url'] }}" alt="Justificatif dommage" class="img-fluid rounded-4 border" style="max-height: 420px; width: 100%; object-fit: contain; background: #f7f9fc;">
                        @else
                            <a href="{{ $resolvedDamageAttachment['temporary_url'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-dark btn-sm">Ouvrir le justificatif</a>
                        @endif
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection
