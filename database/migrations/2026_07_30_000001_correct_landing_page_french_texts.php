<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $replacements = [
            'Parametres' => 'Paramètres',
            'Libelle' => 'Libellé',
            'Icone' => 'Icône',
            'Videos' => 'Vidéos',
            'videos' => 'vidéos',
            'categorie' => 'catégorie',
            'Categorie' => 'Catégorie',
            'Presentation' => 'Présentation',
            'presentation' => 'présentation',
            'Questions frequentes' => 'Questions fréquentes',
            'reponses' => 'réponses',
            'Reponse' => 'Réponse',
            'Telephone' => 'Téléphone',
            'Conditions generales d utilisation' => 'Conditions générales d’utilisation',
            'conditions generales d utilisation' => 'conditions générales d’utilisation',
            'Cadre d utilisation' => 'Cadre d’utilisation',
            'Politique de confidentialite' => 'Politique de confidentialité',
            'politique de confidentialite' => 'politique de confidentialité',
            'donnees' => 'données',
            'Unites Partenaires' => 'Unités Partenaires',
            'unites partenaires' => 'unités partenaires',
            'difficultes liees' => 'difficultés liées',
            'resolution des difficultes' => 'résolution des difficultés',
            'dossiers traites' => 'dossiers traités',
            'Retours collectes' => 'Retours collectés',
            'Deposez' => 'Déposez',
            'reclamation' => 'réclamation',
            'etapes' => 'étapes',
            'Espace securise' => 'Espace sécurisé',
            'securise' => 'sécurisé',
            'probleme' => 'problème',
            'collectes' => 'collectés',
            'Decrire' => 'Décrire',
            'Cloturer' => 'Clôturer',
            'dossier traite' => 'dossier traité',
            'retour d experience' => 'retour d’expérience',
            'retours d experience' => 'retours d’expérience',
            'Retour d experience' => 'Retour d’expérience',
            'Retours d experience' => 'Retours d’expérience',
            'etape' => 'étape',
            'depot' => 'dépôt',
            'resolution' => 'résolution',
            'Accedez a' => 'Accédez à',
            'Banniere acces' => 'Bannière accès',
            'acces' => 'accès',
            'Types d usagers publics' => 'Types d’usagers publics',
            'independant' => 'indépendant',
            'Fonctionnalites' => 'Fonctionnalités',
            'pense' => 'pensé',
            'apres resolution' => 'après résolution',
            'encadres' => 'encadrés',
            'declarent' => 'déclarent',
            'prevenues' => 'prévenues',
            'dedies' => 'dédiés',
            'periode de grace' => 'période de grâce',
            'journee' => 'journée',
            'Parametrage' => 'Paramétrage',
            'declarer' => 'déclarer',
            'resoudre' => 'résoudre',
            'Legende' => 'Légende',
            'accompagnes' => 'accompagnés',
            'abonnees' => 'abonnées',
            'Temoignages' => 'Témoignages',
            'Role' => 'Rôle',
            'Pret a' => 'Prêt à',
            'meme parcours' => 'même parcours',
            'adapte a' => 'adapté à',
            'apres achat' => 'après achat',
            'liee a' => 'liée à',
            'qualite' => 'qualité',
            'Sante' => 'Santé',
            'Energie' => 'Énergie',
            'prives' => 'privés',
            's appuient' => 's’appuient',
            'COLLECTIVITES' => 'COLLECTIVITÉS',
            'RESEAUX' => 'RÉSEAUX',
            'MEDIATION' => 'MÉDIATION',
            'Legal' => 'Légal',
            'A propos' => 'À propos',
            'Conditions générales d\'utilisation' => 'Conditions générales d’utilisation',
        ];

        $this->correctTable('landing_page_sections', ['label', 'title', 'subtitle', 'body', 'meta'], $replacements);
        $this->correctTable('landing_page_section_items', ['title', 'subtitle', 'body', 'icon', 'value', 'meta'], $replacements);
    }

    public function down(): void
    {
        //
    }

    private function correctTable(string $table, array $columns, array $replacements): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        DB::table($table)
            ->orderBy('id')
            ->lazyById()
            ->each(function (object $row) use ($table, $columns, $replacements): void {
                $updates = [];

                foreach ($columns as $column) {
                    if (! property_exists($row, $column) || $row->{$column} === null) {
                        continue;
                    }

                    $value = $row->{$column};
                    $corrected = $column === 'meta'
                        ? $this->correctJsonValue($value, $replacements)
                        : strtr((string) $value, $replacements);

                    if ($corrected !== $value) {
                        $updates[$column] = $corrected;
                    }
                }

                if ($updates !== []) {
                    $updates['updated_at'] = now();
                    DB::table($table)->where('id', $row->id)->update($updates);
                }
            });
    }

    private function correctJsonValue(mixed $value, array $replacements): mixed
    {
        $decoded = is_string($value) ? json_decode($value, true) : $value;

        if (! is_array($decoded)) {
            return is_string($value) ? strtr($value, $replacements) : $value;
        }

        $corrected = $this->correctArrayStrings($decoded, $replacements);

        return json_encode($corrected, JSON_UNESCAPED_UNICODE);
    }

    private function correctArrayStrings(array $items, array $replacements): array
    {
        foreach ($items as $key => $value) {
            if (is_array($value)) {
                $items[$key] = $this->correctArrayStrings($value, $replacements);
            } elseif (is_string($value)) {
                $items[$key] = strtr($value, $replacements);
            }
        }

        return $items;
    }
};
