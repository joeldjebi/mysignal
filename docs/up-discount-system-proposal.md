# Proposition Technique - Cartes De Reduction UP

## Objectif

Mettre en place un systeme de reduction reserve aux usagers publics (`UP`) ayant un abonnement actif, avec :

- une carte de reduction virtuelle visible dans le profil
- un QR code contenant un `uuid` opaque
- des etablissements partenaires disposant d un dashboard web et d une app mobile
- des etablissements partenaires pouvant verifier la carte et appliquer une reduction
- une tracabilite complete des reductions appliquees

## Alignement Avec L Existant

Le projet dispose deja des briques suivantes :

- `public_users` pour les comptes UP
- `subscription_plans` pour les plans d abonnement
- `up_subscriptions` pour les souscriptions UP
- `subscription_payments` pour les paiements d abonnement
- `organizations` et `users` pour les comptes back-office
- `roles`, `permissions`, `features` pour le controle d acces

La proposition ci-dessous reutilise ce socle pour eviter un sous-systeme parallele.

## Principe General

1. Un UP souscrit et paie un abonnement.
2. Lorsqu un abonnement passe a `active`, le systeme genere ou active une carte de reduction.
3. La carte porte un `card_uuid` unique encode dans le QR code.
4. Un agent partenaire scanne le QR depuis l application mobile partenaire.
5. Le backend verifie l etat de la carte, de l abonnement et les droits du partenaire.
6. Si la carte est valable, l agent applique une offre de reduction autorisee.
7. Une transaction de reduction est enregistree.
8. Le dashboard web partenaire permet de suivre l activite et de gerer les utilisateurs mobiles.

## Decision D Architecture

### 1. La carte est rattachee a l abonnement

Une carte n existe que parce qu un abonnement UP est actif.

Consequences :

- une carte peut etre expiree automatiquement lorsque l abonnement expire
- l historique des cartes suit l historique des abonnements
- le profil mobile peut afficher la carte active la plus recente

### 2. Les partenaires reutilisent `organizations` et `users`

Il ne faut pas creer une table specifique de comptes partenaires.

Proposition :

- une `organization` represente un etablissement partenaire ou une enseigne
- des `users` rattaches a cette `organization` representent les admins web, superviseurs et agents mobiles du partenaire
- un nouveau `organization_type` permet d identifier les partenaires

Avantages :

- meme mecanisme d authentification back-office
- meme logique de permissions
- meme supervision super admin
- pas de duplication de gestion des comptes

## Canaux Partenaires A Supporter

Le systeme partenaire doit etre pense des le depart pour deux usages distincts.

### 1. Dashboard web partenaire

Utilise par :

- admin partenaire
- manager ou superviseur partenaire

Fonctions minimales :

- consulter les reductions appliquees
- filtrer l historique par date, agent, offre
- gerer les offres de reduction du partenaire
- creer, modifier, suspendre les utilisateurs mobiles
- consulter les indicateurs d usage

### 2. Application mobile partenaire

Utilisee par :

- agents de caisse
- agents d accueil
- operateurs terrain habilites

Fonctions minimales :

- connexion
- scan QR code
- verification de validite de la carte
- selection d une offre
- application de la reduction
- consultation de son historique recent

## Tables A Ajouter

## 1. `up_discount_cards`

Carte virtuelle de reduction remise a un UP.

Champs recommandes :

- `id`
- `public_user_id` FK vers `public_users`
- `up_subscription_id` FK vers `up_subscriptions`
- `card_uuid` string 36 unique
- `card_number` string 50 unique
- `status` string 30 index
- `issued_at` timestamp nullable
- `activated_at` timestamp nullable
- `expires_at` timestamp nullable
- `suspended_at` timestamp nullable
- `revoked_at` timestamp nullable
- `last_used_at` timestamp nullable
- `metadata` json nullable
- `created_at`
- `updated_at`

Index recommandes :

- unique sur `card_uuid`
- unique sur `card_number`
- index sur `public_user_id, status`
- index sur `up_subscription_id`
- index sur `expires_at`

Statuts recommandes :

- `pending`
- `active`
- `suspended`
- `expired`
- `revoked`

