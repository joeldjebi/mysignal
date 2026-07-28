@extends('super-admin.layouts.app')

@section('title', 'Rapports à la demande | Super Admin')
@section('page-title', 'Rapports à la demande')
@section('page-description', 'Composez un rapport personnalisé, prévisualisez-le, puis exportez-le au format souhaité.')

@section('content')
    <style>
        .reports-loader-overlay {
            position: fixed;
            inset: 0;
            z-index: 1090;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: rgba(7, 18, 28, .42);
            backdrop-filter: blur(10px);
        }

        .reports-loader-overlay.is-visible {
            display: flex;
        }

        .reports-loader-card {
            width: min(420px, 100%);
            border: 1px solid rgba(255, 255, 255, .2);
            border-radius: 18px;
            background: rgba(255, 255, 255, .96);
            box-shadow: 0 28px 80px rgba(7, 18, 28, .28);
            padding: 1.4rem;
            text-align: center;
        }

        .reports-loader-ring {
            width: 52px;
            height: 52px;
            margin: 0 auto 1rem;
            border-radius: 50%;
            border: 4px solid rgba(25, 75, 112, .14);
            border-top-color: #194b70;
            animation: reportsLoaderSpin .82s linear infinite;
        }

        .reports-loader-bar {
            height: 6px;
            overflow: hidden;
            border-radius: 999px;
            background: rgba(25, 75, 112, .1);
        }

        .reports-loader-bar span {
            display: block;
            width: 38%;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #194b70, #c49b48);
            animation: reportsLoaderBar 1.18s ease-in-out infinite;
        }

        @keyframes reportsLoaderSpin {
            to { transform: rotate(360deg); }
        }

        @keyframes reportsLoaderBar {
            0% { transform: translateX(-110%); }
            100% { transform: translateX(280%); }
        }
    </style>

    <div class="reports-loader-overlay" data-reports-loader aria-hidden="true">
        <div class="reports-loader-card" role="status" aria-live="polite">
            <div class="reports-loader-ring" aria-hidden="true"></div>
            <div class="fw-bold mb-1" data-reports-loader-title>Génération du rapport</div>
            <div class="text-secondary small mb-3" data-reports-loader-message>Veuillez patienter pendant la préparation des données.</div>
            <div class="reports-loader-bar" aria-hidden="true"><span></span></div>
        </div>
    </div>

    <form method="GET" action="{{ route('super-admin.reports-builder.index') }}" class="panel-card mb-4" data-report-loader-form data-loader-title="Prévisualisation du rapport" data-loader-message="Nous appliquons vos filtres et préparons l’aperçu.">
        <input type="hidden" name="preview" value="1">
        <input type="hidden" name="metrics_present" value="1">
        <div class="row g-3">
            <div class="col-lg-3">
                <label class="form-label small fw-semibold">Sujet du rapport</label>
                <select name="subject" class="form-select">
                    @foreach ($options['subjects'] as $value => $label)
                        <option value="{{ $value }}" @selected($filters['subject'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <label class="form-label small fw-semibold">Du</label>
                <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="form-control">
            </div>
            <div class="col-lg-2">
                <label class="form-label small fw-semibold">Au</label>
                <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="form-control">
            </div>
            <div class="col-lg-3">
                <label class="form-label small fw-semibold">Catégorie</label>
                <select name="application_id" class="form-select">
                    <option value="">Toutes</option>
                    @foreach ($options['applications'] as $application)
                        <option value="{{ $application->id }}" @selected((string) $filters['application_id'] === (string) $application->id)>{{ $application->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <label class="form-label small fw-semibold">Statut</label>
                <select name="status" class="form-select">
                    @foreach ($options['statuses'] as $value => $label)
                        <option value="{{ $value }}" @selected((string) $filters['status'] === (string) $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-4">
                <label class="form-label small fw-semibold">Institution</label>
                <select name="organization_id" class="form-select">
                    <option value="">Toutes</option>
                    @foreach ($options['organizations'] as $organization)
                        <option value="{{ $organization->id }}" @selected((string) $filters['organization_id'] === (string) $organization->id)>{{ $organization->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <label class="form-label small fw-semibold">Regrouper par</label>
                <select name="group_by" class="form-select">
                    @foreach ($options['groupings'] as $value => $label)
                        <option value="{{ $value }}" @selected($filters['group_by'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <label class="form-label small fw-semibold">Puis par</label>
                <select name="second_group_by" class="form-select">
                    @foreach ($options['groupings'] as $value => $label)
                        <option value="{{ $value }}" @selected($filters['second_group_by'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-4">
                <label class="form-label small fw-semibold">Indicateurs</label>
                <div class="d-flex flex-wrap gap-2">
                    @foreach ($options['metrics'] as $value => $label)
                        <label class="badge-soft">
                            <input type="checkbox" name="metrics[]" value="{{ $value }}" class="form-check-input me-1" @checked(in_array($value, $filters['metrics'], true))>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="col-12 d-flex flex-wrap align-items-center justify-content-end gap-3 pt-2">
                <button type="submit" class="btn btn-dark" data-loader-submit-label="Préparation...">Prévisualiser</button>
            </div>
        </div>
    </form>

    @if ($report)
        <section class="panel-card mb-4">
            <div class="d-flex flex-column flex-xl-row justify-content-between gap-3 mb-3">
                <div>
                    <div class="small text-secondary fw-semibold">Aperçu du rapport</div>
                    <h2 class="h4 mb-1">{{ $report['title'] }}</h2>
                    <div class="text-secondary small">
                        {{ $report['record_count'] }} élément(s) analysé(s), généré le {{ $report['generated_at']->format('d/m/Y H:i') }}.
                        @if ($report['limit_reached'])
                            Limite de lecture atteinte, affinez les filtres.
                        @endif
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2 mt-2">
                    @foreach ($report['filters'] as $filterLabel => $filterValue)
                        <span class="badge-soft">{{ $filterLabel }} : {{ $filterValue }}</span>
                    @endforeach
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @foreach (['csv' => 'CSV', 'xls' => 'Excel', 'pdf' => 'PDF', 'pptx' => 'PowerPoint'] as $format => $label)
                        <form method="POST" action="{{ route('super-admin.reports-builder.download') }}" data-report-loader-form data-loader-auto-hide="1" data-loader-title="Export {{ $label }}" data-loader-message="Le fichier est en cours de préparation.">
                            @csrf
                            <input type="hidden" name="metrics_present" value="1">
                            @foreach (collect($filters)->except(['preview']) as $key => $value)
                                @if (is_array($value))
                                    @foreach ($value as $item)
                                        <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                                    @endforeach
                                @else
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            <input type="hidden" name="format" value="{{ $format }}">
                            <button type="submit" class="btn btn-outline-dark btn-sm" data-loader-submit-label="Export...">{{ $label }}</button>
                        </form>
                    @endforeach
                </div>
            </div>

            <div class="row g-3 mb-4">
                @foreach ($report['summary'] as $label => $value)
                    <div class="col-sm-6 col-xl-3">
                        <div class="stat-card p-3 h-100">
                            <div class="small text-secondary">{{ $label }}</div>
                            <div class="fs-4 fw-bold">{{ is_numeric($value) ? number_format((float) $value, 0, ',', ' ') : $value }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="p-3 rounded-3 border bg-white mb-4">
                <div class="small text-secondary fw-semibold mb-2">Analyse du rapport</div>
                <div style="white-space: pre-line">{{ $analysis['text'] ?? 'Aucune synthèse disponible.' }}</div>
            </div>

            <div class="table-responsive">
                <table class="table table-modern align-middle">
                    <thead>
                        <tr>
                            @foreach (array_keys($report['rows'][0] ?? []) as $header)
                                <th>{{ $header }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($report['rows'] as $row)
                            <tr>
                                @foreach ($row as $value)
                                    <td>{{ is_numeric($value) ? number_format((float) $value, 0, ',', ' ') : $value }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td class="text-center text-secondary py-4">Aucune donnée pour cette combinaison.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif
@endsection

@section('scripts')
    <script>
        (() => {
            const overlay = document.querySelector('[data-reports-loader]');
            const title = document.querySelector('[data-reports-loader-title]');
            const message = document.querySelector('[data-reports-loader-message]');

            if (!overlay) {
                return;
            }

            const hideLoader = () => {
                overlay.classList.remove('is-visible');
                overlay.setAttribute('aria-hidden', 'true');
                document.querySelectorAll('[data-loader-disabled="1"]').forEach((button) => {
                    button.disabled = false;
                    delete button.dataset.loaderDisabled;
                });
                document.querySelectorAll('[data-original-text]').forEach((button) => {
                    button.textContent = button.dataset.originalText;
                    delete button.dataset.originalText;
                });
            };

            const showLoader = (form) => {
                title.textContent = form.dataset.loaderTitle || 'Génération du rapport';
                message.textContent = form.dataset.loaderMessage || 'Veuillez patienter pendant la préparation des données.';
                overlay.classList.add('is-visible');
                overlay.setAttribute('aria-hidden', 'false');

                form.querySelectorAll('button').forEach((button) => {
                    button.dataset.loaderDisabled = '1';
                    button.disabled = true;
                });

                const submitter = form.querySelector('button[type="submit"]');
                if (submitter) {
                    submitter.dataset.originalText = submitter.textContent.trim();
                    submitter.textContent = submitter.dataset.loaderSubmitLabel || 'Préparation...';
                }

                if (form.dataset.loaderAutoHide === '1') {
                    window.setTimeout(hideLoader, 3500);
                }
            };

            window.addEventListener('pageshow', () => {
                hideLoader();
            });

            document.querySelectorAll('[data-report-loader-form]').forEach((form) => {
                form.addEventListener('submit', () => {
                    showLoader(form);
                });
            });
        })();
    </script>
@endsection
