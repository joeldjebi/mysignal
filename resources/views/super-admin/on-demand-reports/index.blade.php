@extends('super-admin.layouts.app')

@section('title', 'Rapports à la demande | Super Admin')
@section('page-title', 'Rapports à la demande')
@section('page-description', 'Composez un rapport personnalisé, prévisualisez-le, puis exportez-le au format souhaité.')

@section('content')
    <form method="GET" action="{{ route('super-admin.reports-builder.index') }}" class="panel-card mb-4">
        <input type="hidden" name="preview" value="1">
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
                <input type="text" name="status" value="{{ $filters['status'] }}" class="form-control" placeholder="paid, active...">
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
            <div class="col-12 d-flex flex-wrap align-items-center justify-content-between gap-3 pt-2">
                <label class="form-check">
                    <input type="checkbox" name="with_ai" value="1" class="form-check-input" @checked($filters['with_ai'])>
                    <span class="form-check-label">Ajouter une synthèse OpenAI</span>
                </label>
                <button type="submit" class="btn btn-dark">Prévisualiser</button>
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
                <div class="d-flex flex-wrap gap-2">
                    @foreach (['csv' => 'CSV', 'xls' => 'Excel', 'pdf' => 'PDF', 'pptx' => 'PowerPoint'] as $format => $label)
                        <form method="POST" action="{{ route('super-admin.reports-builder.download') }}">
                            @csrf
                            @foreach ($filters as $key => $value)
                                @if (is_array($value))
                                    @foreach ($value as $item)
                                        <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                                    @endforeach
                                @else
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            <input type="hidden" name="format" value="{{ $format }}">
                            <button type="submit" class="btn btn-outline-dark btn-sm">{{ $label }}</button>
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
                <div class="small text-secondary fw-semibold mb-2">Synthèse</div>
                <div style="white-space: pre-line">{{ $analysis['text'] ?? 'Aucune synthèse disponible.' }}</div>
                @if (! empty($analysis['notice']))
                    <div class="small text-warning mt-2">{{ $analysis['notice'] }}</div>
                @endif
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