Remarques :

- le QR code doit encoder `card_uuid` et non des donnees personnelles
- `card_number` sert a l affichage humain et au support

## 2. `partner_discount_offers`

Catalogue d offres qu un partenaire peut appliquer.

Champs recommandes :

- `id`
- `organization_id` FK vers `organizations`
- `code` string 60 unique
- `name` string 180
- `description` text nullable
- `discount_type` string 30
- `discount_value` decimal(12,2) nullable
- `currency` string 10 nullable
- `minimum_purchase_amount` decimal(12,2) nullable
- `maximum_discount_amount` decimal(12,2) nullable
- `max_uses_per_card` unsigned integer nullable
- `max_uses_per_day` unsigned integer nullable
- `starts_at` timestamp nullable
- `ends_at` timestamp nullable
- `status` string 30 index
- `metadata` json nullable
- `created_by` FK vers `users` nullable
- `updated_by` FK vers `users` nullable
- `created_at`
- `updated_at`

Types recommandes :

- `percentage`
- `fixed_amount`
- `custom`

Statuts recommandes :

- `draft`
- `active`
- `inactive`
- `archived`

## 3. `partner_discount_transactions`

Historique de chaque reduction appliquee.

Champs recommandes :

- `id`
- `up_discount_card_id` FK vers `up_discount_cards`
- `partner_discount_offer_id` FK vers `partner_discount_offers`
- `organization_id` FK vers `organizations`
- `partner_user_id` FK vers `users`
- `public_user_id` FK vers `public_users`
- `up_subscription_id` FK vers `up_subscriptions`
- `scan_reference` string 80 unique
- `verification_status` string 30
- `status` string 30 index
- `original_amount` decimal(12,2) nullable
- `discount_amount` decimal(12,2) nullable
- `final_amount` decimal(12,2) nullable
- `discount_type_snapshot` string 30 nullable
- `discount_value_snapshot` decimal(12,2) nullable
- `applied_at` timestamp nullable
- `cancelled_at` timestamp nullable
- `metadata` json nullable
- `created_at`
- `updated_at`

Statuts recommandes :

- `validated`
- `cancelled`
- `reversed`
- `rejected`

Cette table doit contenir un snapshot de l offre pour conserver l historique meme si l offre change apres coup.

## 4. `partner_discount_scans` optionnelle

Cette table n est pas obligatoire en V1, mais elle est utile si vous voulez tracer tous les scans, y compris ceux refuses.

Champs recommandes :

- `id`
- `organization_id`
- `partner_user_id`
- `card_uuid`
- `up_discount_card_id` nullable
- `result_code`
- `result_message`
- `scanned_at`
- `metadata`

V1 simplifiee :

- on peut commencer sans cette table
- les refus peuvent d abord etre traces dans les logs applicatifs

## Evolution Des Referentiels

## 1. Nouveau `organization_type`

Ajouter un type :

- `PARTNER_ESTABLISHMENT`

Utilisation :

- rattacher les boutiques, supermarches, pharmacies, enseignes partenaires

## 2. Nouvelles `features`

Proposition :

- `PARTNER_DASHBOARD_ACCESS`
- `PARTNER_DISCOUNT_SCAN`
- `PARTNER_DISCOUNT_APPLY`
- `PARTNER_DISCOUNT_HISTORY`
- `PARTNER_DISCOUNT_OFFERS_MANAGE`
- `PARTNER_USERS_MANAGE`

## 3. Nouvelles `permissions`

Proposition cote super admin :

- `SA_PARTNER_ORGANIZATIONS_MANAGE`
- `SA_PARTNER_USERS_MANAGE`
- `SA_DISCOUNT_CARDS_VIEW`
- `SA_DISCOUNT_OFFERS_MANAGE`
- `SA_DISCOUNT_TRANSACTIONS_VIEW`
- `SA_DISCOUNT_TRANSACTIONS_REVERSE`

Proposition cote partenaire :

