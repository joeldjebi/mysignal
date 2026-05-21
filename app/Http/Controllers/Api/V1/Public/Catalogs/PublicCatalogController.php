<?php

namespace App\Http\Controllers\Api\V1\Public\Catalogs;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Public\Catalogs\ApplicationResource;
use App\Http\Resources\Api\V1\Public\Catalogs\BusinessSectorResource;
use App\Http\Resources\Api\V1\Public\Catalogs\CityResource;
use App\Http\Resources\Api\V1\Public\Catalogs\CountryResource;
use App\Http\Resources\Api\V1\Public\Catalogs\OrganizationResource;
use App\Http\Resources\Api\V1\Public\Catalogs\OrganizationTypeResource;
use App\Http\Resources\Api\V1\Public\Catalogs\ServiceCategoryResource;
use App\Models\Application;
use App\Models\BusinessSector;
use App\Models\City;
use App\Models\Country;
use App\Models\Organization;
use App\Models\OrganizationType;
use App\Support\Api\ApiResponse;
use Illuminate\Http\Request;

class PublicCatalogController extends Controller
{
    public function applications()
    {
        $applications = Application::query()
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return ApiResponse::success([
            'applications' => ApplicationResource::collection($applications),
        ]);
    }

    public function applicationTypes()
    {
        return $this->organizationTypes('application_types');
    }

    public function organizationTypes(string $responseKey = 'organization_types')
    {
        $types = OrganizationType::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return ApiResponse::success([
            $responseKey => OrganizationTypeResource::collection($types),
        ]);
    }

    public function organizations(Request $request)
    {
        $organizations = Organization::query()
            ->with(['application', 'organizationType'])
            ->where('status', 'active')
            ->when(
                $request->filled('application_id'),
                fn ($query) => $query->where('application_id', (int) $request->integer('application_id'))
            )
            ->when(
                $request->filled('organization_type_id'),
                fn ($query) => $query->where('organization_type_id', (int) $request->integer('organization_type_id'))
            )
            ->orderBy('name')
            ->get();

        return ApiResponse::success([
            'organizations' => OrganizationResource::collection($organizations),
        ]);
    }

    public function countries()
    {
        $countries = Country::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return ApiResponse::success([
            'countries' => CountryResource::collection($countries),
        ]);
    }

    public function cities(Request $request)
    {
        $cities = City::query()
            ->with('country')
            ->where('status', 'active')
            ->when(
                $request->filled('country_id'),
                fn ($query) => $query->where('country_id', (int) $request->integer('country_id'))
            )
            ->orderBy('name')
            ->get();

        return ApiResponse::success([
            'cities' => CityResource::collection($cities),
        ]);
    }

    public function businessSectors()
    {
        $businessSectors = BusinessSector::query()
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return ApiResponse::success([
            'business_sectors' => BusinessSectorResource::collection($businessSectors),
        ]);
    }

    public function serviceCategories(Request $request)
    {
        return ApiResponse::success([
            'service_categories' => ServiceCategoryResource::collection(collect()),
        ]);
    }
}
