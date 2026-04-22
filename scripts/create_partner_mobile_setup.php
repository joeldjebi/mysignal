<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Organization;
use App\Models\PartnerDiscountOffer;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

$organization = Organization::query()
    ->where('code', 'PARTNER_DEMO')
    ->firstOrFail();

$adminUser = User::query()
    ->where('organization_id', $organization->id)
    ->where('email', 'admin.partenaire.demo@mysignal.local')
    ->firstOrFail();

$offers = [
    [
        'code' => 'PARTNER_DEMO_5PCT',
        'name' => 'Reduction 5%',
        'description' => 'Reduction immediate de 5% sur les achats eligibles.',
        'discount_type' => 'percentage',
        'discount_value' => 5,
        'currency' => 'XOF',
        'minimum_purchase_amount' => 5000,
        'maximum_discount_amount' => 5000,
        'max_uses_per_card' => 1,
        'max_uses_per_day' => 20,
        'status' => 'active',
    ],
    [
        'code' => 'PARTNER_DEMO_1000XOF',
        'name' => 'Reduction 1000 FCFA',
        'description' => 'Reduction fixe de 1000 FCFA sur les achats eligibles.',
        'discount_type' => 'fixed_amount',
        'discount_value' => 1000,
        'currency' => 'XOF',
        'minimum_purchase_amount' => 10000,
        'maximum_discount_amount' => 1000,
        'max_uses_per_card' => 1,
        'max_uses_per_day' => 20,
        'status' => 'active',
    ],
];

$createdOffers = [];

foreach ($offers as $payload) {
    $offer = PartnerDiscountOffer::query()->updateOrCreate(
        ['code' => $payload['code']],
        [
            'organization_id' => $organization->id,
            'name' => $payload['name'],
            'description' => $payload['description'],
            'discount_type' => $payload['discount_type'],
            'discount_value' => $payload['discount_value'],
            'currency' => $payload['currency'],
            'minimum_purchase_amount' => $payload['minimum_purchase_amount'],
            'maximum_discount_amount' => $payload['maximum_discount_amount'],
            'max_uses_per_card' => $payload['max_uses_per_card'],
            'max_uses_per_day' => $payload['max_uses_per_day'],
            'starts_at' => now(),
            'ends_at' => now()->addYear(),
            'status' => $payload['status'],
            'created_by' => $adminUser->id,
            'updated_by' => $adminUser->id,
        ],
    );

    $createdOffers[] = [
        'id' => $offer->id,
        'code' => $offer->code,
        'name' => $offer->name,
        'status' => $offer->status,
    ];
}

$role = Role::query()
    ->whereNull('organization_id')
    ->where('status', 'active')
    ->where('code', 'PARTNER_AGENT')
    ->firstOrFail();

$permissionIds = Permission::query()
    ->whereIn('code', [
        'PARTNER_ACCESS_PORTAL',
        'PARTNER_DASHBOARD_VIEW',
        'PARTNER_DISCOUNT_SCAN',
        'PARTNER_DISCOUNT_APPLY',
        'PARTNER_DISCOUNT_HISTORY_VIEW',
    ])
    ->pluck('id')
    ->all();

$mobileUser = User::query()->updateOrCreate(
    ['email' => 'agent.mobile.demo@mysignal.local'],
    [
        'organization_id' => $organization->id,
        'name' => 'Agent Mobile Demo',
        'phone' => '2250758754662',
        'password' => Hash::make('12345678'),
        'is_super_admin' => false,
        'status' => 'active',
        'created_by' => $adminUser->id,
    ],
);

$mobileUser->roles()->sync([$role->id]);
$mobileUser->permissions()->sync($permissionIds);

echo json_encode([
    'organization' => [
        'id' => $organization->id,
        'code' => $organization->code,
        'name' => $organization->name,
    ],
    'mobile_user' => [
        'id' => $mobileUser->id,
        'name' => $mobileUser->name,
        'email' => $mobileUser->email,
        'phone' => $mobileUser->phone,
        'password' => '12345678',
        'role_code' => 'PARTNER_AGENT',
    ],
    'offers' => $createdOffers,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL;
