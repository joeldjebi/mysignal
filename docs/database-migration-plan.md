# Plan De Migration De Base De Donnees

## Objectif

Preparer la migration de la base actuelle sans perdre :

- les donnees systeme
- le compte Super Admin
- les referentiels
- les donnees metier deja saisies

Ce travail se fait sur la branche `develop`.

## Etat Actuel

- Branche de stabilisation publiee : `main`
- Branche de travail migration : `develop`
- Base actuelle utilisee en pratique : `pgsql`
- Le projet Laravel supporte deja en configuration :
  - `sqlite`
  - `mysql`
  - `mariadb`
  - `pgsql`
  - `sqlsrv`

## Ce Qui Doit Absolument Etre Conserve

### Donnees systeme

- `users` pour le compte `Super Admin`
- `features`
- `permissions`
- `applications`
- `organization_types`
- `signal_types`
- `pricing_rules`
- `public_user_types`
- `countries`
- `cities`
- `communes`
- `business_sectors`
- `organization_type_signal_slas`

### Donnees metier

- `organizations`
- `public_users`
- `meters`
- `incident_reports`
- `payments`
- `roles`
- `role_user`
- `permission_role`
- `feature_user`
- `feature_organization`
- `application_feature`
- tables foyer / invitations / membres

## Zones Sensibles Detectees

Le projet n'est pas fortement verrouille a PostgreSQL, mais plusieurs points devront etre verifies selon la cible choisie.

### 1. Requetes SQL brutes a valider

Fichiers concernes :

- `app/Domain/Reports/Actions/CreateIncidentReportAction.php`
- `app/Http/Controllers/Web/Institution/DamageController.php`
- `app/Http/Controllers/Web/Institution/SignalTypeController.php`
- `app/Http/Controllers/Web/Institution/DashboardController.php`
- `app/Http/Controllers/Web/Institution/StatisticController.php`
- `app/Http/Controllers/Web/SuperAdmin/CountryController.php`
- `app/Http/Controllers/Web/SuperAdmin/DashboardController.php`
- `app/Http/Controllers/Web/SuperAdmin/OrganizationController.php`

Points a surveiller :

- `COALESCE(...)`
- `DATE(column)`
- `CASE WHEN ... THEN ... END`
- `groupByRaw(...)`
- `orderByRaw(...)`

Ces requetes sont souvent portables, mais doivent etre testees sur la base cible.

### 2. Agr egations dashboard

Les dashboards `SA` et `AI` utilisent plusieurs agregations :

- repartition par statuts
- top communes
- top types de signaux
- tendances journalieres
- regroupements dommages

Ces ecrans sont prioritaires dans la recette post-migration.

### 3. Import / export des donnees

Le plus gros risque n'est pas le code Laravel, mais la reprise des donnees :

- ordre d'import des tables
- conservation des cles primaires
- conservation des cles etrangeres
- coherence des pivots
- conservation du compte `Super Admin`

## Strategie Recommandee

### Etape 1. Base cible retenue

Base cible validee :

- `mysql`

Parametres fournis pour l'environnement de migration :

- `DB_CONNECTION=mysql`
- `DB_HOST=127.0.0.1`
- `DB_PORT=8889`
- `DB_DATABASE=mysignal`
- `DB_USERNAME=root`
- `DB_PASSWORD=root`

### Etape 2. Creer un environnement de test cible

Mettre en place une base vide de la cible choisie, puis tester :

1. `php artisan migrate:fresh --seed`
2. connexion application
3. login SA
4. dashboards
5. parcours public
6. parcours institution

### Etape 3. Corriger les incompatibilites code

Priorite :

1. migrations
2. seeders
3. requetes brutes
4. dashboards
5. ecrans statistiques

### Correctifs deja engages sur `develop`

- configuration `.env` basculee vers `mysql`
- calcul `sla_breached_count` du dashboard `SA` rendu portable
  - suppression de la dependance a `EXTRACT(EPOCH ...)`
  - calcul de depassement SLA reporte en PHP
- filtres dommages `AI` rendus moins dependants du SQL brut
  - plus de filtre `whereRaw(COALESCE(...))` pour la resolution des dommages
- requetes de collecte paiements et de repartition dommages `AI`
  - reecrites avec conditions explicites plutot qu'avec des constructions ambiguës autour de `when(...)`

### Etape 4. Preparer la reprise des donnees

Deux options possibles :

#### Option A. Migration complete des donnees

On exporte toutes les tables actuelles, puis on les reimporte dans la base cible.

Avantage :

- aucune perte de donnees

Impact :

- plus exigeant en verification

#### Option B. Reinitialisation partielle

On conserve seulement :

- donnees systeme
- compte `Super Admin`

Puis on recree les donnees metier.

Avantage :

- plus simple

Impact :

- perte volontaire des donnees operationnelles

### Etape 5. Recette post-migration

Checklist minimale :

- login `Super Admin`
- affichage dashboards `SA`, `AI`, public
- creation organisation
- creation AI
- creation usager public
- creation compteur
- declaration signalement
- paiement
- dommage
- upload Wasabi
- cartes
- SLA
- features

## Prochaine Action Recommandee

La cible etant maintenant fixee a `mysql`, la suite du chantier sur `develop` est :

1. verifier l'environnement local `php` et `mysql`
2. lancer `migrate:fresh --seed` sur MySQL dans un environnement de test
3. corriger les incompatibilites detectees
4. preparer la strategie de reprise des donnees PostgreSQL vers MySQL
5. documenter la bascule finale
