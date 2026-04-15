<?php

namespace App\Http\Controllers\Api\V1\Public\Rex;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Public\Rex\RexFeedbackResource;
use App\Models\IncidentReport;
use App\Models\ReparationCase;
use App\Models\RexFeedback;
use App\Models\RexSetting;
use App\Support\Api\ApiResponse;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PublicRexFeedbackController extends Controller
{
    public function index(Request $request)
    {
        $feedbacks = RexFeedback::query()
            ->with(['incidentReport', 'application', 'organization'])
            ->where('public_user_id', $request->user('public_api')->id)
            ->latest('id')
            ->get();

        return ApiResponse::success([
            'feedbacks' => RexFeedbackResource::collection($feedbacks),
        ]);
    }

    public function store(Request $request)
    {
        $attributes = $request->validate([
            'context_type' => ['required', Rule::in(['incident_report', 'damage_declaration', 'reparation_case'])],
            'context_id' => ['required', 'integer', 'min:1'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'is_resolved' => ['nullable', 'boolean'],
            'response_time_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'communication_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'quality_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'fairness_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:3000'],
        ]);

        $setting = RexSetting::current();

        if (! $setting->is_enabled) {
            throw ValidationException::withMessages(['rex' => ['Le module REX est actuellement desactive.']]);
        }

        [$report, $availableAt] = $this->resolveEligibleContext($request, $attributes['context_type'], (int) $attributes['context_id'], $setting);

        if ($availableAt instanceof CarbonInterface && now()->greaterThan($availableAt->copy()->addDays((int) $setting->available_days))) {
            throw ValidationException::withMessages(['rex' => ['Le delai de retour d experience est depasse.']]);
        }

        $feedback = RexFeedback::query()->updateOrCreate(
            [
                'public_user_id' => $request->user('public_api')->id,
                'context_type' => $attributes['context_type'],
                'context_id' => (int) $attributes['context_id'],
            ],
            [
                'incident_report_id' => $report?->id,
                'application_id' => $report?->application_id,
                'organization_id' => $report?->organization_id,
                'rating' => (int) $attributes['rating'],
                'is_resolved' => $request->has('is_resolved') ? $request->boolean('is_resolved') : null,
                'response_time_rating' => $attributes['response_time_rating'] ?? null,
                'communication_rating' => $attributes['communication_rating'] ?? null,
                'quality_rating' => $attributes['quality_rating'] ?? null,
                'fairness_rating' => $attributes['fairness_rating'] ?? null,
                'comment' => $attributes['comment'] ?? null,
                'status' => 'submitted',
                'submitted_at' => now(),
            ],
        );

        return ApiResponse::success([
            'feedback' => new RexFeedbackResource($feedback->load(['incidentReport', 'application', 'organization'])),
        ], 'Retour d experience enregistre.', 201);
    }

    private function resolveEligibleContext(Request $request, string $contextType, int $contextId, RexSetting $setting): array
    {
        if ($contextType === 'incident_report') {
            abort_unless($setting->incident_report_enabled, 422, 'Le REX signalement est desactive.');
            $report = IncidentReport::query()->findOrFail($contextId);
            abort_unless((int) $report->public_user_id === (int) $request->user('public_api')->id, 404);
            $eligible = in_array($report->status, ['resolved', 'closed'], true) || $report->resolution_confirmation_status === 'confirmed';
            abort_unless($eligible, 422, 'Ce signalement n est pas encore eligible au REX.');

            return [$report, $report->resolution_confirmed_at ?? $report->resolved_at ?? $report->updated_at];
        }

        if ($contextType === 'damage_declaration') {
            abort_unless($setting->damage_enabled, 422, 'Le REX dommage est desactive.');
            $report = IncidentReport::query()->findOrFail($contextId);
            abort_unless((int) $report->public_user_id === (int) $request->user('public_api')->id, 404);
            $eligible = $report->damage_declared_at !== null
                && in_array($report->damage_resolution_status, ['resolved', 'rejected', 'compensated', 'closed'], true);
            abort_unless($eligible, 422, 'Ce dommage n est pas encore eligible au REX.');

            return [$report, $report->damage_resolved_at ?? $report->updated_at];
        }

        abort_unless($setting->reparation_case_enabled, 422, 'Le REX dossier est desactive.');
        $case = ReparationCase::query()->with('incidentReport')->findOrFail($contextId);
        abort_unless((int) $case->public_user_id === (int) $request->user('public_api')->id, 404);
        abort_unless(in_array($case->status, ['approved', 'rejected', 'compensated', 'closed'], true), 422, 'Ce dossier n est pas encore eligible au REX.');

        return [$case->incidentReport, $case->closed_at ?? $case->updated_at];
    }
}
