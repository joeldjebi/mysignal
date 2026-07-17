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
        'profile_photo_path',
        'country_id',
        'city_id',
        'commune_id',
        'company_name',
        'company_registration_number',
        'tax_identifier',
        'business_sector',
        'company_address',
        'commune',
        'country',
        'city',
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

    public function profilePhotoUrl(): ?string
    {
        if (! filled($this->profile_photo_path)) {
            return null;
        }

        if (filter_var((string) $this->profile_photo_path, FILTER_VALIDATE_URL)) {
            return (string) $this->profile_photo_path;
        }

        if (str_starts_with((string) $this->profile_photo_path, 'public-users/')) {
            return app(\App\Services\WasabiService::class)->temporaryUrl((string) $this->profile_photo_path);
        }

        return asset((string) $this->profile_photo_path);
    }

    public function meters(): BelongsToMany
    {
        return $this->belongsToMany(Meter::class, 'meter_assignments')
            ->withPivot(['is_primary', 'assignment_source'])
            ->withTimestamps();
    }

    public function meterAssignments(): HasMany
    {
        return $this->hasMany(MeterAssignment::class);
    }

    public function purchaseReceipts(): HasMany
    {
        return $this->hasMany(PurchaseReceipt::class);
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

    public function latestSubscription(): HasOne
    {
        return $this->hasOne(UpSubscription::class)->latestOfMany();
    }

    public function subscriptionPayments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    public function discountCards(): HasMany
    {
        return $this->hasMany(UpDiscountCard::class);
    }

    public function privilegeCards(): HasMany
    {
        return $this->hasMany(PrivilegeCard::class);
    }

    public function activePrivilegeCard(): HasOne
    {
        return $this->hasOne(PrivilegeCard::class)
            ->where('status', 'active')
            ->latestOfMany();
    }

    public function privilegeCardPaymentSessions(): HasMany
    {
        return $this->hasMany(PrivilegeCardPaymentSession::class);
    }

    public function activeDiscountCard(): HasOne
    {
        return $this->hasOne(UpDiscountCard::class)
            ->where('status', 'active')
            ->latestOfMany();
    }

    public function partnerDiscountTransactions(): HasMany
    {
        return $this->hasMany(PartnerDiscountTransaction::class);
    }

    public function reparationCases(): HasMany
    {
        return $this->hasMany(ReparationCase::class);
    }

    public function deviceTokens(): HasMany
    {
        return $this->hasMany(DeviceToken::class, 'recipient_id')
            ->where('recipient_type', 'public');
    }

    public function activeDeviceTokens(): HasMany
    {
        return $this->deviceTokens()->whereNull('revoked_at');
    }

    public function latestDeviceToken(): HasOne
    {
        return $this->hasOne(DeviceToken::class, 'recipient_id')
            ->where('recipient_type', 'public')
            ->latestOfMany('last_seen_at');
    }

    public function publicUserType(): BelongsTo
    {
        return $this->belongsTo(PublicUserType::class);
    }

    public function countryReference(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function cityReference(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function communeReference(): BelongsTo
    {
        return $this->belongsTo(Commune::class, 'commune_id');
    }

    public function businessSector(): BelongsTo
    {
        return $this->belongsTo(BusinessSector::class, 'business_sector', 'name');
    }
}
