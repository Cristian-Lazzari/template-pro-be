<?php

namespace Tests\Unit;

use App\Models\Campaign;
use App\Models\Customer;
use App\Models\CustomerPromotion;
use App\Models\Promotion;
use App\Services\Marketing\MarketingTemplateRenderer;
use Illuminate\Support\Collection;
use Tests\TestCase;

class MarketingTemplateRendererTest extends TestCase
{
    public function test_it_builds_marketing_template_variables(): void
    {
        config(['configurazione.domain' => 'https://ristorante.test']);

        $promotion = new Promotion([
            'name' => 'Sconto tavolo',
            'slug' => 'sconto-tavolo',
            'cta' => '/prenota',
            'discount' => 10,
            'type_discount' => 'percentage',
            'case_use' => 'table',
            'minimum_pretest' => 3,
            'expiring_at' => null,
        ]);
        $promotion->setRelation('targets', new Collection());

        $campaign = new Campaign([
            'name' => 'Clienti primavera',
        ]);

        $customer = new Customer([
            'name' => 'Mario',
            'surname' => 'Rossi',
            'email' => 'mario@example.com',
            'phone' => '+39 333 000 0000',
        ]);

        $customerPromotion = new CustomerPromotion([
            'tracking_token' => 'token-test',
        ]);
        $customerPromotion->setRelation('customer', $customer);
        $customerPromotion->setRelation('promotion', $promotion);
        $customerPromotion->setRelation('campaign', $campaign);
        $customerPromotion->setRelation('automation', null);

        $variables = app(MarketingTemplateRenderer::class)->buildVariables($customerPromotion);

        $this->assertSame('Mario Rossi', $variables['customer_name']);
        $this->assertSame('+39 333 000 0000', $variables['customer_phone']);
        $this->assertSame('10%', $variables['promotion_discount_label']);
        $this->assertSame('%', $variables['promotion_type_discount_label']);
        $this->assertSame('/check-out', $variables['promotion_cta']);
        $this->assertSame('', $variables['promotion_expiring_at']);
        $this->assertSame('table', $variables['promotion_case_use']);
        $this->assertSame('3.00', $variables['promotion_minimum_pretest']);
        $this->assertSame('Clienti primavera', $variables['campaign_name']);
        $this->assertSame('Clienti primavera', $variables['marketing_source_name']);
        $this->assertStringStartsWith('https://ristorante.test/api/marketing/click/token-test', $variables['tracking_click_url']);
        $this->assertSame('/check-out', $this->queryRedirect($variables['tracking_click_url']));
    }

    public function test_take_away_promotion_redirects_to_order_path(): void
    {
        $variables = $this->variablesForPromotionCaseUse('take_away', '/promo');

        $this->assertSame('/ordina', $variables['promotion_cta']);
        $this->assertSame('/ordina', $this->queryRedirect($variables['tracking_click_url']));
    }

    public function test_delivery_promotion_redirects_to_order_path(): void
    {
        $variables = $this->variablesForPromotionCaseUse('delivery', '/promo');

        $this->assertSame('/ordina', $variables['promotion_cta']);
        $this->assertSame('/ordina', $this->queryRedirect($variables['tracking_click_url']));
    }

    public function test_table_promotion_redirects_to_checkout_path(): void
    {
        $variables = $this->variablesForPromotionCaseUse('table', '/prenota');

        $this->assertSame('/check-out', $variables['promotion_cta']);
        $this->assertSame('/check-out', $this->queryRedirect($variables['tracking_click_url']));
    }

    public function test_generic_promotion_uses_safe_relative_cta(): void
    {
        $variables = $this->variablesForPromotionCaseUse('generic', '/promo-speciale');

        $this->assertSame('/promo-speciale', $variables['promotion_cta']);
        $this->assertSame('/promo-speciale', $this->queryRedirect($variables['tracking_click_url']));
    }

    public function test_external_cta_is_ignored_for_tracking_redirect(): void
    {
        $variables = $this->variablesForPromotionCaseUse('generic', 'https://example.com/promo');

        $this->assertSame('/', $variables['promotion_cta']);
        $this->assertSame('/', $this->queryRedirect($variables['tracking_click_url']));
    }

    private function variablesForPromotionCaseUse(?string $caseUse, ?string $cta): array
    {
        config(['configurazione.domain' => 'https://ristorante.test']);

        $promotion = new Promotion([
            'name' => 'Promo test',
            'slug' => 'promo-test',
            'cta' => $cta,
            'case_use' => $caseUse,
        ]);
        $promotion->setRelation('targets', new Collection());

        $customerPromotion = new CustomerPromotion([
            'tracking_token' => 'token-test',
        ]);
        $customerPromotion->setRelation('customer', new Customer());
        $customerPromotion->setRelation('promotion', $promotion);
        $customerPromotion->setRelation('campaign', null);
        $customerPromotion->setRelation('automation', null);

        return app(MarketingTemplateRenderer::class)->buildVariables($customerPromotion);
    }

    private function queryRedirect(string $url): ?string
    {
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        return $query['redirect'] ?? null;
    }
}
