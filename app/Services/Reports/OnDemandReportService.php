<?php

namespace App\Services\Reports;

use App\Models\ActivityLog;
use App\Models\Application;
use App\Models\IncidentReport;
use App\Models\Organization;
use App\Models\PartnerDiscountTransaction;
use App\Models\Payment;
use App\Models\PrivilegeCard;
use App\Models\PrivilegeCardPaymentSession;
use App\Models\PublicUser;
use App\Models\ReparationCase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class OnDemandReportService
{
    public function options(): array
    {
        return [
            'subjects' => [
                'global' => 'Rapport global',
                'reports' => 'Signalements publics',
                'payments' => 'Paiements',
                'privilege_cards' => 'Cartes privilèges émises',
                'privilege_purchases' => 'Achats de cartes privilèges',
                'privilege_scans' => 'Scans de cartes privilèges',
                'public_users' => 'Usagers publics',
                'reparation_cases' => 'Dossiers contentieux',
                'activity_logs' => 'Journaux d’activité',
            ],
            'groupings' => [
                'none' => 'Aucun regroupement',
                'day' => 'Jour',
                'month' => 'Mois',
                'application' => 'Catégorie',
                'organization' => 'Institution',
                'status' => 'Statut',
                'payment_status' => 'Statut de paiement',
                'card_type' => 'Type de carte',
                'commune' => 'Commune',
                'partner' => 'Partenaire',
            ],
            'formats' => [
                'html' => 'Aperçu',
                'csv' => 'CSV',
                'xls' => 'Excel',
                'pdf' => 'PDF',
                'pptx' => 'PowerPoint',
            ],
            'metrics' => [
                'count' => 'Nombre total',
                'amount' => 'Montants',
                'paid' => 'Éléments payés',
                'resolved' => 'Signalements résolus',
                'damages' => 'Dommages déclarés',
                'active' => 'Éléments actifs',
                'expired' => 'Éléments expirés',
            ],
            'statuses' => [
                '' => 'Tous',
                'active' => 'Actif',
                'inactive' => 'Inactif',
                'pending' => 'En attente',
                'submitted' => 'Soumis',
                'in_progress' => 'En cours',
                'paid' => 'Payé',
                'failed' => 'Échoué',
                'resolved' => 'Résolu',
                'rejected' => 'Rejeté',
                'closed' => 'Clôturé',
                'expired' => 'Expiré',
            ],
            'applications' => Application::query()->orderBy('name')->get(['id', 'name']),
            'organizations' => Organization::query()->orderBy('name')->get(['id', 'name']),
        ];
    }

    public function build(array $filters): array
    {
        $subject = $filters['subject'] ?? 'global';
        $metrics = collect($filters['metrics'] ?? ['count'])->filter()->values()->all();
        $groups = collect([$filters['group_by'] ?? 'none', $filters['second_group_by'] ?? 'none'])
            ->filter(fn ($group) => filled($group) && $group !== 'none')
            ->unique()
            ->take(2)
            ->values()
            ->all();

        if ($subject === 'global') {
            return $this->buildGlobal($filters, $metrics);
        }

        $records = $this->records($subject, $filters);
        $rows = $this->groupRows($records, $groups, $metrics);

        return [
            'title' => $this->subjectLabel($subject),
            'subject' => $subject,
            'filters' => $this->filterSummary($filters),
            'metrics' => $metrics,
            'groups' => $groups,
            'summary' => $this->metricsFor($records, $metrics),
            'rows' => $rows,
            'generated_at' => now(),
            'record_count' => $records->count(),
            'limit_reached' => $records->count() >= $this->limit(),
        ];
    }

    private function buildGlobal(array $filters, array $metrics): array
    {
        $subjects = ['reports', 'payments', 'privilege_cards', 'privilege_purchases', 'privilege_scans', 'public_users', 'reparation_cases'];
        $rows = collect($subjects)->map(function (string $subject) use ($filters, $metrics): array {
            $records = $this->records($subject, $filters);
            $summary = $this->metricsFor($records, $metrics);

            return [
                'Groupe' => $this->subjectLabel($subject),
                'Nombre' => $summary['Nombre'] ?? $records->count(),
                'Montant' => $summary['Montant'] ?? 0,
                'Payés' => $summary['Payés'] ?? 0,
                'Résolus' => $summary['Résolus'] ?? 0,
                'Actifs' => $summary['Actifs'] ?? 0,
            ];
        })->values()->all();

        return [
            'title' => 'Rapport global',
            'subject' => 'global',
            'filters' => $this->filterSummary($filters),
            'metrics' => $metrics,
            'groups' => ['module'],
            'summary' => [
                'Modules analysés' => count($subjects),
                'Signalements' => $rows[0]['Nombre'] ?? 0,
                'Paiements' => $rows[1]['Nombre'] ?? 0,
                'Cartes privilèges' => $rows[2]['Nombre'] ?? 0,
            ],
            'rows' => $rows,
            'generated_at' => now(),
            'record_count' => array_sum(array_map(fn ($row) => (int) ($row['Nombre'] ?? 0), $rows)),
            'limit_reached' => false,
        ];
    }

    private function records(string $subject, array $filters): Collection
    {
        $query = match ($subject) {
            'reports' => IncidentReport::query()->with(['application', 'organization', 'commune']),
            'payments' => Payment::query()->with(['incidentReport.application', 'incidentReport.organization', 'publicUser']),
            'privilege_cards' => PrivilegeCard::query()->with(['type', 'publicUser']),
            'privilege_purchases' => PrivilegeCardPaymentSession::query()->with(['type', 'card', 'publicUser']),
            'privilege_scans' => PartnerDiscountTransaction::query()->with(['privilegeCard.type', 'organization', 'publicUser']),
            'public_users' => PublicUser::query()->with(['publicUserType']),
            'reparation_cases' => ReparationCase::query()->with(['application', 'organization', 'publicUser', 'incidentReport']),
            'activity_logs' => ActivityLog::query(),
            default => IncidentReport::query()->with(['application', 'organization', 'commune']),
        };

        $this->applyDateFilter($query, $this->dateField($subject), $filters);
        $this->applySharedFilters($query, $subject, $filters);

        return $query->latest($this->dateField($subject))->limit($this->limit())->get();
    }

    private function applyDateFilter(Builder $query, string $field, array $filters): void
    {
        if (filled($filters['date_from'] ?? null)) {
            $query->whereDate($field, '>=', Carbon::parse($filters['date_from'])->toDateString());
        }

        if (filled($filters['date_to'] ?? null)) {
            $query->whereDate($field, '<=', Carbon::parse($filters['date_to'])->toDateString());
        }
    }

    private function applySharedFilters(Builder $query, string $subject, array $filters): void
    {
        if (filled($filters['status'] ?? null) && in_array($subject, ['reports', 'payments', 'privilege_cards', 'privilege_purchases', 'privilege_scans', 'public_users', 'reparation_cases'], true)) {
            $status = (string) $filters['status'];

            if ($subject === 'reports' && in_array($status, ['paid', 'failed', 'pending'], true)) {
                $query->where('payment_status', $status);
            } else {
                $query->where('status', $status);
            }
        }

        if (filled($filters['application_id'] ?? null) && in_array($subject, ['reports', 'payments', 'reparation_cases'], true)) {
            $applicationId = (int) $filters['application_id'];
            in_array($subject, ['payments'], true)
                ? $query->whereHas('incidentReport', fn ($subQuery) => $subQuery->where('application_id', $applicationId))
                : $query->where('application_id', $applicationId);
        }

        if (filled($filters['organization_id'] ?? null) && in_array($subject, ['reports', 'payments', 'privilege_scans', 'reparation_cases'], true)) {
            $organizationId = (int) $filters['organization_id'];
            in_array($subject, ['payments'], true)
                ? $query->whereHas('incidentReport', fn ($subQuery) => $subQuery->where('organization_id', $organizationId))
                : $query->where('organization_id', $organizationId);
        }
    }

    public function localAnalysis(array $report): array
    {
        $rows = collect($report['rows'] ?? []);
        $summary = $report['summary'] ?? [];
        $recordCount = (int) ($report['record_count'] ?? ($summary['Nombre'] ?? 0));
        $amount = (float) ($summary['Montant'] ?? $rows->sum(fn ($row) => (float) ($row['Montant'] ?? 0)));
        $paid = (int) ($summary['Payés'] ?? $rows->sum(fn ($row) => (int) ($row['Payés'] ?? 0)));
        $resolved = (int) ($summary['Résolus'] ?? $rows->sum(fn ($row) => (int) ($row['Résolus'] ?? 0)));
        $active = (int) ($summary['Actifs'] ?? $rows->sum(fn ($row) => (int) ($row['Actifs'] ?? 0)));
        $topRow = $rows
            ->filter(fn ($row) => isset($row['Nombre']))
            ->sortByDesc(fn ($row) => (int) ($row['Nombre'] ?? 0))
            ->first();
        $topGroup = $topRow['Groupe'] ?? null;
        $paidRate = $recordCount > 0 ? round(($paid / $recordCount) * 100) : 0;
        $resolvedRate = $recordCount > 0 ? round(($resolved / $recordCount) * 100) : 0;

        $lines = [];
        $lines[] = 'Bilan : '.$recordCount.' élément(s) analysé(s) sur la période sélectionnée.';

        if ($amount > 0) {
            $lines[] = 'Commentaire : le montant total observé est de '.number_format($amount, 0, ',', ' ').' FCFA.';
        } elseif ($recordCount > 0) {
            $lines[] = 'Commentaire : aucun montant significatif n’apparaît dans cette combinaison de filtres.';
        } else {
            $lines[] = 'Commentaire : aucune donnée ne correspond à la combinaison de filtres sélectionnée.';
        }

        if ($paid > 0 || array_key_exists('Payés', $summary) || $rows->contains(fn ($row) => array_key_exists('Payés', $row))) {
            $lines[] = 'Point paiement : '.$paid.' élément(s) payé(s), soit environ '.$paidRate.' % du volume analysé.';
        }

        if ($resolved > 0 || array_key_exists('Résolus', $summary) || $rows->contains(fn ($row) => array_key_exists('Résolus', $row))) {
            $lines[] = 'Point traitement : '.$resolved.' élément(s) résolu(s), soit environ '.$resolvedRate.' % du volume analysé.';
        }

        if ($active > 0) {
            $lines[] = 'Point activité : '.$active.' élément(s) actif(s) sont encore à suivre.';
        }

        if ($topGroup) {
            $lines[] = 'Observation : le groupe le plus représenté est « '.$topGroup.' ».';
            $lines[] = 'Recommandation : prioriser une vérification de ce groupe et comparer son évolution avec la période précédente.';
        } elseif ($recordCount === 0) {
            $lines[] = 'Observation : aucun élément ne correspond aux filtres choisis.';
            $lines[] = 'Recommandation : élargir la période ou retirer un filtre pour confirmer qu’il n’y a pas de données attendues.';
        } else {
            $lines[] = 'Observation : les données sont disponibles, mais aucun groupe dominant ne ressort clairement.';
            $lines[] = 'Recommandation : ajouter un regroupement pour mieux identifier les zones, institutions ou statuts à traiter.';
        }

        if ($report['limit_reached'] ?? false) {
            $lines[] = 'Attention : la limite de lecture est atteinte. Il est préférable d’affiner les filtres avant export.';
        }

        return [
            'enabled' => false,
            'text' => implode("\n", $lines),
            'notice' => 'Analyse locale générée sans consommation de crédits OpenAI.',
        ];
    }

    private function groupRows(Collection $records, array $groups, array $metrics): array
    {
        if ($groups === []) {
            return [$this->metricsFor($records, $metrics) + ['Groupe' => 'Tous les éléments']];
        }

        return $records
            ->groupBy(fn ($record) => implode(' | ', array_map(fn ($group) => $this->groupValue($record, $group), $groups)))
            ->map(function (Collection $items, string $groupLabel) use ($metrics): array {
                return ['Groupe' => $groupLabel] + $this->metricsFor($items, $metrics);
            })
            ->sortBy('Groupe')
            ->values()
            ->all();
    }

    private function metricsFor(Collection $records, array $metrics): array
    {
        $data = ['Nombre' => $records->count()];

        if (in_array('amount', $metrics, true)) {
            $data['Montant'] = $records->sum(fn ($record) => (float) ($record->amount ?? $record->final_amount ?? $record->discount_amount ?? 0));
        }

        if (in_array('paid', $metrics, true)) {
            $data['Payés'] = $records->filter(fn ($record) => ($record->status ?? $record->payment_status ?? null) === 'paid')->count();
        }

        if (in_array('resolved', $metrics, true)) {
            $data['Résolus'] = $records->where('status', 'resolved')->count();
        }

        if (in_array('damages', $metrics, true)) {
            $data['Dommages'] = $records->filter(fn ($record) => filled($record->damage_declared_at ?? null))->count();
        }

        if (in_array('active', $metrics, true)) {
            $data['Actifs'] = $records->where('status', 'active')->count();
        }

        if (in_array('expired', $metrics, true)) {
            $data['Expirés'] = $records->filter(fn ($record) => filled($record->expires_at ?? null) && $record->expires_at->isPast())->count();
        }

        return $data;
    }

    private function groupValue(object $record, string $group): string
    {
        return match ($group) {
            'day' => optional($record->created_at)->format('d/m/Y') ?: '-',
            'month' => optional($record->created_at)->format('m/Y') ?: '-',
            'application' => $record->application?->name ?? $record->incidentReport?->application?->name ?? '-',
            'organization' => $record->organization?->name ?? $record->incidentReport?->organization?->name ?? '-',
            'status' => $this->statusLabel((string) ($record->status ?? '-')),
            'payment_status' => $this->statusLabel((string) ($record->payment_status ?? $record->status ?? '-')),
            'card_type' => $record->type?->name ?? $record->privilegeCard?->type?->name ?? '-',
            'commune' => $record->commune?->name ?? $record->incidentReport?->commune?->name ?? '-',
            'partner' => $record->organization?->name ?? '-',
            default => '-',
        };
    }

    private function dateField(string $subject): string
    {
        return match ($subject) {
            'payments' => 'initiated_at',
            'privilege_cards' => 'issued_at',
            'privilege_purchases' => 'initiated_at',
            'privilege_scans' => 'applied_at',
            'reparation_cases' => 'opened_at',
            default => 'created_at',
        };
    }

    private function filterSummary(array $filters): array
    {
        return [
            'Période du' => $filters['date_from'] ?? '-',
            'Période au' => $filters['date_to'] ?? '-',
            'Catégorie' => filled($filters['application_id'] ?? null)
                ? (Application::query()->find($filters['application_id'])?->name ?? 'Catégorie sélectionnée')
                : 'Toutes',
            'Institution' => filled($filters['organization_id'] ?? null)
                ? (Organization::query()->find($filters['organization_id'])?->name ?? 'Institution sélectionnée')
                : 'Toutes',
            'Statut' => $this->statusLabel((string) ($filters['status'] ?? '')) ?: 'Tous',
            'Regroupement' => $this->options()['groupings'][$filters['group_by'] ?? 'none'] ?? 'Aucun regroupement',
            'Second regroupement' => $this->options()['groupings'][$filters['second_group_by'] ?? 'none'] ?? 'Aucun regroupement',
        ];
    }

    private function subjectLabel(string $subject): string
    {
        return $this->options()['subjects'][$subject] ?? 'Rapport';
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'active' => 'Actif',
            'inactive' => 'Inactif',
            'pending' => 'En attente',
            'paid' => 'Payé',
            'failed' => 'Échoué',
            'submitted' => 'Soumis',
            'in_progress' => 'En cours',
            'resolved' => 'Résolu',
            'rejected' => 'Rejeté',
            'closed' => 'Clôturé',
            'expired' => 'Expiré',
            default => filled($status) ? str($status)->replace(['_', '-'], ' ')->title()->toString() : 'Tous',
        };
    }

    private function limit(): int
    {
        return 10000;
    }
}
