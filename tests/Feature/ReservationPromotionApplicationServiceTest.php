<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerPromotion;
use App\Models\Promotion;
use App\Services\Marketing\ReservationPromotionApplicationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReservationPromotionApplicationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_table_promotion_is_applicable_to_reservation(): void
    {
        $customer = $this->createCustomer('table-promo@example.com');
        $promotion = $this->createPromotion([
            'case_use' => 'table',
            'type_discount' => 'gift',
        ]);
        $customerPromotion = $this->assignPromotion($customer, $promotion);

        $result = $this->service()->evaluate($customer, $customerPromotion->id, [
            'n_adult' => 2,
            'n_child' => 1,
            'date_slot' => '10/05/2026 20:00',
        ]);

        $this->assertTrue($result['applicable']);
        $this->assertNull($result['reason']);
        $this->assertSame($customerPromotion->id, $result['customer_promotion']->id);
        $this->assertEquals(0.0, $result['discount_amount']);
        $this->assertSame('reservation', $result['affected_items'][0]['type']);
        $this->assertSame(3, $result['affected_items'][0]['people']);
        $this->assertTrue($result['affected_items'][0]['gift_benefit']);
    }

    public function test_take_away_promotion_is_not_applicable_to_reservation(): void
    {
        $customer = $this->createCustomer('takeaway-promo@example.com');
        $promotion = $this->createPromotion([
            'case_use' => 'take_away',
        ]);
        $customerPromotion = $this->assignPromotion($customer, $promotion);

        $result = $this->service()->evaluate($customer, $customerPromotion->id, [
            'people' => 2,
        ]);

        $this->assertFalse($result['applicable']);
        $this->assertSame('invalid_case_use', $result['reason']);
    }

    public function test_other_customer_promotion_is_not_applicable(): void
    {
        $customer = $this->createCustomer('reservation-owner@example.com');
        $otherCustomer = $this->createCustomer('reservation-other@example.com');
        $promotion = $this->createPromotion([
            'case_use' => 'table',
        ]);
        $customerPromotion = $this->assignPromotion($otherCustomer, $promotion);

        $result = $this->service()->evaluate($customer, $customerPromotion->id, [
            'people' => 2,
        ]);

        $this->assertFalse($result['applicable']);
        $this->assertSame('customer_promotion_customer_mismatch', $result['reason']);
    }

    public function test_minimum_pretest_uses_reservation_people_when_available(): void
    {
        $customer = $this->createCustomer('reservation-minimum@example.com');
        $promotion = $this->createPromotion([
            'case_use' => 'table',
            'minimum_pretest' => 4,
        ]);
        $customerPromotion = $this->assignPromotion($customer, $promotion);

        $result = $this->service()->evaluate($customer, $customerPromotion->id, [
            'n_adult' => 2,
            'n_child' => 1,
        ]);

        $this->assertFalse($result['applicable']);
        $this->assertSame('minimum_not_reached', $result['reason']);
    }

    private function service(): ReservationPromotionApplicationService
    {
        return app(ReservationPromotionApplicationService::class);
    }

    private function createCustomer(string $email): Customer
    {
        return Customer::query()->create([
            'name' => 'Reservation',
            'surname' => 'Customer',
            'email' => $email,
            'phone' => '3331112222',
        ]);
    }

    private function createPromotion(array $attributes = []): Promotion
    {
        return Promotion::query()->create(array_merge([
            'name' => 'Promo prenotazione',
            'slug' => 'promo-reservation-' . Str::uuid(),
            'status' => 'active',
            'case_use' => 'table',
            'type_discount' => 'fixed',
            'discount' => 0,
            'minimum_pretest' => null,
            'permanent' => true,
            'schedule_at' => null,
            'expiring_at' => null,
        ], $attributes));
    }

    private function assignPromotion(Customer $customer, Promotion $promotion, array $attributes = []): CustomerPromotion
    {
        return CustomerPromotion::query()->create(array_merge([
            'customer_id' => $customer->id,
            'promotion_id' => $promotion->id,
            'tracking_token' => (string) Str::uuid(),
            'status' => 'assigned',
        ], $attributes));
    }
}
