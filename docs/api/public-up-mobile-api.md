# API Mobile UP

## Objet
Cette documentation couvre l integration mobile des `UP` via l API publique versionnee disponible sous `/api/v1/public`.

Elle est accompagnee de deux fichiers Postman :
- `postman/MYSIGNAL-UP-Mobile.postman_collection.json`
- `postman/MYSIGNAL-UP-Mobile.postman_environment.json`

## Base URL
- Local : `http://127.0.0.1:8000/api`
- Prefixe commun : `/v1/public`

Exemple complet :
- `http://127.0.0.1:8000/api/v1/public/auth/login`

## Authentification
Le parcours d authentification mobile pour un `UP` est le suivant :
1. demander un OTP
2. verifier l OTP
3. creer le compte avec `verification_token`
4. reutiliser le `Bearer access_token` retourne par `register` ou `login`

Header attendu sur les routes protegees :
```http
Authorization: Bearer {access_token}
Accept: application/json
Content-Type: application/json
```

Les tokens UP retournes par `register` et `login` expirent apres 2 ans par defaut
(`expires_in` = `63072000` secondes).

## Format standard des reponses de succes
L API renvoie un enveloppe uniforme :

```json
{
  "success": true,
  "message": "Connexion reussie.",
  "data": {}
}
```

## Format standard des erreurs
Les erreurs de validation Laravel reviennent classiquement en `422`.

Exemple type :
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "phone": [
      "Les identifiants fournis sont invalides."
    ]
  }
}
```

Autres statuts importants :
- `401` : token absent ou invalide
- `403` : acces refuse
- `404` : ressource non trouvee ou non possedee par l usager
- `422` : regle metier ou validation non respectee

## Variables utiles pour Postman
- `baseUrl` : ex. `http://127.0.0.1:8000/api`
- `accessToken`
- `verificationToken`
- `phone`
- `password`
- `publicUserTypeId`
- `meterId`
- `signalCode`
- `signalSubTypeCode`
- `reportId`
- `paymentId`
- `householdId`
- `invitationId`

## Parcours d integration recommande

### 1. Catalogues publics
Avant authentification, l application mobile peut charger :
- `GET /v1/public/applications`
- `GET /v1/public/application-types?application_id={id}`
- `GET /v1/public/organizations?application_id={id}&organization_type_id={id}`
- `GET /v1/public/locations`
- `GET /v1/public/business-sectors`
- `GET /v1/public/signal-types`

Ces endpoints servent a alimenter :
- categories, sous categories et institutions
- pays, villes, communes, quartiers
- secteurs d activite pour les profils UPE/UPTI
- types de signaux

`GET /v1/public/applications` retourne les categories dans l ordre d affichage configure par le SA (`sort_order`, puis `name`). Chaque categorie expose aussi `identifier_label`, a utiliser comme libelle du champ identifiant : par exemple `Identifiant`, `Police d'assurance`, etc. Le champ identifiant doit etre affiche si la categorie retourne `requires_public_user_identifier: true` ou si le type de signal choisi retourne `requires_public_user_identifier: true`.

### 2. OTP et creation de compte
- `POST /v1/public/auth/request-otp`
- `POST /v1/public/auth/verify-otp`
- `POST /v1/public/auth/register`

### 3. Connexion
- `POST /v1/public/auth/login`

### 4. Mot de passe oublie
- `POST /v1/public/auth/forgot-password/request-otp`
- `POST /v1/public/auth/forgot-password/verify-otp`
- `POST /v1/public/auth/forgot-password/reset-password`

### 5. Session mobile
- `GET /v1/public/me`
- `GET /v1/public/profile`
- `PUT /v1/public/profile`

### 6. Donnees metier principales
- compteurs : `meters`
- recus d achat : `purchase-receipts`
- foyers Gonhi : `households`
- signalements : `reports`
- paiements : `payments`
- dossiers contentieux : `reparation-cases`

## Notes d integration importantes

### OTP en local
Pour les besoins de test, le code OTP UP par defaut est `2604`.
Ce code est utilise pour l inscription et la reinitialisation du mot de passe tant que le systeme d envoi OTP n est pas encore branche.

### Types d usagers publics
La route publique `GET /v1/public/user-types` retourne les types UP actifs a afficher dans l inscription.

Impact :
- l app mobile doit choisir ou preselectionner `public_user_type_id` au moment de l inscription
- les champs entreprise et secteur restent conditionnels selon le type selectionne

### Secteurs d activite
La route publique `GET /v1/public/business-sectors` retourne les secteurs actifs disponibles pour le champ `business_sector`.

Reponse :
```json
{
  "success": true,
  "message": "Request processed successfully.",
  "data": {
    "business_sectors": [
      {
        "id": 1,
        "code": "ENERGIE",
        "name": "Energie",
        "description": "Production, distribution et services lies a l energie."
      }
    ]
  }
}
```

