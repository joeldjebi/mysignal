<?php

namespace App\Services\Maintenance;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use InvalidArgumentException;

class DatabaseCleanupService
{
    public const CONFIRMATION = 'VIDER';

    private const TABLE_METADATA = [
        'incident_report_payment_sessions' => [
            'label' => 'Sessions de paiement signalement',
            'role' => 'Conserve les demandes de signalement en attente de paiement FineoPay.',
            'importance' => 'Indispensable pour transformer un paiement confirme en signalement.',
            'cleanup_impact' => 'Les paiements non finalises ne pourront plus creer leur signalement.',
        ],
        'payments' => [
            'label' => 'Paiements signalements',
            'role' => 'Historique des paiements rattaches aux signalements UP.',
            'importance' => 'Permet le suivi financier et la preuve de paiement.',
            'cleanup_impact' => 'L historique financier des signalements vides disparait.',
        ],
        'incident_report_notification_contexts' => [
            'label' => 'Contextes de notification signalement',
            'role' => 'Stocke les informations utilisees pour notifier autour des signalements.',
            'importance' => 'Aide a tracer et contextualiser les notifications envoyees.',
            'cleanup_impact' => 'Les anciens contextes de notification ne seront plus consultables.',
        ],
        'reparation_case_steps' => [
            'label' => 'Etapes dossiers reparation',
            'role' => 'Detail des actions et etapes sur les dossiers de reparation/contentieux.',
            'importance' => 'Conserve la chronologie operationnelle des dossiers.',
            'cleanup_impact' => 'Les etapes de dossiers vides seront supprimees.',
        ],
        'reparation_case_histories' => [
            'label' => 'Historique dossiers reparation',
            'role' => 'Historique des changements de statut et mouvements des dossiers.',
            'importance' => 'Utile pour l audit et le suivi juridique.',
            'cleanup_impact' => 'La tracabilite historique des dossiers vides disparait.',
        ],
        'reparation_cases' => [
            'label' => 'Dossiers de reparation',
            'role' => 'Dossiers contentieux ouverts a partir des signalements avec dommages.',
            'importance' => 'Supporte le workflow huissier, AODA et avocat.',
            'cleanup_impact' => 'Les dossiers de reparation de test seront supprimes.',
        ],
        'incident_reports' => [
            'label' => 'Signalements',
            'role' => 'Table centrale des signalements soumis par les UP.',
            'importance' => 'Alimente les dashboards, institutions, stats, SLA et resolution.',
            'cleanup_impact' => 'Tous les signalements de test vides seront perdus.',
        ],
        'rex_feedbacks' => [
            'label' => 'Retours experience UP',
            'role' => 'Avis et retours envoyes par les UP apres usage du service.',
            'importance' => 'Sert a mesurer la satisfaction et ameliorer le parcours.',
            'cleanup_impact' => 'Les retours de test seront supprimes.',
        ],
        'subscription_payments' => [
            'label' => 'Paiements abonnements',
            'role' => 'Historique des paiements lies aux abonnements UP.',
            'importance' => 'Supporte le suivi financier des abonnements.',
            'cleanup_impact' => 'Les traces de paiement d abonnement de test disparaitront.',
        ],
        'up_subscriptions' => [
            'label' => 'Abonnements UP',
            'role' => 'Abonnements actifs, expires ou en attente des usagers publics.',
            'importance' => 'Controle l acces aux avantages lies aux formules UP.',
            'cleanup_impact' => 'Les abonnements de test seront retires.',
        ],
        'up_discount_cards' => [
            'label' => 'Cartes reduction UP',
            'role' => 'Cartes de reduction generees pour les UP eligibles.',
            'importance' => 'Permet la verification chez les partenaires.',
            'cleanup_impact' => 'Les cartes de test ne seront plus valides.',
        ],
        'partner_discount_transactions' => [
            'label' => 'Transactions reductions partenaire',
            'role' => 'Historique des reductions appliquees par les partenaires.',
            'importance' => 'Trace les avantages consommes par les UP.',
            'cleanup_impact' => 'Les reductions appliquees en test seront supprimees.',
        ],
        'user_notifications' => [
            'label' => 'Notifications utilisateurs',
            'role' => 'Notifications envoyees aux UP et utilisateurs concernes.',
            'importance' => 'Alimente l historique de notification dans les applications.',
            'cleanup_impact' => 'Les anciennes notifications de test ne seront plus visibles.',
        ],
        'device_tokens' => [
            'label' => 'Tokens appareils',
            'role' => 'Identifiants push des appareils mobiles/web.',
            'importance' => 'Necessaire pour envoyer des notifications push.',
            'cleanup_impact' => 'Les appareils devront se reenregistrer pour recevoir des push.',
        ],
        'household_invitations' => [
            'label' => 'Invitations Gbonhi',
            'role' => 'Invitations envoyees pour rejoindre un Gbonhi.',
            'importance' => 'Gere les invitations en attente et leur acceptation.',
            'cleanup_impact' => 'Les invitations de test encore ouvertes seront annulees.',
        ],
        'household_members' => [
            'label' => 'Membres Gbonhi',
            'role' => 'Lien entre un Gbonhi et ses membres UP.',
            'importance' => 'Definit qui appartient a quel Gbonhi.',
            'cleanup_impact' => 'Les rattachements membres de test seront supprimes.',
        ],
        'households' => [
            'label' => 'Gbonhi',
            'role' => 'Groupes/foyers crees par les UP.',
            'importance' => 'Supporte la gestion collective des identifiants et membres.',
            'cleanup_impact' => 'Les Gbonhi de test seront supprimes.',
        ],
        'meter_assignments' => [
            'label' => 'Affectations identifiants',
            'role' => 'Rattache les identifiants/compteurs aux UP.',
            'importance' => 'Permet de savoir quel UP peut signaler sur quel identifiant.',
            'cleanup_impact' => 'Les rattachements UP-identifiants de test disparaitront.',
        ],
        'meters' => [
            'label' => 'Identifiants / compteurs',
            'role' => 'Identifiants techniques utilises pour les signalements.',
            'importance' => 'Porte les informations de localisation reutilisees lors du signalement.',
            'cleanup_impact' => 'Les identifiants de test seront supprimes.',
        ],
        'public_user_otps' => [
            'label' => 'OTP UP',
            'role' => 'Codes temporaires de connexion ou validation des UP.',
            'importance' => 'Sert a l authentification et verification ponctuelle.',
            'cleanup_impact' => 'Les codes de test encore actifs deviendront invalides.',
        ],
        'public_user_phone_verifications' => [
            'label' => 'Verifications telephone UP',
            'role' => 'Historique des validations de numeros de telephone UP.',
            'importance' => 'Trace la verification des comptes publics.',
            'cleanup_impact' => 'Les validations de test seront supprimees.',
        ],
        'public_users' => [
            'label' => 'Usagers publics',
            'role' => 'Comptes UP/UPE crees dans la plateforme.',
            'importance' => 'Base des parcours publics: signalements, abonnements, Gbonhi, notifications.',
            'cleanup_impact' => 'Les comptes publics de test et leurs donnees rattachees seront supprimes.',
        ],
        'contact_submissions' => [
            'label' => 'Demandes de contact',
            'role' => 'Messages envoyes depuis la landing page.',
            'importance' => 'Permet au SA de suivre les prospects et demandes entrantes.',
            'cleanup_impact' => 'Les demandes de contact de test seront supprimees.',
        ],
    ];

