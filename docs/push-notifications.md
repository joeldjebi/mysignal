# Push notifications Firebase

## Configuration serveur

Le backend utilise Firebase Cloud Messaging HTTP v1.

Variables `.env` attendues :

```env
FIREBASE_PUSH_ENABLED=true
FIREBASE_PROJECT_ID=my-signal-1b9d9
FIREBASE_CREDENTIALS=/chemin/absolu/firebase-service-account.json
FIREBASE_WEB_API_KEY=
FIREBASE_WEB_AUTH_DOMAIN=
FIREBASE_WEB_STORAGE_BUCKET=
FIREBASE_WEB_MESSAGING_SENDER_ID=
FIREBASE_WEB_APP_ID=
FIREBASE_WEB_VAPID_KEY=
```

Le fichier JSON du service account ne doit jamais être commité.

Les variables `FIREBASE_WEB_*` viennent de la configuration Web Firebase. La clé VAPID se crée dans Firebase Console, Cloud Messaging, Web Push certificates.

## Enregistrement du token mobile

Après login, l'application mobile envoie son token FCM au backend.

UP :

```http
POST /api/v1/public/push-tokens
Authorization: Bearer <token>
Content-Type: application/json
```

```json
{
  "token": "FCM_TOKEN",
  "platform": "android",
  "device_name": "Samsung A15",
  "app_version": "1.0.0"
}
```

Partenaire :

```http
POST /api/v1/partner/push-tokens
Authorization: Bearer <token>
Content-Type: application/json
```

## Suppression du token

À la déconnexion ou quand Firebase renouvelle le token :

```http
DELETE /api/v1/public/push-tokens
Authorization: Bearer <token>
Content-Type: application/json
```

```json
{
  "token": "FCM_TOKEN"
}
```

Même logique pour :

```http
DELETE /api/v1/partner/push-tokens
```

## Notifications web

Les notifications sont aussi stockées pour l'espace web.

Dans le dashboard UP, le navigateur enregistre automatiquement son token Firebase après connexion si les variables `FIREBASE_WEB_*` sont configurées et si l'utilisateur accepte les notifications. La console navigateur affiche :

```text
[MYSIGNAL] Payload push-token UP
[MYSIGNAL] Reponse push-token UP
```

UP :

```http
GET /api/v1/public/notifications
POST /api/v1/public/notifications/{id}/read
POST /api/v1/public/notifications/read-all
```

Partenaire :

```http
GET /api/v1/partner/notifications
POST /api/v1/partner/notifications/{id}/read
POST /api/v1/partner/notifications/read-all
```

## Payload reçu dans l'application

Le backend envoie toujours `notification` et `data`.

Exemple :

```json
{
  "notification": {
    "title": "Invitation Gbonhi reçue",
    "body": "Vous avez reçu une invitation à rejoindre un Gbonhi."
  },
  "data": {
    "type": "household_invitation_created",
    "screen": "household",
    "notification_id": "12",
    "invitation_id": "3",
    "household_id": "7"
  }
}
```

L'application doit utiliser `data.screen` pour ouvrir le bon écran après clic sur la notification.