Pour l inscription ou la mise a jour du profil, envoyer la valeur `name` dans `business_sector`.

### Paiement
Les signalements et les cartes privilèges utilisent FineoPay avec des URLs séparées.

Impact :
- `POST /reports/{report}/payments` initialise un paiement
- `POST /payments/{payment}/confirm` le confirme côté API
- `GET /payments/{payment}/receipt` télécharge un PDF si le paiement est `paid`
- `POST /privilege-cards/{type}/payments` initialise un achat de carte privilège
- `GET /privilege-card-payment-sessions` retourne l historique des achats de cartes privilèges
- `GET /privilege-card-payment-sessions/{syncRef}` vérifie le paiement d une carte privilège
- `GET /privilege-cards/{card}/wallet-pass` retourne les liens Wallet Apple et Android
- `GET /privilege-cards/{card}/wallet-pass?platform=ios|android` retourne le lien Wallet demandé

### Dommages
Un dommage ne peut etre declare que si :
- le signalement est marque `resolved`
- l usager a confirme la resolution
- la declaration intervient dans la fenetre autorisee

### Dossiers contentieux
L usager public ne cree pas lui-meme un dossier contentieux.
Il consulte seulement les dossiers ouverts par le `SA` et leur historique via :
- `GET /v1/public/reparation-cases`

## Endpoints documentes

### Auth

#### POST `/v1/public/auth/request-otp`
Demande un OTP pour un numero.

Body :
```json
{
  "phone": "0700000000"
}
```

Succes :
```json
{
  "success": true,
  "message": "OTP envoye avec succes.",
  "data": {
    "phone": "0700000000",
    "expires_at": "2026-04-10T17:00:00+00:00",
    "otp_code_for_testing": "2604"
  }
}
```

#### POST `/v1/public/auth/verify-otp`
Verifie le code OTP et retourne un `verification_token`.

Body :
```json
{
  "phone": "0700000000",
  "code": "2604"
}
```

#### POST `/v1/public/auth/register`
Cree le compte public et retourne directement un token d acces.

#### POST `/v1/public/auth/forgot-password/request-otp`
Demande un OTP de reinitialisation du mot de passe pour un compte UP actif.

Body :
```json
{
  "phone": "2250700000000"
}
```

#### POST `/v1/public/auth/forgot-password/verify-otp`
Verifie le code OTP et retourne un `verification_token`.

Body :
```json
{
  "phone": "2250700000000",
  "code": "2604"
}
```

#### POST `/v1/public/auth/forgot-password/reset-password`
Change le mot de passe avec le `verification_token`.

Body :
```json
{
  "phone": "2250700000000",
  "verification_token": "{{verificationToken}}",
  "password": "12345678",
  "password_confirmation": "12345678"
}
```

Body minimal `UP` :
```json
{
  "public_user_type_id": 1,
  "first_name": "Jean",
  "last_name": "Doe",
  "phone": "0700000000",
  "country_id": 1,
  "city_id": 1,
  "commune_id": 6,
  "password": "12345678",
  "password_confirmation": "12345678",
  "verification_token": "{{verificationToken}}"
}
```

Localisation a l inscription :
- l app mobile doit faire choisir `country_id`, puis `city_id`, puis `commune_id`
- utiliser `GET /v1/public/locations` pour recuperer l arbre complet pays > villes > communes
- l API verifie que la commune appartient bien a la ville et au pays envoyes
- le backend remplit automatiquement les IDs, les libelles `country`, `city`, `commune` et `location_references` dans le profil retourne

Champs conditionnels :
- `business_sector`, `tax_identifier`, `company_address` facultatifs a la creation
- `company_name`, `company_registration_number` obligatoires pour `UPE`

#### POST `/v1/public/auth/login`
Connexion classique par numero et mot de passe.

Body :
```json
{
  "phone": "0700000000",
  "password": "12345678"
}
```

### Profil

#### GET `/v1/public/me`
Retourne l usager courant authentifie.

#### GET `/v1/public/profile`
Retourne le profil detaille.

#### PUT `/v1/public/profile`
Pour mettre a jour la localisation du profil, envoyer ensemble `country_id`, `city_id` et `commune_id`. Le backend recalculera les libelles `country`, `city` et `commune`.
Met a jour le profil.

Remarque :
- `public_user_type_id` ne peut pas etre modifie depuis cette route

#### POST `/v1/public/profile/photo`
Met a jour la photo de profil UP.

Format : `multipart/form-data`

| Champ | Obligatoire | Description |
| --- | --- | --- |
| `profile_photo` | oui | Image JPG, PNG ou WEBP, taille max 4 Mo. |

