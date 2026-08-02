<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\MobileAppUpdateSetting;
use App\Support\Audit\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MobileAppUpdateSettingController extends Controller
{
    public function edit(): View
    {
        $setting = MobileAppUpdateSetting::activeFor();

        return view('super-admin.mobile-app-update.edit', [
            'setting' => $setting,
            'messages' => $setting->messages ?: MobileAppUpdateSetting::defaultMessages(),
        ]);
    }

    public function update(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $attributes = $request->validate([
            'app_name' => ['required', 'string', 'max:80'],
            'latest_version_android' => ['required', 'string', 'max:30'],
            'build_version_android' => ['required', 'integer', 'min:1'],
            'play_store_url' => ['required', 'url', 'max:255'],
            'latest_version_ios' => ['required', 'string', 'max:30'],
            'build_version_ios' => ['required', 'integer', 'min:1'],
            'app_store_url' => ['required', 'url', 'max:255'],
            'update_type' => ['required', Rule::in(['minor', 'major', 'urgent'])],
            'messages.minor.title' => ['required', 'string', 'max:120'],
            'messages.minor.message' => ['required', 'string', 'max:500'],
            'messages.major.title' => ['required', 'string', 'max:120'],
            'messages.major.message' => ['required', 'string', 'max:500'],
            'messages.urgent.title' => ['required', 'string', 'max:120'],
            'messages.urgent.message' => ['required', 'string', 'max:500'],
        ]);

        $setting = MobileAppUpdateSetting::activeFor();
        $before = $setting->apiPayload();

        $setting->update([
            'app_name' => $attributes['app_name'],
            'latest_version_android' => $attributes['latest_version_android'],
            'build_version_android' => $attributes['build_version_android'],
            'play_store_url' => $attributes['play_store_url'],
            'latest_version_ios' => $attributes['latest_version_ios'],
            'build_version_ios' => $attributes['build_version_ios'],
            'app_store_url' => $attributes['app_store_url'],
            'update_type' => $attributes['update_type'],
            'messages' => $attributes['messages'],
            'status' => 'active',
        ]);

        $activityLogger->log(
            'mobile_app_update.updated',
            'Mise à jour de la configuration mobile.',
            $setting,
            [
                'before' => $before,
                'after' => $setting->fresh()->apiPayload(),
            ],
            $request,
            $request->user(),
        );

        return back()->with('success', 'La configuration de mise à jour mobile a été enregistrée.');
    }
}
