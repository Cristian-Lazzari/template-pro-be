<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerPromotion;
use App\Models\Promotion;
use App\Services\Marketing\MarketingCustomerSegmentService;
use App\Services\Marketing\MarketingEmailDispatchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketingConsentTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_marketing_audience_uses_explicit_consent_with_legacy_fallback(): void
    {
        $explicit = $this->createCustomer('explicit@example.com', [
            'email_marketing_consent_at' => now(),
        ]);
        $legacy = $this->createCustomer('legacy@example.com', [
            'marketing_consent_at' => now()->subDay(),
        ]);
        $revoked = $this->createCustomer('revoked@example.com', [
            'marketing_consent_at' => now()->subDay(),
            'consents_updated_at' => now(),
        ]);
        $withoutConsent = $this->createCustomer('no-consent@example.com');

        $customerIds = app(MarketingCustomerSegmentService::class)
            ->queryForSegment('all')
            ->pluck('id')
            ->all();

        $this->assertContains($explicit->id, $customerIds);
        $this->assertContains($legacy->id, $customerIds);
        $this->assertNotContains($revoked->id, $customerIds);
        $this->assertNotContains($withoutConsent->id, $customerIds);
    }

    public function test_marketing_email_dispatch_requires_email_marketing_consent_or_legacy_fallback(): void
    {
        $dispatchService = app(MarketingEmailDispatchService::class);

        $explicit = $this->createCustomerPromotion($this->createCustomer('send-explicit@example.com', [
            'email_marketing_consent_at' => now(),
        ]));
        $legacy = $this->createCustomerPromotion($this->createCustomer('send-legacy@example.com', [
            'marketing_consent_at' => now()->subDay(),
        ]));
        $revoked = $this->createCustomerPromotion($this->createCustomer('send-revoked@example.com', [
            'marketing_consent_at' => now()->subDay(),
            'consents_updated_at' => now(),
        ]));
        $withoutConsent = $this->createCustomerPromotion($this->createCustomer('send-no-consent@example.com'));

        $this->assertTrue($dispatchService->sendCustomerPromotion($explicit, true)['can_send']);
        $this->assertTrue($dispatchService->sendCustomerPromotion($legacy, true)['can_send']);
        $this->assertFalse($dispatchService->sendCustomerPromotion($revoked, true)['can_send']);
        $this->assertFalse($dispatchService->sendCustomerPromotion($withoutConsent, true)['can_send']);
    }

    public function test_open_and_click_tracking_are_not_saved_without_tracking_consent(): void
    {
        config(['configurazione.domain' => 'https://restaurant.test']);

        $customerPromotion = $this->createCustomerPromotion($this->createCustomer('no-tracking@example.com', [
            'email_marketing_consent_at' => now(),
        ]));

        $this->get('/api/marketing/open/' . $customerPromotion->tracking_token)
            ->assertOk()
            ->assertHeader('Content-Type', 'image/gif');

        $customerPromotion->refresh();
        $this->assertNull($customerPromotion->email_open_at);

        $this->get('/api/marketing/click/' . $customerPromotion->tracking_token . '?redirect=' . rawurlencode('/promo'))
            ->assertRedirect('https://restaurant.test/promo');

        $customerPromotion->refresh();
        $this->assertNull($customerPromotion->email_click_at);
    }

    public function test_open_and_click_tracking_are_saved_with_tracking_consent(): void
    {
        config(['configurazione.domain' => 'https://restaurant.test']);

        $customerPromotion = $this->createCustomerPromotion($this->createCustomer('tracking@example.com', [
            'email_marketing_consent_at' => now(),
            'tracking_consent_at' => now(),
        ]));

        $this->get('/api/marketing/open/' . $customerPromotion->tracking_token)
            ->assertOk();

        $customerPromotion->refresh();
        $this->assertNotNull($customerPromotion->email_open_at);

        $this->get('/api/marketing/click/' . $customerPromotion->tracking_token . '?redirect=' . rawurlencode('/promo'))
            ->assertRedirect('https://restaurant.test/promo');

        $customerPromotion->refresh();
        $this->assertNotNull($customerPromotion->email_click_at);
    }

    private function createCustomer(string $email, array $attributes = []): Customer
    {
        return Customer::query()->create(array_merge([
            'name' => 'Marketing',
            'surname' => 'Customer',
            'email' => $email,
            'phone' => '3331112222',
        ], $attributes));
    }

    private function createCustomerPromotion(Customer $customer): CustomerPromotion
    {
        $promotion = Promotion::query()->create([
            'name' => 'Promo test',
            'slug' => 'promo-' . $customer->id,
            'status' => 'active',
            'cta' => '/promo',
            'permanent' => true,
        ]);

        return CustomerPromotion::query()->create([
            'customer_id' => $customer->id,
            'promotion_id' => $promotion->id,
            'tracking_token' => '00000000-0000-4000-8000-' . str_pad((string) $customer->id, 12, '0', STR_PAD_LEFT),
            'status' => 'assigned',
        ]);
    }
}
