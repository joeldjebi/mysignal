<?php

namespace App\Http\Controllers\Api\V1\Public\Signals;

use App\Domain\Reports\Actions\GetSignalTypeCatalogAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Public\Signals\SignalTypeResource;
use App\Support\Api\ApiResponse;

class PublicSignalTypeController extends Controller
{
    public function index(GetSignalTypeCatalogAction $action)
    {
        $catalog = collect($action->handle())->map(function (array $type, string $code) {
            return ['code' => $code, ...$type];
        })->values();

        return ApiResponse::success([
            'signal_types' => SignalTypeResource::collection($catalog),
        ]);
    }
}