La reponse retourne `user.profile_photo_url`.

### Catalogues

#### GET `/v1/public/locations`
Retourne l arbre geographique complet :
- countries
- cities
- communes
- neighborhoods
- sub_neighborhoods

#### GET `/v1/public/signal-types`
Retourne le catalogue des types de signaux actifs avec :
- application
- organisation
- TCM cible
- `requires_public_user_identifier` : `true` quand ce type de signal impose le choix d un identifiant, meme si la categorie ne l impose pas.
- `sub_types` : sous-types actifs du type de signal. Quand ce tableau est non vide, l app doit afficher un champ sous-type obligatoire.
- `requires_sub_type` : `true` quand `sub_types` est non vide.

Quand un type de signal a des sous-types, l API ajoute automatiquement l option :
```json
{
  "code": "OTHER",
  "label": "Autre",
  "is_other": true
}
```

L app mobile doit envoyer `signal_sub_type_code` uniquement quand `requires_sub_type` vaut `true`. Utiliser `OTHER` si le motif voulu n existe pas dans la liste.
- `precise_gps` et `gps_location` sont pre-remplis avec la position GPS courante quand elle est disponible.

### Identifiants

#### GET `/v1/public/meters`
Liste les identifiants du compte courant.

#### POST `/v1/public/meters`
Ajoute un identifiant.

Un UP peut ajouter plusieurs identifiants, y compris pour la meme categorie ou la meme institution. Chaque appel cree un nouveau rattachement tant que l identifiant n est pas deja rattache au compte courant.

Body d exemple :
```json
{
  "application_id": 1,
  "organization_id": 1,
  "meter_number": "AB12345678",
  "label": "Compteur principal",
  "city": "Abidjan",
  "commune": "Cocody",
  "address": "Rue 12",
  "is_primary": true
}
```

Si la categorie choisie retourne `requires_public_user_identifier: true`, l app peut ajouter une photo optionnelle de l identifiant. Dans ce cas, envoyer le payload en `multipart/form-data` avec la cle `identifier_photo`.

Exemple `multipart/form-data` :
```text
application_id=1
organization_id=1
meter_number=AB12345678
label=Identifiant principal
city=Abidjan
commune=Cocody
address=Rue 12
is_primary=true
identifier_photo=@photo-identifiant.jpg
```

`identifier_photo` est optionnel. Formats acceptes : image `jpg`, `jpeg`, `png` ou `webp`. Taille max : 4 Mo. Le fichier est stocke sur Wasabi et retourne dans `identifier_photo_url`.

#### GET `/v1/public/meters/{meter}`
Retourne un identifiant possede par l usager.

#### PATCH `/v1/public/meters/{meter}`
Met a jour un identifiant. Envoyer `identifier_photo` en `multipart/form-data` pour remplacer la photo stockee sur Wasabi.

Pour une mise a jour avec fichier, le mobile peut aussi appeler `POST /v1/public/meters/{meter}` en `multipart/form-data`.

#### DELETE `/v1/public/meters/{meter}`
Supprime l identifiant du compte courant.

La suppression retire le rattachement entre l usager connecte et l identifiant. Les signalements historiques et les rattachements d autres usagers restent conserves.

Les identifiants retournes par l API incluent aussi :
- `assignment_type` : `personal` ou `gbonhi`
- `is_gbonhi` : booleen
- `assignment_label` : libelle pret a afficher cote mobile
- `identifier_photo_url` : URL temporaire Wasabi de la photo, ou `null`

### Recus d achat

#### GET `/v1/public/purchase-receipts`
Liste les recus d achat de materiel enregistres par l UP connecte.

#### POST `/v1/public/purchase-receipts`
Enregistre un recu d achat.

Body `multipart/form-data` :
```text
material_name=Television
purchase_date=2026-05-20
amount=150000
receipt_file=@recu.pdf
```

`receipt_file` est optionnel. Formats acceptes : image `jpeg`, `png`, `webp`, `gif`, `heic`, `heif` ou PDF. Taille max : 10 Mo. Le fichier est stocke sur Wasabi et retourne dans `attachment.temporary_url`.

#### PATCH `/v1/public/purchase-receipts/{purchaseReceipt}`
Met a jour un recu d achat. Envoyer `receipt_file` en `multipart/form-data` pour remplacer le fichier stocke sur Wasabi.

#### DELETE `/v1/public/purchase-receipts/{purchaseReceipt}`
Supprime un recu d achat du compte courant.

### Foyers Gonhi

#### POST `/v1/public/households`
Creation d un foyer.

Un UP peut creer plusieurs Gonhi. Chaque Gonhi cree rattache automatiquement son createur comme proprietaire de ce Gonhi.

