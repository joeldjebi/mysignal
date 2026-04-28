# API Mobile Partenaire

## Objet
Cette documentation couvre les endpoints utiles a l application mobile des users partenaires qui scannent les QR codes et appliquent les reductions.

Prefixe commun :
- `/api/v1/partner`

Headers sur les routes protegees :
```http
Authorization: Bearer {access_token}
Accept: application/json
Content-Type: application/json
```

## Authentification

### POST `/v1/partner/auth/login`
Connexion du user partenaire.

Body :
```json
{
  "phone": "+2250758754662",
  "password": "12345678"
}
```

La reponse retourne `access_token`, a reutiliser en Bearer token.

### GET `/v1/partner/me`
Retourne le user partenaire connecte, son organisation et ses permissions.

## Push notifications

### POST `/v1/partner/push-tokens`
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

Valeurs autorisees pour `platform` :
- `android`
- `ios`
- `web`

Reponse :
```json
{
  "success": true,
  "message": "Token de notification enregistre.",
  "data": {
    "device_token": {
      "id": 9,
      "platform": "android",
      "device_name": "Samsung A15",
      "app_version": "1.0.0",
      "last_seen_at": "2026-04-28T15:30:00+00:00"
    }
  }
}
```

Le token brut n est pas retourne dans la reponse.

### DELETE `/v1/partner/push-tokens`
Revoque le token a la deconnexion ou quand Firebase renouvelle le token.

Body :
```json
{
  "token": "FCM_TOKEN_MOBILE"
}
```

### GET `/v1/partner/notifications`
Retourne simplement les notifications du user partenaire connecte. Aucun filtre n est necessaire pour l app mobile partenaire.

Query optionnelle :
- `limit` : de `1` a `100`, defaut `30`

Exemple :
```http
GET /v1/partner/notifications?limit=30
```

Reponse :
```json
{
  "success": true,
  "message": "Operation effectuee avec succes.",
  "data": {
    "notifications": [
      {
        "id": 21,
        "type": "general",
        "title": "Reduction appliquee",
        "body": "Une reduction a ete enregistree avec succes.",
        "category": "general",
        "category_label": "Général",
        "data": {},
        "read_at": null,
        "created_at": "2026-04-28T15:30:00+00:00"
      }
    ],
    "unread_count": 1
  }
}
```

### POST `/v1/partner/notifications/{id}/read`
Marque une notification comme lue.

### POST `/v1/partner/notifications/read-all`
Marque toutes les notifications du user partenaire comme lues.

## Reductions

### POST `/v1/partner/discount-cards/verify`
Verifie une carte de reduction scannee par QR code.

Body :
```json
{
  "card_uuid": "UUID_DU_QR_CODE",
  "offer_id": 1
}
```

Permission requise :
- `PARTNER_DISCOUNT_SCAN`

### POST `/v1/partner/discount-transactions`
Applique la reduction apres verification.

Body :
```json
{
  "card_uuid": "UUID_DU_QR_CODE",
  "offer_id": 1,
  "original_amount": 15000,
  "discount_amount": 1000,
  "final_amount": 14000,
  "metadata": {
    "source": "mobile-app"
  }
}
```

Permission requise :
- `PARTNER_DISCOUNT_APPLY`

### GET `/v1/partner/mobile/history`
Retourne les derniers scans du user partenaire connecte.

### GET `/v1/partner/mobile/stats`
Retourne les statistiques personnelles de scan.
