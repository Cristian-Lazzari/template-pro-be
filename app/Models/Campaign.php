<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class Campaign extends EloquentModel
{
    use HasFactory;

    public const CHANNEL_EMAIL = 'email';
    public const CHANNEL_WHATSAPP = 'whatsapp';

    public const CONSENT_BASIS_EXPLICIT_EMAIL_MARKETING = 'explicit_email_marketing';
    public const CONSENT_BASIS_SOFT_EMAIL_MARKETING = 'soft_email_marketing';
    public const CONSENT_BASIS_WHATSAPP_MARKETING = 'whatsapp_marketing';

    protected $fillable = [
        'name',
        'status',
        'channel',
        'consent_basis',
        'segment',
        'model_id',
        'scheduled_at',
        'sent_at',
        'total_activation',
        'total_sent',
        'metadata',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function model()
    {
        return $this->belongsTo(Model::class, 'model_id');
    }

    public function promotions()
    {
        return $this->belongsToMany(Promotion::class, 'campaign_promotion')
            ->withPivot('total_activation', 'total_sent')
            ->withTimestamps();
    }

    public function customerPromotions()
    {
        return $this->hasMany(CustomerPromotion::class);
    }

    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'customer_promotion')
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

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->whereNotNull('scheduled_at');
    }

    public function scopeSent(Builder $query): Builder
    {
        return $query->whereNotNull('sent_at');
    }

    public static function consentBasisOptions(): array
    {
        return [
            self::CONSENT_BASIS_EXPLICIT_EMAIL_MARKETING => 'Email marketing con consenso esplicito',
            self::CONSENT_BASIS_SOFT_EMAIL_MARKETING => 'Email soft opt-in',
            self::CONSENT_BASIS_WHATSAPP_MARKETING => 'WhatsApp marketing',
        ];
    }

    public static function consentBasisValues(): array
    {
        return array_keys(self::consentBasisOptions());
    }

    public static function channelValues(): array
    {
        return [
            self::CHANNEL_EMAIL,
            self::CHANNEL_WHATSAPP,
        ];
    }

    public static function normalizeConsentBasis(?string $consentBasis): string
    {
        return in_array($consentBasis, self::consentBasisValues(), true)
            ? $consentBasis
            : self::CONSENT_BASIS_EXPLICIT_EMAIL_MARKETING;
    }

    public static function channelForConsentBasis(?string $consentBasis): string
    {
        return self::normalizeConsentBasis($consentBasis) === self::CONSENT_BASIS_WHATSAPP_MARKETING
            ? self::CHANNEL_WHATSAPP
            : self::CHANNEL_EMAIL;
    }

    public function consentBasis(): string
    {
        return self::normalizeConsentBasis($this->consent_basis);
    }

    public function consentBasisLabel(): string
    {
        return self::consentBasisOptions()[$this->consentBasis()];
    }

    public function isEmailChannel(): bool
    {
        return ($this->channel ?: self::channelForConsentBasis($this->consentBasis())) === self::CHANNEL_EMAIL;
    }

    public function isWhatsappChannel(): bool
    {
        return ($this->channel ?: self::channelForConsentBasis($this->consentBasis())) === self::CHANNEL_WHATSAPP;
    }

    public function usesExplicitEmailMarketingConsent(): bool
    {
        return $this->consentBasis() === self::CONSENT_BASIS_EXPLICIT_EMAIL_MARKETING;
    }

    public function usesSoftEmailMarketing(): bool
    {
        return $this->consentBasis() === self::CONSENT_BASIS_SOFT_EMAIL_MARKETING;
    }

    public function usesWhatsappMarketingConsent(): bool
    {
        return $this->consentBasis() === self::CONSENT_BASIS_WHATSAPP_MARKETING;
    }
}
