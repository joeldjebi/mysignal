<?php

namespace App\Http\Controllers\Api\V1\Public\Locations;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Public\Locations\LocationTreeResource;
use App\Models\Country;
use App\Support\Api\ApiResponse;

class PublicLocationController extends Controller
{
    public function index()
    {
        $countries = Country::query()
            ->where('status', 'active')
            ->with([
                'cities' => fn ($query) => $query->where('status', 'active')->with([
                    'communes' => fn ($communeQuery) => $communeQuery->where('status', 'active')->with([
                        'neighborhoods' => fn ($neighborhoodQuery) => $neighborhoodQuery->where('status', 'active')->with([
                            'subNeighborhoods' => fn ($subNeighborhoodQuery) => $subNeighborhoodQuery->where('status', 'active'),
                        ]),
                    ]),
                ]),
            ])
            ->get();

        return ApiResponse::success([
            'countries' => LocationTreeResource::collection($countries),
        ]);
    }
}
