@extends('super-admin.layouts.app')

@section('title', config('app.name').' | '.$reparationCase->reference)
@section('page-title', 'Dossier contentieux')
@section('page-description', 'Instruction du dossier avec attribution huissier, avocat et suivi des etapes de procedure.')

@push('styles')
    <style>
        .case-detail-grid { display: grid; grid-template-columns: minmax(260px, 340px) minmax(0, 1fr); gap: 1rem; align-items: start; }
        .case-summary-sticky { position: sticky; top: 1.1rem; }
        .case-kv { display: grid; gap: .85rem; }
        .case-kv-item { padding-bottom: .85rem; border-bottom: 1px solid rgba(15,41,64,.08); }
        .case-kv-item:last-child { border-bottom: 0; padding-bottom: 0; }
        .case-section-head { display: flex; align-items: center; justify-content: space-between; gap: .75rem; margin-bottom: .9rem; }
        .case-scroll-list { max-height: 620px; overflow: auto; padding-right: .25rem; }
        .case-feed-item { border: 1px solid rgba(15,41,64,.1); border-radius: 12px; padding: .9rem; background: rgba(255,255,255,.72); }
        .case-feed-item + .case-feed-item { margin-top: .75rem; }
        .case-compact-summary { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .75rem; }
        .case-mini-panel { border: 1px solid rgba(15,41,64,.1); border-radius: 12px; padding: .85rem; background: rgba(255,255,255,.65); min-height: 84px; }
        .case-text-clamp { display: -webkit-box; -webkit-line-clamp: 4; -webkit-box-orient: vertical; overflow: hidden; }
        details.case-collapsible > summary { cursor: pointer; list-style: none; }
        details.case-collapsible > summary::-webkit-details-marker { display: none; }
        @media (max-width: 1199.98px) {
            .case-detail-grid { grid-template-columns: 1fr; }
            .case-summary-sticky { position: static; }
            .case-compact-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .case-scroll-list { max-height: none; overflow: visible; padding-right: 0; }
        }
        @media (max-width: 575.98px) {
            .case-compact-summary { grid-template-columns: 1fr; }
        }
    </style>
@endpush

