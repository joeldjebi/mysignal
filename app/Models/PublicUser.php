<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class PublicUser extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $fillable = [
        'public_user_type_id',
        'first_name',
        'last_name',
        'phone',
        'is_whatsapp_number',
        'email',
        'company_name',
        'company_registration_number',
        'tax_identifier',
        'business_sector',
        'company_address',
        'commune',
        'address',
        'latitude',
        'longitude',
        'location_accuracy',
        'location_source',
        'password',
        'phone_verified_at',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_whatsapp_number' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'location_accuracy' => 'integer',
        ];
    }

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'guard' => 'public_api',
            'phone' => $this->phone,
        ];
    }

    public function meters(): BelongsToMany
    {
        return $this->belongsToMany(Meter::class, 'meter_assignments')
            ->withPivot(['is_primary'])
            ->withTimestamps();
    }

    public function meterAssignments(): HasMany
    {
        return $this->hasMany(MeterAssignment::class);
    }

    public function ownedHousehold(): HasOne
    {
        return $this->hasOne(Household::class, 'owner_public_user_id');
    }

    public function householdMembers(): HasMany
    {
        return $this->hasMany(HouseholdMember::class);
    }

    public function incidentReports(): HasMany
    {
        return $this->hasMany(IncidentReport::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UpSubscription::class);
    }

    public function subscriptionPayments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    public function reparationCases(): HasMany
    {
        return $this->hasMany(ReparationCase::class);
    }

    public function publicUserType(): BelongsTo
    {
        return $this->belongsTo(PublicUserType::class);
    }

    public function businessSector(): BelongsTo
    {
        return $this->belongsTo(BusinessSector::class, 'business_sector', 'name');
    }
}
