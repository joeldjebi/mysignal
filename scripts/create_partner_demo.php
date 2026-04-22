<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$organizationType = App\Models\OrganizationType::query()
    ->where('code', 'PARTNER_ESTABLISHMENT')
    ->firstOrFail();

$organization = App\Models\Organization::query()->updateOrCreate(
    ['code' => 'PARTNER_DEMO'],
    [
        'application_id' => null,
        'organization_type_id' => $organizationType->id,
        'name' => 'Partenaire Demo',
        'portal_key' => 'partner-demo',
        'email' => 'admin.partenaire.demo@mysignal.local',
        'phone' => null,
        'description' => 'Etablissement partenaire de demonstration cree pour le module reductions.',
        'status' => 'active',
    ],
);

$role = App\Models\Role::query()
    ->whereNull('organization_id')
    ->where('code', 'PARTNER_ADMIN')
    ->firstOrFail();

$permissionIds = App\Models\Permission::query()
    ->whereIn('code', [
        'PARTNER_ACCESS_PORTAL',
        'PARTNER_DASHBOARD_VIEW',
        'PARTNER_DISCOUNT_SCAN',
        'PARTNER_DISCOUNT_APPLY',
        'PARTNER_DISCOUNT_HISTORY_VIEW',
        'PARTNER_DISCOUNT_OFFERS_MANAGE',
        'PARTNER_USERS_MANAGE',
        'PARTNER_USERS_CREATE',
        'PARTNER_USERS_UPDATE',
        'PARTNER_USERS_TOGGLE_STATUS',
    ])
    ->pluck('id')
    ->all();

$user = App\Models\User::query()->updateOrCreate(
    ['email' => 'admin.partenaire.demo@mysignal.local'],
    [
        'organization_id' => $organization->id,
        'name' => 'Admin Partenaire Demo',
        'phone' => null,
        'password' => Illuminate\Support\Facades\Hash::make('12345678'),
        'is_super_admin' => false,
        'status' => 'active',
    ],
);

$user->roles()->sync([$role->id]);
$user->permissions()->sync($permissionIds);

echo json_encode([
    'organization_id' => $organization->id,
    'organization_code' => $organization->code,
    'organization_name' => $organization->name,
    'user_id' => $user->id,
    'email' => $user->email,
    'password' => '12345678',
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL;
