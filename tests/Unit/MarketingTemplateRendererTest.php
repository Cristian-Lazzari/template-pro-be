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
        $this->assertSame('', $variables['promotion_expiring_at']);
        $this->assertSame('table', $variables['promotion_case_use']);
        $this->assertSame('3.00', $variables['promotion_minimum_pretest']);
        $this->assertSame('Clienti primavera', $variables['campaign_name']);
        $this->assertSame('Clienti primavera', $variables['marketing_source_name']);
        $this->assertStringContainsString('/api/marketing/click/token-test', $variables['tracking_click_url']);
    }
}
