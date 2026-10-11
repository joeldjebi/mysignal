<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\InstitutionActivationLetter;
use App\Models\InstitutionActivationLetterBatch;
use App\Models\InstitutionActivationLetterSetting;
use App\Models\User;
use App\Models\UserType;
use App\Services\SmsService;
use App\Services\InstitutionActivationLetterPdfService;
use App\Services\TopTeaserEmailService;
use App\Services\WasabiService;
use App\Support\Audit\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;
use ZipArchive;

class InstitutionActivationLetterController extends Controller
{
    public function edit(Request $request, User $institutionAdmin): View
    {
        $this->abortIfNotInstitutionAdmin($institutionAdmin);

        return view('super-admin.institution-admins.activation-letter', [
            'institutionAdmin' => $institutionAdmin->load('organization'),
            'letter' => $this->letterFor($institutionAdmin, $request),
            'logoPositions' => $this->logoPositions(),
        ]);
    }

    public function batchStore(Request $request, ActivityLogger $activityLogger): RedirectResponse|JsonResponse
    {
        $attributes = $request->validate([
            'institution_admin_ids' => ['required', 'array', 'min:1', 'max:100'],
            'institution_admin_ids.*' => ['integer', 'distinct', 'exists:users,id'],
            'batch_label' => ['nullable', 'string', 'max:180'],
            'letter_subject' => ['required', 'string', 'max:255'],
            'issue_place' => ['required', 'string', 'max:120'],
            'issue_date' => ['required', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:issue_date'],
        ]);

        $admins = User::query()
            ->with('organization')
            ->whereIn('id', $attributes['institution_admin_ids'])
            ->where('is_super_admin', false)
            ->where('user_type_id', UserType::idFor(UserType::INSTITUTION_ADMIN))
            ->whereNotNull('organization_id')
            ->get();

        foreach ($admins as $admin) {
            $this->abortIfNotInstitutionAdmin($admin);
        }

        if ($admins->count() !== count($attributes['institution_admin_ids'])) {
            return back()->withErrors([
                'institution_admin_ids' => 'Au moins un administrateur institutionnel sélectionné est invalide.',
            ]);
        }

        $duplicateInstitutionCount = $admins->count() - $admins->unique('organization_id')->count();
        $admins = $admins->unique('organization_id')->values();
        $generatedAt = now();
        $actorId = $request->user()?->id;

        $batch = DB::transaction(function () use ($attributes, $admins, $generatedAt, $actorId): InstitutionActivationLetterBatch {
            $batch = InstitutionActivationLetterBatch::query()->create([
                'reference' => $this->nextBatchReference(),
                'label' => filled($attributes['batch_label']) ? $attributes['batch_label'] : 'Courriers du '.$generatedAt->format('d/m/Y H:i'),
                'created_by' => $actorId,
                'generated_at' => $generatedAt,
                'letters_count' => $admins->count(),
                'metadata' => [
                    'issue_place' => $attributes['issue_place'],
                    'issue_date' => $attributes['issue_date'],
                    'expires_at' => $attributes['expires_at'] ?? null,
                ],
            ]);

            foreach ($admins as $institutionAdmin) {
                $this->supersedePendingLetters($institutionAdmin, $actorId, $generatedAt);
                $this->createBatchLetter($batch, $institutionAdmin, $attributes, $actorId, $generatedAt);
            }

            return $batch;
        });

        $activityLogger->log(
            'institution_activation_letter.batch_generated',
            'Génération groupée de courriers de désignation de points focaux.',
            $batch,
            [
                'batch_reference' => $batch->reference,
                'letters_count' => $batch->letters_count,
                'institution_admin_ids' => $admins->pluck('id')->all(),
                'duplicate_institutions_ignored' => $duplicateInstitutionCount,
            ],
            $request,
            $request->user(),
        );

        $message = $batch->letters_count.' courrier(s) ont été générés dans le lot '.$batch->reference.'.';

        if ($duplicateInstitutionCount > 0) {
            $message .= ' '.$duplicateInstitutionCount.' sélection(s) visant une institution déjà retenue ont été ignorées.';
        }

        $downloadUrl = route('super-admin.institution-activation-letter-batches.download', $batch);

        if ($request->expectsJson()) {
            $letters = $batch->letters()
                ->orderBy('id')
                ->get(['id']);

            return response()->json([
                'message' => $message,
                'total' => $letters->count(),
                'letters' => $letters->map(fn (InstitutionActivationLetter $letter): array => [
                    'id' => $letter->id,
                    'prepare_url' => route('super-admin.institution-activation-letter-batches.letters.prepare', [$batch, $letter]),
                ])->values(),
                'archive_url' => route('super-admin.institution-activation-letter-batches.archive', $batch),
            ]);
        }

        return redirect()
            ->to($downloadUrl)
            ->with('success', $message);
    }

    public function batchPrepareLetter(
        InstitutionActivationLetterBatch $batch,
        InstitutionActivationLetter $letter,
        InstitutionActivationLetterPdfService $pdf,
    ): JsonResponse {
        abort_unless((int) $letter->batch_id === (int) $batch->id, 404);

        $this->prepareBatchLetterPdf($batch, $letter, $pdf);

        return response()->json([
            'letter_id' => $letter->id,
            'prepared' => true,
        ]);
    }

    public function batchArchive(InstitutionActivationLetterBatch $batch, InstitutionActivationLetterPdfService $pdf): JsonResponse
    {
        $this->buildBatchArchive($batch, $pdf);

        return response()->json([
            'download_url' => route('super-admin.institution-activation-letter-batches.download', $batch),
        ]);
    }

    public function batchDownload(InstitutionActivationLetterBatch $batch, InstitutionActivationLetterPdfService $pdf): BinaryFileResponse
    {
        $path = $this->buildBatchArchive($batch, $pdf, false);

        return response()->download($path, 'courriers-'.$batch->reference.'.zip', [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    private function buildBatchArchive(
        InstitutionActivationLetterBatch $batch,
        InstitutionActivationLetterPdfService $pdf,
        bool $refresh = true,
    ): string
    {
        $batch->load(['letters.organization', 'letters.institutionAdmin']);

        abort_if($batch->letters->isEmpty(), 404);
        abort_unless(class_exists(ZipArchive::class), 503, 'La génération de fichiers ZIP est indisponible sur ce serveur.');

        $directory = $this->batchWorkingDirectory($batch);
        File::ensureDirectoryExists($directory);
        $path = $directory.'/courriers-'.Str::slug($batch->reference).'.zip';

        if (!$refresh && File::isFile($path)) {
            return $path;
        }

        File::delete($path);
        $zip = new ZipArchive();

        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Impossible de préparer l’archive des courriers.');
        }

        try {
            foreach ($batch->letters as $letter) {
                $organization = Str::slug((string) ($letter->organization?->name ?: 'institution'));
                $filename = 'courrier-'.($letter->letter_number ? Str::replace('/', '-', $letter->letter_number).'-' : '').$organization.'-'.$letter->id.'.pdf';
                $zip->addFile($this->prepareBatchLetterPdf($batch, $letter, $pdf), $filename);
            }
        } finally {
            $zip->close();
        }

        return $path;
    }

    private function prepareBatchLetterPdf(
        InstitutionActivationLetterBatch $batch,
        InstitutionActivationLetter $letter,
        InstitutionActivationLetterPdfService $pdf,
    ): string {
        $directory = $this->batchWorkingDirectory($batch).'/letters';
        $path = $directory.'/courrier-'.$letter->id.'.pdf';

        if (File::isFile($path) && File::size($path) > 0) {
            return $path;
        }

        File::ensureDirectoryExists($directory);
        File::put($path, $pdf->make($letter->loadMissing(['organization', 'institutionAdmin'])));

        return $path;
    }

    private function batchWorkingDirectory(InstitutionActivationLetterBatch $batch): string
    {
        return storage_path('app/private/tmp/institution-activation-letter-batches/batch-'.$batch->id);
    }

    public function history(User $institutionAdmin): View
    {
        $this->abortIfNotInstitutionAdmin($institutionAdmin);

        return view('super-admin.institution-admins.activation-letter-history', [
            'institutionAdmin' => $institutionAdmin->load('organization'),
            'letters' => InstitutionActivationLetter::query()
                ->with(['creator', 'batch'])
                ->where('institution_admin_id', $institutionAdmin->id)
                ->latest('generated_at')
                ->latest('id')
                ->paginate(20),
        ]);
    }

    public function downloadHistoryLetter(User $institutionAdmin, InstitutionActivationLetter $letter, InstitutionActivationLetterPdfService $pdf): Response
    {
        $this->abortIfNotInstitutionAdmin($institutionAdmin);
        abort_unless((int) $letter->institution_admin_id === (int) $institutionAdmin->id, 404);

        return response($pdf->make($letter->loadMissing(['organization', 'institutionAdmin'])), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="courrier-'.$letter->activation_code.'.pdf"',
        ]);
    }

    public function update(Request $request, User $institutionAdmin, ActivityLogger $activityLogger, WasabiService $wasabiService): RedirectResponse
    {
        $this->abortIfNotInstitutionAdmin($institutionAdmin);
        $letter = $this->letterFor($institutionAdmin, $request);

        $attributes = $request->validate([
            'letter_number' => ['nullable', 'string', 'max:80'],
            'issue_place' => ['required', 'string', 'max:120'],
            'issue_date' => ['nullable', 'date'],
            'letter_subject' => ['required', 'string', 'max:255'],
            'letter_content' => ['required', 'string', 'max:20000'],
            'signature_name' => ['nullable', 'string', 'max:180'],
            'signature_title' => ['nullable', 'string', 'max:180'],
            'signature_content' => ['nullable', 'string', 'max:3000'],
            'signature_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_signature_image' => ['nullable', 'boolean'],
            'footer_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_footer_logo' => ['nullable', 'boolean'],
            'logo_position' => ['required', 'string', 'in:left,center,right,none'],
            'logo_width' => ['required', 'integer', 'min:60', 'max:260'],
            'header_title_text' => ['required', 'string', 'max:180'],
            'header_title_size' => ['required', 'integer', 'min:9', 'max:28'],
            'header_title_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'header_title_bold' => ['nullable', 'boolean'],
            'header_title_italic' => ['nullable', 'boolean'],
            'header_subtitle_text' => ['nullable', 'string', 'max:180'],
            'header_subtitle_size' => ['required', 'integer', 'min:9', 'max:28'],
            'header_subtitle_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'header_subtitle_bold' => ['nullable', 'boolean'],
            'header_subtitle_italic' => ['nullable', 'boolean'],
            'header_description_text' => ['nullable', 'string', 'max:255'],
            'header_description_size' => ['required', 'integer', 'min:8', 'max:20'],
            'header_description_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'header_description_bold' => ['nullable', 'boolean'],
            'header_description_italic' => ['nullable', 'boolean'],
            'footer_address_text' => ['nullable', 'string', 'max:1000'],
            'footer_logo_size' => ['required', 'integer', 'min:32', 'max:120'],
            'footer_address_size' => ['required', 'integer', 'min:8', 'max:16'],
            'footer_address_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'footer_address_bold' => ['nullable', 'boolean'],
            'footer_address_italic' => ['nullable', 'boolean'],
            'footer_phone_text' => ['nullable', 'string', 'max:1000'],
            'footer_phone_size' => ['required', 'integer', 'min:8', 'max:16'],
            'footer_phone_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'footer_phone_bold' => ['nullable', 'boolean'],
            'footer_phone_italic' => ['nullable', 'boolean'],
            'footer_email_text' => ['nullable', 'string', 'max:1000'],
            'footer_email_size' => ['required', 'integer', 'min:8', 'max:16'],
            'footer_email_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'footer_email_bold' => ['nullable', 'boolean'],
            'footer_email_italic' => ['nullable', 'boolean'],
            'footer_website_text' => ['nullable', 'string', 'max:1000'],
            'footer_website_size' => ['required', 'integer', 'min:8', 'max:16'],
            'footer_website_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'footer_website_bold' => ['nullable', 'boolean'],
            'footer_website_italic' => ['nullable', 'boolean'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_logo' => ['nullable', 'boolean'],
            'expires_at' => ['nullable', 'date'],
        ]);

        if ($request->boolean('remove_logo') && filled($letter->logo_path)) {
            $this->deleteLetterAssetIfUnused($wasabiService, $letter, 'logo_path');
            $letter->forceFill(['logo_path' => null])->save();
        }

        if ($request->hasFile('logo')) {
            if (filled($letter->logo_path)) {
                $this->deleteLetterAssetIfUnused($wasabiService, $letter, 'logo_path');
            }

            $letter->forceFill([
                'logo_path' => $wasabiService->uploadFile($request->file('logo'), 'institution-activation-letters/logos', 'letter-logo'),
            ])->save();
        }

        if ($request->boolean('remove_signature_image') && filled($letter->signature_path)) {
            $this->deleteLetterAssetIfUnused($wasabiService, $letter, 'signature_path');
            $letter->forceFill(['signature_path' => null])->save();
        }

        if ($request->hasFile('signature_image')) {
            if (filled($letter->signature_path)) {
                $this->deleteLetterAssetIfUnused($wasabiService, $letter, 'signature_path');
            }

            $letter->forceFill([
                'signature_path' => $wasabiService->uploadFile($request->file('signature_image'), 'institution-activation-letters/signatures', 'signature'),
            ])->save();
        }

        if ($request->boolean('remove_footer_logo') && filled($letter->footer_logo_path)) {
            $this->deleteLetterAssetIfUnused($wasabiService, $letter, 'footer_logo_path');
            $letter->forceFill(['footer_logo_path' => null])->save();
        }

        if ($request->hasFile('footer_logo')) {
            if (filled($letter->footer_logo_path)) {
                $this->deleteLetterAssetIfUnused($wasabiService, $letter, 'footer_logo_path');
            }

            $letter->forceFill([
                'footer_logo_path' => $wasabiService->uploadFile($request->file('footer_logo'), 'institution-activation-letters/footer-logos', 'footer-logo'),
            ])->save();
        }

        $letter->update([
            'letter_subject' => $attributes['letter_subject'],
            'letter_number' => $attributes['letter_number'] ?: $letter->letter_number ?: $this->nextLetterNumber(),
            'issue_place' => $attributes['issue_place'],
            'issue_date' => $attributes['issue_date'] ?? now()->toDateString(),
            'letter_content' => $this->cleanLetterContent($attributes['letter_content']),
            'signature_name' => $attributes['signature_name'] ?? null,
            'signature_title' => $attributes['signature_title'] ?? null,
            'signature_content' => $this->cleanLetterContent($attributes['signature_content'] ?? ''),
            'logo_position' => $attributes['logo_position'],
            'header_settings' => $this->headerSettingsFromRequest($request),
            'footer_settings' => $this->footerSettingsFromRequest($request),
            'expires_at' => $attributes['expires_at'] ?? null,
            'status' => $letter->status === 'draft' ? 'generated' : $letter->status,
            'generated_at' => $letter->generated_at ?? now(),
            'activation_url' => $this->activationUrl($letter->activation_code),
        ]);

        $letter->refresh();
        $this->savePresentationDefaults($letter, $request->user()?->id);
        $syncedLettersCount = $this->syncPresentationDefaultsToPendingLetters($letter);

        $activityLogger->log(
            'institution_activation_letter.updated',
            'Mise à jour du courrier de désignation du point focal.',
            $letter,
            [
                'organization_id' => $letter->organization_id,
                'institution_admin_id' => $letter->institution_admin_id,
                'activation_code' => $letter->activation_code,
                'logo_changed' => $request->hasFile('logo') || $request->boolean('remove_logo'),
                'synced_letters_count' => $syncedLettersCount,
            ],
            $request,
            $request->user(),
        );

        return back()->with(
            'success',
            'Le courrier a été enregistré. Ces paramètres seront repris par défaut sur les prochains courriers.'
                .($syncedLettersCount > 0 ? ' '.$syncedLettersCount.' courrier(s) en préparation ont aussi été mis à jour.' : '')
        );
    }

    public function download(Request $request, User $institutionAdmin, InstitutionActivationLetterPdfService $pdf): Response
    {
        $this->abortIfNotInstitutionAdmin($institutionAdmin);
        $letter = $this->letterFor($institutionAdmin, $request);
        $filename = 'courrier-point-focal-'.$letter->activation_code.'.pdf';

        return response($pdf->make($letter), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function print(Request $request, User $institutionAdmin): View
    {
        $this->abortIfNotInstitutionAdmin($institutionAdmin);

        return view('super-admin.institution-admins.activation-letter-print', [
            'institutionAdmin' => $institutionAdmin->load('organization'),
            'letter' => $this->letterFor($institutionAdmin, $request),
        ]);
    }

    public function approveAndSendAccess(Request $request, User $institutionAdmin, SmsService $smsService, TopTeaserEmailService $emailService, ActivityLogger $activityLogger): RedirectResponse
    {
        $this->abortIfNotInstitutionAdmin($institutionAdmin);

        $letter = $this->letterFor($institutionAdmin, $request);

        if ($letter->status !== 'submitted') {
            return back()->withErrors([
                'activation' => 'Le point focal doit d’abord soumettre ses informations avant l’envoi des accès.',
            ]);
        }

        if (blank($letter->focal_phone) || blank($letter->focal_email)) {
            return back()->withErrors([
                'activation' => 'Le téléphone et l’email du point focal sont obligatoires pour envoyer les accès.',
            ]);
        }

        $emailOwner = User::query()
            ->where('email', $letter->focal_email)
            ->whereKeyNot($institutionAdmin->id)
            ->first();

        if ($emailOwner && ! $this->canArchiveDuplicateInstitutionAdmin($emailOwner, $institutionAdmin)) {
            return back()->withErrors([
                'activation' => 'L’email du point focal est déjà utilisé par un autre compte qui ne dépend pas de cette institution.',
            ]);
        }

        $previousUserData = $institutionAdmin->only(['name', 'email', 'phone', 'password', 'status']);
        $previousEmailOwnerData = $emailOwner?->only(['name', 'email', 'phone', 'password', 'status']);
        $previousLetterStatus = $letter->status;
        $password = $this->temporaryPassword();
        $loginUrl = $this->portalUrl('/institution/login');

        if ($emailOwner) {
            $emailOwner->forceFill([
                'name' => trim(($emailOwner->name ?: 'Compte provisoire').' - remplacé'),
                'email' => $this->archivedEmail($emailOwner),
                'phone' => null,
                'status' => 'inactive',
            ])->save();
        }

        $institutionAdmin->update([
            'name' => trim($letter->focal_first_names.' '.$letter->focal_last_name),
            'email' => $letter->focal_email,
            'phone' => $letter->focal_phone,
            'password' => Hash::make($password),
            'status' => 'active',
        ]);

        $letter->update([
            'status' => 'approved',
            'approved_by' => $request->user()?->id,
        ]);

        $message = "My-Signal: votre acces au portail institutionnel est active. Lien: {$loginUrl} Identifiant: {$institutionAdmin->email} Mot de passe temporaire: {$password}";
        $mailSent = false;
        $mailError = null;

        try {
            $smsService->sendSmsMtarget($message, (string) $institutionAdmin->phone);
        } catch (Throwable $exception) {
            $institutionAdmin->forceFill($previousUserData)->save();
            if ($emailOwner && $previousEmailOwnerData) {
                $emailOwner->forceFill($previousEmailOwnerData)->save();
            }
            $letter->forceFill(['status' => $previousLetterStatus, 'approved_by' => null])->save();

            $activityLogger->log(
                'institution_activation.access_send_failed',
                'Échec d’envoi des accès du point focal institutionnel.',
                $letter,
                [
                    'organization_id' => $letter->organization_id,
                    'institution_admin_id' => $institutionAdmin->id,
                    'error' => $exception->getMessage(),
                ],
                $request,
                $request->user(),
            );

            return back()->withErrors([
                'activation' => 'L’envoi SMS a échoué. Cause: '.$exception->getMessage(),
            ]);
        }

        if ((bool) config('services.institution_activation.send_email', false)) {
            try {
                $emailService->send(
                    (string) $institutionAdmin->email,
                    'Vos accès au portail institutionnel My-Signal',
                    $this->accessEmailHtml($institutionAdmin, $loginUrl, $password),
                    $this->accessEmailText($institutionAdmin, $loginUrl, $password),
                );
                $mailSent = true;
            } catch (Throwable $exception) {
                $mailError = $exception->getMessage();
            }
        }

        $activityLogger->log(
            'institution_activation.access_sent',
            'Validation du point focal et envoi des accès institutionnels.',
            $letter,
            [
                'organization_id' => $letter->organization_id,
                'institution_admin_id' => $institutionAdmin->id,
                'login_url' => $loginUrl,
                'archived_duplicate_user_id' => $emailOwner?->id,
                'mail_sent' => $mailSent,
                'mail_error' => $mailError,
            ],
            $request,
            $request->user(),
        );

        $success = 'Le point focal a été validé et les accès ont été envoyés par SMS. Lien: '.$loginUrl.' Identifiant: '.$institutionAdmin->email.' Mot de passe temporaire: '.$password;

        if ($mailError) {
            $success .= ' L’envoi par e-mail est prévu mais a échoué. Cause: '.$mailError;
        } elseif ($mailSent) {
            $success .= ' Les accès ont également été envoyés par e-mail.';
        }

        return back()->with('success', $success);
    }

    private function letterFor(User $institutionAdmin, Request $request): InstitutionActivationLetter
    {
        $institutionAdmin->loadMissing('organization');

        $letter = InstitutionActivationLetter::query()
            ->where('institution_admin_id', $institutionAdmin->id)
            ->where('organization_id', $institutionAdmin->organization_id)
            ->where('status', '!=', 'superseded')
            ->latest('id')
            ->first();

        if ($letter) {
            $activationUrl = $this->activationUrl($letter->activation_code);

            if ($letter->activation_url !== $activationUrl) {
                $letter->forceFill([
                    'activation_url' => $activationUrl,
                ])->save();
            }

            return $letter->loadMissing(['organization', 'institutionAdmin']);
        }

        $code = $this->uniqueActivationCode($institutionAdmin);
        $presentationDefaults = $this->letterPresentationDefaults();

        return InstitutionActivationLetter::query()->create([
            'organization_id' => $institutionAdmin->organization_id,
            'institution_admin_id' => $institutionAdmin->id,
            'created_by' => $request->user()?->id,
            'activation_code' => $code,
            'activation_url' => $this->activationUrl($code),
            'letter_number' => $this->nextLetterNumber(),
            'issue_place' => 'Abidjan',
            'issue_date' => now()->toDateString(),
            'letter_subject' => 'Désignation du point focal My-Signal',
            'letter_content' => $this->defaultLetterContent($institutionAdmin, $code),
            'signature_name' => $presentationDefaults['signature_name'],
            'signature_title' => $presentationDefaults['signature_title'],
            'signature_content' => $presentationDefaults['signature_content'],
            'signature_path' => $presentationDefaults['signature_path'],
            'footer_logo_path' => $presentationDefaults['footer_logo_path'],
            'logo_position' => $presentationDefaults['logo_position'],
            'logo_path' => $presentationDefaults['logo_path'],
            'header_settings' => $presentationDefaults['header_settings'],
            'footer_settings' => $presentationDefaults['footer_settings'],
            'status' => 'draft',
            'expires_at' => now()->addDays(30),
        ])->loadMissing(['organization', 'institutionAdmin']);
    }

    private function letterPresentationDefaults(): array
    {
        $blankLetter = new InstitutionActivationLetter();
        $headerDefaults = $blankLetter->defaultHeaderSettings();
        $footerDefaults = $blankLetter->defaultFooterSettings();

        if (Schema::hasTable('institution_activation_letter_settings')) {
            $setting = InstitutionActivationLetterSetting::current();

            if ($setting) {
                return $setting->presentationDefaults();
            }
        }

        $template = InstitutionActivationLetter::query()
            ->where(function ($query): void {
                $query->whereNotNull('header_settings')
                    ->orWhereNotNull('footer_settings')
                    ->orWhereNotNull('logo_path')
                    ->orWhereNotNull('footer_logo_path')
                    ->orWhereNotNull('signature_path');
            })
            ->latest('updated_at')
            ->latest('id')
            ->first();

        if (! $template) {
            return [
                'logo_position' => 'left',
                'logo_path' => null,
                'signature_name' => 'Le Coordonnateur du programme My-Signal',
                'signature_title' => 'Union Fédérale des Consommateurs',
                'signature_content' => '<p>Pour l’Union Fédérale des Consommateurs</p>',
                'signature_path' => null,
                'footer_logo_path' => null,
                'header_settings' => $headerDefaults,
                'footer_settings' => $footerDefaults,
            ];
        }

        return [
            'logo_position' => $template->logo_position ?: 'left',
            'logo_path' => $template->logo_path,
            'signature_name' => $template->signature_name ?: 'Le Coordonnateur du programme My-Signal',
            'signature_title' => $template->signature_title ?: 'Union Fédérale des Consommateurs',
            'signature_content' => $template->signature_content ?: '<p>Pour l’Union Fédérale des Consommateurs</p>',
            'signature_path' => $template->signature_path,
            'footer_logo_path' => $template->footer_logo_path,
            'header_settings' => array_replace_recursive($headerDefaults, $template->header_settings ?? []),
            'footer_settings' => array_replace_recursive($footerDefaults, $template->footer_settings ?? []),
        ];
    }

    private function savePresentationDefaults(InstitutionActivationLetter $letter, ?int $updatedBy): void
    {
        if (! Schema::hasTable('institution_activation_letter_settings')) {
            return;
        }

        InstitutionActivationLetterSetting::query()->updateOrCreate(
            ['key' => InstitutionActivationLetterSetting::DEFAULT_KEY],
            InstitutionActivationLetterSetting::attributesFromLetter($letter, $updatedBy)
        );
    }

    private function syncPresentationDefaultsToPendingLetters(InstitutionActivationLetter $source): int
    {
        $presentationAttributes = $this->presentationAttributesFromLetter($source);
        $syncedCount = 0;

        InstitutionActivationLetter::query()
            ->whereKeyNot($source->id)
            ->whereIn('status', ['draft', 'generated'])
            ->get()
            ->each(function (InstitutionActivationLetter $letter) use ($presentationAttributes, &$syncedCount): void {
                $letter->forceFill($presentationAttributes)->save();
                $syncedCount++;
            });

        return $syncedCount;
    }

    private function presentationAttributesFromLetter(InstitutionActivationLetter $letter): array
    {
        return [
            'logo_position' => $letter->logo_position ?: 'left',
            'logo_path' => $letter->logo_path,
            'signature_name' => $letter->signature_name,
            'signature_title' => $letter->signature_title,
            'signature_content' => $letter->signature_content,
            'signature_path' => $letter->signature_path,
            'footer_logo_path' => $letter->footer_logo_path,
            'header_settings' => $letter->header_settings,
            'footer_settings' => $letter->footer_settings,
        ];
    }

    private function deleteLetterAssetIfUnused(WasabiService $wasabiService, InstitutionActivationLetter $letter, string $column): void
    {
        $path = $letter->{$column};

        if (blank($path)) {
            return;
        }

        if (Str::startsWith((string) $path, ['public:', 'http://', 'https://']) || file_exists(public_path((string) $path))) {
            return;
        }

        $isUsedByAnotherLetter = InstitutionActivationLetter::query()
            ->whereKeyNot($letter->id)
            ->where($column, $path)
            ->exists();
        $isUsedByDefaultSetting = Schema::hasTable('institution_activation_letter_settings')
            && InstitutionActivationLetterSetting::query()
                ->where($column, $path)
                ->exists();

        if (! $isUsedByAnotherLetter && ! $isUsedByDefaultSetting) {
            $wasabiService->deleteFile($path);
        }
    }

    private function uniqueActivationCode(User $institutionAdmin): string
    {
        $organization = $institutionAdmin->organization;
        $seed = $organization?->code ?: $organization?->name ?: 'INST';
        $prefix = 'UFC-'.Str::upper(Str::slug((string) $seed, '-'));
        $prefix = Str::limit($prefix, 28, '');

        do {
            $code = $prefix.'-'.Str::upper(Str::random(5));
        } while (InstitutionActivationLetter::query()->where('activation_code', $code)->exists());

        return $code;
    }

    private function createBatchLetter(InstitutionActivationLetterBatch $batch, User $institutionAdmin, array $attributes, ?int $actorId, mixed $generatedAt): InstitutionActivationLetter
    {
        $code = $this->uniqueActivationCode($institutionAdmin);
        $presentationDefaults = $this->letterPresentationDefaults();

        return InstitutionActivationLetter::query()->create([
            'batch_id' => $batch->id,
            'organization_id' => $institutionAdmin->organization_id,
            'institution_admin_id' => $institutionAdmin->id,
            'created_by' => $actorId,
            'activation_code' => $code,
            'activation_url' => $this->activationUrl($code),
            'letter_number' => $this->nextLetterNumber(),
            'issue_place' => $attributes['issue_place'],
            'issue_date' => $attributes['issue_date'],
            'letter_subject' => $attributes['letter_subject'],
            'letter_content' => $this->defaultLetterContent($institutionAdmin, $code),
            'signature_name' => $presentationDefaults['signature_name'],
            'signature_title' => $presentationDefaults['signature_title'],
            'signature_content' => $presentationDefaults['signature_content'],
            'signature_path' => $presentationDefaults['signature_path'],
            'footer_logo_path' => $presentationDefaults['footer_logo_path'],
            'logo_position' => $presentationDefaults['logo_position'],
            'logo_path' => $presentationDefaults['logo_path'],
            'header_settings' => $presentationDefaults['header_settings'],
            'footer_settings' => $presentationDefaults['footer_settings'],
            'status' => 'generated',
            'generated_at' => $generatedAt,
            'expires_at' => $attributes['expires_at'] ?? now()->addDays(30),
        ]);
    }

    private function supersedePendingLetters(User $institutionAdmin, ?int $actorId, mixed $supersededAt): void
    {
        InstitutionActivationLetter::query()
            ->where('institution_admin_id', $institutionAdmin->id)
            ->whereIn('status', ['draft', 'generated'])
            ->update([
                'status' => 'superseded',
                'superseded_at' => $supersededAt,
                'superseded_by_id' => $actorId,
                'updated_at' => $supersededAt,
            ]);
    }

    private function nextBatchReference(): string
    {
        do {
            $reference = 'LOT-UFC-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(4));
        } while (InstitutionActivationLetterBatch::query()->where('reference', $reference)->exists());

        return $reference;
    }

    private function defaultLetterContent(User $institutionAdmin, string $code): string
    {
        $organizationName = $institutionAdmin->organization?->name ?: 'votre institution';
        $activationUrl = $this->activationUrl($code);

        return <<<HTML
<p>Madame, Monsieur,</p>
<p>Dans le cadre du déploiement de la plateforme <strong>My-Signal</strong>, initiative portée par l’Union Fédérale des Consommateurs, nous vous prions de bien vouloir désigner officiellement le point focal habilité à administrer l’espace institutionnel de <strong>{$organizationName}</strong>.</p>
<p>Cette personne sera l’interlocuteur opérationnel chargé du suivi, de l’orientation et du traitement des signalements relevant de votre institution.</p>
<p>Le point focal désigné devra renseigner ses informations à partir du lien sécurisé ci-dessous :</p>
<p><strong>{$activationUrl}</strong></p>
<p>Code d’activation : <strong>{$code}</strong></p>
<p>Ce code est strictement rattaché à votre institution. Il permet de garantir l’identification correcte de la structure concernée et la traçabilité de la désignation.</p>
<p>Nous vous remercions de transmettre ce courrier à la personne dûment mandatée afin de finaliser l’activation de votre espace institutionnel.</p>
<p>Veuillez agréer, Madame, Monsieur, l’expression de nos salutations distinguées.</p>
<p><strong>L’équipe My-Signal</strong></p>
HTML;
    }

    private function cleanLetterContent(string $html): string
    {
        $allowedTags = '<p><br><strong><b><em><i><u><ul><ol><li><blockquote><h2><h3><h4><div><span><a>';
        $html = strip_tags($html, $allowedTags);
        $html = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? $html;
        $html = preg_replace('/\s+style\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? $html;
        $html = preg_replace('/javascript\s*:/i', '', $html) ?? $html;
        $html = preg_replace('/<(span|div)>\s*<\/\1>/i', '', $html) ?? $html;

        return trim($html);
    }

    private function headerSettingsFromRequest(Request $request): array
    {
        return [
            'logo_width' => (int) $request->input('logo_width', 145),
            'show_platform_logo' => false,
            'platform_logo_width' => (int) $request->input('platform_logo_width', 72),
            'title' => $this->textStyleFromRequest($request, 'header_title', 'UNION FÉDÉRALE DES CONSOMMATEURS'),
            'subtitle' => $this->textStyleFromRequest($request, 'header_subtitle', "DE CÔTE D'IVOIRE"),
            'description' => $this->textStyleFromRequest($request, 'header_description', 'UFC - Côte d’Ivoire • Association de défense des consommateurs'),
        ];
    }

    private function footerSettingsFromRequest(Request $request): array
    {
        return [
            'logo' => [
                'label' => 'Logo',
                'size' => (int) $request->input('footer_logo_size', 72),
            ],
            'address' => $this->textStyleFromRequest($request, 'footer_address', 'Abidjan, Côte d’Ivoire', 'Adresse'),
            'phone' => $this->textStyleFromRequest($request, 'footer_phone', '', 'Téléphone'),
            'email' => $this->textStyleFromRequest($request, 'footer_email', '', 'Email'),
            'website' => $this->textStyleFromRequest($request, 'footer_website', 'https://my-signal.pro', 'Site web'),
        ];
    }

    private function textStyleFromRequest(Request $request, string $prefix, string $defaultText = '', ?string $label = null): array
    {
        $settings = [
            'text' => (string) $request->input($prefix.'_text', $defaultText),
            'size' => (int) $request->input($prefix.'_size', 10),
            'color' => (string) $request->input($prefix.'_color', '#475467'),
            'bold' => $request->boolean($prefix.'_bold'),
            'italic' => $request->boolean($prefix.'_italic'),
        ];

        if ($label !== null) {
            $settings['label'] = $label;
        }

        return $settings;
    }

    private function activationUrl(string $code): string
    {
        return $this->portalUrl('/institution/activation?code='.urlencode($code));
    }

    private function portalUrl(string $path): string
    {
        return rtrim((string) config('services.institution_activation.base_url', 'https://my-signal.pro'), '/').'/'.ltrim($path, '/');
    }

    private function canArchiveDuplicateInstitutionAdmin(User $emailOwner, User $institutionAdmin): bool
    {
        return ! $emailOwner->is_super_admin
            && (int) $emailOwner->organization_id === (int) $institutionAdmin->organization_id
            && (int) $emailOwner->user_type_id === (int) UserType::idFor(UserType::INSTITUTION_ADMIN);
    }

    private function archivedEmail(User $user): string
    {
        $base = 'archive-ai-'.$user->id.'-'.now()->format('YmdHis').'@my-signal.local';

        return Str::lower($base);
    }

    private function accessEmailHtml(User $institutionAdmin, string $loginUrl, string $password): string
    {
        $name = e($institutionAdmin->name ?: 'Point focal');
        $email = e($institutionAdmin->email);
        $url = e($loginUrl);
        $temporaryPassword = e($password);

        return <<<HTML
<div style="font-family:Arial,sans-serif;color:#152536;line-height:1.55">
  <h2 style="margin:0 0 12px">Vos accès My-Signal</h2>
  <p>Bonjour {$name},</p>
  <p>Votre accès au portail institutionnel My-Signal est activé.</p>
  <p><strong>Lien de connexion :</strong> <a href="{$url}">{$url}</a><br>
  <strong>Identifiant :</strong> {$email}<br>
  <strong>Mot de passe temporaire :</strong> {$temporaryPassword}</p>
  <p>Pour des raisons de sécurité, veuillez modifier ce mot de passe après votre première connexion.</p>
</div>
HTML;
    }

    private function accessEmailText(User $institutionAdmin, string $loginUrl, string $password): string
    {
        return "Bonjour {$institutionAdmin->name},\n\nVotre accès au portail institutionnel My-Signal est activé.\nLien de connexion: {$loginUrl}\nIdentifiant: {$institutionAdmin->email}\nMot de passe temporaire: {$password}\n\nVeuillez modifier ce mot de passe après votre première connexion.";
    }

    private function temporaryPassword(): string
    {
        return 'MS-'.Str::upper(Str::random(4)).'-'.random_int(1000, 9999);
    }

    private function nextLetterNumber(): string
    {
        $next = InstitutionActivationLetter::query()->count() + 1;

        return 'UFC/MS/'.now()->format('Y').'/'.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    private function logoPositions(): array
    {
        return [
            'left' => 'À gauche',
            'center' => 'Au centre',
            'right' => 'À droite',
            'none' => 'Sans logo',
        ];
    }

    private function abortIfNotInstitutionAdmin(User $user): void
    {
        $user->loadMissing('organization.organizationType');

        abort_if(
            $user->is_super_admin
                || (int) $user->user_type_id !== (int) UserType::idFor(UserType::INSTITUTION_ADMIN)
                || $user->organization_id === null
                || $user->organization?->organizationType?->code === 'PARTNER_ESTABLISHMENT',
            404
        );
    }
}