Body :
```json
{
  "name": "Famille Doe"
}
```

`commune` et `address` ne sont plus demandes a la creation d un Gonhi. La localisation utile est recuperee plus tard depuis l identifiant partage au moment de l invitation.

#### GET `/v1/public/households/me`
Retourne les Gonhi rattaches au compte courant.

La reponse garde `household` pour compatibilite avec les anciennes versions mobiles, mais le nouveau champ a utiliser est `households`.

```json
{
  "household": {},
  "households": []
}
```

#### DELETE `/v1/public/households/{household}`
Supprime un Gonhi.

Seul le proprietaire du Gonhi peut le supprimer. La suppression retire les membres, les invitations associees et les acces compteur crees par les invitations de ce Gonhi quand ils ne sont pas partages par un autre Gonhi.

Succes :
```json
{
  "success": true,
  "message": "Gbonhi supprime avec succes.",
  "data": {
    "household": null,
    "households": []
  }
}
```

#### GET `/v1/public/households/invitations/pending`
Retourne les invitations en attente. Les invitations Gonhi n expirent pas : elles restent disponibles tant qu elles ne sont pas acceptees ou refusees.

#### POST `/v1/public/households/{household}/invitations`
Invite un membre.

Body :
```json
{
  "phone": "0711111111",
  "meter_id": 1
}
```

`relationship` est optionnel. Si l app mobile ne l envoie pas, l API enregistre automatiquement `membre`.

#### DELETE `/v1/public/households/invitations/{invitation}`
Annule une invitation Gonhi envoyee.

Seul l invitateur ou le proprietaire du Gonhi peut annuler une invitation encore en attente. Une invitation deja acceptee ne peut plus etre annulee.

Succes :
```json
{
  "success": true,
  "message": "Invitation Gbonhi annulee avec succes.",
  "data": {
    "invitation": {
      "id": 1,
      "status": "declined"
    },
    "household": {},
    "households": []
  }
}
```

#### DELETE `/v1/public/households/{household}/members/{member}`
Retire un membre d un Gonhi.

Seul le proprietaire du Gonhi peut retirer un membre. Le proprietaire ne peut pas se retirer lui-meme via cette route. Si le membre retire avait recu des acces compteur via ce Gonhi, ces acces sont retires lorsqu ils ne sont pas aussi justifies par un autre Gonhi.

Succes :
```json
{
  "success": true,
  "message": "Membre retire du Gbonhi avec succes.",
  "data": {
    "household": {},
    "households": []
  }
}
```

#### POST `/v1/public/households/invitations/accept`
Accepte une invitation. Un compte UP peut etre rattache a plusieurs Gonhi.

#### POST `/v1/public/households/invitations/decline`
Refuse une invitation.

### Signalements

#### GET `/v1/public/reports`
Liste les signalements du compte.

#### GET `/v1/public/reports/monthly-category-stats`
Retourne les statistiques globales de la plateforme pour le mois précédent et le mois en cours. Cette route est disponible pour le web UP et le mobile UP, mais les chiffres ne sont pas limites au compte connecte.

Reponse :
```json
{
  "success": true,
  "data": {
    "previous_month": {
      "month": "2026-06",
      "label": "juin 2026",
      "date_from": "2026-06-01",
      "date_to": "2026-06-30",
      "total_reports": 12,
      "categories": [
        {
          "application_id": 1,
          "category_code": "SERVICE_PUBLIC",
          "category_name": "Service public",
          "reports_count": 7
        }
      ]
    },
    "current_month": {
      "month": "2026-07",
      "label": "juillet 2026",
      "date_from": "2026-07-01",
      "date_to": "2026-07-31",
      "total_reports": 18,
      "categories": []
    }
  }
}
```

#### POST `/v1/public/reports`
Initialise le paiement FineoPay d un signalement. Le signalement n est cree en base qu apres callback FineoPay avec `status: success`.

Avant de creer un signalement, l app mobile doit appeler les catalogues publics, puis filtrer les types de signaux compatibles avec la catégorie, la sous catégorie, l institution et/ou l identifiant selectionne.

L app mobile ne doit plus envoyer `country_id`, `city_id`, `commune_id` ni `address` lors de la creation d un signalement. Le backend recupere automatiquement le pays, la ville, la commune et l adresse depuis l identifiant selectionne (`meter_id`) ou le profil UP. Si l identifiant ne contient pas de commune exploitable, l API retourne `422` sur `meter_id`.

Compatibilite d un type de signal avec un compteur :
- `application_id` du type de signal doit correspondre a la catégorie choisie ou a l `application_id` du compteur
- si le type de signal retourne `organization_ids`, l institution choisie ou celle du compteur doit faire partie de cette liste
- si le type de signal a `organization_id: null` et `organization_ids: []`, il sert de type generique pour la catégorie
- `organization_type_id` correspond a la Sous Catégorie. Il devient obligatoire pour les categories qui retournent `requires_organization_type_on_report: true` dans `GET /v1/public/applications`

