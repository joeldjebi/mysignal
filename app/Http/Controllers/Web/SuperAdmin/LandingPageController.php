<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ApplicationContentBlock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LandingPageController extends Controller
{
    public function edit(): View
    {
        return view('super-admin.landing-page.edit', [
            'landingPage' => $this->landingPageBlock(),
            'defaultLogoUrl' => asset('image/logo/logo-my-signal.png'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $attributes = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'primary_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'accent_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $landingPage = $this->landingPageBlock();
        $landingPage->fill([
            'title' => $attributes['title'],
            'subtitle' => $attributes['subtitle'] ?? null,
            'body' => $attributes['body'] ?? null,
            'status' => $request->boolean('is_active') ? 'active' : 'inactive',
            'sort_order' => 1,
            'meta' => [
                'primary_color' => $attributes['primary_color'] ?: '#183447',
                'secondary_color' => $attributes['secondary_color'] ?: '#256f8f',
                'accent_color' => $attributes['accent_color'] ?: '#ff0068',
            ],
        ]);
        $landingPage->save();

        return redirect()
            ->route('super-admin.landing-page.edit')
            ->with('success', 'La landing page a ete mise a jour.');
    }

    private function landingPageBlock(): ApplicationContentBlock
    {
        return ApplicationContentBlock::query()->firstOrNew(
            [
                'application_id' => null,
                'page_key' => 'public_landing',
                'block_key' => 'custom_page',
            ],
            [
                'title' => 'MySignal - Plateforme de signalement consommateur',
                'subtitle' => 'Landing page publique',
                'body' => null,
                'status' => 'inactive',
                'sort_order' => 1,
                'meta' => [
                    'primary_color' => '#183447',
                    'secondary_color' => '#256f8f',
                    'accent_color' => '#ff0068',
                ],
            ],
        );
    }
}
