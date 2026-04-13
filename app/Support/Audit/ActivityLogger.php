<?php

namespace App\Support\Audit;

use App\Models\ActivityLog;
use App\Models\PublicUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ActivityLogger
{
    public function log(
        string $action,
        ?string $description = null,
        Model|string|null $subject = null,
        array $properties = [],
        ?Request $request = null,
        User|PublicUser|null $actor = null,
        ?string $portal = null,
    ): ActivityLog {
        $request ??= request();
        $actor ??= $this->resolveActor($request);

        return ActivityLog::query()->create([
            'actor_user_id' => $actor instanceof User ? $actor->id : null,
            'actor_public_user_id' => $actor instanceof PublicUser ? $actor->id : null,
            'portal' => $portal ?? $this->resolvePortal($actor, $request),
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject instanceof Model ? $subject->getMorphClass() : (is_string($subject) ? $subject : null),
            'subject_id' => $subject instanceof Model ? $subject->getKey() : null,
            'properties' => $properties !== [] ? $properties : null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }

    private function resolveActor(?Request $request): User|PublicUser|null
    {
        $publicUser = auth('public_api')->user();

        if ($publicUser instanceof PublicUser) {
            return $publicUser;
        }

        $user = $request?->user();

        return $user instanceof User ? $user : null;
    }

    private function resolvePortal(User|PublicUser|null $actor, ?Request $request): string
    {
        if ($actor instanceof PublicUser) {
            return 'public';
        }

        if ($actor instanceof User) {
            if ($actor->is_super_admin) {
                return 'super_admin';
            }

            if ($actor->organization_id !== null) {
                return 'institution';
            }

            return 'backoffice';
        }

        if ($request?->is('institution') || $request?->is('institution/*')) {
            return 'institution';
        }

        if ($request?->is('sa') || $request?->is('sa/*')) {
            return 'super_admin';
        }

        if ($request?->is('backoffice') || $request?->is('backoffice/*')) {
            return 'backoffice';
        }

        return 'public';
    }
}
