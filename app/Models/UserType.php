<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'description', 'status'])]
class UserType extends Model
{
    public const SUPER_ADMIN = 'SUPER_ADMIN';
    public const SA_USER = 'SA_USER';
    public const CALLCENTER = 'CALLCENTER';
    public const INSTITUTION_ADMIN = 'INSTITUTION_ADMIN';
    public const PARTNER_MANAGER = 'PARTNER_MANAGER';
    public const PARTNER_SCAN_AGENT = 'PARTNER_SCAN_AGENT';

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public static function idFor(string $code): ?int
    {
        return self::query()->where('code', $code)->value('id');
    }
}
