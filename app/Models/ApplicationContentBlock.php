<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationContentBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'page_key',
        'block_key',
        'title',
        'subtitle',
        'body',
        'image_path',
        'meta',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'sort_order' => 'integer',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
