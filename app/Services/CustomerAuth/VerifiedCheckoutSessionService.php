<?php

namespace App\Services\CustomerAuth;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class VerifiedCheckoutSessionService
{
    private const HEADER_NAME = 'X-Verified-Checkout-Session';
    private const DURATION_DAYS = 30;

    public function issue(string $email): array
    {
        $normalizedEmail = Customer::normalizeEmail($email);
        $expiresAt = now()->addDays(self::DURATION_DAYS);

        return [
            'token' => Crypt::encryptString(json_encode([
                'email' => $normalizedEmail,
                'expires_at' => $expiresAt->toISOString(),
            ], JSON_THROW_ON_ERROR)),
            'email' => $normalizedEmail,
            'expires_at' => $expiresAt->toISOString(),
        ];
    }

    public function extractTokenFromRequest(Request $request): ?string
    {
        $token = $request->header(self::HEADER_NAME);

        if (filled($token)) {
            return (string) $token;
        }

        $bodyToken = $request->input('verified_session_token');

        return filled($bodyToken) ? (string) $bodyToken : null;
    }

    public function isValidForEmail(?string $token, string $email): bool
    {
        $payload = $this->parse($token);

        return $payload !== null
            && Customer::normalizeEmail($email) === $payload['email'];
    }

    public function parse(?string $token): ?array
    {
        if (!filled($token)) {
            return null;
        }

        try {
            $decoded = json_decode(Crypt::decryptString((string) $token), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $exception) {
            return null;
        }

        if (!is_array($decoded) || !isset($decoded['email'], $decoded['expires_at'])) {
            return null;
        }

        try {
            $expiresAt = Carbon::parse($decoded['expires_at']);
        } catch (\Throwable $exception) {
            return null;
        }

        if ($expiresAt->isPast()) {
            return null;
        }

        return [
            'email' => Customer::normalizeEmail((string) $decoded['email']),
            'expires_at' => $expiresAt->toISOString(),
        ];
    }
}
