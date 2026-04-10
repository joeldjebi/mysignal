<section class="panel-card mt-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
        <div>
            <div class="fw-bold mb-1">Signalements et pieces jointes</div>
            <div class="small text-secondary">Consultez les signalements declares par cet usager ainsi que les images et justificatifs envoyes.</div>
        </div>
        <div class="small text-secondary">
            {{ $reports->total() }} resultat{{ $reports->total() > 1 ? 's' : '' }}
        </div>
    </div>

    <form method="GET" action="{{ route('super-admin.public-users.show', $publicUser) }}" class="border rounded-4 p-3 mb-4" style="background: #f8fbff;">
        <div class="row g-3 align-items-end">
            <div class="col-lg-5">
                <label class="form-label small fw-semibold">Recherche</label>
                <input
                    type="text"
                    name="report_search"
                    class="form-control"
                    value="{{ request('report_search') }}"
                    placeholder="Reference, signalement, organisation, application..."
                >
            </div>
            <div class="col-md-3 col-lg-2">
                <label class="form-label small fw-semibold">Statut</label>
                <select name="report_status" class="form-select">
                    <option value="">Tous</option>
                    @foreach ($reportStatuses as $status)
                        <option value="{{ $status }}" @selected(request('report_status') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 col-lg-3">
                <label class="form-label small fw-semibold">Dossier</label>
                <select name="report_case_status" class="form-select">
                    <option value="">Tous</option>
                    <option value="opened" @selected(request('report_case_status') === 'opened')>Dossier ouvert</option>
                    <option value="to_open" @selected(request('report_case_status') === 'to_open')>Dossier a ouvrir</option>
                    <option value="not_eligible" @selected(request('report_case_status') === 'not_eligible')>Non eligible</option>
                </select>
            </div>
            <div class="col-lg-2">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-dark w-100">Filtrer</button>
                    <a href="{{ route('super-admin.public-users.show', $publicUser) }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </div>
        </div>
    </form>

    @if ($reports->isEmpty())
        <div class="text-secondary">Aucun signalement n a encore ete enregistre pour cet usager.</div>
    @else
        <div class="table-responsive border rounded-4">
            <table class="table align-middle mb-0">
                <thead style="background: #f8fbff;">
                    <tr>
                        <th class="px-3 py-3">Reference</th>
                        <th class="px-3 py-3">Signalement</th>
                        <th class="px-3 py-3">Organisation</th>
                        <th class="px-3 py-3">Statut</th>
                        <th class="px-3 py-3">Dossier</th>
                        <th class="px-3 py-3">Date</th>
                        <th class="px-3 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reports as $report)
                        @php
                            $signalPayload = $report->resolvedSignalPayload();
                            $damageAttachment = $report->resolvedDamageAttachment();
                            $hasDamage = $report->damage_declared_at !== null || filled($report->damage_summary) || filled($report->damage_amount_estimated);
                            $slaBreached = filled($report->target_sla_hours) && $report->created_at !== null
                                ? (($report->created_at->diffInMinutes($report->resolved_at ?? now()) / 60) >= (float) $report->target_sla_hours)
                                : false;
                            $isEligibleForReparationCase = $slaBreached || $hasDamage;
                            $reparationCaseStatusLabel = $report->reparationCase
                                ? 'Dossier ouvert'
                                : ($isEligibleForReparationCase ? 'Dossier a ouvrir' : 'Non eligible');
                            $reparationCaseStatusClass = $report->reparationCase
                                ? 'bg-success-subtle text-success border border-success-subtle'
                                : ($isEligibleForReparationCase ? 'bg-warning-subtle text-warning border border-warning-subtle' : 'bg-light text-secondary border');
                            $eligibilityReasonLabel = $slaBreached && $hasDamage
                                ? 'SLA depasse et dommage declare'
                                : ($slaBreached ? 'SLA depasse' : ($hasDamage ? 'Dommage declare' : 'Aucune condition remplie'));
                        @endphp
                        <tr>
                            <td class="px-3 py-3">
                                <div class="fw-semibold">{{ $report->reference }}</div>
                                <div class="small text-secondary">{{ $report->application?->name ?: 'Application non definie' }}</div>
                            </td>
                            <td class="px-3 py-3">
                                <div class="fw-semibold">{{ $report->signal_label ?: $report->signal_code }}</div>
                                <div class="small text-secondary text-truncate" style="max-width: 240px;">{{ $report->description ?: 'Aucune description fournie.' }}</div>
                            </td>
                            <td class="px-3 py-3">
                                <div>{{ $report->organization?->name ?: 'Organisation non definie' }}</div>
                                <div class="small text-secondary">{{ $report->commune?->name ?: '-' }}</div>
                            </td>
                            <td class="px-3 py-3">
                                <span class="status-chip">{{ $report->status }}</span>
                            </td>
                            <td class="px-3 py-3">
                                <div>
                                    <span class="badge rounded-pill px-3 py-2 {{ $reparationCaseStatusClass }}">{{ $reparationCaseStatusLabel }}</span>
                                </div>
                                <div class="small text-secondary mt-1">{{ $eligibilityReasonLabel }}</div>
                            </td>
                            <td class="px-3 py-3">
                                <div>{{ $report->created_at?->format('d/m/Y') ?: '-' }}</div>
                                <div class="small text-secondary">{{ $report->created_at?->format('H:i') ?: '' }}</div>
                            </td>
                            <td class="px-3 py-3">
                                <div class="d-flex flex-wrap gap-2">
                                    <button class="btn btn-sm btn-outline-dark" type="button" data-bs-toggle="modal" data-bs-target="#reportDetailsModal-{{ $report->id }}">Details</button>
                                    @if ($report->damage_summary || !empty($damageAttachment))
                                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#reportDamageModal-{{ $report->id }}">Detail dommage</button>
                                    @endif
                                    @if ($report->reparationCase)
                                        <a href="{{ route('super-admin.reparation-cases.show', $report->reparationCase) }}" class="btn btn-sm btn-outline-dark">Voir le dossier</a>
                                    @elseif ($isEligibleForReparationCase)
                                        <button class="btn btn-sm btn-dark" type="button" data-bs-toggle="modal" data-bs-target="#openReparationCaseModal-{{ $report->id }}">Ouvrir un dossier</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @foreach ($reports as $report)
            @php
                $signalPayload = $report->resolvedSignalPayload();
                $damageAttachment = $report->resolvedDamageAttachment();
                $hasDamage = $report->damage_declared_at !== null || filled($report->damage_summary) || filled($report->damage_amount_estimated);
                $slaBreached = filled($report->target_sla_hours) && $report->created_at !== null
                    ? (($report->created_at->diffInMinutes($report->resolved_at ?? now()) / 60) >= (float) $report->target_sla_hours)
                    : false;
                $isEligibleForReparationCase = $slaBreached || $hasDamage;
                $reparationCaseStatusLabel = $report->reparationCase
                    ? 'Dossier ouvert'
                    : ($isEligibleForReparationCase ? 'Dossier a ouvrir' : 'Non eligible');
                $eligibilityReasonLabel = $slaBreached && $hasDamage
                    ? 'SLA depasse et dommage declare'
                    : ($slaBreached ? 'SLA depasse' : ($hasDamage ? 'Dommage declare' : 'Aucune condition remplie'));
            @endphp

            <div class="modal fade" id="reportDetailsModal-{{ $report->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title fw-bold mb-0">{{ $report->reference }}</h5>
                                <div class="small text-secondary">{{ $report->signal_label ?: $report->signal_code }} · {{ $report->application?->name ?: 'Application non definie' }}</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-4">
                                <div class="col-lg-4">
                                    <div class="border rounded-4 p-3 h-100">
                                        <div class="small text-secondary fw-semibold mb-2">Synthese</div>
                                        <div class="mb-2"><strong>Statut signalement :</strong> {{ $report->status }}</div>
                                        <div class="mb-2"><strong>Statut dossier :</strong> {{ $reparationCaseStatusLabel }}</div>
                                        <div class="mb-2"><strong>Decision d ouverture :</strong> {{ $eligibilityReasonLabel }}</div>
                                        <div class="mb-2"><strong>Organisation :</strong> {{ $report->organization?->name ?: 'Non definie' }}</div>
                                        <div class="mb-2"><strong>Commune :</strong> {{ $report->commune?->name ?: '-' }}</div>
                                        <div class="mb-2"><strong>Cree le :</strong> {{ $report->created_at?->format('d/m/Y H:i') ?: '-' }}</div>
                                        <div class="mb-0"><strong>SLA :</strong>
                                            @if ($slaBreached)
                                                Depasse
                                            @elseif (filled($report->target_sla_hours))
                                                Dans le delai
                                            @else
                                                Non configure
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-8">
                                    <div class="border rounded-4 p-3 mb-3">
                                        <div class="small text-secondary fw-semibold mb-2">Description</div>
                                        <div>{{ $report->description ?: 'Aucune description fournie.' }}</div>
                                    </div>
                                    @if (!empty($signalPayload))
                                        <div class="border rounded-4 p-3 mb-3">
                                            <div class="small text-secondary fw-semibold mb-2">Donnees complementaires</div>
                                            <div class="row g-3">
                                                @foreach ($signalPayload as $key => $value)
                                                    <div class="col-md-6">
                                                        <div class="border rounded-4 p-3 h-100">
                                                            <div class="small text-secondary mb-1">{{ $key }}</div>
                                                            @if (is_array($value) && filled($value['temporary_url'] ?? null))
                                                                @if (str_starts_with((string) ($value['mime_type'] ?? ''), 'image/'))
                                                                    <img src="{{ $value['temporary_url'] }}" alt="{{ $key }}" class="img-fluid rounded-4 border mb-2" style="max-height: 180px; width: 100%; object-fit: cover;">
                                                                @endif
                                                                <div class="fw-semibold">{{ $value['name'] ?? $key }}</div>
                                                            @else
                                                                <div class="fw-semibold">{{ $value }}</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fermer</button>
                        </div>
                    </div>
                </div>
            </div>

            @if ($report->damage_summary || !empty($damageAttachment))
                <div class="modal fade" id="reportDamageModal-{{ $report->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content border-0 shadow-lg">
                            <div class="modal-header">
                                <div>
                                    <h5 class="modal-title fw-bold mb-0">Detail dommage</h5>
                                    <div class="small text-secondary">{{ $report->reference }} · {{ $report->signal_label ?: $report->signal_code }}</div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-2"><strong>Resume :</strong> {{ $report->damage_summary ?: 'Non renseigne' }}</div>
                                <div class="mb-2"><strong>Montant estime :</strong> {{ $report->damage_amount_estimated !== null ? number_format((float) $report->damage_amount_estimated, 0, ',', ' ').' FCFA' : 'Non renseigne' }}</div>
                                <div class="mb-3"><strong>Commentaires :</strong> {{ $report->damage_notes ?: 'Aucun detail complementaire fourni.' }}</div>
                                @if (!empty($damageAttachment) && filled($damageAttachment['temporary_url'] ?? null))
                                    <div class="border rounded-4 p-3">
                                        <div class="fw-semibold small mb-2">{{ $damageAttachment['name'] ?? 'Justificatif dommage' }}</div>
                                        <div class="small text-secondary mb-2">{{ $damageAttachment['mime_type'] ?? 'Fichier' }}</div>
                                        @if (str_starts_with((string) ($damageAttachment['mime_type'] ?? ''), 'image/'))
                                            <img src="{{ $damageAttachment['temporary_url'] }}" alt="Justificatif dommage" class="img-fluid rounded-4 border" style="max-height: 260px; width: 100%; object-fit: contain; background: #f7f9fc;">
                                        @else
                                            <a href="{{ $damageAttachment['temporary_url'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-dark btn-sm">Ouvrir le justificatif</a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fermer</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if (! $report->reparationCase && $isEligibleForReparationCase)
                <div class="modal fade" id="openReparationCaseModal-{{ $report->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg">
                            <div class="modal-header">
                                <div>
                                    <h5 class="modal-title fw-bold mb-0">Ouvrir un dossier de reparation</h5>
                                    <div class="small text-secondary">{{ $report->reference }} · {{ $report->signal_label ?: $report->signal_code }}</div>
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
                                    </div>
                                    <div class="small text-secondary mb-3">
                                        Motif d eligibilite :
                                        @if ($slaBreached && $hasDamage)
                                            SLA depasse et dommage declare
                                        @elseif ($slaBreached)
                                            SLA depasse
                                        @else
                                            Dommage declare
                                        @endif
                                    </div>
                                    <label class="form-label">Notes d ouverture</label>
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
            @endif
        @endforeach

        <div class="mt-4">
            {{ $reports->links() }}
        </div>
    @endif
</section>
