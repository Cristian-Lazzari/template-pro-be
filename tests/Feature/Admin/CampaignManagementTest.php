<?php

namespace Tests\Feature\Admin;

use App\Models\Campaign;
use App\Models\Customer;
use App\Models\CustomerPromotion;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CampaignManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_incomplete_campaign_creation_redirects_to_index(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.campaigns.store'), [
            'name' => 'Campagna da finire',
            'submit_action' => 'draft',
            'consent_basis' => Campaign::CONSENT_BASIS_SOFT_EMAIL_MARKETING,
            'segment' => 'all',
            'schedule_window' => 'next_available',
            'promotions' => [],
        ]);

        $response->assertRedirect(route('admin.campaigns.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('campaigns', [
            'name' => 'Campagna da finire',
            'status' => 'draft',
        ]);
    }

    public function test_draft_campaign_can_be_deleted_with_connections(): void
    {
        $admin = User::factory()->create();
        $campaign = $this->createCampaign(['status' => 'draft']);
        $promotion = $this->createPromotion();
        $customer = $this->createCustomer();

        $campaign->promotions()->attach($promotion);
        CustomerPromotion::query()->create([
            'customer_id' => $customer->id,
            'promotion_id' => $promotion->id,
            'campaign_id' => $campaign->id,
            'status' => 'assigned',
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.campaigns.destroy', $campaign));

        $response->assertRedirect(route('admin.campaigns.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('campaigns', ['id' => $campaign->id]);
        $this->assertFalse(DB::table('campaign_promotion')->where('campaign_id', $campaign->id)->exists());
        $this->assertFalse(DB::table('customer_promotion')->where('campaign_id', $campaign->id)->exists());
    }

    public function test_campaign_index_renders_send_rate_and_draft_actions(): void
    {
        $admin = User::factory()->create();
        $promotion = $this->createPromotion(['slug' => 'promo-index-test']);
        $activeCampaign = $this->createCampaign([
            'name' => 'Campagna invii',
            'status' => 'running',
            'scheduled_at' => now(),
        ]);
        $draftCampaign = $this->createCampaign([
            'name' => 'Bozza da completare',
            'status' => 'draft',
        ]);

        $activeCampaign->promotions()->attach($promotion);
        $draftCampaign->promotions()->attach($promotion);

        for ($index = 1; $index <= 5; $index++) {
            CustomerPromotion::query()->create([
                'customer_id' => $this->createCustomer($index)->id,
                'promotion_id' => $promotion->id,
                'campaign_id' => $activeCampaign->id,
                'email_sent_at' => $index <= 2 ? now() : null,
                'status' => $index <= 2 ? 'sent' : 'assigned',
            ]);
        }

        $response = $this->actingAs($admin)->get(route('admin.campaigns.index'));

        $response->assertOk();
        $response->assertSee('40%');
        $response->assertSee('promo-index-test');
        $response->assertSee('Completa');
        $response->assertSee('Elimina');
        $response->assertSee('Apri');
        $response->assertSee('Archivia');
    }

    private function createCampaign(array $attributes = []): Campaign
    {
        return Campaign::query()->create(array_merge([
            'name' => 'Campagna test',
            'status' => 'draft',
            'channel' => Campaign::CHANNEL_EMAIL,
            'consent_basis' => Campaign::CONSENT_BASIS_EXPLICIT_EMAIL_MARKETING,
            'segment' => 'all',
        ], $attributes));
    }

    private function createPromotion(array $attributes = []): Promotion
    {
        return Promotion::query()->create(array_merge([
            'name' => 'Promo test',
            'slug' => 'promo-test-' . uniqid(),
            'status' => 'active',
            'case_use' => 'generic',
            'discount' => 10,
            'type_discount' => 'percentage',
            'minimum_pretest' => null,
            'cta' => null,
            'permanent' => true,
            'metadata' => [
                'reusable' => false,
            ],
        ], $attributes));
    }

    private function createCustomer(int $index = 1): Customer
    {
        return Customer::query()->create([
            'name' => 'Cliente',
            'surname' => (string) $index,
            'email' => 'campaign-customer-' . $index . '-' . uniqid() . '@example.com',
        ]);
    }
}
