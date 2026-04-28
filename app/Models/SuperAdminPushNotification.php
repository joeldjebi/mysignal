<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'sent_by_user_id',
    'target_scope',
    'status',
    'title',
    'body',
    'requested_count',
    'sent_count',
    'failed_count',
    'target_user_ids',
    'sent_user_ids',
    'failed_user_ids',
    'failure_details',
    'sent_at',
])]
class SuperAdminPushNotification extends Model
{
    protected function casts(): array
    {
        return [
            'target_user_ids' => 'array',
            'sent_user_ids' => 'array',
            'failed_user_ids' => 'array',
            'failure_details' => 'array',
            'sent_at' => 'datetime',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by_user_id');
    }
}
