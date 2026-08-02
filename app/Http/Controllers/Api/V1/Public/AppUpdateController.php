<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\MobileAppUpdateSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppUpdateController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $appName = (string) $request->query('app_name', 'mysignal');

        return response()->json(
            MobileAppUpdateSetting::activeFor($appName)->apiPayload()
        );
    }
}
