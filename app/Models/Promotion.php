<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'status',
        'case_use',
        'discount',
        'type_discount',
        'minimum_pretest',
        'cta',
        'permanent',
        'schedule_at',
        'expiring_at',
        'total_activation',
        'total_sent',
        'total_used',
        'metadata',
    ];

    protected $casts = [
        'discount' => 'decimal:2',
        'minimum_pretest' => 'decimal:2',
        'permanent' => 'boolean',
        'schedule_at' => 'datetime',
        'expiring_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class, 'campaign_promotion')
            ->withPivot('total_activation', 'total_sent')
            ->withTimestamps();
    }

    public function automations()
    {
        return $this->belongsToMany(Automation::class, 'automation_promotion')
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

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->whereNotNull('schedule_at');
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query
            ->where('permanent', false)
            ->whereNotNull('expiring_at')
            ->where('expiring_at', '<', now());
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isExpired(): bool
    {
        return ! $this->isPermanent()
            && $this->expiring_at
            && $this->expiring_at->isPast();
    }

    public function isPermanent(): bool
    {
        return (bool) $this->permanent;
    }

    public function isReusable(): bool
    {
        $metadata = is_array($this->metadata) ? $this->metadata : [];

        return ($metadata['reusable'] ?? false) === true;
    }

    public function hasUsageLimit(): bool
    {
        return false;
    }

    public function isWithinValidPeriod(): bool
    {
        if ($this->isPermanent()) {
            return true;
        }

        if ($this->schedule_at && $this->schedule_at->isFuture()) {
            return false;
        }

        if ($this->expiring_at && $this->expiring_at->isPast()) {
            return false;
        }

        return true;
    }
}
