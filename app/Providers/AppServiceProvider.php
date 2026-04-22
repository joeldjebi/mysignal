<?php

namespace App\Providers;

use App\Models\Country;
use Illuminate\Pagination\Paginator;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        View::share('dialCodeOptions', $this->dialCodeOptions());

        RateLimiter::for('public-auth-otp', function (Request $request): array {
            return [
                Limit::perMinute(3)->by($request->ip()),
                Limit::perMinute(3)->by((string) $request->input('phone')),
            ];
        });

        RateLimiter::for('public-auth-register', function (Request $request): Limit {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('public-auth-login', function (Request $request): array {
            return [
                Limit::perMinute(5)->by($request->ip()),
                Limit::perMinute(5)->by((string) $request->input('phone')),
            ];
        });

        RateLimiter::for('partner-auth-otp', function (Request $request): array {
            return [
                Limit::perMinute(3)->by($request->ip()),
                Limit::perMinute(3)->by((string) $request->input('phone')),
            ];
        });

        RateLimiter::for('partner-auth-password-reset', function (Request $request): Limit {
            return Limit::perMinute(5)->by($request->ip());
        });
    }

    private function dialCodeOptions(): array
    {
        if (! Schema::hasTable('countries') || ! Schema::hasColumn('countries', 'dial_code') || ! Schema::hasColumn('countries', 'flag')) {
            return $this->fallbackDialCodeOptions();
        }

        $options = Country::query()
            ->where('status', 'active')
            ->whereNotNull('dial_code')
            ->where('dial_code', '!=', '')
            ->orderBy('name')
            ->get(['dial_code', 'code', 'name', 'flag'])
            ->map(fn (Country $country) => [
                'value' => (string) $country->dial_code,
                'label' => trim(($country->flag ? $country->flag.' ' : '').'+'.$country->dial_code.' · '.$country->code),
                'country' => $country->name,
            ])
            ->unique('value')
            ->values()
            ->all();

        return $options !== [] ? $options : $this->fallbackDialCodeOptions();
    }

    private function fallbackDialCodeOptions(): array
    {
        return [
            ['value' => '225', 'label' => '🇨🇮 +225 · CI', 'country' => 'Cote d Ivoire'],
            ['value' => '223', 'label' => '🇲🇱 +223 · ML', 'country' => 'Mali'],
            ['value' => '226', 'label' => '🇧🇫 +226 · BF', 'country' => 'Burkina Faso'],
            ['value' => '228', 'label' => '🇹🇬 +228 · TG', 'country' => 'Togo'],
            ['value' => '229', 'label' => '🇧🇯 +229 · BJ', 'country' => 'Benin'],
            ['value' => '221', 'label' => '🇸🇳 +221 · SN', 'country' => 'Senegal'],
        ];
    }
}