- `PARTNER_ACCESS_PORTAL`
- `PARTNER_DASHBOARD_VIEW`
- `PARTNER_DISCOUNT_SCAN`
- `PARTNER_DISCOUNT_APPLY`
- `PARTNER_DISCOUNT_HISTORY_VIEW`
- `PARTNER_DISCOUNT_OFFERS_MANAGE`
- `PARTNER_USERS_MANAGE`
- `PARTNER_USERS_CREATE`
- `PARTNER_USERS_UPDATE`
- `PARTNER_USERS_TOGGLE_STATUS`

## Roles Partenaires Recommandes

Pour eviter une granularite trop complexe en V1, je recommande trois roles de base.

### 1. `PARTNER_ADMIN`

Peut :

- acceder au dashboard web
- gerer les offres
- voir tout l historique de son etablissement
- creer et gerer les utilisateurs partenaires
- activer ou suspendre les agents mobiles

### 2. `PARTNER_MANAGER`

Peut :

- acceder au dashboard web
- voir l historique
- suivre les indicateurs
- consulter les offres

Ne peut pas :

- creer d utilisateurs
- modifier les droits d acces

### 3. `PARTNER_AGENT`

Compte dedie principalement a l application mobile.

Peut :

- se connecter a l app mobile
- scanner un QR code
- verifier une carte
- appliquer une reduction
- consulter son historique recent

Ne peut pas :

- gerer les offres
- creer d utilisateurs
- acceder a l administration globale partenaire

## Modeles Laravel A Creer

- `App\Models\UpDiscountCard`
- `App\Models\PartnerDiscountOffer`
- `App\Models\PartnerDiscountTransaction`

Relations recommandees :

### `UpDiscountCard`

- `belongsTo(PublicUser::class)`
- `belongsTo(UpSubscription::class)`
- `hasMany(PartnerDiscountTransaction::class)`

### `PartnerDiscountOffer`

- `belongsTo(Organization::class)`
- `belongsTo(User::class, 'created_by')`
- `belongsTo(User::class, 'updated_by')`
- `hasMany(PartnerDiscountTransaction::class)`

### `PartnerDiscountTransaction`

- `belongsTo(UpDiscountCard::class)`
- `belongsTo(PartnerDiscountOffer::class)`
- `belongsTo(Organization::class)`
- `belongsTo(User::class, 'partner_user_id')`
- `belongsTo(PublicUser::class)`
- `belongsTo(UpSubscription::class)`

## Gestion Des Utilisateurs Mobiles Partenaires

Le besoin exprime implique que l admin d un etablissement partenaire puisse creer des comptes pour l equipe mobile.

### Modele recommande

- les comptes mobiles sont stockes dans `users`
- ils appartiennent a la meme `organization` partenaire
- un champ ou une convention de role permet de distinguer les comptes mobiles des comptes web

Deux approches possibles :

### Option A. Pilotage par roles uniquement

- `PARTNER_ADMIN` pour web
- `PARTNER_MANAGER` pour web
- `PARTNER_AGENT` pour mobile

Avantage :

- simple
- coherent avec l existant

### Option B. Ajouter un indicateur de canal

Ajouter sur `users` un champ facultatif du type :

- `access_channel` avec valeurs `web`, `mobile`, `hybrid`

Avantage :

- plus explicite pour filtrer les comptes mobiles
- facilite les ecrans d administration partenaire

Recommendation :

- V1 : rester simple avec les roles
- V2 : ajouter `access_channel` si le besoin de filtrage ou d audit devient important

## Flux Metier Recommande

## 1. Activation De Carte

Quand un paiement d abonnement est confirme :

- le paiement passe a `paid`
- l abonnement passe a `active`
- une carte est creee si aucune carte active n existe pour cet abonnement

Le meilleur point d integration initial est l action :

- [ConfirmUpSubscriptionPaymentAction.php](/Users/macbookpro/Documents/BG/SIGNAL/MYSIGNAL/app/Domain/Subscriptions/Actions/ConfirmUpSubscriptionPaymentAction.php:1)

Logique attendue :

1. activer l abonnement
2. creer ou reactiver la carte
3. renseigner `issued_at`, `activated_at`, `expires_at`

Regle recommandee :

- `expires_at` de la carte = `end_date` de l abonnement

## 2. Verification Cote Partenaire

Un agent partenaire scanne un QR depuis l app mobile puis envoie `card_uuid`.

