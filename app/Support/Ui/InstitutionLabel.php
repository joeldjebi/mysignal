<?php

namespace App\Support\Ui;

class InstitutionLabel
{
    public static function status(?string $status): string
    {
        return match ($status) {
            'active' => 'Actif',
            'inactive' => 'Inactif',
            'submitted' => 'Soumis',
            'pending' => 'En attente',
            'in_progress' => 'En cours',
            'resolved' => 'Résolu',
            'rejected' => 'Rejeté',
            'closed' => 'Clôturé',
            'failed' => 'Échoué',
            'paid' => 'Payé',
            'cancelled' => 'Annulé',
            'draft' => 'Brouillon',
            default => filled($status) ? self::humanize($status) : '-',
        };
    }

    public static function payment(?string $status): string
    {
        return match ($status) {
            'pending' => 'En attente',
            'paid' => 'Payé',
            'failed' => 'Échoué',
            'cancelled' => 'Annulé',
            'refunded' => 'Remboursé',
            default => self::status($status),
        };
    }

    public static function damage(?string $status): string
    {
        return match ($status) {
            'submitted' => 'Soumis',
            'in_progress' => 'En cours',
            'resolved' => 'Résolu',
            'rejected' => 'Rejeté',
            default => self::status($status ?: 'submitted'),
        };
    }

    public static function sla(?string $status): string
    {
        return match ($status) {
            'within' => 'Dans le délai',
            'at_risk' => 'À surveiller',
            'breached' => 'Délai dépassé',
            'unconfigured' => 'Non configuré',
            default => self::status($status),
        };
    }

    public static function portal(?string $portal): string
    {
        return match ($portal) {
            'institution' => 'Portail institutionnel',
            'super_admin' => 'Portail SA',
            'backoffice' => 'Back-office',
            'partner' => 'Portail partenaire',
            'huissier' => 'Espace huissier',
            'aoda' => 'Espace ordre des avocats',
            'avocat' => 'Espace avocat',
            default => self::humanize($portal),
        };
    }

    public static function action(?string $action): string
    {
        $labels = [
            'institution.login' => 'Connexion',
            'institution.logout' => 'Déconnexion',
            'institution_report.take_over' => 'Prise en charge d’un signalement',
            'institution_report.resolved' => 'Résolution d’un signalement',
            'institution_report.rejected' => 'Rejet d’un signalement',
            'institution_report.damage_resolution_updated' => 'Mise à jour d’un dommage',
            'institution_user.created' => 'Création d’un collaborateur',
            'institution_user.updated' => 'Mise à jour d’un collaborateur',
            'institution_user.deleted' => 'Suppression d’un collaborateur',
            'institution_user.status_toggled' => 'Changement de statut d’un collaborateur',
            'institution_role.created' => 'Création d’un rôle',
            'institution_role.updated' => 'Mise à jour d’un rôle',
            'institution_role.deleted' => 'Suppression d’un rôle',
            'institution_role.status_toggled' => 'Changement de statut d’un rôle',
            'institution_profile.updated' => 'Mise à jour du profil',
        ];

        return $labels[$action] ?? self::humanize($action);
    }

    public static function humanize(?string $value): string
    {
        if (blank($value)) {
            return '-';
        }

        return str((string) $value)
            ->replace(['_', '-'], ' ')
            ->squish()
            ->ucfirst()
            ->toString();
    }
}
