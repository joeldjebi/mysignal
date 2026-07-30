<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ApplicationContentBlock;
use App\Models\LandingPageSection;
use App\Services\WasabiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class LandingPageController extends Controller
{
    public function edit(): View
    {
        $definitions = $this->sections();
        $storedSections = LandingPageSection::query()
            ->with('items')
            ->whereIn('key', array_keys($definitions))
            ->get()
            ->keyBy('key');

        return view('super-admin.landing-page.edit', [
            'sections' => collect($definitions)
                ->reject(fn (array $definition, string $key): bool => $key === 'settings')
                ->map(fn (array $definition, string $key): array => $this->sectionForForm($key, $definition, $storedSections->get($key)))
                ->all(),
            'settings' => $this->settingsSection($storedSections->get('settings')),
            'publicPages' => $this->publicPages(),
            'defaultLogoUrl' => asset('image/logo/logo-my-signal.png'),
        ]);
    }

    public function update(Request $request, WasabiService $wasabiService): RedirectResponse
    {
        $attributes = $request->validate([
            'primary_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'accent_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'sections' => ['required', 'array'],
            'sections.*.title' => ['nullable', 'string', 'max:180'],
            'sections.*.subtitle' => ['nullable', 'string', 'max:255'],
            'sections.*.body' => ['nullable', 'string'],
            'sections.*.is_active' => ['nullable', 'boolean'],
            'sections.*.meta' => ['nullable', 'array'],
            'sections.*.meta.*' => ['nullable', 'string', 'max:255'],
            'items' => ['nullable', 'array'],
            'items.*.*.*.title' => ['nullable', 'string', 'max:180'],
            'items.*.*.*.subtitle' => ['nullable', 'string', 'max:255'],
            'items.*.*.*.body' => ['nullable', 'string'],
            'items.*.*.*.icon' => ['nullable', 'string', 'max:2048'],
            'items.*.*.*.url' => ['nullable', 'string', 'max:2048'],
            'items.*.*.*.existing_url' => ['nullable', 'string', 'max:2048'],
            'items.*.*.*.url_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'items.*.*.*.video_file' => ['nullable', 'file', 'mimes:mp4,mov,webm,m4v', 'max:102400'],
            'items.*.*.*.value' => ['nullable', 'string', 'max:120'],
            'items.*.*.*.is_active' => ['nullable', 'boolean'],
        ]);

        LandingPageSection::query()->updateOrCreate(
            ['key' => 'settings'],
            [
                'label' => 'Paramètres visuels',
                'title' => 'Paramètres visuels',
                'subtitle' => 'Couleurs de la landing publique',
                'body' => null,
                'is_active' => true,
                'sort_order' => 1,
                'meta' => [
                    'primary_color' => $attributes['primary_color'] ?: '#183447',
                    'secondary_color' => $attributes['secondary_color'] ?: '#256f8f',
                    'accent_color' => $attributes['accent_color'] ?: '#ff0068',
                ],
            ],
        );

        ApplicationContentBlock::query()
            ->whereNull('application_id')
            ->where('page_key', 'public_landing')
            ->where('block_key', 'custom_page')
            ->update(['status' => 'inactive']);

        foreach ($this->sections() as $key => $definition) {
            if ($key === 'settings') {
                continue;
            }

            $sectionInput = $attributes['sections'][$key] ?? [];
            $meta = [];

            foreach ($definition['meta_fields'] as $field => $fieldDefinition) {
                $meta[$field] = trim((string) Arr::get($sectionInput, "meta.$field", $fieldDefinition['default'] ?? ''));
            }

            $section = LandingPageSection::query()->updateOrCreate(
                ['key' => $key],
                [
                    'label' => $definition['label'],
                    'title' => trim((string) ($sectionInput['title'] ?? $definition['title'])),
                    'subtitle' => trim((string) ($sectionInput['subtitle'] ?? $definition['subtitle'])),
                    'body' => $this->normalizeSectionBody($key, (string) ($sectionInput['body'] ?? $definition['body'])),
                    'is_active' => ! empty($sectionInput['is_active']),
                    'sort_order' => $definition['sort_order'],
                    'meta' => $meta,
                ],
            );

            $section->items()->delete();

            foreach ($definition['item_groups'] as $groupKey => $group) {
                foreach (($attributes['items'][$key][$groupKey] ?? []) as $index => $itemInput) {
                    $itemInput = collect($itemInput)
                        ->map(fn ($value) => is_string($value) ? trim($value) : $value)
                        ->all();

                    if ($key === 'partners' && $groupKey === 'items') {
                        $existingUrl = trim((string) ($itemInput['existing_url'] ?? ''));
                        $itemInput['url'] = $existingUrl !== '' ? $existingUrl : ($itemInput['url'] ?? null);

                        if ($request->hasFile("items.$key.$groupKey.$index.url_file")) {
                            if ($existingUrl !== '' && str_starts_with($existingUrl, 'landing/')) {
                                $wasabiService->deleteFile($existingUrl);
                            }

                            $itemInput['url'] = $wasabiService->uploadFile(
                                $request->file("items.$key.$groupKey.$index.url_file"),
                                config('wasabi.landing_partner_logo_directory', 'landing/partners'),
                                'landing-partner-logo'
                            );
                        }
                    }

                    if ($key === 'page_tv' && $groupKey === 'videos') {
                        $existingUrl = trim((string) ($itemInput['existing_url'] ?? ''));
                        $itemInput['url'] = $existingUrl !== '' ? $existingUrl : ($itemInput['url'] ?? null);

                        if ($request->hasFile("items.$key.$groupKey.$index.video_file")) {
                            if ($existingUrl !== '' && str_starts_with($existingUrl, 'landing/')) {
                                $wasabiService->deleteFile($existingUrl);
                            }

                            $itemInput['url'] = $wasabiService->uploadFile(
                                $request->file("items.$key.$groupKey.$index.video_file"),
                                config('wasabi.landing_video_directory', 'landing/videos'),
                                'landing-video'
                            );
                        }
                    }

                    if (! $this->hasUsefulItemValue($itemInput, $group['columns'])) {
                        continue;
                    }

                    $section->items()->create([
                        'item_key' => $groupKey,
                        'title' => $itemInput['title'] ?? null,
                        'subtitle' => $itemInput['subtitle'] ?? null,
                        'body' => $itemInput['body'] ?? null,
                        'icon' => $itemInput['icon'] ?? null,
                        'url' => $itemInput['url'] ?? null,
                        'value' => $itemInput['value'] ?? null,
                        'is_active' => ! array_key_exists('is_active', $itemInput) || ! empty($itemInput['is_active']),
                        'sort_order' => ((int) $index) + 1,
                    ]);
                }
            }
        }

        return redirect()
            ->route('super-admin.landing-page.edit')
            ->with('success', 'Les sections de la landing page ont ete mises a jour.');
    }

    private function settingsSection(?LandingPageSection $settings): object
    {
        return (object) [
            'meta' => $settings?->meta ?? [
                'primary_color' => '#183447',
                'secondary_color' => '#256f8f',
                'accent_color' => '#ff0068',
            ],
        ];
    }

    private function sectionForForm(string $key, array $definition, ?LandingPageSection $storedSection): array
    {
        $storedItems = $storedSection?->items?->groupBy('item_key') ?? collect();

        foreach ($definition['item_groups'] as $groupKey => $group) {
            $items = $storedItems->has($groupKey)
                ? $storedItems->get($groupKey)->map(fn ($item): array => $item->only(['title', 'subtitle', 'body', 'icon', 'url', 'value', 'is_active']))->values()->all()
                : $group['items'];

            $definition['item_groups'][$groupKey]['items'] = array_merge($items, $this->emptyRows($group['empty_rows'] ?? 1));
        }

        return $definition + [
            'key' => $key,
            'title_value' => old("sections.$key.title", $storedSection->title ?? $definition['title']),
            'subtitle_value' => old("sections.$key.subtitle", $storedSection->subtitle ?? $definition['subtitle']),
            'body_value' => $this->bodyForForm($key, old("sections.$key.body", $storedSection->body ?? $definition['body'])),
            'is_active_value' => old("sections.$key.is_active", $storedSection ? $storedSection->is_active : true),
            'meta_value' => array_merge($definition['meta_defaults'], $storedSection?->meta ?? []),
        ];
    }

    private function hasUsefulItemValue(array $itemInput, array $columns): bool
    {
        foreach (array_keys($columns) as $field) {
            if (filled($itemInput[$field] ?? null)) {
                return true;
            }
        }

        return false;
    }

    private function normalizeSectionBody(string $key, string $body): string
    {
        $body = trim($body);

        if (in_array($key, ['page_about', 'page_contact', 'page_tv', 'page_terms', 'page_privacy'], true)) {
            return html_entity_decode($body, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return $body;
    }

    private function bodyForForm(string $key, mixed $body): string
    {
        $body = (string) $body;

        if (in_array($key, ['page_about', 'page_contact', 'page_tv', 'page_terms', 'page_privacy'], true)) {
            return html_entity_decode($body, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return $body;
    }

    private function emptyRows(int $count): array
    {
        return array_fill(0, $count, [
            'title' => null,
            'subtitle' => null,
            'body' => null,
            'icon' => null,
            'url' => null,
            'value' => null,
            'is_active' => true,
        ]);
    }

    private function publicPages(): array
    {
        return [
            'page_about' => ['label' => 'Qui sommes-nous ?', 'route' => 'public.pages.about'],
            'page_tv' => ['label' => 'My-Signal TV', 'route' => 'public.pages.tv'],
            'page_faq' => ['label' => 'FAQ', 'route' => 'public.pages.faq'],
            'page_contact' => ['label' => 'Contactez-nous', 'route' => 'public.pages.contact'],
            'page_terms' => ['label' => 'Conditions générales d’utilisation', 'route' => 'public.pages.terms'],
            'page_privacy' => ['label' => 'Politique de confidentialité', 'route' => 'public.pages.privacy'],
        ];
    }

    private function sections(): array
    {
        return [
            'settings' => [
                'label' => 'Paramètres visuels',
                'title' => 'Paramètres visuels',
                'subtitle' => null,
                'body' => null,
                'meta_defaults' => [],
                'meta_fields' => [],
                'item_groups' => [],
                'sort_order' => 1,
            ],
            'navigation' => [
                'label' => 'Menu principal',
                'title' => 'My-Signal',
                'subtitle' => 'Liens affichés dans le menu',
                'body' => null,
                'meta_defaults' => ['cta_label' => 'Se connecter et signaler maintenant'],
                'meta_fields' => ['cta_label' => ['label' => 'Libellé du bouton principal', 'default' => 'Se connecter et signaler maintenant']],
                'item_groups' => [
                    'links' => [
                        'label' => 'Liens du menu',
                        'columns' => ['title' => 'Libellé', 'url' => 'Lien'],
                        'items' => [
                            ['title' => 'Accueil', 'url' => '/', 'is_active' => true],
                            ['title' => 'Qui sommes-nous ?', 'url' => '/qui-sommes-nous', 'is_active' => true],
                            ['title' => 'Nos domaines', 'url' => '/#domains', 'is_active' => true],
                            ['title' => 'Fonctionnalités', 'url' => '/#features', 'is_active' => true],
                            ['title' => 'My-Signal TV', 'url' => '/my-signal-tv', 'is_active' => true],
                            ['title' => 'FAQ', 'url' => '/faq', 'is_active' => true],
                            ['title' => 'Contactez-nous', 'url' => '/contactez-nous', 'is_active' => true],
                        ],
                        'empty_rows' => 2,
                    ],
                ],
                'sort_order' => 10,
            ],
            'page_about' => [
                'label' => 'Page - Qui sommes-nous ?',
                'title' => 'Qui sommes-nous ?',
                'subtitle' => 'My-Signal',
                'body' => "<p>My-Signal accompagne les consommateurs, les unités partenaires et les institutions dans le signalement, le suivi et la résolution des difficultés liées aux services du quotidien.</p>",
                'meta_defaults' => ['icon' => 'bi-people-fill'],
                'meta_fields' => ['icon' => ['label' => 'Icône Bootstrap', 'default' => 'bi-people-fill']],
                'item_groups' => [],
                'sort_order' => 11,
            ],
            'page_tv' => [
                'label' => 'Page - My-Signal TV',
                'title' => 'My-Signal TV',
                'subtitle' => 'Vidéos et informations',
                'body' => "Retrouvez ici les contenus vidéo, les campagnes d'information et les annonces importantes autour de My-Signal.",
                'meta_defaults' => ['icon' => 'bi-play-btn-fill', 'video_url' => ''],
                'meta_fields' => [
                    'icon' => ['label' => 'Icône Bootstrap', 'default' => 'bi-play-btn-fill'],
                ],
                'item_groups' => [
                    'videos' => [
                        'label' => 'Vidéos par catégorie',
                        'columns' => ['title' => 'Titre', 'value' => 'Catégorie', 'body' => 'Description', 'url' => 'Fichier vidéo'],
                        'items' => [
                            ['title' => 'Présentation My-Signal', 'value' => 'Présentation', 'body' => 'Vidéo de présentation de la plateforme.', 'url' => null, 'is_active' => true],
                        ],
                        'empty_rows' => 3,
                    ],
                ],
                'sort_order' => 14,
            ],
            'page_faq' => [
                'label' => 'Page - FAQ',
                'title' => 'FAQ',
                'subtitle' => 'Questions fréquentes',
                'body' => "Les réponses aux questions les plus courantes sur le compte UP, les signalements, les notifications, les réductions et les espaces partenaires.",
                'meta_defaults' => ['icon' => 'bi-question-circle-fill'],
                'meta_fields' => ['icon' => ['label' => 'Icône Bootstrap', 'default' => 'bi-question-circle-fill']],
                'item_groups' => [
                    'questions' => [
                        'label' => 'Questions / réponses',
                        'columns' => ['title' => 'Question', 'body' => 'Réponse'],
                        'items' => [
                            ['title' => 'Comment fonctionne My-Signal ?', 'body' => 'Le SA peut personnaliser cette réponse depuis le backoffice.', 'is_active' => true],
                        ],
                        'empty_rows' => 5,
                    ],
                ],
                'sort_order' => 15,
            ],
            'page_contact' => [
                'label' => 'Page - Contactez-nous',
                'title' => 'Contactez-nous',
                'subtitle' => 'Besoin d’aide ?',
                'body' => "L'équipe My-Signal reste disponible pour vous orienter, vous accompagner ou recevoir vos demandes d'information.",
                'meta_defaults' => ['icon' => 'bi-envelope-paper-fill', 'email' => 'contact@my-signal.online', 'phone' => '', 'address' => ''],
                'meta_fields' => [
                    'icon' => ['label' => 'Icône Bootstrap', 'default' => 'bi-envelope-paper-fill'],
                    'email' => ['label' => 'Email', 'default' => 'contact@my-signal.online'],
                    'phone' => ['label' => 'Téléphone', 'default' => ''],
                    'address' => ['label' => 'Adresse', 'default' => ''],
                ],
                'item_groups' => [],
                'sort_order' => 16,
            ],
            'page_terms' => [
                'label' => 'Page - CGU',
                'title' => 'Conditions générales d’utilisation',
                'subtitle' => 'Cadre d’utilisation',
                'body' => '<p>Renseignez ici les conditions générales d’utilisation de My-Signal.</p>',
                'meta_defaults' => ['icon' => 'bi-file-earmark-text-fill'],
                'meta_fields' => ['icon' => ['label' => 'Icône Bootstrap', 'default' => 'bi-file-earmark-text-fill']],
                'item_groups' => [],
                'sort_order' => 17,
            ],
            'page_privacy' => [
                'label' => 'Page - Politique de confidentialité',
                'title' => 'Politique de confidentialité',
                'subtitle' => 'Protection des données',
                'body' => '<p>Renseignez ici la politique de confidentialité et de protection des données personnelles.</p>',
                'meta_defaults' => ['icon' => 'bi-shield-lock-fill'],
                'meta_fields' => ['icon' => ['label' => 'Icône Bootstrap', 'default' => 'bi-shield-lock-fill']],
                'item_groups' => [],
                'sort_order' => 18,
            ],
            'hero' => [
                'label' => 'Hero',
                'title' => 'Signalez, suivez et faites valoir vos droits',
                'subtitle' => 'Plateforme de protection consommateur',
                'body' => 'My-Signal accompagne les consommateurs et les Unités Partenaires dans le suivi des signalements, des abonnements, des REX et des dossiers traités.',
                'meta_defaults' => ['primary_button' => 'Signalement maintenant', 'secondary_button' => 'Voir le parcours'],
                'meta_fields' => [
                    'primary_button' => ['label' => 'Bouton principal', 'default' => 'Signalement maintenant'],
                    'secondary_button' => ['label' => 'Bouton secondaire', 'default' => 'Voir le parcours'],
                ],
                'item_groups' => [
                    'stats' => [
                        'label' => 'Statistiques du hero',
                        'columns' => ['value' => 'Valeur', 'title' => 'Libellé'],
                        'items' => [
                            ['value' => '573K+', 'title' => 'Utilisateurs actifs', 'is_active' => true],
                            ['value' => '26,675', 'title' => 'Signalements suivis', 'is_active' => true],
                            ['value' => '9.2K', 'title' => 'Retours collectés', 'is_active' => true],
                        ],
                        'empty_rows' => 1,
                    ],
                ],
                'sort_order' => 20,
            ],
            'feature_strip' => [
                'label' => 'Bande des avantages',
                'title' => 'Avantages rapides',
                'subtitle' => null,
                'body' => null,
                'meta_defaults' => [],
                'meta_fields' => [],
                'item_groups' => [
                    'items' => [
                        'label' => 'Avantages',
                        'columns' => ['title' => 'Titre', 'body' => 'Description', 'icon' => 'Icône'],
                        'items' => [
                            ['title' => 'Signalement rapide', 'body' => 'Déposez un dommage ou une réclamation en quelques étapes claires.', 'icon' => 'bi-lightning-charge-fill', 'is_active' => true],
                            ['title' => 'Espace sécurisé', 'body' => 'Vos dossiers, abonnements et retours restent accessibles depuis votre compte.', 'icon' => 'bi-shield-fill-check', 'is_active' => true],
                            ['title' => 'Suivi lisible', 'body' => "Consultez l'état de vos signalements, dossiers et traitements.", 'icon' => 'bi-bar-chart-fill', 'is_active' => true],
                            ['title' => 'Dialogue UP', 'body' => "Les Unités Partenaires disposent d'un espace pour traiter les demandes.", 'icon' => 'bi-people-fill', 'is_active' => true],
                        ],
                        'empty_rows' => 2,
                    ],
                ],
                'sort_order' => 30,
            ],
            'manage' => [
                'label' => 'Pourquoi My-Signal',
                'title' => 'Un parcours clair pour signaler et suivre un problème',
                'subtitle' => 'Pourquoi My-Signal ?',
                'body' => "My-Signal transforme chaque signalement en dossier lisible : les faits sont collectés, transmis au bon interlocuteur, suivis jusqu'au traitement, puis enrichis par un retour d'expérience.",
                'meta_defaults' => ['button' => 'Signaler maintenant'],
                'meta_fields' => ['button' => ['label' => 'Bouton', 'default' => 'Signaler maintenant']],
                'item_groups' => [
                    'items' => [
                        'label' => 'Étapes du processus',
                        'columns' => ['title' => 'Étape', 'body' => 'Description', 'icon' => 'Icône'],
                        'items' => [
                            ['title' => 'Décrire le problème', 'body' => 'Le consommateur renseigne les faits, le lieu, les preuves et les informations utiles.', 'icon' => 'bi-pencil-square', 'is_active' => true],
                            ['title' => 'Transmettre le signalement', 'body' => "My-Signal structure la demande et l'oriente vers le bon circuit de traitement.", 'icon' => 'bi-send-check', 'is_active' => true],
                            ['title' => "Suivre l'avancement", 'body' => 'Chaque changement de statut reste visible dans un espace clair et sécurisé.', 'icon' => 'bi-activity', 'is_active' => true],
                            ['title' => 'Clôturer avec retour', 'body' => "Une fois le dossier traité, le consommateur peut partager son retour d'expérience.", 'icon' => 'bi-chat-square-heart', 'is_active' => true],
                        ],
                        'empty_rows' => 2,
                    ],
                ],
                'sort_order' => 40,
            ],
            'share' => [
                'label' => 'Signalement guide',
                'title' => 'Déclarez un dommage et gardez la trace',
                'subtitle' => 'Signalement guide',
                'body' => 'Le consommateur peut suivre chaque étape : dépôt, traitement, résolution, dossier ouvert et retour d’expérience après la prise en charge.',
                'meta_defaults' => ['button' => 'Commencer'],
                'meta_fields' => ['button' => ['label' => 'Bouton', 'default' => 'Commencer']],
                'item_groups' => [
                    'cards' => [
                        'label' => 'Cartes',
                        'columns' => ['title' => 'Titre', 'body' => 'Description', 'icon' => 'Icône'],
                        'items' => [
                            ['title' => 'Dépôt simplifié', 'body' => 'Un parcours clair pour signaler.', 'icon' => '🔗', 'is_active' => true],
                            ['title' => 'Dossier protégé', 'body' => 'Accès depuis votre espace.', 'icon' => '🔒', 'is_active' => true],
                        ],
                        'empty_rows' => 2,
                    ],
                ],
                'sort_order' => 50,
            ],
            'access_banner' => [
                'label' => 'Bannière accès',
                'title' => 'Accédez à votre espace My-Signal',
                'subtitle' => 'Disponible en ligne',
                'body' => 'Activez votre abonnement, suivez vos signalements et retrouvez votre carte membre depuis votre profil.',
                'meta_defaults' => [],
                'meta_fields' => [],
                'item_groups' => [
                    'buttons' => [
                        'label' => 'Types d’usagers publics',
                        'columns' => ['title' => 'Titre', 'subtitle' => 'Sur-titre', 'icon' => 'Icône'],
                        'items' => [
                            ['title' => 'Particulier', 'subtitle' => 'Usager public', 'icon' => 'bi-person', 'is_active' => true],
                            ['title' => 'Entreprises, institutions', 'subtitle' => 'Usager public entreprise', 'icon' => 'bi-building', 'is_active' => true],
                            ['title' => 'Auto-entrepreneur', 'subtitle' => 'Travailleur indépendant', 'icon' => 'bi-person-workspace', 'is_active' => true],
                        ],
                        'empty_rows' => 2,
                    ],
                ],
                'sort_order' => 60,
            ],
            'app_features' => [
                'label' => 'Fonctionnalités',
                'title' => 'Fonctionnalités My-Signal',
                'subtitle' => 'Ce que My-Signal couvre',
                'body' => 'Un parcours pensé pour signaler, suivre, renouveler son abonnement et donner un retour après résolution.',
                'meta_defaults' => [],
                'meta_fields' => [],
                'item_groups' => [
                    'items' => [
                        'label' => 'Fonctionnalités',
                        'columns' => ['title' => 'Titre', 'body' => 'Description', 'icon' => 'Icône'],
                        'items' => [
                            ['title' => 'Signalements encadrés', 'body' => 'Les consommateurs déclarent les dommages avec les informations utiles au traitement.', 'icon' => 'bi-people', 'is_active' => true],
                            ['title' => 'Notifications utiles', 'body' => 'Les UP sont prévenues avant expiration et gardent la main sur leur renouvellement.', 'icon' => 'bi-headset', 'is_active' => true],
                            ['title' => 'Historique complet', 'body' => 'Abonnements, statuts et REX restent consultables dans les espaces dédiés.', 'icon' => 'bi-graph-up-arrow', 'is_active' => true],
                            ['title' => 'Renouvellement manuel', 'body' => "Le statut d'abonnement reste visible, avec une période de grâce d'une journée.", 'icon' => 'bi-calendar-check', 'is_active' => true],
                            ['title' => 'Carte membre', 'body' => "Les membres actifs disposent d'une carte virtuelle avec QR code sur leur profil.", 'icon' => 'bi-cloud-check', 'is_active' => true],
                            ['title' => 'Paramétrage SA', 'body' => 'Le Super Administrateur configure les plans, les modules, les historiques et les accès.', 'icon' => 'bi-puzzle', 'is_active' => true],
                        ],
                        'empty_rows' => 2,
                    ],
                ],
                'sort_order' => 70,
            ],
            'process' => [
                'label' => 'Parcours de traitement',
                'title' => 'Parcours de traitement',
                'subtitle' => 'Comment ça marche',
                'body' => 'Un circuit simple pour déclarer, suivre, résoudre et capitaliser les retours d’expérience.',
                'meta_defaults' => [],
                'meta_fields' => [],
                'item_groups' => [
                    'steps' => [
                        'label' => 'Étapes',
                        'columns' => ['title' => 'Titre', 'body' => 'Description'],
                        'items' => [
                            ['title' => 'Dépôt du signalement', 'body' => 'Le consommateur renseigne son dommage et garde une trace dans son espace personnel.', 'is_active' => true],
                            ['title' => 'Traitement du dossier', 'body' => "L'UP suit les demandes, gère son abonnement et consulte les informations utiles.", 'is_active' => true],
                            ['title' => 'Résolution et REX', 'body' => "Après résolution ou traitement, le consommateur partage son retour d'expérience.", 'is_active' => true],
                        ],
                        'empty_rows' => 1,
                    ],
                    'legend' => [
                        'label' => 'Légende du graphique',
                        'columns' => ['title' => 'Libellé'],
                        'items' => [
                            ['title' => 'Signalement', 'is_active' => true],
                            ['title' => 'Abonnement', 'is_active' => true],
                            ['title' => 'Traitement', 'is_active' => true],
                            ['title' => 'REX', 'is_active' => true],
                        ],
                        'empty_rows' => 1,
                    ],
                ],
                'sort_order' => 90,
            ],
            'stats' => [
                'label' => 'Statistiques',
                'title' => 'Statistiques',
                'subtitle' => null,
                'body' => null,
                'meta_defaults' => [],
                'meta_fields' => [],
                'item_groups' => [
                    'items' => [
                        'label' => 'Statistiques',
                        'columns' => ['value' => 'Valeur', 'title' => 'Libellé'],
                        'items' => [
                            ['value' => '10K+', 'title' => 'Consommateurs accompagnés', 'is_active' => true],
                            ['value' => '245', 'title' => 'Dossiers traités', 'is_active' => true],
                            ['value' => '45+', 'title' => 'UP abonnées', 'is_active' => true],
                            ['value' => '12+', 'title' => 'Modules actifs', 'is_active' => true],
                        ],
                        'empty_rows' => 2,
                    ],
                ],
                'sort_order' => 100,
            ],
            'faq' => [
                'label' => 'FAQ',
                'title' => 'Comprendre My-Signal',
                'subtitle' => 'Questions fréquentes',
                'body' => 'Les points essentiels sur l’abonnement, le signalement, la carte membre et les REX.',
                'meta_defaults' => [],
                'meta_fields' => [],
                'item_groups' => [
                    'questions' => [
                        'label' => 'Questions / réponses',
                        'columns' => ['title' => 'Question', 'body' => 'Réponse'],
                        'items' => [
                            ['title' => 'Comment activer mon espace My-Signal ?', 'body' => "Créez votre compte, connectez-vous, puis suivez l'invitation d'abonnement. L'activation vous donne accès aux fonctions liées à votre profil.", 'is_active' => true],
                            ['title' => 'Le renouvellement est-il automatique ?', 'body' => "Non. Le renouvellement est manuel. Une notification est envoyée avant l'expiration, avec une période de grâce d'un jour.", 'is_active' => true],
                            ['title' => "Quand puis-je faire un retour d'expérience ?", 'body' => 'Le REX est proposé après la résolution d’un dommage ou après le traitement d’un dossier ouvert, si le module est autorisé.', 'is_active' => true],
                            ['title' => 'Qui peut obtenir la carte membre ?', 'body' => "Les membres éligibles avec un abonnement actif disposent d'une carte virtuelle visible dans leur profil, avec QR code.", 'is_active' => true],
                        ],
                        'empty_rows' => 2,
                    ],
                ],
                'sort_order' => 110,
            ],
            'testimonials' => [
                'label' => 'Retours d’expérience',
                'title' => 'Ce que les utilisateurs peuvent partager',
                'subtitle' => 'Retours d’expérience',
                'body' => null,
                'meta_defaults' => [],
                'meta_fields' => [],
                'item_groups' => [
                    'items' => [
                        'label' => 'Témoignages',
                        'columns' => ['body' => 'Texte', 'title' => 'Auteur', 'subtitle' => 'Rôle', 'icon' => 'Avatar'],
                        'items' => [
                            ['body' => "Le suivi m'a permis de savoir exactement où en était mon signalement et quand mon dossier a été traité.", 'title' => 'Consommateur', 'subtitle' => 'Signalement résolu', 'icon' => '👩', 'is_active' => true],
                            ['body' => "Les notifications d'expiration et l'historique des abonnements rendent la gestion plus claire pour notre équipe.", 'title' => 'Unité Partenaire', 'subtitle' => 'Abonnement actif', 'icon' => '👨', 'is_active' => true],
                            ['body' => "Après traitement de mon dossier, j'ai pu laisser un REX simple sur le délai, la communication et la qualité de prise en charge.", 'title' => 'Membre consommateur', 'subtitle' => 'REX après dossier', 'icon' => '👩', 'is_active' => true],
                        ],
                        'empty_rows' => 1,
                    ],
                ],
                'sort_order' => 120,
            ],
            'cta' => [
                'label' => 'Appel à action',
                'title' => 'Prêt à suivre vos signalements autrement ?',
                'subtitle' => null,
                'body' => 'My-Signal rassemble le signalement, le suivi, l’abonnement annuel, la carte membre et les retours d’expérience dans un même parcours.',
                'meta_defaults' => ['button' => 'Activer mon espace'],
                'meta_fields' => ['button' => ['label' => 'Bouton', 'default' => 'Activer mon espace']],
                'item_groups' => [],
                'sort_order' => 130,
            ],
            'clients' => [
                'label' => 'Domaines couverts',
                'title' => 'Domaines couverts',
                'subtitle' => null,
                'body' => 'My-Signal accompagne plusieurs univers de consommation et de services avec un parcours de signalement adapté à chaque situation.',
                'meta_defaults' => [],
                'meta_fields' => [],
                'item_groups' => [
                    'items' => [
                        'label' => 'Domaines',
                        'columns' => ['title' => 'Domaine', 'body' => 'Texte explicatif', 'icon' => 'Image'],
                        'items' => [
                            ['title' => 'Commerce', 'body' => 'Signaler une pratique commerciale confuse, un service non conforme ou un litige après achat.', 'icon' => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=900&q=80', 'is_active' => true],
                            ['title' => 'Services', 'body' => 'Suivre une demande liée à un prestataire, une intervention ou une qualité de service attendue.', 'icon' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=900&q=80', 'is_active' => true],
                            ['title' => 'Assurance', 'body' => 'Documenter un dossier, garder les preuves et suivre les réponses obtenues.', 'icon' => 'https://images.unsplash.com/photo-1450101499163-c8848c66ca85?auto=format&fit=crop&w=900&q=80', 'is_active' => true],
                            ['title' => 'Transport', 'body' => 'Déclarer une difficulté de transport, un retard, une prestation ou un incident de parcours.', 'icon' => 'https://images.unsplash.com/photo-1494412651409-8963ce7935a7?auto=format&fit=crop&w=900&q=80', 'is_active' => true],
                            ['title' => 'Santé', 'body' => 'Centraliser les informations utiles pour suivre une réclamation ou une expérience de prise en charge.', 'icon' => 'https://images.unsplash.com/photo-1505751172876-fa1923c5c528?auto=format&fit=crop&w=900&q=80', 'is_active' => true],
                            ['title' => 'Énergie', 'body' => 'Signaler une coupure, une surtension, un compteur ou tout incident lié à la fourniture.', 'icon' => 'https://images.unsplash.com/photo-1509391366360-2e959784a276?auto=format&fit=crop&w=900&q=80', 'is_active' => true],
                        ],
                        'empty_rows' => 2,
                    ],
                ],
                'sort_order' => 150,
            ],
            'partners' => [
                'label' => 'Partenaires',
                'title' => 'Ils nous font confiance',
                'subtitle' => 'Partenaires',
                'body' => 'Des acteurs publics, privés et communautaires s’appuient sur My-Signal pour rendre le traitement des signalements plus lisible.',
                'meta_defaults' => [],
                'meta_fields' => [],
                'item_groups' => [
                    'items' => [
                        'label' => 'Logos partenaires',
                        'columns' => ['title' => 'Nom', 'url' => 'Logo', 'icon' => 'Sigle de secours'],
                        'items' => [
                            ['title' => 'ACEPEN', 'url' => null, 'icon' => 'AC', 'is_active' => true],
                            ['title' => 'MON NRJ', 'url' => null, 'icon' => 'NRJ', 'is_active' => true],
                            ['title' => 'MON EAU', 'url' => null, 'icon' => 'EAU', 'is_active' => true],
                            ['title' => 'CITOYENS', 'url' => null, 'icon' => 'CT', 'is_active' => true],
                            ['title' => 'SERVICES CI', 'url' => null, 'icon' => 'SCI', 'is_active' => true],
                            ['title' => 'COLLECTIVITÉS', 'url' => null, 'icon' => 'COL', 'is_active' => true],
                            ['title' => 'RÉSEAUX', 'url' => null, 'icon' => 'RX', 'is_active' => true],
                            ['title' => 'ASSISTANCE', 'url' => null, 'icon' => 'AST', 'is_active' => true],
                            ['title' => 'MÉDIATION', 'url' => null, 'icon' => 'MED', 'is_active' => true],
                            ['title' => 'OBSERVATOIRE', 'url' => null, 'icon' => 'OBS', 'is_active' => true],
                        ],
                        'empty_rows' => 2,
                    ],
                ],
                'sort_order' => 155,
            ],
            'footer' => [
                'label' => 'Footer',
                'title' => 'My-Signal',
                'subtitle' => 'Plateforme de protection consommateur',
                'body' => "La plateforme qui facilite le signalement, le suivi des dossiers, l'abonnement annuel des UP et les retours d'expérience.",
                'meta_defaults' => [
                    'column_1_title' => 'My-Signal',
                    'column_2_title' => 'Modules',
                    'column_3_title' => 'Légal',
                    'newsletter_title' => 'Alertes',
                    'newsletter_text' => 'Recevez les informations importantes sur les modules My-Signal.',
                ],
                'meta_fields' => [
                    'column_1_title' => ['label' => 'Titre colonne 1', 'default' => 'My-Signal'],
                    'column_2_title' => ['label' => 'Titre colonne 2', 'default' => 'Modules'],
                    'column_3_title' => ['label' => 'Titre colonne 3', 'default' => 'Légal'],
                    'newsletter_title' => ['label' => 'Titre newsletter', 'default' => 'Alertes'],
                    'newsletter_text' => ['label' => 'Texte newsletter', 'default' => 'Recevez les informations importantes sur les modules My-Signal.'],
                ],
                'item_groups' => [
                    'column_1_links' => [
                        'label' => 'Liens colonne 1',
                        'columns' => ['title' => 'Libellé', 'url' => 'Lien'],
                        'items' => [
                            ['title' => 'À propos', 'url' => '#', 'is_active' => true],
                            ['title' => 'Protection consommateur', 'url' => '#', 'is_active' => true],
                            ['title' => 'Unités Partenaires', 'url' => '#', 'is_active' => true],
                            ['title' => 'Contact', 'url' => '#', 'is_active' => true],
                        ],
                        'empty_rows' => 2,
                    ],
                    'column_2_links' => [
                        'label' => 'Liens colonne 2',
                        'columns' => ['title' => 'Libellé', 'url' => 'Lien'],
                        'items' => [
                            ['title' => 'Fonctionnalités', 'url' => '#features', 'is_active' => true],
                            ['title' => 'FAQ', 'url' => '#faq', 'is_active' => true],
                            ['title' => 'REX', 'url' => '#testimonials', 'is_active' => true],
                            ['title' => 'Domaines couverts', 'url' => '#domains', 'is_active' => true],
                        ],
                        'empty_rows' => 2,
                    ],
                    'column_3_links' => [
                        'label' => 'Liens colonne 3',
                        'columns' => ['title' => 'Libellé', 'url' => 'Lien'],
                        'items' => [
                            ['title' => 'Conditions générales d’utilisation', 'url' => '/conditions-generales-utilisation', 'is_active' => true],
                            ['title' => 'Politique de confidentialité', 'url' => '/politique-confidentialite', 'is_active' => true],
                            ['title' => 'Contact', 'url' => '/contactez-nous', 'is_active' => true],
                        ],
                        'empty_rows' => 2,
                    ],
                ],
                'sort_order' => 160,
            ],
        ];
    }
}
