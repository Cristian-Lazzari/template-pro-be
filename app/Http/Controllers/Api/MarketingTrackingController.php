<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CustomerPromotion;
use App\Services\Marketing\CustomerPromotionService;
use App\Services\Marketing\MarketingConsentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

class MarketingTrackingController extends Controller
{
    public function open(string $token, CustomerPromotionService $service, MarketingConsentService $consentService): Response
    {
        $customerPromotion = $this->findCustomerPromotion($token);

        if ($customerPromotion) {
            try {
                if ($consentService->customerHasTrackingConsent($customerPromotion->customer)) {
                    $customerPromotion = $service->markOpened($customerPromotion);

                    Log::debug('Marketing email open tracked.', [
                        'customer_promotion_id' => $customerPromotion->getKey(),
                        'opened_at' => $customerPromotion->email_open_at?->toDateTimeString(),
                    ]);
                }
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return $this->pixelResponse();
    }

    public function click(string $token, Request $request, CustomerPromotionService $service, MarketingConsentService $consentService): RedirectResponse
    {
        $customerPromotion = $this->findCustomerPromotion($token);

        if ($customerPromotion) {
            try {
                if ($consentService->customerHasTrackingConsent($customerPromotion->customer)) {
                    $service->markClicked($customerPromotion);
                }
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return redirect()->away($this->safeRedirectUrl($request->query('redirect')));
    }

    public function unsubscribe(string $token): Response
    {
        $customerPromotion = CustomerPromotion::query()
            ->with(['customer', 'campaign'])
            ->where('tracking_token', $token)
            ->first();

        if (! $customerPromotion || ! $customerPromotion->customer) {
            return $this->unsubscribeResponse('Richiesta gestita.');
        }

        $customer = $customerPromotion->customer;
        $basis = $customerPromotion->campaign?->consentBasis();
        $hasKnownBasis = $customerPromotion->campaign
            && in_array($customerPromotion->campaign->consent_basis, Campaign::consentBasisValues(), true);
        $now = now();

        if ($hasKnownBasis && $basis === Campaign::CONSENT_BASIS_EXPLICIT_EMAIL_MARKETING) {
            $customer->email_marketing_consent_at = null;
        } elseif ($hasKnownBasis && $basis === Campaign::CONSENT_BASIS_SOFT_EMAIL_MARKETING) {
            if ($customer->soft_email_marketing_unsubscribed_at === null) {
                $customer->soft_email_marketing_unsubscribed_at = $now;
            }
        } else {
            $customer->email_marketing_consent_at = null;
            if ($customer->soft_email_marketing_unsubscribed_at === null) {
                $customer->soft_email_marketing_unsubscribed_at = $now;
            }
        }

        $customer->consents_updated_at = $now;
        $customer->save();

        Log::info('Marketing email unsubscribe processed.', [
            'customer_id' => $customer->getKey(),
            'campaign_id' => $customerPromotion->campaign_id,
            'basis' => $hasKnownBasis ? $basis : 'fallback',
        ]);

        return $this->unsubscribeResponse('Iscrizione annullata correttamente.');
    }

    private function findCustomerPromotion(string $token): ?CustomerPromotion
    {
        return CustomerPromotion::query()
            ->with('customer')
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

    private function unsubscribeResponse(string $message): Response
    {
        $html = '<!DOCTYPE html>'
            . '<html lang="it">'
            . '<head>'
            . '<meta charset="UTF-8">'
            . '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
            . '<title>Annulla iscrizione</title>'
            . '</head>'
            . '<body style="font-family: Arial, sans-serif; background:#f5f7fb; color:#111827; margin:0; padding:40px 16px;">'
            . '<main style="max-width:560px; margin:0 auto; background:#fff; border-radius:12px; padding:28px; box-shadow:0 18px 40px rgba(15,23,42,.12);">'
            . '<h1 style="font-size:24px; margin:0 0 12px;">' . e($message) . '</h1>'
            . '<p style="font-size:15px; line-height:1.6; margin:0; color:#4b5563;">Puoi chiudere questa pagina.</p>'
            . '</main>'
            . '</body>'
            . '</html>';

        return response($html, 200)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
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
