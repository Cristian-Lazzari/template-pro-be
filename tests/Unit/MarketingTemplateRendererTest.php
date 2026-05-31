<?php

namespace Tests\Unit;

use App\Models\Campaign;
use App\Models\Customer;
use App\Models\CustomerPromotion;
use App\Models\Model as MailModel;
use App\Models\Promotion;
use App\Services\Marketing\MarketingTemplateRenderer;
use Illuminate\Support\Collection;
use Tests\TestCase;

class MarketingTemplateRendererTest extends TestCase
{
    public function test_it_builds_marketing_template_variables(): void
    {
        $this->configureDomains();

        $promotion = new Promotion([
            'name' => 'Sconto tavolo',
            'slug' => 'sconto-tavolo',
            'cta' => 'Prenota ora',
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
        $this->assertSame('Prenota ora', $variables['promotion_cta']);
        $this->assertSame('Prenota ora', $variables['promotion_cta_label']);
        $this->assertSame('https://ristorante.test/check-out', $variables['promotion_url']);
        $this->assertSame('', $variables['promotion_expiring_at']);
        $this->assertSame('table', $variables['promotion_case_use']);
        $this->assertSame('3.00', $variables['promotion_minimum_pretest']);
        $this->assertSame('Clienti primavera', $variables['campaign_name']);
        $this->assertSame('Clienti primavera', $variables['marketing_source_name']);
        $this->assertStringStartsWith('https://backend.test/api/marketing/click/token-test', $variables['tracking_click_url']);
        $this->assertSame('https://ristorante.test/check-out', $this->queryRedirect($variables['tracking_click_url']));
    }

    public function test_take_away_promotion_redirects_to_order_path(): void
    {
        $variables = $this->variablesForPromotionCaseUse('take_away', 'Ordina subito');

        $this->assertSame('Ordina subito', $variables['promotion_cta']);
        $this->assertSame('https://ristorante.test/ordina', $variables['promotion_url']);
        $this->assertSame('https://ristorante.test/ordina', $this->queryRedirect($variables['tracking_click_url']));
    }

    public function test_delivery_promotion_redirects_to_order_path(): void
    {
        $variables = $this->variablesForPromotionCaseUse('delivery', null);

        $this->assertSame('Ordina ora', $variables['promotion_cta']);
        $this->assertSame('https://ristorante.test/ordina', $variables['promotion_url']);
        $this->assertSame('https://ristorante.test/ordina', $this->queryRedirect($variables['tracking_click_url']));
    }

    public function test_table_promotion_redirects_to_checkout_path(): void
    {
        $variables = $this->variablesForPromotionCaseUse('table', 'Prenota il tuo tavolo');

        $this->assertSame('Prenota il tuo tavolo', $variables['promotion_cta']);
        $this->assertSame('https://ristorante.test/check-out', $variables['promotion_url']);
        $this->assertSame('https://ristorante.test/check-out', $this->queryRedirect($variables['tracking_click_url']));
    }

    public function test_generic_promotion_redirects_to_order_path_and_uses_custom_cta_label(): void
    {
        $variables = $this->variablesForPromotionCaseUse('generic', 'Approfittane ora');

        $this->assertSame('Approfittane ora', $variables['promotion_cta']);
        $this->assertSame('https://ristorante.test/ordina', $variables['promotion_url']);
        $this->assertSame('https://ristorante.test/ordina', $this->queryRedirect($variables['tracking_click_url']));
    }

    public function test_legacy_link_cta_is_ignored_as_button_text(): void
    {
        $variables = $this->variablesForPromotionCaseUse('generic', 'https://example.com/promo');

        $this->assertSame('Ordina ora', $variables['promotion_cta']);
        $this->assertSame('https://ristorante.test/ordina', $variables['promotion_url']);
        $this->assertSame('https://ristorante.test/ordina', $this->queryRedirect($variables['tracking_click_url']));
    }

    public function test_render_uses_promotion_cta_as_button_label(): void
    {
        $this->configureDomains();

        $template = new MailModel([
            'has_promotion' => true,
            'heading' => 'Promo per te',
            'body_html' => '<p>Ciao @customer_first_name</p>',
        ]);

        $campaign = new Campaign([
            'name' => 'Campagna CTA',
            'model_id' => 9,
        ]);
        $campaign->setRelation('model', $template);

        $promotion = new Promotion([
            'name' => 'Promo test',
            'slug' => 'promo-test',
            'cta' => 'Ordina la tua cena',
            'case_use' => 'take_away',
        ]);
        $promotion->setRelation('targets', new Collection());

        $customer = new Customer([
            'name' => 'Mario',
            'email' => 'mario@example.com',
        ]);

        $customerPromotion = new CustomerPromotion([
            'campaign_id' => 9,
            'tracking_token' => 'token-test',
        ]);
        $customerPromotion->setRelation('customer', $customer);
        $customerPromotion->setRelation('promotion', $promotion);
        $customerPromotion->setRelation('campaign', $campaign);
        $customerPromotion->setRelation('automation', null);

        $rendered = app(MarketingTemplateRenderer::class)->render($customerPromotion);

        $this->assertSame('Ordina la tua cena', $rendered['cta_label']);
        $this->assertSame('https://ristorante.test/ordina', $this->queryRedirect($rendered['tracking_click_url']));
    }

    public function test_render_keeps_only_one_manual_promotion_block(): void
    {
        $rendered = $this->renderTemplate(new MailModel([
            'has_promotion' => true,
            'heading' => 'Promo per te',
            'body_html' => '<p>Prima @promotion</p><p>Dopo @promotion</p>',
        ]));

        $this->assertSame(1, substr_count($rendered['body_html'], 'Promo duplicata'));
    }

    public function test_solo_message_template_does_not_render_promotion_block_variable(): void
    {
        $rendered = $this->renderTemplate(new MailModel([
            'has_promotion' => false,
            'heading' => 'Solo messaggio',
            'body_html' => '<p>Ciao @customer_first_name, @promotion</p>',
        ]));

        $this->assertStringContainsString('Mario', $rendered['body_html']);
        $this->assertStringNotContainsString('Promo duplicata', $rendered['body_html']);
        $this->assertStringNotContainsString('@promotion', $rendered['body_html']);
    }

    public function test_marketing_mail_uses_public_storage_image_urls(): void
    {
        config([
            'app.url' => 'https://db-demo3.future-plus.it',
            'configurazione.APP_URL' => 'https://db-demo3.future-plus.it',
            'configurazione.domain' => 'https://ristorante.test',
        ]);

        $html = view('emails.marketing-promotion', [
            'rendered' => [
                'subject' => 'Promo test',
                'heading' => 'Promo test',
                'body_html' => '<p>Ciao</p>',
                'img_1' => 'public/uploads/beOeNq8vw5q6qC7N00YLP9q7xNUIanQmLsjuw0cH.png',
                'img_2' => null,
                'tracking_open_url' => '',
                'tracking_click_url' => '',
            ],
        ])->render();

        $this->assertStringContainsString(
            'src="https://db-demo3.future-plus.it/public/storage/public/uploads/beOeNq8vw5q6qC7N00YLP9q7xNUIanQmLsjuw0cH.png"',
            $html
        );
        $this->assertStringNotContainsString(
            'src="https://db-demo3.future-plus.it/storage/public/uploads/beOeNq8vw5q6qC7N00YLP9q7xNUIanQmLsjuw0cH.png"',
            $html
        );
    }

    private function variablesForPromotionCaseUse(?string $caseUse, ?string $cta): array
    {
        $this->configureDomains();

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

    private function renderTemplate(MailModel $template): array
    {
        $this->configureDomains();

        $campaign = new Campaign([
            'name' => 'Campagna promo',
            'model_id' => 9,
        ]);
        $campaign->setRelation('model', $template);

        $promotion = new Promotion([
            'name' => 'Promo duplicata',
            'slug' => 'promo-duplicata',
            'cta' => 'Ordina ora',
            'discount' => 20,
            'type_discount' => 'percentage',
            'case_use' => 'take_away',
        ]);
        $promotion->setRelation('targets', new Collection());

        $customer = new Customer([
            'name' => 'Mario',
            'surname' => 'Rossi',
            'email' => 'mario@example.com',
        ]);

        $customerPromotion = new CustomerPromotion([
            'campaign_id' => 9,
            'tracking_token' => 'token-test',
        ]);
        $customerPromotion->setRelation('customer', $customer);
        $customerPromotion->setRelation('promotion', $promotion);
        $customerPromotion->setRelation('campaign', $campaign);
        $customerPromotion->setRelation('automation', null);

        return app(MarketingTemplateRenderer::class)->render($customerPromotion);
    }

    private function configureDomains(): void
    {
        config([
            'app.url' => 'https://backend.test',
            'configurazione.APP_URL' => 'https://backend.test',
            'configurazione.domain' => 'https://ristorante.test',
        ]);
    }

    private function queryRedirect(string $url): ?string
    {
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        return $query['redirect'] ?? null;
    }
}
