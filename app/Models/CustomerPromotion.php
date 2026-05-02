<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerPromotion extends Model
{
    use HasFactory;

    protected $table = 'customer_promotion';

    protected $fillable = [
        'customer_id',
        'promotion_id',
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
    ];

    protected $casts = [
        'email_sent_at' => 'datetime',
        'email_click_at' => 'datetime',
        'email_open_at' => 'datetime',
        'promo_used' => 'datetime',
        'discount_amount' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function automation()
    {
        return $this->belongsTo(Automation::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function isSent(): bool
    {
        return $this->email_sent_at !== null;
    }

    public function isOpened(): bool
    {
        return $this->email_open_at !== null;
    }

    public function isClicked(): bool
    {
        return $this->email_click_at !== null;
    }

    public function isUsed(): bool
    {
        return $this->promo_used !== null;
    }
}
