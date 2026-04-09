<?php

namespace Tests\Feature;

use App\Mail\CustomerOtpMail;
use App\Models\Customer;
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
            'tot_price' => 2500,
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
            'tot_price' => 2500,
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
            'tot_price' => 2500,
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
}
