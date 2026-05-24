<?php

namespace App\Http\Controllers\Api\V1\Public\PurchaseReceipts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Public\PurchaseReceipts\StorePurchaseReceiptRequest;
use App\Http\Requests\Api\V1\Public\PurchaseReceipts\UpdatePurchaseReceiptRequest;
use App\Http\Resources\Api\V1\Public\PurchaseReceipts\PurchaseReceiptResource;
use App\Models\PurchaseReceipt;
use App\Services\WasabiService;
use App\Support\Api\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class PublicPurchaseReceiptController extends Controller
{
    public function __construct(private readonly WasabiService $wasabiService) {}

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
        $attributes = $request->validated();
        unset($attributes['receipt_file']);

        if ($request->hasFile('receipt_file')) {
            $attributes['attachment'] = $this->storeReceiptFile($request->file('receipt_file'), (string) $request->user('public_api')->id);
        }

        $receipt = $request->user('public_api')
            ->purchaseReceipts()
            ->create($attributes);

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

        $attributes = $request->validated();
        unset($attributes['receipt_file']);

        if ($request->hasFile('receipt_file')) {
            $previousPath = $purchaseReceipt->attachment['path'] ?? null;
            $attributes['attachment'] = $this->storeReceiptFile($request->file('receipt_file'), (string) $purchaseReceipt->public_user_id);

            if (filled($previousPath)) {
                $this->wasabiService->deleteFile($previousPath);
            }
        }

        $purchaseReceipt->update($attributes);

        return ApiResponse::success([
            'purchase_receipt' => new PurchaseReceiptResource($purchaseReceipt->fresh()),
        ], 'Recu d achat mis a jour avec succes.');
    }

    public function destroy(Request $request, PurchaseReceipt $purchaseReceipt)
    {
        abort_unless((int) $purchaseReceipt->public_user_id === (int) $request->user('public_api')->id, 404);

        $attachmentPath = $purchaseReceipt->attachment['path'] ?? null;
        $purchaseReceipt->delete();

        if (filled($attachmentPath)) {
            $this->wasabiService->deleteFile($attachmentPath);
        }

        return ApiResponse::success([], 'Recu d achat supprime avec succes.');
    }

    private function storeReceiptFile(UploadedFile $file, string $userId): array
    {
        $path = $this->wasabiService->uploadFile(
            $file,
            config('wasabi.purchase_receipt_directory', 'purchase-receipts').'/'.$userId,
            'receipt'
        );

        if (! $path) {
            throw ValidationException::withMessages([
                'receipt_file' => ['Impossible de televerser le fichier du recu sur le stockage distant.'],
            ]);
        }

        return [
            'name' => $file->getClientOriginalName() ?: 'recu-achat',
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'size' => $file->getSize(),
            'path' => $path,
        ];
    }
}
