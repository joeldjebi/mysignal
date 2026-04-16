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
- `reportId`
- `paymentId`
- `householdId`
- `invitationId`

## Parcours d integration recommande

### 1. Catalogues publics
Avant authentification, l application mobile peut charger :
- `GET /v1/public/locations`
- `GET /v1/public/signal-types`

Ces deux endpoints servent a alimenter :
- pays, villes, communes, quartiers
- types de signaux
- champs dynamiques `data_fields`

### 2. OTP et creation de compte
- `POST /v1/public/auth/request-otp`
- `POST /v1/public/auth/verify-otp`
- `POST /v1/public/auth/register`

### 3. Connexion
- `POST /v1/public/auth/login`

### 4. Session mobile
- `GET /v1/public/me`
- `GET /v1/public/profile`
- `PUT /v1/public/profile`

### 5. Donnees metier principales
- compteurs : `meters`
- foyers Gonhi : `households`
- signalements : `reports`
- paiements : `payments`
- dossiers contentieux : `reparation-cases`

## Notes d integration importantes

### OTP en local
En environnement `local` ou `testing`, le code OTP de test est expose par l API.
La generation actuelle retourne aussi `1234` comme code OTP de travail en local.

### Types d usagers publics
La route publique de catalogue des `public_user_types` n existe pas encore dans l API.

Impact :
- l app mobile doit connaitre `public_user_type_id` au moment de l inscription
- a court terme, il faut soit l alimenter depuis une configuration distante, soit ajouter une route API dediee plus tard

### Paiement
Le paiement actuellement implemente est `simulated`.

Impact :
- `POST /reports/{report}/payments` initialise un paiement
- `POST /payments/{payment}/confirm` le confirme cote API
- `GET /payments/{payment}/receipt` telecharge un PDF si le paiement est `paid`

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
    "otp_code_for_testing": "1234"
  }
}
```

#### POST `/v1/public/auth/verify-otp`
Verifie le code OTP et retourne un `verification_token`.

Body :
```json
{
  "phone": "0700000000",
  "code": "1234"
}
```

#### POST `/v1/public/auth/register`
Cree le compte public et retourne directement un token d acces.

Body minimal `UP` :
```json
{
  "public_user_type_id": 1,
  "first_name": "Jean",
  "last_name": "Doe",
  "phone": "0700000000",
  "commune": "Cocody",
  "password": "12345678",
  "password_confirmation": "12345678",
  "verification_token": "{{verificationToken}}"
}
```

Champs conditionnels :
- `business_sector` obligatoire pour `UPE` et `UPTI`
- `company_name`, `company_registration_number`, `tax_identifier`, `company_address` obligatoires pour `UPE`

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
Met a jour le profil.

Remarque :
- `public_user_type_id` ne peut pas etre modifie depuis cette route

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
-TCM cible
- `data_fields` pour les formulaires dynamiques

### Compteurs

#### GET `/v1/public/meters`
Liste les compteurs du compte courant.

#### POST `/v1/public/meters`
Ajoute un compteur.

Body d exemple :
```json
{
  "application_id": 1,
  "organization_id": 1,
  "meter_number": "AB12345678",
  "label": "Compteur principal",
  "commune": "Cocody",
  "address": "Rue 12",
  "is_primary": true
}
```

#### GET `/v1/public/meters/{meter}`
Retourne un compteur possede par l usager.

#### PATCH `/v1/public/meters/{meter}`
Met a jour un compteur.

### Foyers Gonhi

#### POST `/v1/public/households`
Creation d un foyer.

#### GET `/v1/public/households/me`
Retourne le foyer de l usager, ou `household: null`.

#### GET `/v1/public/households/invitations/pending`
Retourne les invitations en attente.

#### POST `/v1/public/households/{household}/invitations`
Invite un membre.

Body :
```json
{
  "phone": "0711111111",
  "relationship": "conjoint",
  "meter_id": 1
}
```

#### POST `/v1/public/households/invitations/accept`
Accepte une invitation.

#### POST `/v1/public/households/invitations/decline`
Refuse une invitation.

### Signalements

#### GET `/v1/public/reports`
Liste les signalements du compte.

#### POST `/v1/public/reports`
Cree un signalement.

Body d exemple :
```json
{
  "meter_id": 1,
  "application_id": 1,
  "organization_id": 1,
  "country_id": 1,
  "city_id": 1,
  "commune_id": 1,
  "signal_code": "NETWORK_OUTAGE",
  "description": "Coupure depuis 2 heures",
  "address": "Rue 12",
  "occurred_at": "2026-04-10T12:00:00Z",
  "latitude": 5.348,
  "longitude": -4.001,
  "location_source": "gps",
  "signal_payload": {
    "service_category": "Internet"
  }
}
```

#### GET `/v1/public/reports/{report}`
Retourne le detail d un signalement.

#### POST `/v1/public/reports/{report}/confirm-resolution`
Confirme la resolution d un signalement resolu.

#### POST `/v1/public/reports/{report}/damages`
Declare un dommage.

Body d exemple :
```json
{
  "damage_summary": "Materiel endommage",
  "damage_amount_estimated": 25000,
  "damage_notes": "Routeur grille",
  "damage_attachment": {
    "name": "preuve.jpg",
    "mime_type": "image/jpeg",
    "data_url": "data:image/jpeg;base64,..."
  }
}
```

### Paiements

#### GET `/v1/public/payments`
Liste l historique des paiements du compte.

#### POST `/v1/public/reports/{report}/payments`
Initialise un paiement pour un signalement.

#### POST `/v1/public/payments/{payment}/confirm`
Confirme un paiement simule.

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

## Recommandations mobile
- stocker le `Bearer token` de maniere securisee
- gerer `422` champ par champ pour les formulaires
- precharger `locations` et `signal-types` au lancement ou au premier acces
- exploiter `data_fields` de `signal-types` pour construire des formulaires dynamiques
- prevoir un rendu PDF ou un telechargement externe pour le recu de paiement
- considerer que `public_user_type_id` n a pas encore de route publique de catalogue

## Fichiers fournis
- Collection Postman : [MYSIGNAL-UP-Mobile.postman_collection.json](/Users/macbookpro/Documents/BG/SIGNAL/MYSIGNAL/postman/MYSIGNAL-UP-Mobile.postman_collection.json)
- Environment Postman : [MYSIGNAL-UP-Mobile.postman_environment.json](/Users/macbookpro/Documents/BG/SIGNAL/MYSIGNAL/postman/MYSIGNAL-UP-Mobile.postman_environment.json)
