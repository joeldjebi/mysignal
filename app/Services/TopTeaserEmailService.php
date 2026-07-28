<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class TopTeaserEmailService
{
    public function send(string $to, string $subject, string $html, string $text): void
    {
        $url = (string) config('services.top_teaser.email_url');
        $key = (string) config('services.top_teaser.key');
        $token = (string) config('services.top_teaser.token');

        if (blank($url) || blank($key) || blank($token)) {
            throw new RuntimeException('Le service email TOP TEASER n’est pas configuré.');
        }

        $response = Http::withHeaders([
            'X-TT-Key' => $key,
            'X-TT-Token' => $token,
        ])
            ->acceptJson()
            ->asJson()
            ->timeout(20)
            ->post($url, [
                'to' => $to,
                'subject' => $subject,
                'html' => $html,
                'text' => $text,
            ]);

        if (! $response->successful()) {
            $body = trim((string) $response->body());
            $body = $body !== '' ? mb_substr(strip_tags($body), 0, 500) : 'Réponse vide';

            throw new RuntimeException('TOP TEASER a refusé l’envoi email. Statut HTTP: '.$response->status().'. Réponse: '.$body);
        }
    }
}