Le backend doit verifier :

- que la carte existe
- que la carte est `active`
- que la carte n est pas expiree
- que l abonnement associe est `active`
- que le partenaire est actif
- que l offre demandee est active et appartient a l organisation du partenaire
- que les plafonds d usage ne sont pas depasses

Reponse recommandee :

- `is_valid`
- `card_status`
- `subscription_status`
- `member_display_name`
- `offer_eligibility`
- `message`

Important :

- ne jamais renvoyer trop de donnees personnelles
- afficher au partenaire seulement les informations minimales necessaires

## 3. Application De Reduction

Une fois la carte validee :

1. l agent partenaire selectionne une offre autorisee
2. le backend recalcule toutes les regles
3. la transaction est creee
4. `last_used_at` de la carte est mise a jour

## Endpoints API Recommandes

## 1. API mobile public

Routes candidates :

- `GET /api/v1/public/discount-card`
- `GET /api/v1/public/discount-transactions`

Payload de `discount-card` :

- carte active
- `card_uuid`
- `card_number`
- `status`
- `expires_at`
- donnees d abonnement
- QR code brut ou donnees a encoder

Remarque :

- il est souvent preferable de laisser le mobile generer l image QR a partir du `card_uuid`
- le backend peut aussi fournir une string `qr_payload`

## 2. API partenaire

Le projet doit prevoir des API pour l equipe mobile partenaire ainsi qu un socle pour le dashboard web partenaire.

### Recommendation d architecture

- conserver `organizations` + `users` comme socle d identite
- creer un namespace dedie `partner`
- exposer des endpoints API dedies a l application mobile partenaire
- autoriser aussi le dashboard web a consommer certaines APIs

### 2.1 Authentification partenaire

Endpoints recommandes :

- `POST /api/v1/partner/auth/login`
- `POST /api/v1/partner/auth/logout`
- `GET /api/v1/partner/me`

Payload minimal de `me` :

- utilisateur connecte
- organisation partenaire
- roles
- permissions

### 2.2 APIs mobile partenaire

Endpoints prioritaires pour l equipe mobile :

- `POST /api/v1/partner/discount-cards/verify`
- `POST /api/v1/partner/discount-transactions`
- `GET /api/v1/partner/discount-offers`
- `GET /api/v1/partner/mobile/history`

Usage :

- `verify` pour scanner et controler la carte
- `discount-transactions` pour appliquer la reduction
- `discount-offers` pour charger les offres applicables
- `mobile/history` pour afficher les operations recentes de l agent

### 2.3 APIs dashboard web partenaire

Endpoints recommandes :

- `GET /api/v1/partner/dashboard/summary`
- `GET /api/v1/partner/discount-transactions`
- `GET /api/v1/partner/discount-transactions/{transaction}`
- `GET /api/v1/partner/discount-offers`
- `POST /api/v1/partner/discount-offers`
- `PUT /api/v1/partner/discount-offers/{offer}`
- `PATCH /api/v1/partner/discount-offers/{offer}/toggle-status`
- `GET /api/v1/partner/users`
- `POST /api/v1/partner/users`
- `PUT /api/v1/partner/users/{user}`
- `PATCH /api/v1/partner/users/{user}/toggle-status`

### 2.4 Separation des usages

Le meme compte technique peut fonctionner sur web et mobile, mais il est preferable de separer les usages metier :

- `PARTNER_ADMIN` et `PARTNER_MANAGER` majoritairement web
- `PARTNER_AGENT` majoritairement mobile

Ainsi, l admin partenaire cree les agents mobiles depuis le dashboard web, puis ces agents se connectent a l application mobile partenaire.

## Back-Office Super Admin

Ecrans a prevoir :

- liste des partenaires
- creation et activation d un partenaire
- gestion des comptes utilisateurs partenaires
- liste des cartes UP
- consultation des reductions appliquees
- tableau de bord des reductions par partenaire
- consultation des offres partenaires
- consultation des agents mobiles partenaires

## Ecrans Partenaires

V1 minimum :

### Dashboard web partenaire

