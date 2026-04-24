<?php

namespace App\Models;

use App\Services\WasabiService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class LandingPageSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'label',
        'title',
        'subtitle',
        'body',
        'meta',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(LandingPageSectionItem::class)->orderBy('sort_order')->orderBy('id');
    }

    public function landingBody(): ?string
    {
        return match ($this->key) {
            'navigation' => $this->lineItems('links', ['title', 'url']),
            'feature_strip' => $this->lineItems('items', ['title', 'body', 'icon']),
            'stats' => $this->lineItems('items', ['value', 'title']),
            default => $this->body,
        };
    }

    public function landingMeta(): array
    {
        $meta = $this->meta ?? [];

        return match ($this->key) {
            'hero' => $meta + ['stats' => $this->lineItems('stats', ['value', 'title'])],
            'manage' => $meta + ['items' => $this->lineItems('items', ['title', 'body', 'icon'])],
            'share' => $meta + ['cards' => $this->lineItems('cards', ['title', 'body', 'icon'])],
            'access_banner' => $meta + ['buttons' => $this->lineItems('buttons', ['title', 'subtitle', 'icon'])],
            'app_features' => $meta + ['items' => $this->lineItems('items', ['title', 'body', 'icon'])],
            'screenshots' => $meta + ['items' => $this->lineItems('items', ['title', 'icon'])],
            'process' => $meta + [
                'steps' => $this->lineItems('steps', ['title', 'body']),
                'legend' => $this->lineItems('legend', ['title']),
            ],
            'faq' => $meta + ['questions' => $this->lineItems('questions', ['title', 'body'])],
            'testimonials' => $meta + ['items' => $this->lineItems('items', ['body', 'title', 'subtitle', 'icon'])],
            'news' => $meta + ['items' => $this->lineItems('items', ['subtitle', 'title', 'body', 'icon', 'value'])],
            'clients' => $meta + ['items' => $this->lineItems('items', ['title', 'body', 'icon'])],
            'partners' => $meta + [
                'items' => $this->lineItems('items', ['title', 'icon']),
                'cards' => $this->structuredItems('items')
                    ->map(fn (array $item): array => $item + [
                        'logo_url' => $this->resolveAssetUrl($item['url'] ?? null),
                    ])
                    ->all(),
            ],
            'footer' => $meta + [
                'column_1_links' => $this->lineItems('column_1_links', ['title', 'url']),
                'column_2_links' => $this->lineItems('column_2_links', ['title', 'url']),
                'column_3_links' => $this->lineItems('column_3_links', ['title', 'url']),
            ],
            default => $meta,
        };
    }

    private function lineItems(string $group, array $fields): string
    {
        return $this->activeItems($group)
            ->map(function (LandingPageSectionItem $item) use ($fields): string {
                return collect($fields)
                    ->map(fn (string $field): string => trim((string) ($item->{$field} ?? '')))
                    ->implode(' | ');
            })
            ->filter(fn (string $line): bool => trim(str_replace('|', '', $line)) !== '')
            ->implode("\n");
    }

    private function activeItems(string $group): Collection
    {
        return $this->items
            ->where('item_key', $group)
            ->where('is_active', true)
            ->values();
    }

    private function structuredItems(string $group): Collection
    {
        return $this->activeItems($group)
            ->map(fn (LandingPageSectionItem $item): array => [
                'title' => $item->title,
                'subtitle' => $item->subtitle,
                'body' => $item->body,
                'icon' => $item->icon,
                'url' => $item->url,
                'value' => $item->value,
            ])
            ->values();
    }

    private function resolveAssetUrl(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        if (str_starts_with((string) $path, 'applications/') || str_starts_with((string) $path, 'landing/')) {
            return app(WasabiService::class)->temporaryUrl($path);
        }

        return asset((string) $path);
    }
}
