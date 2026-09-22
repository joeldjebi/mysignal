<?php

namespace App\Services;

use App\Models\User;
use Throwable;

class AccessCredentialDeliveryService
{
    public function __construct(
        private readonly SmsService $smsService,
        private readonly TopTeaserEmailService $emailService,
    ) {}

    public function send(User $user, string $portalLabel, string $loginUrl, string $password): array
    {
        $result = [
            'sms_sent' => false,
            'mail_sent' => false,
            'errors' => [],
        ];

        if (filled($user->phone)) {
            try {
                $this->smsService->sendSmsMtarget(
                    "My-Signal: accès {$portalLabel}. Lien: {$loginUrl} Identifiant: {$user->email} Mot de passe temporaire: {$password}",
                    (string) $user->phone,
                );
                $result['sms_sent'] = true;
            } catch (Throwable $exception) {
                $result['errors'][] = 'SMS: '.$exception->getMessage();
            }
        }

        if (filled($user->email)) {
            try {
                $this->emailService->send(
                    (string) $user->email,
                    'Vos accès My-Signal',
                    $this->emailHtml($user, $portalLabel, $loginUrl, $password),
                    $this->emailText($user, $portalLabel, $loginUrl, $password),
                );
                $result['mail_sent'] = true;
            } catch (Throwable $exception) {
                $result['errors'][] = 'E-mail: '.$exception->getMessage();
            }
        }

        return $result;
    }

    private function emailHtml(User $user, string $portalLabel, string $loginUrl, string $password): string
    {
        $name = e($user->name ?: 'Utilisateur');
        $portal = e($portalLabel);
        $email = e($user->email);
        $url = e($loginUrl);
        $temporaryPassword = e($password);

        return <<<HTML
<div style="font-family:Arial,sans-serif;color:#152536;line-height:1.55">
  <h2 style="margin:0 0 12px">Vos accès My-Signal</h2>
  <p>Bonjour {$name},</p>
  <p>Vos accès au {$portal} sont prêts.</p>
  <p><strong>Lien de connexion :</strong> <a href="{$url}">{$url}</a><br>
  <strong>Identifiant :</strong> {$email}<br>
  <strong>Mot de passe temporaire :</strong> {$temporaryPassword}</p>
  <p>Pour des raisons de sécurité, veuillez modifier ce mot de passe après votre première connexion.</p>
</div>
HTML;
    }

    private function emailText(User $user, string $portalLabel, string $loginUrl, string $password): string
    {
        $name = $user->name ?: 'Utilisateur';

        return "Bonjour {$name},\n\nVos accès au {$portalLabel} sont prêts.\nLien de connexion: {$loginUrl}\nIdentifiant: {$user->email}\nMot de passe temporaire: {$password}\n\nVeuillez modifier ce mot de passe après votre première connexion.";
    }
}