Payload attendu sans fichier :

| Champ | Obligatoire | Description |
| --- | --- | --- |
| `meter_id` | conditionnel | Identifiant rattache au UP. Obligatoire si la catégorie retourne `requires_public_user_identifier: true` ou si le type choisi retourne `requires_public_user_identifier: true`. Afficher le libelle retourne dans `identifier_label`. |
| `application_id` | conditionnel | Catégorie concernée. Requis si `meter_id` n est pas envoye. |
| `organization_type_id` | conditionnel | Sous Catégorie concernée, anciennement Type d organisation. Obligatoire si la catégorie retourne `requires_organization_type_on_report: true` et si `meter_id` n est pas envoye. |
| `organization_id` | conditionnel | Institution concernée. Requise quand le parcours UP demande une institution ou pour filtrer les types de signaux institutionnels. |
| `signal_code` | oui | Code du type retourne par `GET /v1/public/signal-types`, compatible avec le compteur choisi. |
| `signal_sub_type_code` | conditionnel | Obligatoire quand le type choisi retourne `requires_sub_type: true`. Envoyer le code d un sous-type actif ou `OTHER` pour Autre. |
| `description` | non | Detail libre saisi par le UP. |
| `occurred_at` | oui | Date ISO 8601 de l incident. |
| `latitude` | non | Latitude GPS courante. |
| `longitude` | non | Longitude GPS courante. |
| `location_accuracy` | non | Precision GPS en metres si disponible. |
| `location_source` | non | Exemple : `gps`. |
| `signal_attachment` | non | Fichier image ou video, en multipart uniquement. |

Body JSON sans sous-type, quand `requires_sub_type: false` :
```json
{
  "meter_id": 1,
  "signal_code": "EL-01",
  "description": "Coupure depuis 2 heures",
  "occurred_at": "2026-04-10T12:00:00Z",
  "latitude": 5.348,
  "longitude": -4.001,
  "location_accuracy": 20,
  "location_source": "gps"
}
```

Body JSON sans identifiant, avec Sous Catégorie et Institution :
```json
{
  "application_id": 9,
  "organization_type_id": 3,
  "organization_id": 12,
  "signal_code": "EDUCATION_ET_FORMATION_01",
  "description": "Demande de prise en charge",
  "occurred_at": "2026-06-18T12:00:00Z",
  "latitude": 5.348,
  "longitude": -4.001,
  "location_accuracy": 20,
  "location_source": "gps"
}
```

Body JSON avec sous-type, quand `requires_sub_type: true` :
```json
{
  "meter_id": 1,
  "signal_code": "EL-01",
  "signal_sub_type_code": "OTHER",
  "description": "Coupure depuis 2 heures",
  "occurred_at": "2026-04-10T12:00:00Z",
  "latitude": 5.348,
  "longitude": -4.001,
  "location_accuracy": 20,
  "location_source": "gps"
}
```

Body multipart avec photo ou video optionnelle, sans sous-type si `requires_sub_type: false` :
```text
POST /api/v1/public/reports
Accept: application/json
Content-Type: multipart/form-data

meter_id=1
application_id=9
organization_type_id=3
organization_id=12
signal_code=EL-01
description=Coupure depuis 2 heures
occurred_at=2026-04-10T12:00:00Z
latitude=5.348
longitude=-4.001
location_accuracy=20
location_source=gps
signal_attachment=@preuve.jpg
```

Ajouter `signal_sub_type_code=OTHER` ou le code du sous-type selectionne uniquement si le type de signal retourne `requires_sub_type: true`.

Si `meter_id` est envoye, l API peut deduire la catégorie et l institution depuis l identifiant. Si `meter_id` n est pas envoye, envoyer `application_id`, `organization_type_id` si requis, et `organization_id`.

Regle d affichage mobile pour l identifiant :
- afficher le champ si `application.requires_public_user_identifier === true`
- ou afficher le champ si `selectedSignalType.requires_public_user_identifier === true`
- sinon, ne pas afficher le champ

`signal_attachment` est optionnel. Formats acceptes : images `jpeg`, `png`, `webp`, `gif`, `heic`, `heif` et videos `mp4`, `mov/quicktime`, `avi`, `mpeg`. Taille max : 50 Mo.

`signal_sub_type_code` est obligatoire uniquement si le `signal_code` choisi a `requires_sub_type: true` dans `GET /v1/public/signal-types`.

