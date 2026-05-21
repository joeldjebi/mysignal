<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\Data\OtpRequestResult;
use App\Domain\Auth\Enums\OtpPurpose;
use App\Models\PublicUserOtp;
use App\Services\SmsService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class RequestPublicOtpAction
{
    public function __construct(private readonly SmsService $smsService)
    {
    }

    public function handle(string $phone, OtpPurpose $purpose = OtpPurpose::Registration): OtpRequestResult
    {
        $digits = max(4, (int) config('services.public_auth.otp_digits', 4));
        $defaultOtp = (string) config('services.public_auth.default_otp', '2604');
        $code = $defaultOtp !== ''
            ? $defaultOtp
            : (string) random_int(10 ** ($digits - 1), (10 ** $digits) - 1);
        $expiresAt = CarbonImmutable::now()->addMinutes(5);

        DB::transaction(function () use ($phone, $purpose, $code, $expiresAt): void {
            PublicUserOtp::query()
                ->where('phone', $phone)
                ->where('purpose', $purpose->value)
                ->delete();

            PublicUserOtp::query()->create([
                'phone' => $phone,
                'code' => Hash::make($code),
                'purpose' => $purpose->value,
                'expires_at' => $expiresAt,
                'attempts' => 0,
                'max_attempts' => 5,
            ]);
        });

        $sms = $this->sendOtpSms($phone, $code, $purpose);

        return new OtpRequestResult(
            phone: $phone,
            code: $code,
            expiresAt: $expiresAt->toIso8601String(),
            smsResponse: $sms['response'] ?? null,
            smsMsisdn: $sms['msisdn'] ?? null,
        );
    }

    private function sendOtpSms(string $phone, string $code, OtpPurpose $purpose): ?array
    {
        if (! (bool) config('services.public_auth.send_sms', false)) {
            return null;
        }

        $message = $this->smsMessage($code, $purpose);
        $msisdn = $this->smsMsisdn($phone);
        $sender = (string) config('services.public_auth.sms_sender', 'MY-SIGNAL');

        try {
            $response = $this->smsService->sendSmsMtarget($message, $msisdn, $sender);

            Log::info('Public OTP SMS sent.', [
                'phone' => $phone,
                'msisdn' => $msisdn,
                'purpose' => $purpose->value,
                'provider' => 'mtarget',
                'response' => $response,
            ]);

            return [
                'response' => $response,
                'msisdn' => $msisdn,
            ];
        } catch (Throwable $exception) {
            Log::warning('Unable to send public OTP SMS.', [
                'phone' => $phone,
                'msisdn' => $msisdn,
                'purpose' => $purpose->value,
                'provider' => 'mtarget',
                'error' => $exception->getMessage(),
            ]);

            $message = config('app.debug')
                ? 'Erreur SMS MTarget : '.$exception->getMessage()
                : 'Impossible d envoyer le code OTP par SMS. Veuillez reessayer.';

            throw ValidationException::withMessages([
                'phone' => [$message],
            ]);
        }
    }

    private function smsMessage(string $code, OtpPurpose $purpose): string
    {
        $label = $purpose === OtpPurpose::PasswordReset
            ? 'reinitialisation de mot de passe'
            : 'inscription';

        return "Votre code OTP My-Signal pour {$label} est {$code}. Il expire dans 5 minutes.";
    }

    private function smsMsisdn(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? $phone;
        $countryCode = (string) config('services.public_auth.sms_country_code', '225');

        if (str_starts_with($digits, '0') && $countryCode !== '') {
            return $countryCode.$digits;
        }

        return $digits;
    }
}
