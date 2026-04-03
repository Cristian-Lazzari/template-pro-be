<?php

namespace Tests\Feature;

use App\Mail\CustomerOtpMail;
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
}
