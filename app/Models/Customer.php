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
        'age',
        'profile_answers',
        'registered_at',
        'marketing_consent_at',
        'profiling_consent_at',
        'email_verified_at',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'registered_at' => 'datetime',
        'marketing_consent_at' => 'datetime',
        'profiling_consent_at' => 'datetime',
        'profile_answers' => 'array',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
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

        if ($this->marketing_consent_at) {
            return 'soft_marketing';
        }

        return 'no_marketing';
    }

    public function toApiPayload(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'surname' => $this->surname,
            'email' => $this->email,
            'phone' => $this->phone,
            'gender' => $this->gender,
            'age' => $this->age,
            'profile_answers' => $this->profile_answers ?? [],
            'account_state' => $this->isRegistered() ? 'registered' : 'guest',
            'marketing_state' => $this->marketingState(),
            'marketing_enabled' => $this->marketing_consent_at !== null,
            'profiling_enabled' => $this->profiling_consent_at !== null,
            'registered_at' => $this->registered_at?->toISOString(),
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
