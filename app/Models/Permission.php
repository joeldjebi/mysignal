<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'profile_scope',
        'category',
        'status',
    ];

    public const PROFILE_SCOPES = [
        'all' => 'Tous les profils',
        'super_admin' => 'Super admin',
        'backoffice' => 'Backoffice',
        'institution' => 'Institution',
        'partner' => 'Partenaire / scanneur',
        'huissier' => 'Huissier',
        'aoda' => 'Ordre des avocats',
        'avocat' => 'Avocat',
        'public' => 'UP',
    ];

    public const CATEGORIES = [
        'dashboard' => 'Tableau de bord',
        'reports' => 'Signalements',
        'reparation_cases' => 'Dossiers / reparations',
        'users' => 'Utilisateurs',
        'roles_permissions' => 'Roles & permissions',
        'settings' => 'Parametrage',
        'payments' => 'Paiements / abonnements',
        'notifications' => 'Notifications',
        'landing_cms' => 'Landing page / CMS',
        'discounts' => 'Reductions',
        'activity_logs' => 'Journal d activite',
        'catalog' => 'Catalogues',
        'other' => 'Autres',
    ];

    public static function inferProfileScope(string $code): string
    {
        return match (true) {
            str_starts_with($code, 'SA_') => 'super_admin',
            str_starts_with($code, 'INSTITUTION_') => 'institution',
            str_starts_with($code, 'PARTNER_') => 'partner',
            str_contains($code, 'HUISSIER'), str_contains($code, 'BAILIFF') => 'huissier',
            str_contains($code, 'AODA') => 'aoda',
            str_contains($code, 'AVOCAT'), str_contains($code, 'LAWYER') => 'avocat',
            str_starts_with($code, 'PUBLIC_'), str_starts_with($code, 'UP_') => 'public',
            default => 'backoffice',
        };
    }

    public static function inferCategory(string $code): string
    {
        return match (true) {
            str_contains($code, 'DASHBOARD') => 'dashboard',
            str_contains($code, 'REPORT'), str_contains($code, 'DAMAGE'), str_contains($code, 'SIGNAL') => 'reports',
            str_contains($code, 'REPARATION'), str_contains($code, 'CASE'), str_contains($code, 'DOSSIER') => 'reparation_cases',
            str_contains($code, 'USER'), str_contains($code, 'ADMIN') => 'users',
            str_contains($code, 'ROLE'), str_contains($code, 'PERMISSION') => 'roles_permissions',
            str_contains($code, 'PAYMENT'), str_contains($code, 'SUBSCRIPTION'), str_contains($code, 'PRICING') => 'payments',
            str_contains($code, 'NOTIFICATION'), str_contains($code, 'PUSH') => 'notifications',
            str_contains($code, 'LANDING'), str_contains($code, 'CMS'), str_contains($code, 'CONTACT') => 'landing_cms',
            str_contains($code, 'DISCOUNT'), str_contains($code, 'CARD') => 'discounts',
            str_contains($code, 'ACTIVITY_LOG'), str_contains($code, 'LOG') => 'activity_logs',
            str_contains($code, 'COUNTRY'), str_contains($code, 'CITY'), str_contains($code, 'COMMUNE'), str_contains($code, 'CATALOG'), str_contains($code, 'FEATURE'), str_contains($code, 'APPLICATION'), str_contains($code, 'ORGANIZATION'), str_contains($code, 'TYPE') => 'catalog',
            str_contains($code, 'SETTING'), str_contains($code, 'SLA'), str_contains($code, 'TCM'), str_contains($code, 'REX') => 'settings',
            default => 'other',
        };
    }

    public static function compatibleProfileScopes(string $profileScope): array
    {
        return match ($profileScope) {
            'super_admin' => ['all', 'super_admin', 'backoffice'],
            'huissier' => ['all', 'huissier', 'backoffice'],
            'aoda' => ['all', 'aoda', 'avocat', 'backoffice'],
            'avocat' => ['all', 'avocat', 'backoffice'],
            'backoffice' => ['all', 'backoffice', 'huissier', 'aoda', 'avocat'],
            default => ['all', $profileScope],
        };
    }

    public function profileScopeLabel(): string
    {
        return self::PROFILE_SCOPES[$this->profile_scope ?: 'all'] ?? (string) $this->profile_scope;
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category ?: 'other'] ?? (string) $this->category;
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)
            ->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps();
    }
}