Reponse `201` :
```json
{
  "success": true,
  "message": "Lien de paiement genere avec succes. Le signalement sera enregistre apres paiement.",
  "data": {
    "checkout_link": "https://dev.fineopay.com/pay/business123/abc123/checkout",
    "payment_session": {
      "id": 12,
      "sync_ref": "RPT-20260515150000-A1B2C3",
      "amount": 5000,
      "currency": "FCFA",
      "status": "pending",
      "provider": "fineopay",
      "checkout_link": "https://dev.fineopay.com/pay/business123/abc123/checkout",
      "incident_report_id": null
    }
  }
}
```

L app mobile doit ouvrir `checkout_link`. Apres paiement reussi, FineoPay appelle le callback backend, puis le signalement apparait dans `GET /v1/public/reports`.

Comme FineoPay ne redirige pas encore vers l app mobile, l app doit conserver `payment_session.sync_ref`, puis verifier le statut lorsque l utilisateur revient dans l app.

#### GET `/v1/public/payment-sessions/{syncRef}`
Verifie le statut serveur d une session de paiement de signalement.

Exemple si paiement encore en attente :
```json
{
  "success": true,
  "data": {
    "payment_session": {
      "sync_ref": "RPT-20260516103000-A1B2C3",
      "amount": 100,
      "currency": "FCFA",
      "status": "pending",
      "provider": "fineopay",
      "incident_report_id": null,
      "report": null
    }
  }
}
```

Exemple si paiement confirme :
```json
{
  "success": true,
  "data": {
    "payment_session": {
      "sync_ref": "RPT-20260516103000-A1B2C3",
      "amount": 100,
      "currency": "FCFA",
      "status": "paid",
      "provider": "fineopay",
      "incident_report_id": 45,
      "report": {
        "id": 45,
        "reference": "SIG-20260516103100-A1B2C3",
        "status": "submitted",
        "payment_status": "paid"
      }
    }
  }
}
```

Flux mobile recommande :
- ouvrir `checkout_link` dans le navigateur
- au retour dans l app, afficher "Verification du paiement"
- appeler `GET /v1/public/payment-sessions/{syncRef}` toutes les 3 a 5 secondes
- si `status = paid`, afficher le succes et ouvrir le detail du signalement
- si `status = pending`, continuer ou proposer "Actualiser"
- si `status = failed`, proposer de relancer le paiement

### Cartes privilèges

#### GET `/v1/public/privilege-cards`
Retourne les cartes privilèges disponibles pour les UP, triées par ordre d affichage.

Réponse :
```json
{
  "success": true,
  "data": {
    "cards": [
      {
        "id": 1,
        "code": "STANDARD",
        "name": "Standard",
        "price": 1000,
        "currency": "FCFA",
        "benefits": ["Accès aux avantages standards"],
        "discount_type": "percentage",
        "discount_value": 10,
        "duration_months": 12,
        "status": "active",
        "sort_order": 1
      }
    ]
  }
}
```

`discount_type` vaut `percentage` pour une réduction en pourcentage ou `fixed_amount` pour une réduction en montant fixe.

#### POST `/v1/public/privilege-cards/{type}/payments`
Initialise l achat FineoPay d une carte privilège. `{type}` est l identifiant numérique retourné par `GET /v1/public/privilege-cards`.

Réponse :
```json
{
  "success": true,
  "message": "Lien de paiement carte privilège généré avec succès.",
  "data": {
    "checkout_link": "https://dev.fineopay.com/pay/business123/abc123/checkout",
    "payment_session": {
      "sync_ref": "PVC-20260717103000-A1B2C3",
      "amount": 1000,
      "currency": "FCFA",
      "status": "pending",
      "provider": "fineopay",
      "checkout_link": "https://dev.fineopay.com/pay/business123/abc123/checkout"
    }
  }
}
```

#### GET `/v1/public/privilege-card-payment-sessions/{syncRef}`
Vérifie le statut serveur d un achat de carte privilège.

Quand le paiement est confirmé, `status` vaut `paid` et `card` contient la carte émise.

#### GET `/v1/public/privilege-card-payment-sessions`
Retourne l historique complet des achats de cartes privilèges du UP connecté.

Réponse :
```json
{
  "success": true,
  "data": {
    "payment_sessions": [
      {
        "sync_ref": "PVC-20260717103000-A1B2C3",
        "amount": 1000,
        "currency": "FCFA",
        "status": "paid",
        "provider": "fineopay",
        "paid_at": "2026-07-21T16:20:00+00:00",
        "type": {
          "id": 1,
          "name": "Standard"
        },
        "card": {
          "id": 4,
          "card_uuid": "0d24899d-5a2b-44b3-9ad5-17f76887485d",
          "card_number": "PVC-STA-260721-ABC123",
          "status": "active",
          "qr_payload": "0d24899d-5a2b-44b3-9ad5-17f76887485d"
        }
      }
    ]
  }
}
```