    private const PROFILES = [
        'launch_operational' => [
            'label' => 'Donnees operationnelles avant production',
            'description' => 'Vide les signalements, paiements, dossiers, notifications, retours UP, abonnements UP, cartes de reduction, compteurs, foyers et comptes UP de test. Les referentiels, organisations, roles, permissions et utilisateurs internes restent conserves.',
            'tables' => [
                'incident_report_payment_sessions',
                'payments',
                'incident_report_notification_contexts',
                'reparation_case_steps',
                'reparation_case_histories',
                'reparation_cases',
                'incident_reports',
                'rex_feedbacks',
                'subscription_payments',
                'up_subscriptions',
                'up_discount_cards',
                'partner_discount_transactions',
                'user_notifications',
                'device_tokens',
                'household_invitations',
                'household_members',
                'households',
                'meter_assignments',
                'meters',
                'public_user_otps',
                'public_user_phone_verifications',
                'public_users',
            ],
        ],
        'reports_payments' => [
            'label' => 'Signalements et paiements',
            'description' => 'Vide uniquement le cycle des signalements: sessions FineoPay, paiements, signalements, dossiers de reparation rattaches, notifications et REX associes.',
            'tables' => [
                'incident_report_payment_sessions',
                'payments',
                'incident_report_notification_contexts',
                'reparation_case_steps',
                'reparation_case_histories',
                'reparation_cases',
                'incident_reports',
                'rex_feedbacks',
                'user_notifications',
            ],
        ],
        'public_users' => [
            'label' => 'Comptes UP de test',
            'description' => 'Vide les comptes usagers publics et les donnees qui leur sont rattachees. Les types d UP et la tarification restent conserves.',
            'tables' => [
                'incident_report_payment_sessions',
                'payments',
                'incident_report_notification_contexts',
                'reparation_case_steps',
                'reparation_case_histories',
                'reparation_cases',
                'incident_reports',
                'rex_feedbacks',
                'subscription_payments',
                'up_subscriptions',
                'up_discount_cards',
                'user_notifications',
                'device_tokens',
                'household_invitations',
                'household_members',
                'households',
                'meter_assignments',
                'meters',
                'public_user_otps',
                'public_user_phone_verifications',
                'public_users',
            ],
        ],
        'landing_contacts' => [
            'label' => 'Contacts landing page',
            'description' => 'Vide les demandes de contact recues via la landing page.',
            'tables' => [
                'contact_submissions',
            ],
        ],
    ];

