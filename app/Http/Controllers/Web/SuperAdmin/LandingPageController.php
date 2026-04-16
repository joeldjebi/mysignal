<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ApplicationContentBlock;
use App\Models\LandingPageSection;
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
            'defaultLogoUrl' => asset('image/logo/logo-my-signal.png'),
        ]);
    }

    public function update(Request $request): RedirectResponse
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
            'items.*.*.*.icon' => ['nullable', 'string', 'max:80'],
            'items.*.*.*.url' => ['nullable', 'string', 'max:255'],
            'items.*.*.*.value' => ['nullable', 'string', 'max:120'],
            'items.*.*.*.is_active' => ['nullable', 'boolean'],
        ]);

        LandingPageSection::query()->updateOrCreate(
            ['key' => 'settings'],
            [
                'label' => 'Parametres visuels',
                'title' => 'Parametres visuels',
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
                    'body' => trim((string) ($sectionInput['body'] ?? $definition['body'])),
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
            'body_value' => old("sections.$key.body", $storedSection->body ?? $definition['body']),
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

    private function sections(): array
    {
        return [
            'settings' => [
                'label' => 'Parametres visuels',
                'title' => 'Parametres visuels',
                'subtitle' => null,
                'body' => null,
                'meta_defaults' => [],
                'meta_fields' => [],
                'item_groups' => [],
                'sort_order' => 1,
            ],
            'navigation' => [
                'label' => 'Menu principal',
                'title' => 'MySignal',
                'subtitle' => 'Liens affiches dans le menu',
                'body' => null,
                'meta_defaults' => ['cta_label' => 'Se connecter et signaler maintenant'],
                'meta_fields' => ['cta_label' => ['label' => 'Libelle du bouton principal', 'default' => 'Se connecter et signaler maintenant']],
                'item_groups' => [
                    'links' => [
                        'label' => 'Liens du menu',
                        'columns' => ['title' => 'Libelle', 'url' => 'Lien'],
                        'items' => [
                            ['title' => 'Fonctionnalites', 'url' => '#features', 'is_active' => true],
                            ['title' => 'Apercus', 'url' => '#screenshots', 'is_active' => true],
                            ['title' => 'FAQ', 'url' => '#faq', 'is_active' => true],
                            ['title' => 'Actualites', 'url' => '#news', 'is_active' => true],
                        ],
                        'empty_rows' => 2,
                    ],
                ],
                'sort_order' => 10,
            ],
            'hero' => [
                'label' => 'Hero',
                'title' => 'Signalez, suivez et faites valoir vos droits',
                'subtitle' => 'Plateforme de protection consommateur',
                'body' => 'MySignal accompagne les consommateurs et les Unites Partenaires dans le suivi des signalements, des abonnements, des REX et des dossiers traites.',
                'meta_defaults' => ['primary_button' => 'Activer mon acces', 'secondary_button' => 'Voir le parcours'],
                'meta_fields' => [
                    'primary_button' => ['label' => 'Bouton principal', 'default' => 'Activer mon acces'],
                    'secondary_button' => ['label' => 'Bouton secondaire', 'default' => 'Voir le parcours'],
                ],
                'item_groups' => [
                    'stats' => [
                        'label' => 'Statistiques du hero',
                        'columns' => ['value' => 'Valeur', 'title' => 'Libelle'],
                        'items' => [
                            ['value' => '573K+', 'title' => 'Utilisateurs actifs', 'is_active' => true],
                            ['value' => '26,675', 'title' => 'Signalements suivis', 'is_active' => true],
                            ['value' => '9.2K', 'title' => 'Retours collectes', 'is_active' => true],
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
                        'columns' => ['title' => 'Titre', 'body' => 'Description', 'icon' => 'Icone'],
                        'items' => [
                            ['title' => 'Signalement rapide', 'body' => 'Deposez un dommage ou une reclamation en quelques etapes claires.', 'icon' => 'bi-lightning-charge-fill', 'is_active' => true],
                            ['title' => 'Espace securise', 'body' => 'Vos dossiers, abonnements et retours restent accessibles depuis votre compte.', 'icon' => 'bi-shield-fill-check', 'is_active' => true],
                            ['title' => 'Suivi lisible', 'body' => "Consultez l'etat de vos signalements, dossiers et traitements.", 'icon' => 'bi-bar-chart-fill', 'is_active' => true],
                            ['title' => 'Dialogue UP', 'body' => "Les Unites Partenaires disposent d'un espace pour traiter les demandes.", 'icon' => 'bi-people-fill', 'is_active' => true],
                        ],
                        'empty_rows' => 2,
                    ],
                ],
                'sort_order' => 30,
            ],
            'manage' => [
                'label' => 'Pourquoi MySignal',
                'title' => 'Un seul espace pour suivre vos droits consommateur',
                'subtitle' => 'Pourquoi MySignal ?',
                'body' => 'MySignal centralise les signalements, les dossiers ouverts, les abonnements annuels, les notifications et les retours d experience.',
                'meta_defaults' => ['button' => 'En savoir plus'],
                'meta_fields' => ['button' => ['label' => 'Bouton', 'default' => 'En savoir plus']],
                'item_groups' => [
                    'items' => [
                        'label' => 'Liste des points forts',
                        'columns' => ['title' => 'Element'],
                        'items' => [
                            ['title' => 'Creation et suivi des signalements consommateurs', 'is_active' => true],
                            ['title' => 'Notifications avant expiration des abonnements', 'is_active' => true],
                            ['title' => 'Carte membre virtuelle avec QR code pour les abonnes actifs', 'is_active' => true],
                            ['title' => 'Historique des abonnements et des REX', 'is_active' => true],
                            ['title' => 'Parametrage par le Super Administrateur', 'is_active' => true],
                            ['title' => 'Tableau de bord clair pour les UP et les consommateurs', 'is_active' => true],
                        ],
                        'empty_rows' => 2,
                    ],
                ],
                'sort_order' => 40,
            ],
            'share' => [
                'label' => 'Signalement guide',
                'title' => 'Declarez un dommage et gardez la trace',
                'subtitle' => 'Signalement guide',
                'body' => 'Le consommateur peut suivre chaque etape: depot, traitement, resolution, dossier ouvert et retour d experience apres la prise en charge.',
                'meta_defaults' => ['button' => 'Commencer'],
                'meta_fields' => ['button' => ['label' => 'Bouton', 'default' => 'Commencer']],
                'item_groups' => [
                    'cards' => [
                        'label' => 'Cartes',
                        'columns' => ['title' => 'Titre', 'body' => 'Description', 'icon' => 'Icone'],
                        'items' => [
                            ['title' => 'Depot simplifie', 'body' => 'Un parcours clair pour signaler', 'icon' => '🔗', 'is_active' => true],
                            ['title' => 'Dossier protege', 'body' => 'Acces depuis votre espace', 'icon' => '🔒', 'is_active' => true],
                        ],
                        'empty_rows' => 2,
                    ],
                ],
                'sort_order' => 50,
            ],
            'access_banner' => [
                'label' => 'Banniere acces',
                'title' => 'Accedez a votre espace MySignal',
                'subtitle' => 'Disponible en ligne',
                'body' => 'Activez votre abonnement, suivez vos signalements et retrouvez votre carte membre depuis votre profil.',
                'meta_defaults' => [],
                'meta_fields' => [],
                'item_groups' => [
                    'buttons' => [
                        'label' => 'Boutons',
                        'columns' => ['title' => 'Titre', 'subtitle' => 'Sur-titre', 'icon' => 'Icone'],
                        'items' => [
                            ['title' => 'Consommateur', 'subtitle' => 'Espace', 'icon' => 'bi-person', 'is_active' => true],
                            ['title' => 'Unite Partenaire', 'subtitle' => 'Espace', 'icon' => 'bi-building', 'is_active' => true],
                        ],
                        'empty_rows' => 1,
                    ],
                ],
                'sort_order' => 60,
            ],
            'app_features' => [
                'label' => 'Fonctionnalites',
                'title' => 'Fonctionnalites MySignal',
                'subtitle' => 'Ce que MySignal couvre',
                'body' => 'Un parcours pense pour signaler, suivre, renouveler son abonnement et donner un retour apres resolution.',
                'meta_defaults' => [],
                'meta_fields' => [],
                'item_groups' => [
                    'items' => [
                        'label' => 'Fonctionnalites',
                        'columns' => ['title' => 'Titre', 'body' => 'Description', 'icon' => 'Icone'],
                        'items' => [
                            ['title' => 'Signalements encadres', 'body' => 'Les consommateurs declarent les dommages avec les informations utiles au traitement.', 'icon' => 'bi-people', 'is_active' => true],
                            ['title' => 'Notifications utiles', 'body' => 'Les UP sont prevenues avant expiration et gardent la main sur leur renouvellement.', 'icon' => 'bi-headset', 'is_active' => true],
                            ['title' => 'Historique complet', 'body' => 'Abonnements, statuts et REX restent consultables dans les espaces dedies.', 'icon' => 'bi-graph-up-arrow', 'is_active' => true],
                            ['title' => 'Renouvellement manuel', 'body' => "Le statut d'abonnement reste visible, avec une periode de grace d'une journee.", 'icon' => 'bi-calendar-check', 'is_active' => true],
                            ['title' => 'Carte membre', 'body' => "Les membres actifs disposent d'une carte virtuelle avec QR code sur leur profil.", 'icon' => 'bi-cloud-check', 'is_active' => true],
                            ['title' => 'Parametrage SA', 'body' => 'Le Super Administrateur configure les plans, modules, historiques et acces.', 'icon' => 'bi-puzzle', 'is_active' => true],
                        ],
                        'empty_rows' => 2,
                    ],
                ],
                'sort_order' => 70,
            ],
            'screenshots' => [
                'label' => 'Apercus plateforme',
                'title' => 'Ecrans essentiels',
                'subtitle' => 'Apercu plateforme',
                'body' => 'Un apercu des espaces utiles pour suivre les signalements, abonnements, REX et parametres.',
                'meta_defaults' => [],
                'meta_fields' => [],
                'item_groups' => [
                    'items' => [
                        'label' => 'Apercus',
                        'columns' => ['title' => 'Libelle', 'icon' => 'Icone'],
                        'items' => [
                            ['title' => 'Tableau de bord', 'icon' => '📈', 'is_active' => true],
                            ['title' => 'Abonnements', 'icon' => '👥', 'is_active' => true],
                            ['title' => 'Signalements', 'icon' => '💬', 'is_active' => true],
                            ['title' => 'Parametres', 'icon' => '⚙️', 'is_active' => true],
                        ],
                        'empty_rows' => 2,
                    ],
                ],
                'sort_order' => 80,
            ],
            'process' => [
                'label' => 'Parcours de traitement',
                'title' => 'Parcours de traitement',
                'subtitle' => 'Comment ca marche',
                'body' => 'Un circuit simple pour declarer, suivre, resoudre et capitaliser les retours d experience.',
                'meta_defaults' => [],
                'meta_fields' => [],
                'item_groups' => [
                    'steps' => [
                        'label' => 'Etapes',
                        'columns' => ['title' => 'Titre', 'body' => 'Description'],
                        'items' => [
                            ['title' => 'Depot du signalement', 'body' => 'Le consommateur renseigne son dommage et garde une trace dans son espace personnel.', 'is_active' => true],
                            ['title' => 'Traitement du dossier', 'body' => "L'UP suit les demandes, gere son abonnement et consulte les informations utiles.", 'is_active' => true],
                            ['title' => 'Resolution et REX', 'body' => "Apres resolution ou traitement, le consommateur partage son retour d'experience.", 'is_active' => true],
                        ],
                        'empty_rows' => 1,
                    ],
                    'legend' => [
                        'label' => 'Legende du graphique',
                        'columns' => ['title' => 'Libelle'],
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
                        'columns' => ['value' => 'Valeur', 'title' => 'Libelle'],
                        'items' => [
                            ['value' => '10K+', 'title' => 'Consommateurs accompagnes', 'is_active' => true],
                            ['value' => '245', 'title' => 'Dossiers traites', 'is_active' => true],
                            ['value' => '45+', 'title' => 'UP abonnees', 'is_active' => true],
                            ['value' => '12+', 'title' => 'Modules actifs', 'is_active' => true],
                        ],
                        'empty_rows' => 2,
                    ],
                ],
                'sort_order' => 100,
            ],
            'faq' => [
                'label' => 'FAQ',
                'title' => 'Comprendre MySignal',
                'subtitle' => 'Questions frequentes',
                'body' => 'Les points essentiels sur l abonnement, le signalement, la carte membre et les REX.',
                'meta_defaults' => [],
                'meta_fields' => [],
                'item_groups' => [
                    'questions' => [
                        'label' => 'Questions / reponses',
                        'columns' => ['title' => 'Question', 'body' => 'Reponse'],
                        'items' => [
                            ['title' => 'Comment activer mon espace MySignal ?', 'body' => "Creez votre compte, connectez-vous, puis suivez l'invitation d'abonnement. L'activation vous donne acces aux fonctions liees a votre profil.", 'is_active' => true],
                            ['title' => 'Le renouvellement est-il automatique ?', 'body' => "Non. Le renouvellement est manuel. Une notification est envoyee avant l'expiration, avec une periode de grace d'un jour.", 'is_active' => true],
                            ['title' => "Quand puis-je faire un retour d'experience ?", 'body' => 'Le REX est propose apres la resolution d un dommage ou apres le traitement d un dossier ouvert, si le module est autorise.', 'is_active' => true],
                            ['title' => 'Qui peut obtenir la carte membre ?', 'body' => "Les membres eligibles avec un abonnement actif disposent d'une carte virtuelle visible dans leur profil, avec QR code.", 'is_active' => true],
                        ],
                        'empty_rows' => 2,
                    ],
                ],
                'sort_order' => 110,
            ],
            'testimonials' => [
                'label' => 'Retours d experience',
                'title' => 'Ce que les utilisateurs peuvent partager',
                'subtitle' => 'Retours d experience',
                'body' => null,
                'meta_defaults' => [],
                'meta_fields' => [],
                'item_groups' => [
                    'items' => [
                        'label' => 'Temoignages',
                        'columns' => ['body' => 'Texte', 'title' => 'Auteur', 'subtitle' => 'Role', 'icon' => 'Avatar'],
                        'items' => [
                            ['body' => "Le suivi m'a permis de savoir exactement ou en etait mon signalement et quand mon dossier a ete traite.", 'title' => 'Consommateur', 'subtitle' => 'Signalement resolu', 'icon' => '👩', 'is_active' => true],
                            ['body' => "Les notifications d'expiration et l'historique des abonnements rendent la gestion plus claire pour notre equipe.", 'title' => 'Unite Partenaire', 'subtitle' => 'Abonnement actif', 'icon' => '👨', 'is_active' => true],
                            ['body' => "Apres traitement de mon dossier, j'ai pu laisser un REX simple sur le delai, la communication et la qualite de prise en charge.", 'title' => 'Membre consommateur', 'subtitle' => 'REX apres dossier', 'icon' => '👩', 'is_active' => true],
                        ],
                        'empty_rows' => 1,
                    ],
                ],
                'sort_order' => 120,
            ],
            'cta' => [
                'label' => 'Appel a action',
                'title' => 'Pret a suivre vos signalements autrement ?',
                'subtitle' => null,
                'body' => 'MySignal rassemble le signalement, le suivi, l abonnement annuel, la carte membre et les retours d experience dans un meme parcours.',
                'meta_defaults' => ['button' => 'Activer mon espace'],
                'meta_fields' => ['button' => ['label' => 'Bouton', 'default' => 'Activer mon espace']],
                'item_groups' => [],
                'sort_order' => 130,
            ],
            'news' => [
                'label' => 'Actualites',
                'title' => 'Points forts MySignal',
                'subtitle' => 'Actualites',
                'body' => 'Les modules importants pour la protection consommateur, les UP et l administration.',
                'meta_defaults' => [],
                'meta_fields' => [],
                'item_groups' => [
                    'items' => [
                        'label' => 'Actualites',
                        'columns' => ['subtitle' => 'Tag', 'title' => 'Titre', 'body' => 'Texte', 'icon' => 'Icone', 'value' => 'Date'],
                        'items' => [
                            ['subtitle' => 'Signalement', 'title' => 'Un parcours clair pour declarer un dommage', 'body' => 'Les consommateurs peuvent deposer un signalement et retrouver son evolution dans leur tableau de bord.', 'icon' => '📱', 'value' => '10 avril 2026', 'is_active' => true],
                            ['subtitle' => 'Abonnement', 'title' => 'Un plan annuel parametrable par le SA', 'body' => "Le Super Administrateur gere les plans, les statuts, les notifications et l'historique des UP.", 'icon' => '💡', 'value' => '5 avril 2026', 'is_active' => true],
                            ['subtitle' => 'REX', 'title' => 'Des retours apres resolution ou traitement', 'body' => "Les REX aident a mesurer le delai, la communication, la qualite et l'equite du traitement.", 'icon' => '🚀', 'value' => '28 mars 2026', 'is_active' => true],
                        ],
                        'empty_rows' => 2,
                    ],
                ],
                'sort_order' => 140,
            ],
            'clients' => [
                'label' => 'Domaines couverts',
                'title' => 'Domaines couverts',
                'subtitle' => null,
                'body' => 'MySignal accompagne plusieurs univers de consommation et de services.',
                'meta_defaults' => [],
                'meta_fields' => [],
                'item_groups' => [
                    'items' => [
                        'label' => 'Domaines',
                        'columns' => ['title' => 'Domaine'],
                        'items' => [
                            ['title' => 'COMMERCE', 'is_active' => true],
                            ['title' => 'SERVICES', 'is_active' => true],
                            ['title' => 'ASSURANCE', 'is_active' => true],
                            ['title' => 'TRANSPORT', 'is_active' => true],
                            ['title' => 'SANTE', 'is_active' => true],
                            ['title' => 'ENERGIE', 'is_active' => true],
                        ],
                        'empty_rows' => 2,
                    ],
                ],
                'sort_order' => 150,
            ],
            'footer' => [
                'label' => 'Footer',
                'title' => 'MySignal',
                'subtitle' => 'Plateforme de protection consommateur',
                'body' => "La plateforme qui facilite le signalement, le suivi des dossiers, l'abonnement annuel des UP et les retours d'experience.",
                'meta_defaults' => [
                    'column_1_title' => 'MySignal',
                    'column_2_title' => 'Modules',
                    'column_3_title' => 'Legal',
                    'newsletter_title' => 'Alertes',
                    'newsletter_text' => 'Recevez les informations importantes sur les modules MySignal.',
                ],
                'meta_fields' => [
                    'column_1_title' => ['label' => 'Titre colonne 1', 'default' => 'MySignal'],
                    'column_2_title' => ['label' => 'Titre colonne 2', 'default' => 'Modules'],
                    'column_3_title' => ['label' => 'Titre colonne 3', 'default' => 'Legal'],
                    'newsletter_title' => ['label' => 'Titre newsletter', 'default' => 'Alertes'],
                    'newsletter_text' => ['label' => 'Texte newsletter', 'default' => 'Recevez les informations importantes sur les modules MySignal.'],
                ],
                'item_groups' => [
                    'column_1_links' => [
                        'label' => 'Liens colonne 1',
                        'columns' => ['title' => 'Libelle', 'url' => 'Lien'],
                        'items' => [
                            ['title' => 'A propos', 'url' => '#', 'is_active' => true],
                            ['title' => 'Protection consommateur', 'url' => '#', 'is_active' => true],
                            ['title' => 'Unites Partenaires', 'url' => '#', 'is_active' => true],
                            ['title' => 'Contact', 'url' => '#', 'is_active' => true],
                        ],
                        'empty_rows' => 2,
                    ],
                    'column_2_links' => [
                        'label' => 'Liens colonne 2',
                        'columns' => ['title' => 'Libelle', 'url' => 'Lien'],
                        'items' => [
                            ['title' => 'Fonctionnalites', 'url' => '#features', 'is_active' => true],
                            ['title' => 'FAQ', 'url' => '#faq', 'is_active' => true],
                            ['title' => 'REX', 'url' => '#testimonials', 'is_active' => true],
                            ['title' => 'Carte membre', 'url' => '#screenshots', 'is_active' => true],
                        ],
                        'empty_rows' => 2,
                    ],
                    'column_3_links' => [
                        'label' => 'Liens colonne 3',
                        'columns' => ['title' => 'Libelle', 'url' => 'Lien'],
                        'items' => [
                            ['title' => 'Confidentialite', 'url' => '#', 'is_active' => true],
                            ['title' => "Conditions d'utilisation", 'url' => '#', 'is_active' => true],
                            ['title' => 'Cookies', 'url' => '#', 'is_active' => true],
                            ['title' => 'Donnees personnelles', 'url' => '#', 'is_active' => true],
                        ],
                        'empty_rows' => 2,
                    ],
                ],
                'sort_order' => 160,
            ],
        ];
    }
}
