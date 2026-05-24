<?php

namespace App\Http\Controllers\Api\V1\Public\PurchaseReceipts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Public\PurchaseReceipts\StorePurchaseReceiptRequest;
use App\Http\Requests\Api\V1\Public\PurchaseReceipts\UpdatePurchaseReceiptRequest;
use App\Http\Resources\Api\V1\Public\PurchaseReceipts\PurchaseReceiptResource;
use App\Models\PurchaseReceipt;
use App\Support\Api\ApiResponse;
use Illuminate\Http\Request;

class PublicPurchaseReceiptController extends Controller
{
    public function index(Request $request)
    {
        $receipts = $request->user('public_api')
            ->purchaseReceipts()
            ->latest('purchase_date')
            ->latest('id')
            ->get();

        return ApiResponse::success([
            'purchase_receipts' => PurchaseReceiptResource::collection($receipts),
        ]);
    }

    public function store(StorePurchaseReceiptRequest $request)
    {
        $receipt = $request->user('public_api')
            ->purchaseReceipts()
            ->create($request->validated());

        return ApiResponse::success([
            'purchase_receipt' => new PurchaseReceiptResource($receipt),
        ], 'Recu d achat enregistre avec succes.', 201);
    }

    public function show(Request $request, PurchaseReceipt $purchaseReceipt)
    {
        abort_unless((int) $purchaseReceipt->public_user_id === (int) $request->user('public_api')->id, 404);

        return ApiResponse::success([
            'purchase_receipt' => new PurchaseReceiptResource($purchaseReceipt),
        ]);
    }

    public function update(UpdatePurchaseReceiptRequest $request, PurchaseReceipt $purchaseReceipt)
    {
        abort_unless((int) $purchaseReceipt->public_user_id === (int) $request->user('public_api')->id, 404);

        $purchaseReceipt->update($request->validated());

        return ApiResponse::success([
            'purchase_receipt' => new PurchaseReceiptResource($purchaseReceipt->fresh()),
        ], 'Recu d achat mis a jour avec succes.');
    }

    public function destroy(Request $request, PurchaseReceipt $purchaseReceipt)
    {
        abort_unless((int) $purchaseReceipt->public_user_id === (int) $request->user('public_api')->id, 404);

        $purchaseReceipt->delete();

        return ApiResponse::success([], 'Recu d achat supprime avec succes.');
    }
}
