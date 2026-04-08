<?php

namespace App\Http\Controllers\Api\V1\Public\Meters;

use App\Domain\Meters\Actions\CreateMeterAction;
use App\Domain\Meters\Actions\UpdateMeterAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Public\Meters\StoreMeterRequest;
use App\Http\Requests\Api\V1\Public\Meters\UpdateMeterRequest;
use App\Http\Resources\Api\V1\Public\Meters\MeterResource;
use App\Models\Meter;
use App\Support\Api\ApiResponse;
use Illuminate\Http\Request;

class PublicMeterController extends Controller
{
    public function index(Request $request)
    {
        $meters = $request->user('public_api')
            ->meters()
            ->with(['application', 'organization.organizationType'])
            ->orderByDesc('meter_assignments.is_primary')
            ->orderByDesc('meters.id')
            ->get();

        return ApiResponse::success([
            'meters' => MeterResource::collection($meters),
        ]);
    }

    public function store(StoreMeterRequest $request, CreateMeterAction $action)
    {
        $meter = $action->handle($request->user('public_api'), $request->validated());

        $meter = $request->user('public_api')
            ->meters()
            ->with(['application', 'organization.organizationType'])
            ->whereKey($meter->id)
            ->firstOrFail();

        return ApiResponse::success([
            'meter' => new MeterResource($meter),
        ], 'Compteur ajoute avec succes.', 201);
    }

    public function show(Request $request, Meter $meter)
    {
        $meter = $request->user('public_api')
            ->meters()
            ->with(['application', 'organization.organizationType'])
            ->whereKey($meter->id)
            ->firstOrFail();

        return ApiResponse::success([
            'meter' => new MeterResource($meter),
        ]);
    }

    public function update(UpdateMeterRequest $request, Meter $meter, UpdateMeterAction $action)
    {
        $ownedMeter = $request->user('public_api')
            ->meters()
            ->with(['application', 'organization.organizationType'])
            ->whereKey($meter->id)
            ->firstOrFail();

        $action->handle($request->user('public_api'), $ownedMeter, $request->validated());

        $ownedMeter = $request->user('public_api')
            ->meters()
            ->with(['application', 'organization.organizationType'])
            ->whereKey($meter->id)
            ->firstOrFail();

        return ApiResponse::success([
            'meter' => new MeterResource($ownedMeter),
        ], 'Compteur mis a jour avec succes.');
    }
}
