<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'surname',
        'email',
        'phone',
        'gender',
        'birthday',
        'profile_answers',
        'registered_at',
        'email_marketing_consent_at',
        'whatsapp_marketing_consent_at',
        'profiling_consent_at',
        'tracking_consent_at',
        'privacy_accepted_at',
        'privacy_accepted_version',
        'consents_updated_at',
        'soft_email_marketing_unsubscribed_at',
        'email_verified_at',
        'customer_score',
        'lifecycle_segment',
        'last_activity_at',
        'last_marketing_contact_at',
        'last_order_at',
        'last_booking_at',
        'first_order_at',
        'first_booking_at',
        'orders_count',
        'reservations_count',
        'interactions_count',
        'total_spent',
        'average_order_value',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'registered_at' => 'datetime',
        'email_marketing_consent_at' => 'datetime',
        'whatsapp_marketing_consent_at' => 'datetime',
        'profiling_consent_at' => 'datetime',
        'tracking_consent_at' => 'datetime',
        'privacy_accepted_at' => 'datetime',
        'consents_updated_at' => 'datetime',
        'soft_email_marketing_unsubscribed_at' => 'datetime',
        'birthday' => 'date',
        'profile_answers' => 'array',
        'last_activity_at' => 'datetime',
        'last_marketing_contact_at' => 'datetime',
        'last_order_at' => 'datetime',
        'last_booking_at' => 'datetime',
        'first_order_at' => 'datetime',
        'first_booking_at' => 'datetime',
        'customer_score' => 'integer',
        'orders_count' => 'integer',
        'reservations_count' => 'integer',
        'interactions_count' => 'integer',
        'total_spent' => 'decimal:2',
        'average_order_value' => 'decimal:2',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function customerPromotions()
    {
        return $this->hasMany(CustomerPromotion::class);
    }

    public function promotions()
    {
        return $this->belongsToMany(Promotion::class, 'customer_promotion')
            ->withPivot([
                'campaign_id',
                'automation_id',
                'email_sent_at',
                'email_click_at',
                'email_open_at',
                'promo_used',
                'tracking_token',
                'status',
                'discount_amount',
                'order_id',
                'reservation_id',
                'metadata',
            ])
            ->withTimestamps();
    }

    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class, 'customer_promotion')
            ->withPivot([
                'promotion_id',
                'automation_id',
                'email_sent_at',
                'email_click_at',
                'email_open_at',
                'promo_used',
                'tracking_token',
                'status',
                'discount_amount',
                'order_id',
                'reservation_id',
                'metadata',
            ])
            ->withTimestamps();
    }

    public function automations()
    {
        return $this->belongsToMany(Automation::class, 'customer_promotion')
            ->withPivot([
                'promotion_id',
                'campaign_id',
                'email_sent_at',
                'email_click_at',
                'email_open_at',
                'promo_used',
                'tracking_token',
                'status',
                'discount_amount',
                'order_id',
                'reservation_id',
                'metadata',
            ])
            ->withTimestamps();
    }

    public static function normalizeEmail(string $email): string
    {
        return mb_strtolower(trim($email));
    }

    public function isRegistered(): bool
    {
        return $this->registered_at !== null;
    }

    public function marketingState(): string
    {
        if ($this->profiling_consent_at) {
            return 'full';
        }

        if ($this->emailMarketingConsentAt(true)) {
            return 'soft_marketing';
        }

        return 'no_marketing';
    }

    public function emailMarketingConsentAt(): mixed
    {
        return $this->email_marketing_consent_at;
    }

    public function hasSoftEmailMarketingOptOut(): bool
    {
        return $this->soft_email_marketing_unsubscribed_at !== null;
    }

    public function toApiPayload(): array
    {
        $emailMarketingConsentAt = $this->emailMarketingConsentAt(true);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'surname' => $this->surname,
            'email' => $this->email,
            'phone' => $this->phone,
            'gender' => $this->gender,
            'birthday' => $this->birthday?->toDateString(),
            'profile_answers' => $this->profile_answers ?? [],
            'account_state' => $this->isRegistered() ? 'registered' : 'guest',
            'marketing_state' => $this->marketingState(),
            'marketing_enabled' => $emailMarketingConsentAt !== null,
            'email_marketing_enabled' => $emailMarketingConsentAt !== null,
            'whatsapp_marketing_enabled' => $this->whatsapp_marketing_consent_at !== null,
            'profiling_enabled' => $this->profiling_consent_at !== null,
            'tracking_enabled' => $this->tracking_consent_at !== null,
            'privacy_accepted' => $this->privacy_accepted_at !== null,
            'soft_email_marketing_unsubscribed' => $this->hasSoftEmailMarketingOptOut(),
            'email_marketing_consent_at' => $emailMarketingConsentAt?->toISOString(),
            'whatsapp_marketing_consent_at' => $this->whatsapp_marketing_consent_at?->toISOString(),
            'profiling_consent_at' => $this->profiling_consent_at?->toISOString(),
            'tracking_consent_at' => $this->tracking_consent_at?->toISOString(),
            'privacy_accepted_at' => $this->privacy_accepted_at?->toISOString(),
            'privacy_accepted_version' => $this->privacy_accepted_version,
            'consents_updated_at' => $this->consents_updated_at?->toISOString(),
            'soft_email_marketing_unsubscribed_at' => $this->soft_email_marketing_unsubscribed_at?->toISOString(),
            'registered_at' => $this->registered_at?->toISOString(),
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
