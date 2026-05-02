<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class Automation extends EloquentModel
{
    use HasFactory;

    protected $fillable = [
        'name',
        'trigger',
        'status',
        'model_id',
        'total_activation',
        'total_sent',
        'last_run_at',
        'metadata',
    ];

    protected $casts = [
        'last_run_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function model()
    {
        return $this->belongsTo(Model::class, 'model_id');
    }

    public function promotions()
    {
        return $this->belongsToMany(Promotion::class, 'automation_promotion')
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

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopePaused(Builder $query): Builder
    {
        return $query->where('status', 'paused');
    }
}