    public function profiles(): array
    {
        return collect(self::PROFILES)
            ->mapWithKeys(fn (array $profile, string $code): array => [
                $code => [
                    ...$profile,
                    'code' => $code,
                    'tables' => $this->existingTables($profile['tables']),
                ],
            ])
            ->all();
    }

    public function tables(): array
    {
        $tables = collect(self::PROFILES)
            ->flatMap(fn (array $profile): array => $profile['tables'])
            ->unique()
            ->values()
            ->all();

        return collect($this->existingTables($tables))
            ->mapWithKeys(fn (string $table): array => [
                $table => [
                    'name' => $table,
                    ...$this->metadataForTable($table),
                    'rows_count' => (int) DB::table($table)->count(),
                ],
            ])
            ->all();
    }

    public function profile(string $code): array
    {
        $profiles = $this->profiles();

        if (! array_key_exists($code, $profiles)) {
            throw new InvalidArgumentException('Profil de nettoyage inconnu.');
        }

        return $profiles[$code];
    }

    public function countsForProfile(string $code): array
    {
        $profile = $this->profile($code);

        return $this->counts($profile['tables']);
    }

    public function cleanup(string $code): array
    {
        $profile = $this->profile($code);
        $tables = $profile['tables'];
        $before = $this->counts($tables);

        if ($tables === []) {
            return [
                'profile' => $profile,
                'before' => [],
                'after' => [],
                'deleted_rows' => 0,
            ];
        }

        DB::transaction(function () use ($tables): void {
            $this->truncateTables($tables);
        });

        $after = $this->counts($tables);

        return [
            'profile' => $profile,
            'before' => $before,
            'after' => $after,
            'deleted_rows' => array_sum($before),
        ];
    }

    public function cleanupTable(string $table, bool $includeDependents = false): array
    {
        $tables = $this->tables();

        if (! array_key_exists($table, $tables)) {
            throw new InvalidArgumentException('Cette table ne peut pas etre videe depuis la maintenance SA.');
        }

        $blockingDependentTables = $this->dependentTablesFor($table);

        if ($blockingDependentTables !== [] && ! $includeDependents) {
            throw new InvalidArgumentException('Tables dependantes non vides: '.implode(', ', $blockingDependentTables));
        }

        $dependentTablesToInclude = collect($this->allDependentTablesFor($table))
            ->when(! $includeDependents, fn ($collection) => $collection->reject(fn (string $dependentTable): bool => DB::table($dependentTable)->count() > 0))
            ->values()
            ->all();
        $tablesToTruncate = collect([$table, ...$dependentTablesToInclude])->unique()->values()->all();
        $before = $this->counts($tablesToTruncate);

        DB::transaction(function () use ($table, $dependentTablesToInclude): void {
            $this->truncateSingleTable($table, $dependentTablesToInclude);
        });

        $after = $this->counts($tablesToTruncate);

        return [
            'table' => $table,
            'tables' => $tablesToTruncate,
            'dependent_tables' => $dependentTablesToInclude,
            'before' => $before,
            'after' => $after,
            'deleted_rows' => array_sum($before),
        ];
    }

    public function dependentTablesFor(string $table): array
    {
        return $this->tablesWithRows($this->allDependentTablesFor($table));
    }

    public function allDependentTablesFor(string $table): array
    {
        $allowedTables = array_keys($this->tables());
        $visited = [];
        $queue = [$table];

        while ($queue !== []) {
            $currentTable = array_shift($queue);

            foreach ($this->directDependentTablesFor($currentTable) as $dependentTable) {
                if (! in_array($dependentTable, $allowedTables, true) || in_array($dependentTable, $visited, true)) {
                    continue;
                }

                $visited[] = $dependentTable;
                $queue[] = $dependentTable;
            }
        }

        sort($visited);

        return $visited;
    }