#### GET `/v1/public/privilege-card`
Retourne la dernière carte privilège du UP connecté, ou `null`.

#### GET `/v1/public/privilege-cards/{card}/wallet-pass`
Retourne les liens Wallet Apple et Android pour une carte privilège active appartenant au UP connecté.

Conditions obligatoires :
- la carte appartient au UP connecté
- la carte est liée à une session FineoPay `paid`
- `status = active`
- `expires_at` est vide ou dans le futur

Paramètres query :
- sans `platform`, l API retourne `apple_url` et `android_url`
- `platform=ios` retourne le lien Apple Wallet dans `data.url`
- `platform=android` retourne le lien Google Wallet dans `data.url`

Réponse :
```json
{
  "success": true,
  "data": {
    "url": null,
    "platform": "all",
    "apple_url": "https://example.com/api/v1/public/privilege-cards/4/pass.pkpass?expires=1784738719",
    "android_url": "https://pay.google.com/gp/v/save/...",
    "links": {
      "apple": "https://example.com/api/v1/public/privilege-cards/4/pass.pkpass?expires=1784738719",
      "android": "https://pay.google.com/gp/v/save/..."
    },
    "expires_at": "2026-07-22T17:30:00+00:00"
  }
}
```

Pour Apple Wallet, ouvrir le lien sur un iPhone. Pour Google Wallet, ouvrir le lien sur un téléphone Android avec Google Wallet.

Exemple avec pièce jointe optionnelle :
```json
{
  "meter_id": 1,
  "signal_code": "EL-01",
  "description": "Compteur illisible",
  "occurred_at": "2026-04-10T12:00:00Z",
  "latitude": 5.348,
  "longitude": -4.001,
  "location_accuracy": 20,
  "location_source": "gps"
}
```

Ajouter `signal_sub_type_code` dans cet exemple seulement si le type de signal choisi exige un sous-type.

La reponse `report` contient maintenant `signal_sub_type` avec `id`, `code`, `label`, `is_other`, et `signal_attachment` avec `name`, `mime_type`, `size`, `path` et `temporary_url` quand un fichier a ete envoye.

#### GET `/v1/public/reports/{report}`
Retourne le detail d un signalement.

#### POST `/v1/public/reports/{report}/confirm-resolution`
Confirme la resolution d un signalement resolu.

#### POST `/v1/public/reports/{report}/damages`
Initialise le paiement FineoPay d une declaration de dommage. Le dommage n est enregistre sur le signalement qu apres callback FineoPay avec `status: success`.

Body multipart :
```text
POST /api/v1/public/reports/{report}/damages
Accept: application/json
Content-Type: multipart/form-data

damage_summary=Materiel endommage
damage_amount_estimated=25000
damage_notes=Routeur grille
damage_attachment=@preuve.jpg
purchase_receipt_id=1
```

`damage_attachment` est obligatoire. Formats acceptes : image `jpeg`, `png`, `webp`, `gif`, `heic`, `heif` ou PDF. Taille max : 10 Mo.
Le recu d achat est facultatif. Envoyer `purchase_receipt_id` pour rattacher un recu existant, ou envoyer directement `receipt_material_name`, `receipt_purchase_date`, `receipt_amount` et optionnellement `receipt_attachment=@recu.pdf` pour creer un recu pendant la declaration. Le fichier du recu est stocke sur Wasabi.

#### PATCH `/v1/public/reports/{report}/damages`
Met a jour un dommage deja declare, sans relancer de paiement.

Body possible :
```json
{
  "damage_summary": "Materiel endommage mis a jour",
  "damage_amount_estimated": 30000,
  "damage_notes": "Routeur et decodeur grilles",
  "purchase_receipt_id": 1
}
```

`purchase_receipt_id` est facultatif. Envoyer `null` pour retirer le recu rattache. Pour creer un recu pendant la mise a jour, envoyer `receipt_material_name`, `receipt_purchase_date`, `receipt_amount` et optionnellement `receipt_attachment` en `multipart/form-data`.

Alias mobile direct :
- `PATCH /v1/public/damages/{report}`

Reponse `201` :
```json
{
  "data": {
    "payment_session": {
      "sync_ref": "DMG-20260410120000-ABC123",
      "payment_context": "damage",
      "amount": 100,
      "currency": "FCFA",
      "status": "pending",
      "checkout_link": "https://..."
    },
    "checkout_link": "https://..."
  }
}
```

L app mobile doit ouvrir `checkout_link`, puis verifier `GET /v1/public/payment-sessions/{syncRef}` comme pour un signalement. Quand `status = paid`, le dommage apparait dans `GET /v1/public/damages` et dans le detail du signalement.

### Paiements

