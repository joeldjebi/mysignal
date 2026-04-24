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
                            ['title' => 'FAQ', 'url' => '#faq', 'is_active' => true],
                            ['title' => 'Domaines', 'url' => '#domains', 'is_active' => true],
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
                'title' => 'Un parcours clair pour signaler et suivre un probleme',
                'subtitle' => 'Pourquoi MySignal ?',
                'body' => "MySignal transforme chaque signalement en dossier lisible: les faits sont collectes, transmis au bon interlocuteur, suivis jusqu'au traitement, puis enrichis par un retour d'experience.",
                'meta_defaults' => ['button' => 'Signaler maintenant'],
                'meta_fields' => ['button' => ['label' => 'Bouton', 'default' => 'Signaler maintenant']],
                'item_groups' => [
                    'items' => [
                        'label' => 'Etapes du processus',
                        'columns' => ['title' => 'Etape', 'body' => 'Description', 'icon' => 'Icone'],
                        'items' => [
                            ['title' => 'Decrire le probleme', 'body' => 'Le consommateur renseigne les faits, le lieu, les preuves et les informations utiles.', 'icon' => 'bi-pencil-square', 'is_active' => true],
                            ['title' => 'Transmettre le signalement', 'body' => "MySignal structure la demande et l'oriente vers le bon circuit de traitement.", 'icon' => 'bi-send-check', 'is_active' => true],
                            ['title' => "Suivre l'avancement", 'body' => 'Chaque changement de statut reste visible dans un espace clair et securise.', 'icon' => 'bi-activity', 'is_active' => true],
                            ['title' => 'Cloturer avec retour', 'body' => "Une fois le dossier traite, le consommateur peut partager son retour d'experience.", 'icon' => 'bi-chat-square-heart', 'is_active' => true],
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
                        'label' => 'Types d usagers publics',
                        'columns' => ['title' => 'Titre', 'subtitle' => 'Sur-titre', 'icon' => 'Icone'],
                        'items' => [
                            ['title' => 'Particulier', 'subtitle' => 'Usager public', 'icon' => 'bi-person', 'is_active' => true],
                            ['title' => 'Entreprises, institutions', 'subtitle' => 'Usager public entreprise', 'icon' => 'bi-building', 'is_active' => true],
                            ['title' => 'Auto entrepreneur', 'subtitle' => 'Travailleur independant', 'icon' => 'bi-person-workspace', 'is_active' => true],
                        ],
                        'empty_rows' => 2,
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
            'clients' => [
                'label' => 'Domaines couverts',
                'title' => 'Domaines couverts',
                'subtitle' => null,
                'body' => 'MySignal accompagne plusieurs univers de consommation et de services avec un parcours de signalement adapte a chaque situation.',
                'meta_defaults' => [],
                'meta_fields' => [],
                'item_groups' => [
                    'items' => [
                        'label' => 'Domaines',
                        'columns' => ['title' => 'Domaine', 'body' => 'Texte explicatif', 'icon' => 'Image'],
                        'items' => [
                            ['title' => 'Commerce', 'body' => 'Signaler une pratique commerciale confuse, un service non conforme ou un litige apres achat.', 'icon' => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=900&q=80', 'is_active' => true],
                            ['title' => 'Services', 'body' => 'Suivre une demande liee a un prestataire, une intervention ou une qualite de service attendue.', 'icon' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=900&q=80', 'is_active' => true],
                            ['title' => 'Assurance', 'body' => 'Documenter un dossier, garder les preuves et suivre les reponses obtenues.', 'icon' => 'https://images.unsplash.com/photo-1450101499163-c8848c66ca85?auto=format&fit=crop&w=900&q=80', 'is_active' => true],
                            ['title' => 'Transport', 'body' => 'Declarer une difficulte de transport, un retard, une prestation ou un incident de parcours.', 'icon' => 'https://images.unsplash.com/photo-1494412651409-8963ce7935a7?auto=format&fit=crop&w=900&q=80', 'is_active' => true],
                            ['title' => 'Sante', 'body' => 'Centraliser les informations utiles pour suivre une reclamation ou une experience de prise en charge.', 'icon' => 'https://images.unsplash.com/photo-1505751172876-fa1923c5c528?auto=format&fit=crop&w=900&q=80', 'is_active' => true],
                            ['title' => 'Energie', 'body' => 'Signaler une coupure, une surtension, un compteur ou tout incident lie a la fourniture.', 'icon' => 'https://images.unsplash.com/photo-1509391366360-2e959784a276?auto=format&fit=crop&w=900&q=80', 'is_active' => true],
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
                'body' => 'Des acteurs publics, prives et communautaires s appuient sur MySignal pour rendre le traitement des signalements plus lisible.',
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
                            ['title' => 'COLLECTIVITES', 'url' => null, 'icon' => 'COL', 'is_active' => true],
                            ['title' => 'RESEAUX', 'url' => null, 'icon' => 'RX', 'is_active' => true],
                            ['title' => 'ASSISTANCE', 'url' => null, 'icon' => 'AST', 'is_active' => true],
                            ['title' => 'MEDIATION', 'url' => null, 'icon' => 'MED', 'is_active' => true],
                            ['title' => 'OBSERVATOIRE', 'url' => null, 'icon' => 'OBS', 'is_active' => true],
                        ],
                        'empty_rows' => 2,
                    ],
                ],
                'sort_order' => 155,
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
                            ['title' => 'Domaines couverts', 'url' => '#domains', 'is_active' => true],
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
