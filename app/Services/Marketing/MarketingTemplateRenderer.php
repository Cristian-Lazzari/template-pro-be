<?php

namespace App\Services\Marketing;

use App\Models\CustomerPromotion;
use App\Models\Model;
use App\Models\Promotion;
use App\Models\PromotionTarget;
use Illuminate\Support\Facades\Route;

class MarketingTemplateRenderer
{
    public function render(CustomerPromotion $customerPromotion): array
    {
        $customerPromotion->loadMissing([
            'automation.model',
            'campaign.model',
            'customer',
            'promotion.targets',
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
            'heading' => $this->replaceVariables($this->resolveHeading($template, $customerPromotion), $variables),
            'body_html' => $bodyHtml,
            'body_text' => $bodyText,
            'ending' => $template && filled($template->ending)
                ? $this->replaceVariables((string) $template->ending, $variables)
                : null,
            'sender' => $template && filled($template->sender)
                ? $this->replaceVariables((string) $template->sender, $variables)
                : config('configurazione.APP_NAME', config('app.name')),
            'img_1' => $template?->img_1,
            'img_2' => $template?->img_2,
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
        $customerPromotion->loadMissing([
            'automation',
            'campaign',
            'customer',
            'promotion.targets',
        ]);

        $customer = $customerPromotion->customer;
        $promotion = $customerPromotion->promotion;
        $customerFirstName = trim((string) ($customer?->name ?? ''));
        $customerLastName = trim((string) ($customer?->surname ?? ''));
        $customerName = trim($customerFirstName . ' ' . $customerLastName);
        $trackingToken = (string) ($customerPromotion->tracking_token ?? '');
        $trackingOpenUrl = $this->trackingOpenUrl($trackingToken);
        $promotionRedirectPath = $promotion
            ? $this->resolvePromotionRedirectPath($promotion)
            : '/';
        $trackingClickUrl = $this->trackingClickUrl($trackingToken, $promotionRedirectPath);
        $promotionTypeDiscount = (string) ($promotion?->type_discount ?? '');
        $campaignName = (string) ($customerPromotion->campaign?->name ?? '');
        $automationName = (string) ($customerPromotion->automation?->name ?? '');

        return [
            'customer_name' => $customerName !== '' ? $customerName : 'Cliente',
            'customer_first_name' => $customerFirstName,
            'customer_last_name' => $customerLastName,
            'customer_email' => (string) ($customer?->email ?? $customer?->mail ?? ''),
            'customer_phone' => (string) ($customer?->phone ?? ''),
            'promotion_name' => (string) ($promotion?->name ?? ''),
            'promotion_slug' => (string) ($promotion?->slug ?? ''),
            'promotion_cta' => $promotionRedirectPath,
            'promotion_discount' => $promotion?->discount !== null ? (string) $promotion->discount : '',
            'promotion_type_discount' => $promotionTypeDiscount,
            'promotion_expiring_at' => $promotion?->expiring_at
                ? $promotion->expiring_at->format('d/m/Y')
                : '',
            'promotion_case_use' => (string) ($promotion?->case_use ?? ''),
            'promotion_minimum_pretest' => $promotion?->minimum_pretest !== null ? (string) $promotion->minimum_pretest : '',
            'promotion_discount_label' => $this->promotionDiscountLabel($promotion),
            'promotion_type_discount_label' => $this->promotionTypeDiscountLabel($promotionTypeDiscount),
            'product_name' => $this->targetName($promotion, PromotionTarget::TYPE_PRODUCT, ['name']),
            'menu_name' => $this->targetName($promotion, PromotionTarget::TYPE_MENU, ['name']),
            'category_name' => $this->targetName($promotion, PromotionTarget::TYPE_CATEGORY, ['name']),
            'post_title' => $this->targetName($promotion, PromotionTarget::TYPE_POST, ['title', 'name']),
            'campaign_name' => $campaignName,
            'automation_name' => $automationName,
            'marketing_source_name' => $campaignName !== '' ? $campaignName : $automationName,
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
                ? $this->stringValue($variables[$matches[1]])
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

    private function resolveHeading(?Model $template, CustomerPromotion $customerPromotion): string
    {
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

    private function promotionTypeDiscountLabel(string $typeDiscount): string
    {
        return match ($typeDiscount) {
            'fixed' => '€',
            'percentage' => '%',
            'gift' => 'Regalo',
            default => $typeDiscount,
        };
    }

    private function promotionDiscountLabel(?Promotion $promotion): string
    {
        if (! $promotion) {
            return '';
        }

        $typeDiscount = (string) ($promotion->type_discount ?? '');

        if ($typeDiscount === 'gift') {
            return 'Regalo';
        }

        if ($promotion->discount === null) {
            return '';
        }

        $discount = $this->formatDecimal($promotion->discount);

        return match ($typeDiscount) {
            'fixed' => $discount . '€',
            'percentage' => $discount . '%',
            default => $discount,
        };
    }

    private function targetName(?Promotion $promotion, string $targetType, array $fields): string
    {
        if (! $promotion) {
            return '';
        }

        $promotion->loadMissing('targets');

        $targetRow = $promotion->targets
            ->first(fn (PromotionTarget $target) => $target->target_type === $targetType);

        if (! $targetRow) {
            return '';
        }

        try {
            $target = $targetRow->target();
        } catch (\Throwable) {
            return '';
        }

        if (! $target) {
            return '';
        }

        foreach ($fields as $field) {
            $value = $target->{$field} ?? null;

            if (is_scalar($value) && trim((string) $value) !== '') {
                return trim((string) $value);
            }
        }

        return '';
    }

    private function formatDecimal($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $formatted = number_format((float) $value, 2, ',', '.');

        return str_ends_with($formatted, ',00')
            ? substr($formatted, 0, -3)
            : $formatted;
    }

    private function stringValue($value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_scalar($value) || $value instanceof \Stringable) {
            return (string) $value;
        }

        return '';
    }

    private function trackingOpenUrl(string $token): string
    {
        if ($token !== '' && Route::has('api.marketing.open')) {
            return $this->absoluteUrl(route('api.marketing.open', ['token' => $token], false));
        }

        return $this->absoluteUrl('/api/marketing/open/' . rawurlencode($token));
    }

    private function trackingClickUrl(string $token, ?string $redirect): string
    {
        $safeRedirect = $this->safeRedirectUrl($redirect);

        if ($token !== '' && Route::has('api.marketing.click')) {
            return $this->absoluteUrl(route(
                'api.marketing.click',
                [
                    'token' => $token,
                    'redirect' => $safeRedirect,
                ],
                false
            ));
        }

        return $this->absoluteUrl(
            '/api/marketing/click/' . rawurlencode($token)
            . '?redirect=' . rawurlencode($safeRedirect)
        );
    }

    private function resolvePromotionRedirectPath(Promotion $promotion): string
    {
        return match ($promotion->case_use) {
            'take_away', 'delivery' => '/ordina',
            'table' => '/check-out',
            default => $this->safeRedirectUrl($promotion->cta),
        };
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

    private function absoluteUrl(string $path): string
    {
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        $baseUrl = rtrim((string) config('configurazione.domain'), '/');

        if ($baseUrl === '') {
            $baseUrl = rtrim((string) env('DOMAIN'), '/');
        }

        if ($baseUrl === '') {
            $baseUrl = rtrim((string) config('app.url'), '/');
        }

        if ($baseUrl === '') {
            $baseUrl = rtrim((string) config('configurazione.APP_URL'), '/');
        }

        if ($baseUrl === '') {
            return url($path);
        }

        return $baseUrl . '/' . ltrim($path, '/');
    }
}