@section('content')
    <div class="case-detail-grid">
        <aside class="case-summary-sticky">
            <section class="panel-card">
                <div class="small text-secondary fw-semibold mb-2">Dossier</div>
                <div class="h5 fw-bold mb-1">{{ $reparationCase->reference }}</div>
                <div class="text-secondary small mb-3">{{ $reparationCase->organization?->name ?: 'Organisation non definie' }} · {{ $reparationCase->application?->name ?: 'Application non definie' }}</div>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="status-chip">{{ $reparationCase->status }}</span>
                    <span class="status-chip">{{ $reparationCase->eligibility_reason }}</span>
                    <span class="status-chip">{{ $caseTypes[$reparationCase->case_type] ?? $reparationCase->case_type }}</span>
                    <span class="status-chip">Priorite {{ $priorities[$reparationCase->priority] ?? $reparationCase->priority }}</span>
                </div>
                <div class="case-kv">
                    <div class="case-kv-item">
                        <div class="small text-secondary">Signalement source</div>
                        <div class="fw-semibold">{{ $reparationCase->incidentReport?->reference ?: '-' }}</div>
                        <div class="small text-secondary">{{ $reparationCase->incidentReport?->signal_label ?: $reparationCase->incidentReport?->signal_code ?: '-' }}</div>
                    </div>
                    <div class="case-kv-item">
                        <div class="small text-secondary">Usager</div>
                        <div class="fw-semibold">{{ trim(($reparationCase->publicUser?->first_name ?? '').' '.($reparationCase->publicUser?->last_name ?? '')) ?: '-' }}</div>
                        <div class="small text-secondary">{{ $reparationCase->publicUser?->phone ?: '-' }}</div>
                    </div>
                    <div class="case-kv-item">
                        <div class="small text-secondary">SLA</div>
                        <div class="fw-semibold">{{ $slaState['label'] }}</div>
                        <div class="small text-secondary">{{ $slaState['elapsed_hours'] !== null ? $slaState['elapsed_hours'].' h ecoulees' : 'Sans valeur exploitable' }}</div>
                    </div>
                    <div class="case-kv-item">
                        <div class="small text-secondary">Ouvert par</div>
                        <div class="fw-semibold">{{ $reparationCase->openedBy?->name ?: '-' }}</div>
                        <div class="small text-secondary">{{ $reparationCase->opened_at?->format('d/m/Y H:i') ?: '-' }}</div>
                    </div>
                    <div class="case-kv-item">
                        <div class="small text-secondary">Huissier</div>
                        <div class="fw-semibold">{{ $reparationCase->bailiff?->name ?: 'Non attribue' }}</div>
                    </div>
                    <div class="case-kv-item">
                        <div class="small text-secondary">Avocat</div>
                        <div class="fw-semibold">{{ $reparationCase->lawyer?->name ?: 'Non attribue' }}</div>
                    </div>
                </div>
            </section>
        </aside>
        <main>
            <details class="panel-card mb-4 case-collapsible" open>
                <summary>
                    <div class="case-section-head mb-0">
                        <div>
                            <div class="fw-bold">Pilotage du dossier</div>
                            <div class="small text-secondary">Attribution, statut, montant valide et notes de traitement</div>
                        </div>
                        <span class="badge-soft">Modifier</span>
                    </div>
                </summary>
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
                            @foreach (['submitted' => 'Soumis', 'under_review' => 'En analyse', 'awaiting_documents' => 'Pieces requises', 'sent_to_organization' => 'Transmis a l organisation', 'organization_responded' => 'Reponse organisation', 'awaiting_lawyer_assignment' => 'En attente AODA', 'lawyer_assigned' => 'Avocat attribue', 'judicial_in_progress' => 'Procedure judiciaire', 'approved' => 'Valide', 'rejected' => 'Rejete', 'compensated' => 'Compense', 'closed' => 'Clos'] as $status => $label)
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
                        <div class="form-control bg-light">{{ $reparationCase->lawyer?->name ?: 'Attribue par AODA apres constat huissier' }}</div>
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
            </details>

            <section class="panel-card mb-4">
                <div class="case-section-head">
                    <div>
                        <div class="fw-bold">Contexte</div>
                        <div class="small text-secondary">Ouverture, dommage et signalement source</div>
                    </div>
                </div>
                <div class="case-compact-summary">
                    <div class="case-mini-panel">
                        <div class="small text-secondary">Montant reclame</div>
                        <div class="fw-bold">{{ $reparationCase->damage_amount_claimed !== null ? number_format((float) $reparationCase->damage_amount_claimed, 0, ',', ' ').' FCFA' : 'Non renseigne' }}</div>
                    </div>
                    <div class="case-mini-panel">
                        <div class="small text-secondary">Montant valide</div>
                        <div class="fw-bold">{{ $reparationCase->damage_amount_validated !== null ? number_format((float) $reparationCase->damage_amount_validated, 0, ',', ' ').' FCFA' : 'Non renseigne' }}</div>
                    </div>
                    <div class="case-mini-panel">
                        <div class="small text-secondary">Adresse</div>
                        <div class="fw-semibold case-text-clamp">{{ $reparationCase->incidentReport?->address ?: '-' }}</div>
                    </div>
                    <div class="case-mini-panel">
                        <div class="small text-secondary">Commune</div>
                        <div class="fw-semibold">{{ $reparationCase->incidentReport?->commune?->name ?: '-' }}</div>
                    </div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-lg-6">
                        <div class="case-mini-panel h-100">
                            <div class="small text-secondary mb-1">Notes d'ouverture</div>
                            <div class="case-text-clamp">{{ $reparationCase->opening_notes ?: 'Aucune note.' }}</div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="case-mini-panel h-100">
                            <div class="small text-secondary mb-1">Resume des dommages</div>
                            <div class="case-text-clamp">{{ $reparationCase->damage_summary ?: 'Non renseigne' }}</div>
                        </div>
                    </div>
                </div>
            </section>

            <details class="panel-card mb-4 case-collapsible">
                <summary>
                    <div class="case-section-head mb-0">
                        <div>
                            <div class="fw-bold">Ajouter une etape</div>
                            <div class="small text-secondary">Action manuelle SA / SA_ADMIN</div>
                        </div>
                        <span class="badge-soft">Ouvrir</span>
                    </div>
                </summary>
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
            </details>

            <div class="row g-4 mb-4">
                <div class="col-xl-7">
                    <section class="panel-card h-100">
                        <div class="case-section-head">
                            <div>
                                <div class="fw-bold">Etapes de procedure</div>
                                <div class="small text-secondary">{{ $reparationCase->steps->count() }} etape{{ $reparationCase->steps->count() > 1 ? 's' : '' }}</div>
                            </div>
                        </div>
                        <div class="case-scroll-list">
                            @forelse ($reparationCase->steps as $step)
                                <div class="case-feed-item">
                                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                                        <div>
                                            <div class="fw-bold">{{ $step->title }}</div>
                                            <div class="small text-secondary">{{ $stepTypes[$step->step_type] ?? $step->step_type }} - {{ $stepStatuses[$step->status] ?? $step->status }}</div>
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
                                    @if (! empty($step->meta['attachments'] ?? []))
                                        <div class="d-flex flex-wrap gap-2 mt-3">
                                            @foreach ($step->meta['attachments'] as $attachment)
                                                @php
                                                    $attachmentUrl = app(\App\Services\WasabiService::class)->temporaryUrl($attachment['path'] ?? null);
                                                @endphp
                                                @if ($attachmentUrl)
                                                    <a href="{{ $attachmentUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-dark">
                                                        {{ $attachment['name'] ?? 'Piece jointe' }}
                                                    </a>
                                                @else
                                                    <span class="badge text-bg-light border">{{ $attachment['name'] ?? 'Piece jointe' }}</span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-secondary">Aucune etape procedurale enregistree pour le moment.</div>
                            @endforelse
                        </div>
                    </section>
                </div>

                <div class="col-xl-5">
                    <section class="panel-card h-100">
                        <div class="case-section-head">
                            <div>
                                <div class="fw-bold">Historique victime</div>
                                <div class="small text-secondary">{{ $reparationCase->histories->count() }} entree{{ $reparationCase->histories->count() > 1 ? 's' : '' }}</div>
                            </div>
                        </div>
                        <div class="case-scroll-list">
                            @forelse ($reparationCase->histories as $history)
                                <div class="case-feed-item">
                                    <div class="fw-bold">{{ $history->title }}</div>
                                    <div class="small text-secondary mb-2">{{ $history->createdBy?->name ?: 'Systeme' }} - {{ $history->created_at?->format('d/m/Y H:i') }}</div>
                                    <div>{{ $history->description ?: '-' }}</div>
                                </div>
                            @empty
                                <div class="text-secondary">Aucun historique visible victime.</div>
                            @endforelse
                        </div>
                    </section>
                </div>
            </div>

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
        </main>
    </div>
@endsection
