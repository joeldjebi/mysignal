<?php

namespace App\Http\Controllers\Api\V1\Public\ReparationCases;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Public\ReparationCases\ReparationCaseResource;
use App\Models\ReparationCase;
use App\Support\Api\ApiResponse;
use Illuminate\Http\Request;

class PublicReparationCaseController extends Controller
{
    public function index(Request $request)
    {
        $cases = ReparationCase::query()
            ->with([
                'incidentReport.application',
                'incidentReport.organization',
                'histories.createdBy',
                'steps.assignedTo',
                'steps.createdBy',
                'bailiff',
                'lawyer',
            ])
            ->where('public_user_id', $request->user('public_api')->id)
            ->latest('id')
            ->get();

        return ApiResponse::success([
            'reparation_cases' => ReparationCaseResource::collection($cases),
        ]);
    }
}
