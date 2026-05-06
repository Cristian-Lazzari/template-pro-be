<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerPromotion;
use App\Services\Marketing\CustomerPromotionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

class MarketingTrackingController extends Controller
{
    public function open(string $token, CustomerPromotionService $service): Response
    {
        $customerPromotion = $this->findCustomerPromotion($token);

        if ($customerPromotion) {
            try {
                $customerPromotion = $service->markOpened($customerPromotion);

                Log::debug('Marketing email open tracked.', [
                    'customer_promotion_id' => $customerPromotion->getKey(),
                    'opened_at' => $customerPromotion->email_open_at?->toDateTimeString(),
                ]);
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return $this->pixelResponse();
    }

    public function click(string $token, Request $request, CustomerPromotionService $service): RedirectResponse
    {
        $customerPromotion = $this->findCustomerPromotion($token);

        if ($customerPromotion) {
            try {
                $service->markClicked($customerPromotion);
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return redirect()->away($this->safeRedirectUrl($request->query('redirect')));
    }

    private function findCustomerPromotion(string $token): ?CustomerPromotion
    {
        return CustomerPromotion::query()
            ->where('tracking_token', $token)
            ->first();
    }

    private function pixelResponse(): Response
    {
        $pixel = base64_decode('R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==');

        return response($pixel, 200)
            ->header('Content-Type', 'image/gif')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    private function safeRedirectUrl(mixed $redirect): string
    {
        $fallback = $this->publicBaseUrl();

        if (! is_string($redirect) || $redirect === '' || strlen($redirect) > 2048) {
            return $fallback;
        }

        if (preg_match('/[\r\n]/', $redirect)) {
            return $fallback;
        }

        if (str_contains($redirect, '\\')) {
            return $fallback;
        }

        if ($this->isRelativeRedirectPath($redirect)) {
            return $this->publicUrl($redirect);
        }

        $parts = parse_url($redirect);

        if ($parts === false || ! isset($parts['scheme'], $parts['host'])) {
            return $fallback;
        }

        if (! in_array(strtolower((string) $parts['scheme']), ['http', 'https'], true)) {
            return $fallback;
        }

        if (isset($parts['user']) || isset($parts['pass'])) {
            return $fallback;
        }

        if (strtolower((string) $parts['host']) !== $this->publicHost()) {
            return $fallback;
        }

        return $redirect;
    }

    private function isRelativeRedirectPath(string $redirect): bool
    {
        if (! str_starts_with($redirect, '/')) {
            return false;
        }

        if (str_starts_with($redirect, '//') || str_starts_with($redirect, '/\\')) {
            return false;
        }

        $parts = parse_url($redirect);

        return $parts !== false && ! isset($parts['scheme'], $parts['host']);
    }

    private function publicUrl(string $path): string
    {
        return $this->publicBaseUrl() . '/' . ltrim($path, '/');
    }

    private function publicBaseUrl(): string
    {
        $baseUrl = rtrim(trim((string) (
            config('configurazione.domain')
                ?: env('DOMAIN')
                ?: config('app.url')
                ?: config('configurazione.APP_URL')
                ?: url('/')
        )), '/');

        if ($baseUrl === '') {
            return url('/');
        }

        if (! preg_match('#^https?://#i', $baseUrl)) {
            $baseUrl = 'https://' . ltrim($baseUrl, '/');
        }

        return $baseUrl;
    }

    private function publicHost(): string
    {
        return strtolower((string) parse_url($this->publicBaseUrl(), PHP_URL_HOST));
    }
}
