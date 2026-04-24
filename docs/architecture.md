# SIGNAL ALERTE - Architecture V1

## Principes directeurs

- `API-first` pour servir l'application mobile et, plus tard, les interfaces partenaires.
- Separation stricte entre `back-office` et `public`.
- Base de donnees `PostgreSQL` en production.
- Auth publique par `JWT`.
- Tous les parametres metier sensibles doivent devenir configurables par le `super_admin`.

## Espaces applicatifs

### 1. Public

Expose les API utilisees par le mobile :

- inscription `numero -> OTP -> verification -> mot de passe`
- connexion `numero + mot de passe`
- profil client public
- foyers et membres
- compteurs CIE/SODECI
- signalements
- paiements
- preuves juridiques
- reclamations d'indemnisation

### 2. Back-office

Expose les API de pilotage :

- super admin
- admins d'organisation
- utilisateurs internes
- roles et permissions
- catalogue de fonctionnalites
- parametrage metier
- suivi des signalements, paiements et indemnisations

## Domaines recommandés

- `Auth`
- `PublicUsers`
- `Households`
- `Meters`
- `Incidents`
- `Reports`
- `Payments`
- `LegalProof`
- `Compensation`
- `Organizations`
- `AccessControl`
- `Settings`

## Parametrage centralise par le super admin

Le super admin doit pouvoir piloter sans redeploiement :

- montant du signalement
- duree de validite OTP
- nombre maximal de tentatives OTP
- types d'incident autorises
- types de reseau (`CIE`, `SODECI`, autres si besoin)
- statuts metier
- fonctionnalites disponibles par type d'organisation
- activation de modules par organisation
- regles d'indemnisation
- contenus legaux, messages SMS, notifications

### Strategie technique

On demarrera avec une configuration codee pour aller vite sur l'authentification, puis on basculera ces valeurs dans des tables de parametrage :

- `system_settings`
- `setting_groups`
- `reference_lists`
- `reference_values`
- `pricing_rules`
- `organization_feature`

### Parametres deja prepares en V1

La V1 du module public prevoit deja des points de bascule vers le parametrage super admin :

- rattachement libre de plusieurs compteurs par UP
- types de reseau supportes
- statuts de compteur
- regles de format metier
- duree de validite des invitations foyer
- pays, villes et communes autorises pour les signalements
- types d'incident autorises

Ces valeurs sont temporairement centralisees dans la configuration applicative avant d'etre migrees vers des tables de parametrage.

## Structure Laravel recommandee

```text
app/
  Domain/
    Auth/
    PublicUsers/
    Households/
    Meters/
    Incidents/
    Reports/
    Payments/
    LegalProof/
    Compensation/
    Organizations/
    AccessControl/
    Settings/
  Http/
    Controllers/Api/V1/Public
    Controllers/Api/V1/BackOffice
    Requests/Api/V1/Public
    Requests/Api/V1/BackOffice
    Resources/Api/V1
  Models/
  Support/
```

## Ordre de developpement

1. Socle technique: PostgreSQL, JWT, conventions API, gestion d'erreurs.
2. Auth publique: OTP, verification numero, creation de compte, login, profil.
3. Profil public et gestion des compteurs.
4. Foyer et invitations famille.
5. Signalement individuel.
6. Paiement du signalement.
7. Generation de preuve juridique.
8. Regroupement d'incidents et alerte foyer.
9. Back-office super admin.
10. Back-office organisation, roles, permissions et fonctionnalites.
11. Indemnisation.
12. Statistiques et tableaux de bord.
