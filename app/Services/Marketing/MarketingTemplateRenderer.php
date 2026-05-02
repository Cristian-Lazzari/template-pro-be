<?php

namespace App\Services\Marketing;

use App\Models\CustomerPromotion;
use App\Models\Model;
use Illuminate\Support\Facades\Route;

class MarketingTemplateRenderer
{
    public function render(CustomerPromotion $customerPromotion): array
    {
        $customerPromotion->loadMissing([
            'automation.model',
            'campaign.model',
            'customer',
            'promotion',
        ]);

        $template = $this->getTemplateFor($customerPromotion);
        $variables = $this->buildVariables($customerPromotion);

        $subject = $this->replaceVariables(
            $this->resolveSubject($template, $customerPromotion),
            $variables
        );

        $bodyHtml = $this->replaceVariables(
            $this->resolveBodyHtml($template, $variables),
            $variables
        );

        $bodyText = $template && filled($template->body_text)
            ? $this->replaceVariables((string) $template->body_text, $variables)
            : $this->htmlToText($bodyHtml);

        return [
            'subject' => $subject,
            'body_html' => $bodyHtml,
            'body_text' => $bodyText,
            'tracking_open_url' => $variables['tracking_open_url'],
            'tracking_click_url' => $variables['tracking_click_url'],
        ];
    }

    public function getTemplateFor(CustomerPromotion $customerPromotion): ?Model
    {
        $customerPromotion->loadMissing([
            'automation.model',
            'campaign.model',
        ]);

        if ($customerPromotion->campaign_id) {
            return $customerPromotion->campaign?->model;
        }

        if ($customerPromotion->automation_id) {
            return $customerPromotion->automation?->model;
        }

        return null;
    }

    public function buildVariables(CustomerPromotion $customerPromotion): array
    {
        $customerPromotion->loadMissing(['customer', 'promotion']);

        $customer = $customerPromotion->customer;
        $promotion = $customerPromotion->promotion;
        $customerFirstName = trim((string) ($customer?->name ?? ''));
        $customerLastName = trim((string) ($customer?->surname ?? ''));
        $customerName = trim($customerFirstName . ' ' . $customerLastName);
        $trackingToken = (string) ($customerPromotion->tracking_token ?? '');
        $trackingOpenUrl = $this->trackingOpenUrl($trackingToken);
        $trackingClickUrl = $this->trackingClickUrl($trackingToken, $promotion?->cta);

        return [
            'customer_name' => $customerName !== '' ? $customerName : 'Cliente',
            'customer_first_name' => $customerFirstName,
            'customer_last_name' => $customerLastName,
            'customer_email' => (string) ($customer?->email ?? ''),
            'promotion_name' => (string) ($promotion?->name ?? ''),
            'promotion_slug' => (string) ($promotion?->slug ?? ''),
            'promotion_cta' => (string) ($promotion?->cta ?? ''),
            'promotion_discount' => $promotion?->discount !== null ? (string) $promotion->discount : '',
            'promotion_type_discount' => (string) ($promotion?->type_discount ?? ''),
            'tracking_token' => $trackingToken,
            'tracking_open_url' => $trackingOpenUrl,
            'tracking_click_url' => $trackingClickUrl,
        ];
    }

    public function replaceVariables(string $content, array $variables): string
    {
        return (string) preg_replace_callback(
            '/{{\s*([A-Za-z0-9_]+)\s*}}/',
            fn (array $matches) => array_key_exists($matches[1], $variables)
                ? (string) $variables[$matches[1]]
                : $matches[0],
            $content
        );
    }

    private function resolveSubject(?Model $template, CustomerPromotion $customerPromotion): string
    {
        if ($template && filled($template->object)) {
            return (string) $template->object;
        }

        if ($template && filled($template->heading)) {
            return (string) $template->heading;
        }

        if (filled($customerPromotion->promotion?->name)) {
            return (string) $customerPromotion->promotion->name;
        }

        return 'Promozione per te';
    }

    private function resolveBodyHtml(?Model $template, array $variables): string
    {
        if ($template && filled($template->body_html)) {
            return (string) $template->body_html;
        }

        if ($template && filled($template->body)) {
            return $this->paragraphHtml((string) $template->body);
        }

        if ($template && (filled($template->heading) || filled($template->ending))) {
            return $this->composeLegacyHtml($template);
        }

        return $this->fallbackHtml($variables);
    }

    private function composeLegacyHtml(Model $template): string
    {
        $parts = [];

        if (filled($template->heading)) {
            $parts[] = '<h1>' . e((string) $template->heading) . '</h1>';
        }

        if (filled($template->body)) {
            $parts[] = $this->paragraphHtml((string) $template->body);
        }

        if (filled($template->ending)) {
            $parts[] = $this->paragraphHtml((string) $template->ending);
        }

        return implode("\n", $parts);
    }

    private function fallbackHtml(array $variables): string
    {
        $promotionName = $variables['promotion_name'] !== ''
            ? $variables['promotion_name']
            : 'Promozione per te';

        return '<h1>' . e($promotionName) . '</h1>'
            . "\n" . '<p>Ciao ' . e($variables['customer_name']) . ', abbiamo una promozione per te.</p>'
            . "\n" . '<p><a href="' . e($variables['tracking_click_url']) . '">Scopri la promozione</a></p>';
    }

    private function paragraphHtml(string $content): string
    {
        $content = str_replace('\n', "\n", $content);

        return '<p>' . nl2br(e($content)) . '</p>';
    }

    private function htmlToText(string $bodyHtml): ?string
    {
        $text = trim(html_entity_decode(strip_tags($bodyHtml), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        return $text !== '' ? $text : null;
    }

    private function trackingOpenUrl(string $token): string
    {
        if ($token !== '' && Route::has('api.marketing.open')) {
            return route('api.marketing.open', ['token' => $token], false);
        }

        return '/api/marketing/open/' . rawurlencode($token);
    }

    private function trackingClickUrl(string $token, ?string $redirect): string
    {
        $safeRedirect = $this->safeRedirectUrl($redirect);

        if ($token !== '' && Route::has('api.marketing.click')) {
            return route('api.marketing.click', [
                'token' => $token,
                'redirect' => $safeRedirect,
            ], false);
        }

        return '/api/marketing/click/' . rawurlencode($token)
            . '?redirect=' . rawurlencode($safeRedirect);
    }

    private function safeRedirectUrl(?string $redirect): string
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