    private function directDependentTablesFor(string $table): array
    {
        if (! Schema::hasTable($table)) {
            return [];
        }

        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            return DB::table('pg_constraint as c')
                ->join('pg_class as child', 'child.oid', '=', 'c.conrelid')
                ->join('pg_class as parent', 'parent.oid', '=', 'c.confrelid')
                ->join('pg_namespace as child_ns', 'child_ns.oid', '=', 'child.relnamespace')
                ->join('pg_namespace as parent_ns', 'parent_ns.oid', '=', 'parent.relnamespace')
                ->where('c.contype', 'f')
                ->where('parent.relname', $table)
                ->where('parent_ns.nspname', DB::raw('current_schema()'))
                ->where('child_ns.nspname', DB::raw('current_schema()'))
                ->where('child.relname', '!=', $table)
                ->distinct()
                ->orderBy('child.relname')
                ->pluck('child.relname')
                ->all();
        }

        if ($driver === 'mysql') {
            return DB::table('information_schema.KEY_COLUMN_USAGE')
                ->where('REFERENCED_TABLE_SCHEMA', DB::raw('DATABASE()'))
                ->where('REFERENCED_TABLE_NAME', $table)
                ->where('TABLE_NAME', '!=', $table)
                ->distinct()
                ->orderBy('TABLE_NAME')
                ->pluck('TABLE_NAME')
                ->all();
        }

        return [];
    }

    private function counts(array $tables): array
    {
        $counts = [];

        foreach ($this->existingTables($tables) as $table) {
            $counts[$table] = (int) DB::table($table)->count();
        }

        return $counts;
    }

    private function tablesWithRows(array $tables): array
    {
        return collect($this->existingTables($tables))
            ->filter(fn (string $table): bool => DB::table($table)->count() > 0)
            ->values()
            ->all();
    }

    private function existingTables(array $tables): array
    {
        return collect($tables)
            ->unique()
            ->filter(fn (string $table): bool => Schema::hasTable($table))
            ->values()
            ->all();
    }

    private function metadataForTable(string $table): array
    {
        return self::TABLE_METADATA[$table] ?? [
            'label' => Str::of($table)->replace('_', ' ')->title()->toString(),
            'role' => 'Donnees operationnelles autorisees au nettoyage.',
            'importance' => 'Table rattachee aux donnees de test purgeables.',
            'cleanup_impact' => 'Les donnees de test de cette table seront supprimees.',
        ];
    }

    private function truncateTables(array $tables): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            $quotedTables = collect($tables)
                ->map(fn (string $table): string => $this->quoteIdentifier($table, '"'))
                ->implode(', ');

            DB::statement('TRUNCATE TABLE '.$quotedTables.' RESTART IDENTITY CASCADE');

            return;
        }

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            try {
                foreach ($tables as $table) {
                    DB::statement('TRUNCATE TABLE '.$this->quoteIdentifier($table, '`'));
                }
            } finally {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }

            return;
        }

        foreach ($tables as $table) {
            DB::table($table)->delete();

            if ($driver === 'sqlite') {
                DB::table('sqlite_sequence')->where('name', $table)->delete();
            }
        }
    }

    private function truncateSingleTable(string $table, array $emptyDependentTables = []): void
    {
        $driver = DB::getDriverName();
        $tables = collect([$table, ...$emptyDependentTables])->unique()->values()->all();

        if ($driver === 'pgsql') {
            $quotedTables = collect($tables)
                ->map(fn (string $tableName): string => $this->quoteIdentifier($tableName, '"'))
                ->implode(', ');

            DB::statement('TRUNCATE TABLE '.$quotedTables.' RESTART IDENTITY');

            return;
        }

        if ($driver === 'mysql') {
            foreach ($tables as $tableName) {
                DB::statement('TRUNCATE TABLE '.$this->quoteIdentifier($tableName, '`'));
            }

            return;
        }

        foreach ($tables as $tableName) {
            DB::table($tableName)->delete();

            if ($driver === 'sqlite') {
                DB::table('sqlite_sequence')->where('name', $tableName)->delete();
            }
        }
    }

    private function quoteIdentifier(string $identifier, string $quote): string
    {
        $cleanIdentifier = Str::of($identifier)->replace($quote, $quote.$quote)->toString();

        return $quote.$cleanIdentifier.$quote;
    }
}
