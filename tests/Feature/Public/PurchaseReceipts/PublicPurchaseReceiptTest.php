<?php

namespace Tests\Feature\Public\PurchaseReceipts;

use App\Models\PublicUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class PublicPurchaseReceiptTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_user_can_manage_purchase_receipts(): void
    {
        $user = PublicUser::query()->create([
            'first_name' => 'Mariam',
            'last_name' => 'Kone',
            'phone' => '0700000300',
            'commune' => 'Yopougon',
            'password' => 'secret123',
            'status' => 'active',
            'phone_verified_at' => now(),
        ]);

        $token = JWTAuth::fromUser($user);

        $createResponse = $this->withToken($token)->postJson('/api/v1/public/purchase-receipts', [
            'material_name' => 'Televiseur',
            'purchase_date' => now()->subMonth()->toDateString(),
            'amount' => 150000,
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.purchase_receipt.material_name', 'Televiseur')
            ->assertJsonPath('data.purchase_receipt.amount', 150000);

        $receiptId = $createResponse->json('data.purchase_receipt.id');

        $this->withToken($token)->patchJson("/api/v1/public/purchase-receipts/{$receiptId}", [
            'amount' => 175000,
        ])->assertOk()
            ->assertJsonPath('data.purchase_receipt.amount', 175000);

        $this->withToken($token)->getJson('/api/v1/public/purchase-receipts')
            ->assertOk()
            ->assertJsonCount(1, 'data.purchase_receipts');

        $this->withToken($token)->deleteJson("/api/v1/public/purchase-receipts/{$receiptId}")
            ->assertOk();

        $this->assertDatabaseMissing('purchase_receipts', [
            'id' => $receiptId,
        ]);
    }
}