#### GET `/v1/public/payments`
Liste l historique des paiements du compte, signalements et dommages inclus. Filtre optionnel : `?payment_context=report` ou `?payment_context=damage`.

#### POST `/v1/public/reports/{report}/payments`
Ancien flux de paiement pour signalements deja crees. Le nouveau flux recommande est `POST /v1/public/reports`, qui retourne directement un `checkout_link` FineoPay avant creation du signalement.

#### POST `/v1/public/payments/{payment}/confirm`
Ancien flux de confirmation simulee.

#### GET `/v1/public/payments/{payment}/receipt`
Telecharge le recu PDF si le paiement est `paid`.

### Dossiers contentieux

#### GET `/v1/public/reparation-cases`
Retourne les dossiers visibles par l usager avec :
- reference
- statut
- priorite
- incident source
- historique public
- etapes publiques

### Notifications push UP

#### POST `/v1/public/push-tokens`
Enregistre ou met a jour le token Firebase Cloud Messaging du telephone apres connexion.

Body :
```json
{
  "token": "FCM_TOKEN_MOBILE",
  "platform": "android",
  "device_name": "Samsung A15",
  "app_version": "1.0.0"
}
```

Reponse :
```json
{
  "success": true,
  "message": "Token de notification enregistre.",
  "data": {
    "device_token": {
      "id": 5,
      "platform": "android",
      "device_name": "Samsung A15",
      "app_version": "1.0.0",
      "last_seen_at": "2026-04-28T15:30:00+00:00"
    }
  }
}
```

Le token brut n est pas retourne dans la reponse.

Valeurs autorisees pour `platform` :
- `android`
- `ios`
- `web`

#### DELETE `/v1/public/push-tokens`
Revoque le token lors de la deconnexion ou quand Firebase renouvelle le token.

Body :
```json
{
  "token": "FCM_TOKEN_MOBILE"
}
```

#### GET `/v1/public/notifications`
Liste les notifications stockees du UP courant.

Query params :
- `status` : `all`, `read` ou `unread`
- `category` : `mysignal`, `gbonhi`, `report`, `payment`, `subscription`, etc.
- `search` : recherche dans le titre, le message ou le type technique
- `limit` : de `1` a `100`, defaut `30`

Exemples :
```http
GET /v1/public/notifications
GET /v1/public/notifications?status=unread
GET /v1/public/notifications?category=gbonhi
GET /v1/public/notifications?status=read&category=mysignal&search=test&limit=50
```

Reponse :
```json
{
  "success": true,
  "message": "Operation effectuee avec succes.",
  "data": {
    "notifications": [
      {
        "id": 12,
        "type": "super_admin_broadcast",
        "title": "Test",
        "body": "Message de notification",
        "category": "mysignal",
        "category_label": "Information My-Signal",
        "data": {
          "screen": "dashboard",
          "source": "super_admin"
        },
        "read_at": null,
        "created_at": "2026-04-28T15:30:00+00:00"
      }
    ],
    "unread_count": 3,
    "filtered_count": 1,
    "available_categories": [
      {
        "key": "gbonhi",
        "label": "Gbonhi"
      },
      {
        "key": "mysignal",
        "label": "Information My-Signal"
      }
    ],
    "filters": {
      "status": "unread",
      "category": "mysignal",
      "search": "test",
      "limit": 50
    }
  }
}
```

L app mobile doit utiliser `available_categories` pour afficher le filtre categorie et envoyer ensuite `category=<key>`.

#### POST `/v1/public/notifications/{id}/read`
Marque une notification comme lue. Retourne la notification mise a jour.

#### POST `/v1/public/notifications/read-all`
Marque toutes les notifications du UP courant comme lues.

## Recommandations mobile
- stocker le `Bearer token` de maniere securisee
- gerer `422` champ par champ pour les formulaires
- precharger `locations` et `signal-types` au lancement ou au premier acces
- enregistrer le token Firebase apres login avec `POST /v1/public/push-tokens`
- utiliser `GET /v1/public/notifications` pour synchroniser lu/non lu et les filtres
- prevoir un rendu PDF ou un telechargement externe pour le recu de paiement
- utiliser `GET /v1/public/user-types` pour alimenter le choix du type UP avant inscription

## Fichiers fournis
- Collection Postman : [MYSIGNAL-UP-Mobile.postman_collection.json](/Users/macbookpro/Documents/BG/SIGNAL/MYSIGNAL/postman/MYSIGNAL-UP-Mobile.postman_collection.json)
- Environment Postman : [MYSIGNAL-UP-Mobile.postman_environment.json](/Users/macbookpro/Documents/BG/SIGNAL/MYSIGNAL/postman/MYSIGNAL-UP-Mobile.postman_environment.json)
