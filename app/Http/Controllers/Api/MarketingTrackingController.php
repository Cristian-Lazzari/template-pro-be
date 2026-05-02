<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerPromotion;
use App\Services\Marketing\CustomerPromotionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class MarketingTrackingController extends Controller
{
    public function open(string $token, CustomerPromotionService $service): Response
    {
        $customerPromotion = $this->findCustomerPromotion($token);

        if ($customerPromotion) {
            try {
                $service->markOpened($customerPromotion);
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

        return redirect()->to($this->safeRedirectUrl($request->query('redirect')));
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
            ->header('Pragma', 'no-cache');
    }

    private function safeRedirectUrl(mixed $redirect): string
    {
        if (! is_string($redirect) || $redirect === '' || strlen($redirect) > 2048) {
            return '/';
        }

        if (preg_match('/[\r\n]/', $redirect)) {
            return '/';
        }

        if (! str_starts_with($redirect, '/')) {
            return '/';
        }

        if (str_starts_with($redirect, '//') || str_starts_with($redirect, '/\\') || str_contains($redirect, '\\')) {
            return '/';
        }

        $parts = parse_url($redirect);

        if ($parts === false || isset($parts['scheme']) || isset($parts['host'])) {
            return '/';
        }

        return $redirect;
    }
}
