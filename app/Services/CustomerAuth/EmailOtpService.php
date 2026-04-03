<?php

namespace App\Services\CustomerAuth;

use App\Mail\CustomerOtpMail;
use App\Models\Customer;
use App\Models\EmailOtp;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class EmailOtpService
{
    private const EXPIRE_MINUTES = 5;
    private const MAX_ATTEMPTS = 5;
    private const MAX_SENDS_PER_WINDOW = 3;
    private const SEND_WINDOW_SECONDS = 600;

    public function send(string $email, string $lang): array
    {
        $email = Customer::normalizeEmail($email);
        $rateLimitKey = $this->rateLimitKey($email);

        if (RateLimiter::tooManyAttempts($rateLimitKey, self::MAX_SENDS_PER_WINDOW)) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => $this->message($lang, 'messages.otp_rate_limited'),
                'retry_after' => RateLimiter::availableIn($rateLimitKey),
            ], 429));
        }

        RateLimiter::hit($rateLimitKey, self::SEND_WINDOW_SECONDS);

        EmailOtp::query()->where('email', $email)->delete();

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Mail::to($email)->locale($lang)->send(
            new CustomerOtpMail($code, self::EXPIRE_MINUTES)
        );

        EmailOtp::query()->create([
            'email' => $email,
            'code' => Hash::make($code),
            'expires_at' => now()->addMinutes(self::EXPIRE_MINUTES),
            'attempts' => 0,
            'created_at' => now(),
        ]);

        return [
            'message' => $this->message($lang, 'messages.otp_sent'),
            'expires_in_minutes' => self::EXPIRE_MINUTES,
        ];
    }

    public function verify(string $email, string $code, string $lang): void
    {
        $email = Customer::normalizeEmail($email);

        $otp = EmailOtp::query()
            ->where('email', $email)
            ->latest('id')
            ->first();

        if (!$otp || $otp->expires_at?->isPast()) {
            $otp?->delete();

            throw ValidationException::withMessages([
                'code' => [$this->message($lang, 'messages.otp_invalid_or_expired')],
            ]);
        }

        if ($otp->attempts >= self::MAX_ATTEMPTS) {
            $otp->delete();

            throw ValidationException::withMessages([
                'code' => [$this->message($lang, 'messages.otp_too_many_attempts')],
            ]);
        }

        if (!Hash::check($code, $otp->code)) {
            $attempts = $otp->attempts + 1;

            if ($attempts >= self::MAX_ATTEMPTS) {
                $otp->delete();
            } else {
                $otp->attempts = $attempts;
                $otp->save();
            }

            throw ValidationException::withMessages([
                'code' => [$this->message($lang, 'messages.otp_incorrect')],
            ]);
        }

        $otp->delete();
    }

    private function rateLimitKey(string $email): string
    {
        return 'email-otp:' . sha1($email);
    }

    private function message(string $lang, string $key, array $replace = []): string
    {
        return Lang::get('customer.' . $key, $replace, $lang);
    }
}
