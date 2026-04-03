<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\CustomerOtpMail;
use App\Models\Customer;
use App\Models\CustomerOtp;
use App\Models\Order;
use App\Models\Reservation;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class CustomerAuthController extends Controller
{
    private const OTP_EXPIRE_MINUTES = 10;
    private const OTP_RESEND_SECONDS = 60;
    private const OTP_MAX_ATTEMPTS = 5;
    private const SUPPORTED_LANGS = ['it', 'en', 'es', 'de', 'fr', 'ja', 'ro'];

    public function requestRegisterOtp(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:100'],
        ]);
        $lang = $this->resolveLang($request);

        $email = Customer::normalizeEmail($data['email']);

        if (Customer::whereRaw('LOWER(email) = ?', [$email])->exists()) {
            throw ValidationException::withMessages([
                'email' => [$this->message($lang, 'messages.email_already_registered')],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $this->dispatchOtp($email, 'register', $lang),
            'expires_in_minutes' => self::OTP_EXPIRE_MINUTES,
        ]);
    }

    public function requestLoginOtp(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:100'],
        ]);
        $lang = $this->resolveLang($request);
        $email = Customer::normalizeEmail($data['email']);

        $customer = Customer::whereRaw('LOWER(email) = ?', [$email])->first();
        if (!$customer) {
            throw ValidationException::withMessages([
                'email' => [$this->message($lang, 'messages.customer_not_found')],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $this->dispatchOtp($email, 'login', $lang),
            'expires_in_minutes' => self::OTP_EXPIRE_MINUTES,
        ]);
    }

    public function register(Request $request)
    {
        $lang = $this->resolveLang($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'surname' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'otp' => ['required', 'digits:6'],
        ]);

        $email = Customer::normalizeEmail($data['email']);

        if (Customer::whereRaw('LOWER(email) = ?', [$email])->exists()) {
            throw ValidationException::withMessages([
                'email' => [$this->message($lang, 'messages.email_already_registered')],
            ]);
        }

        $otp = $this->validateOtpOrFail($email, 'register', (string) $data['otp'], $lang);

        $customer = Customer::create([
            'name' => trim($data['name']),
            'surname' => trim($data['surname']),
            'email' => $email,
            'phone' => isset($data['phone']) ? trim((string) $data['phone']) : null,
            'password' => Hash::make($data['password']),
            'email_verified_at' => now(),
        ]);

        $this->consumeOtp($otp);

        $this->backfillCustomerRelations($customer);

        $token = $customer->createToken('customer-api')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => $this->message($lang, 'messages.register_completed'),
            'token' => $token,
            'customer' => $this->customerPayload($customer),
        ], 201);
    }

    public function login(Request $request)
    {
        $lang = $this->resolveLang($request);
        $data = $request->validate([
            'email' => ['required', 'email', 'max:100'],
            'otp' => ['required', 'digits:6'],
        ]);

        $email = Customer::normalizeEmail($data['email']);
        $customer = Customer::whereRaw('LOWER(email) = ?', [$email])->first();

        if (!$customer) {
            throw ValidationException::withMessages([
                'email' => [$this->message($lang, 'messages.customer_not_found')],
            ]);
        }

        if (!$customer->email_verified_at) {
            throw ValidationException::withMessages([
                'email' => [$this->message($lang, 'messages.email_not_verified')],
            ]);
        }

        $otp = $this->validateOtpOrFail($email, 'login', (string) $data['otp'], $lang);
        $this->consumeOtp($otp);

        $token = $customer->createToken('customer-api')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => $this->message($lang, 'messages.login_completed'),
            'token' => $token,
            'customer' => $this->customerPayload($customer),
        ]);
    }

    public function requestPasswordResetOtp(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:100'],
        ]);
        $lang = $this->resolveLang($request);
        $email = Customer::normalizeEmail($data['email']);

        $customer = Customer::whereRaw('LOWER(email) = ?', [$email])->first();
        if (!$customer) {
            throw ValidationException::withMessages([
                'email' => [$this->message($lang, 'messages.customer_not_found')],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $this->dispatchOtp($email, 'password_reset', $lang),
            'expires_in_minutes' => self::OTP_EXPIRE_MINUTES,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $lang = $this->resolveLang($request);
        $data = $request->validate([
            'email' => ['required', 'email', 'max:100'],
            'otp' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $email = Customer::normalizeEmail($data['email']);
        $customer = Customer::whereRaw('LOWER(email) = ?', [$email])->first();

        if (!$customer) {
            throw ValidationException::withMessages([
                'email' => [$this->message($lang, 'messages.customer_not_found')],
            ]);
        }

        $otp = $this->validateOtpOrFail($email, 'password_reset', (string) $data['otp'], $lang);

        $customer->password = Hash::make($data['password']);
        $customer->save();

        $this->consumeOtp($otp);
        $customer->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => $this->message($lang, 'messages.password_reset_completed'),
        ]);
    }

    public function me(Request $request)
    {
        $customer = $request->user();
        if (!$customer instanceof Customer) {
            abort(403);
        }

        return response()->json([
            'success' => true,
            'customer' => $this->customerPayload($customer),
        ]);
    }

    public function logout(Request $request)
    {
        $customer = $request->user();
        if (!$customer instanceof Customer) {
            abort(403);
        }

        $token = $customer->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        return response()->json([
            'success' => true,
            'message' => $this->message($this->resolveLang($request), 'messages.logout_completed'),
        ]);
    }

    private function dispatchOtp(string $email, string $purpose, string $lang): string
    {
        $lastOtp = CustomerOtp::where('purpose', $purpose)
            ->where('email', $email)
            ->latest()
            ->first();

        if ($lastOtp && $lastOtp->created_at && $lastOtp->created_at->gt(now()->subSeconds(self::OTP_RESEND_SECONDS))) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => $this->message($lang, 'messages.wait_before_new_code'),
                'retry_after' => self::OTP_RESEND_SECONDS - $lastOtp->created_at->diffInSeconds(now()),
            ], 429));
        }

        $code = (string) random_int(100000, 999999);

        Mail::to($email)->locale($lang)->send(new CustomerOtpMail($code, self::OTP_EXPIRE_MINUTES, $purpose));

        CustomerOtp::where('purpose', $purpose)
            ->where('email', $email)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        CustomerOtp::create([
            'email' => $email,
            'purpose' => $purpose,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(self::OTP_EXPIRE_MINUTES),
            'attempts' => 0,
        ]);

        return $this->message($lang, 'messages.otp_sent_' . $purpose);
    }

    private function validateOtpOrFail(string $email, string $purpose, string $code, string $lang): CustomerOtp
    {
        $otp = CustomerOtp::where('purpose', $purpose)
            ->where('email', $email)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otp) {
            throw ValidationException::withMessages([
                'otp' => [$this->message($lang, 'messages.otp_invalid_or_expired')],
            ]);
        }

        if ($otp->attempts >= self::OTP_MAX_ATTEMPTS) {
            $otp->used_at = now();
            $otp->save();

            throw ValidationException::withMessages([
                'otp' => [$this->message($lang, 'messages.otp_too_many_attempts')],
            ]);
        }

        if (!Hash::check($code, $otp->code_hash)) {
            $otp->attempts = $otp->attempts + 1;
            if ($otp->attempts >= self::OTP_MAX_ATTEMPTS) {
                $otp->used_at = now();
            }
            $otp->save();

            throw ValidationException::withMessages([
                'otp' => [$this->message($lang, 'messages.otp_incorrect')],
            ]);
        }

        return $otp;
    }

    private function consumeOtp(CustomerOtp $otp): void
    {
        $otp->used_at = now();
        $otp->save();
    }

    private function resolveLang(Request $request): string
    {
        $lang = $request->input('lang') ?? $request->input('lango') ?? config('app.locale');
        $lang = is_string($lang) ? mb_strtolower(trim($lang)) : config('app.locale');

        return in_array($lang, self::SUPPORTED_LANGS, true) ? $lang : config('app.locale');
    }

    private function message(string $lang, string $key, array $replace = []): string
    {
        return Lang::get('customer.' . $key, $replace, $lang);
    }

    private function backfillCustomerRelations(Customer $customer): void
    {
        $normalizedEmail = Customer::normalizeEmail($customer->email);

        Order::whereNull('customer_id')
            ->whereRaw('LOWER(TRIM(email)) = ?', [$normalizedEmail])
            ->update(['customer_id' => $customer->id]);

        Reservation::whereNull('customer_id')
            ->whereRaw('LOWER(TRIM(email)) = ?', [$normalizedEmail])
            ->update(['customer_id' => $customer->id]);
    }

    private function customerPayload(Customer $customer): array
    {
        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'surname' => $customer->surname,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'email_verified_at' => $customer->email_verified_at?->toISOString(),
            'created_at' => $customer->created_at?->toISOString(),
        ];
    }
}
