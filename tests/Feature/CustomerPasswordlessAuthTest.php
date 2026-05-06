<?php

namespace Tests\Feature;

use App\Mail\CustomerOtpMail;
use App\Models\Customer;
use App\Services\CustomerAuth\CustomerAccessService;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CustomerPasswordlessAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_otp_can_create_customer_access_and_return_token(): void
    {
        Mail::fake();

        $this->postJson('/api/auth/send-otp', [
            'email' => 'anna@example.com',
            'lang' => 'it',
        ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'customer_exists' => false,
            ]);

        $otpCode = null;

        Mail::assertSent(CustomerOtpMail::class, function (CustomerOtpMail $mail) use (&$otpCode) {
            $otpCode = $mail->code;

            return true;
        });

        $this->assertNotNull($otpCode);

        $response = $this->postJson('/api/auth/verify-otp', [
            'email' => 'anna@example.com',
            'code' => $otpCode,
            'intent' => 'account',
            'lang' => 'it',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('customer.email', 'anna@example.com')
            ->assertJsonPath('verified_session.email', 'anna@example.com');

        $this->assertNotEmpty($response->json('token'));

        $this->assertDatabaseHas('customers', [
            'email' => 'anna@example.com',
        ]);
    }

    public function test_send_otp_is_rate_limited_per_email(): void
    {
        Mail::fake();

        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->postJson('/api/auth/send-otp', [
                'email' => 'limited@example.com',
                'lang' => 'it',
            ])->assertOk();
        }

        $this->postJson('/api/auth/send-otp', [
            'email' => 'limited@example.com',
            'lang' => 'it',
        ])->assertStatus(429);
    }

    public function test_checkout_send_otp_is_skipped_when_email_exists_in_legacy_orders(): void
    {
        Mail::fake();

        DB::table('orders')->insert([
            'date_slot' => '2026-04-08 20:00',
            'status' => 1,
            'name' => 'Anna',
            'surname' => 'Legacy',
            'email' => 'anna@example.com',
            'phone' => '3331234567',
            'checkout_session_id' => null,
            'address' => null,
            'address_n' => null,
            'comune' => null,
            'whatsapp_message_id' => null,
            'tot_price' => 25.00,
            'message' => null,
            'news_letter' => false,
            'notificated' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->postJson('/api/auth/send-otp', [
            'email' => 'anna@example.com',
            'intent' => 'checkout',
            'lang' => 'it',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'otp_required' => false,
                'customer_exists' => false,
            ])
            ->assertJsonPath('verified_session.email', 'anna@example.com');

        Mail::assertNothingSent();
    }

    public function test_account_send_otp_still_requires_code_for_legacy_order_email(): void
    {
        Mail::fake();

        DB::table('orders')->insert([
            'date_slot' => '2026-04-08 20:00',
            'status' => 1,
            'name' => 'Anna',
            'surname' => 'Legacy',
            'email' => 'anna@example.com',
            'phone' => '3331234567',
            'checkout_session_id' => null,
            'address' => null,
            'address_n' => null,
            'comune' => null,
            'whatsapp_message_id' => null,
            'tot_price' => 25.00,
            'message' => null,
            'news_letter' => false,
            'notificated' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->postJson('/api/auth/send-otp', [
            'email' => 'anna@example.com',
            'intent' => 'account',
            'lang' => 'it',
        ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'otp_required' => true,
                'customer_exists' => false,
            ]);

        Mail::assertSent(CustomerOtpMail::class);
    }

    public function test_legacy_orders_and_reservations_are_linked_when_customer_access_is_completed(): void
    {
        Mail::fake();

        DB::table('orders')->insert([
            'date_slot' => '2026-04-08 20:00',
            'status' => 1,
            'name' => 'Anna',
            'surname' => 'Legacy',
            'email' => 'anna@example.com',
            'phone' => '3331234567',
            'checkout_session_id' => null,
            'address' => null,
            'address_n' => null,
            'comune' => null,
            'whatsapp_message_id' => null,
            'tot_price' => 25.00,
            'message' => null,
            'news_letter' => true,
            'notificated' => false,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        DB::table('reservations')->insert([
            'date_slot' => '08/04/2026 21:00',
            'status' => 1,
            'name' => 'Anna',
            'surname' => 'Legacy',
            'email' => 'anna@example.com',
            'phone' => '3331234567',
            'n_person' => json_encode(['adult' => 2, 'child' => 0]),
            'sala' => null,
            'message' => null,
            'whatsapp_message_id' => null,
            'news_letter' => false,
            'notificated' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->postJson('/api/auth/send-otp', [
            'email' => 'anna@example.com',
            'intent' => 'account',
            'lang' => 'it',
        ])->assertOk();

        $otpCode = null;
        Mail::assertSent(CustomerOtpMail::class, function (CustomerOtpMail $mail) use (&$otpCode) {
            $otpCode = $mail->code;

            return true;
        });

        $response = $this->postJson('/api/auth/verify-otp', [
            'email' => 'anna@example.com',
            'code' => $otpCode,
            'intent' => 'account',
            'lang' => 'it',
        ])->assertOk();

        $customerId = $response->json('customer.id');

        $this->assertNotNull($customerId);
        $this->assertDatabaseHas('orders', [
            'email' => 'anna@example.com',
            'customer_id' => $customerId,
        ]);
        $this->assertDatabaseHas('reservations', [
            'email' => 'anna@example.com',
            'customer_id' => $customerId,
        ]);
        $this->assertSame(
            'soft_marketing',
            Customer::query()->findOrFail($customerId)->marketingState()
        );
    }

    public function test_customer_can_complete_profile_and_disable_consents(): void
    {
        Mail::fake();

        $this->postJson('/api/auth/send-otp', [
            'email' => 'profile@example.com',
            'intent' => 'account',
            'lang' => 'it',
        ])->assertOk();

        $otpCode = null;
        Mail::assertSent(CustomerOtpMail::class, function (CustomerOtpMail $mail) use (&$otpCode) {
            $otpCode = $mail->code;

            return true;
        });

        $response = $this->postJson('/api/auth/verify-otp', [
            'email' => 'profile@example.com',
            'code' => $otpCode,
            'intent' => 'account',
            'lang' => 'it',
        ])->assertOk();

        $token = $response->json('token');
        $customerId = $response->json('customer.id');

        $this->putJson('/api/auth/profile', [
            'name' => 'Mario',
            'surname' => 'Rossi',
            'phone' => '3339876543',
            'gender' => 'male',
            'age' => 34,
            'profile_answers' => [],
            'marketing_enabled' => true,
            'profiling_enabled' => true,
        ], [
            'Authorization' => 'Bearer ' . $token,
        ])
            ->assertOk()
            ->assertJsonPath('customer.account_state', 'registered')
            ->assertJsonPath('customer.marketing_state', 'full');

        $this->patchJson('/api/auth/consents', [
            'marketing_enabled' => false,
            'profiling_enabled' => false,
        ], [
            'Authorization' => 'Bearer ' . $token,
        ])
            ->assertOk()
            ->assertJsonPath('customer.marketing_state', 'no_marketing')
            ->assertJsonPath('customer.marketing_enabled', false)
            ->assertJsonPath('customer.profiling_enabled', false);

        $customer = Customer::query()->findOrFail($customerId);
        $this->assertNotNull($customer->registered_at);
        $this->assertNull($customer->marketing_consent_at);
        $this->assertNull($customer->profiling_consent_at);
    }

    public function test_customer_lookup_keeps_email_as_primary_identifier(): void
    {
        $service = app(CustomerAccessService::class);

        $first = $service->findOrCreateForVerifiedCheckout('SAME@example.com', [
            'name' => 'Anna',
            'surname' => 'Rossi',
            'phone' => '3331112222',
        ]);

        $second = $service->findOrCreateForVerifiedCheckout('same@example.com', [
            'name' => 'Anna',
            'surname' => 'Rossi',
            'phone' => '3339998888',
        ]);

        $this->assertSame($first->id, $second->id);
        $this->assertSame('3331112222', $second->phone);
        $this->assertSame(1, Customer::query()->where('email', 'same@example.com')->count());
    }

    public function test_customer_lookup_does_not_merge_different_emails_with_same_phone(): void
    {
        $service = app(CustomerAccessService::class);

        $first = $service->findOrCreateForVerifiedCheckout('first@example.com', [
            'name' => 'Mario',
            'surname' => 'Rossi',
            'phone' => '3331112222',
        ]);

        $second = $service->findOrCreateForVerifiedCheckout('second@example.com', [
            'name' => 'Anna',
            'surname' => 'Bianchi',
            'phone' => '+39 333 111 2222',
        ]);

        $this->assertNotSame($first->id, $second->id);
        $this->assertSame(2, Customer::query()->count());
    }

    public function test_phone_lookup_is_only_a_fallback_for_customers_without_email(): void
    {
        $service = app(CustomerAccessService::class);

        $withoutEmail = Customer::query()->create([
            'name' => 'Telefono',
            'surname' => 'Solo',
            'email' => '',
            'phone' => '3331112222',
        ]);

        $this->assertSame(
            $withoutEmail->id,
            $service->findExistingCustomer(null, '+39 333 111 2222')?->id
        );
    }

    public function test_phone_lookup_does_not_merge_when_existing_customer_has_email(): void
    {
        $service = app(CustomerAccessService::class);

        Customer::query()->create([
            'name' => 'Cliente',
            'surname' => 'Email',
            'email' => 'with-email@example.com',
            'phone' => '3331112222',
        ]);

        $this->assertNull($service->findExistingCustomer(null, '+39 333 111 2222'));
    }
}