- connexion
- tableau de bord synthese
- liste des reductions appliquees
- filtres par date, offre, agent
- gestion des offres
- gestion des utilisateurs mobiles

### App mobile partenaire

- connexion
- ecran scan ou saisie du code
- resultat de verification
- selection de l offre
- formulaire d application de reduction
- historique recent de l agent

## Regles Metier V1

- seule une carte liee a un abonnement actif est valide
- un QR code n embarque qu un identifiant opaque
- une offre ne peut etre appliquee que par son organisation proprietaire
- seul un utilisateur partenaire actif et autorise peut verifier ou appliquer une reduction
- seuls les admins partenaires peuvent creer les comptes des agents mobiles
- une transaction ne peut pas etre modifiee silencieusement apres validation
- toute annulation doit laisser une trace
- une carte suspendue ou revoquee doit etre refusee
- une offre inactive doit etre refusee

## Regles Anti-Fraude Recommandees

- plafonner le nombre d usages par carte et par jour
- empecher l usage si abonnement expire
- horodater et identifier l utilisateur partenaire ayant applique la reduction
- journaliser tous les scans refuses en V2
- prevoir la suspension manuelle d une carte par le super admin

## Migrations Recommandees

Ordre conseille :

1. creer `up_discount_cards`
2. creer `partner_discount_offers`
3. creer `partner_discount_transactions`
4. ajouter les `organization_types`, `features`, `permissions`
5. ajouter les pages et endpoints

Noms de migrations suggeres :

- `2026_04_22_000001_create_up_discount_cards_table.php`
- `2026_04_22_000002_create_partner_discount_offers_table.php`
- `2026_04_22_000003_create_partner_discount_transactions_table.php`
- `2026_04_22_000004_add_partner_discount_reference_data.php`

## Actions Et Services Recommandes

Dans `app/Domain/Discounts/Actions/` :

- `IssueUpDiscountCardAction`
- `VerifyPartnerDiscountCardAction`
- `ApplyPartnerDiscountAction`
- `ExpireUpDiscountCardsAction`
- `SuspendUpDiscountCardAction`
- `CreatePartnerUserAction`
- `UpdatePartnerUserAction`
- `TogglePartnerUserStatusAction`

## Resources API Recommandees

- `UpDiscountCardResource`
- `PartnerDiscountOfferResource`
- `PartnerDiscountTransactionResource`

## Strategie De Livraison

### Phase 1. Fondations

- migrations
- modeles
- seed referentiels
- creation automatique de carte a l activation abonnement

### Phase 2. Consultation Mobile

- endpoint profil ou endpoint dedie carte
- affichage carte dans le profil UP
- QR code base sur `card_uuid`

### Phase 3. Espace Partenaire

- comptes partenaires
- gestion des agents mobiles par l admin partenaire
- verification carte depuis l app mobile
- application reduction depuis l app mobile
- historique web et mobile

### Phase 4. Supervision

- vues super admin
- statistiques
- annulation ou suspension

## V1 Recommandee

Pour une premiere mise en production simple et robuste, je recommande :

- une seule carte active par abonnement actif
- des offres configurees par partenaire
- une verification synchrone serveur a chaque scan
- un historique complet des reductions appliquees
- une administration web partenaire pour creer les comptes mobiles
- des APIs dediees a l application mobile partenaire
- pas de fonctionnement offline en V1

## Point D Attention Important

Le projet parle deja de "carte membre" dans le parcours UP. Il faut donc eviter deux objets differents cote produit.

Recommendation produit :

- conserver une seule carte visible dans le profil
- cette meme carte sert a la fois de carte membre et de carte de reduction
- son eligibility depend de l abonnement actif

## Prochaine Etape Technique

L implementation peut maintenant se faire en trois premiers lots tres concrets :

1. Creer les migrations et modeles `up_discount_cards`, `partner_discount_offers`, `partner_discount_transactions`.
2. Brancher la creation de carte dans l activation d abonnement.
3. Mettre en place le socle partenaire `organization type`, permissions, roles et users.
4. Exposer un endpoint public `GET /api/v1/public/discount-card` pour afficher la carte dans le profil mobile.
5. Exposer les APIs `partner` pour l app mobile et le dashboard web.
