<?php

namespace App\Services\CustomerAuth;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Reservation;

class CustomerAccessService
{
    public function customerExists(string $email): bool
    {
        return $this->findExistingCustomer($email) !== null;
    }

    public function findExistingCustomer(?string $email, ?string $phone = null): ?Customer
    {
        $normalizedEmail = $this->normalizeEmail($email);

        if ($normalizedEmail !== null) {
            return $this->findByEmail($normalizedEmail);
        }

        $normalizedPhone = $this->normalizePhone($phone);

        return $normalizedPhone !== null
            ? $this->findByPhoneWithoutEmail($normalizedPhone)
            : null;
    }

    public function hasHistoricalEmailEvidence(string $email): bool
    {
        $normalizedEmail = Customer::normalizeEmail($email);

        if ($this->customerExists($normalizedEmail)) {
            return true;
        }

        return Order::query()
            ->whereRaw('LOWER(TRIM(email)) = ?', [$normalizedEmail])
            ->exists()
            || Reservation::query()
                ->whereRaw('LOWER(TRIM(email)) = ?', [$normalizedEmail])
                ->exists();
    }

    public function completeVerifiedAccess(
        string $email,
        array $attributes = [],
        bool $rememberDetails = false,
        bool $forceCustomerAccount = false,
    ): array {
        $normalizedEmail = Customer::normalizeEmail($email);
        $customer = $this->findExistingCustomer($normalizedEmail, $attributes['phone'] ?? null);
        $createdCustomer = false;

        if (!$customer && ($rememberDetails || $forceCustomerAccount)) {
            $customer = Customer::query()->create(
                $this->buildCustomerAttributes($normalizedEmail, $attributes)
            );
            $createdCustomer = true;
        }

        if ($customer) {
            $this->syncCustomerProfile($customer, $attributes);
            $this->backfillCustomerRelations($customer);
        }

        return [
            'customer' => $customer?->fresh(),
            'token' => $customer?->createToken('customer-api')->plainTextToken,
            'customer_exists' => $customer !== null,
            'created_customer' => $createdCustomer,
        ];
    }

    public function findOrCreateForVerifiedCheckout(string $email, array $attributes = [], bool $newsletterOptIn = false): Customer
    {
        $normalizedEmail = Customer::normalizeEmail($email);
        $customer = $this->findExistingCustomer($normalizedEmail, $attributes['phone'] ?? null);

        if (!$customer) {
            $customer = Customer::query()->create(
                $this->buildCustomerAttributes($normalizedEmail, $attributes)
            );
        }

        $this->syncCustomerProfile($customer, $attributes);
        $this->syncMarketingConsentFromNewsletter($customer, $newsletterOptIn);
        $this->backfillCustomerRelations($customer);

        return $customer->fresh();
    }

    public function syncCustomerProfile(Customer $customer, array $attributes = []): Customer
    {
        $normalizedEmail = $this->normalizeEmail($customer->email);
        $guestProfile = $normalizedEmail !== null ? $this->latestGuestProfile($normalizedEmail) : [];

        $customer->name = $this->preferredProfileValue(
            $attributes['name'] ?? null,
            $customer->name,
            $guestProfile['name'] ?? ''
        );

        $customer->surname = $this->preferredProfileValue(
            $attributes['surname'] ?? null,
            $customer->surname,
            $guestProfile['surname'] ?? ''
        );

        $phone = $this->preferredProfileValue(
            $attributes['phone'] ?? null,
            $customer->phone,
            $guestProfile['phone'] ?? null
        );

        $customer->phone = $phone !== '' ? $phone : null;
        $customer->email_verified_at = $customer->email_verified_at ?: now();

        if ($customer->isDirty(['name', 'surname', 'phone', 'email_verified_at'])) {
            $customer->save();
        }

        return $customer;
    }

    private function findByEmail(string $email): ?Customer
    {
        return Customer::query()
            ->whereRaw('LOWER(TRIM(email)) = ?', [Customer::normalizeEmail($email)])
            ->first();
    }

    private function findByPhoneWithoutEmail(string $normalizedPhone): ?Customer
    {
        return Customer::query()
            ->where(function ($query) {
                $query->whereNull('email')
                    ->orWhereRaw("TRIM(email) = ''");
            })
            ->whereNotNull('phone')
            ->get()
            ->first(function (Customer $customer) use ($normalizedPhone) {
                return $this->normalizePhone($customer->phone) === $normalizedPhone;
            });
    }

    private function buildCustomerAttributes(string $email, array $attributes = []): array
    {
        $guestProfile = $this->latestGuestProfile($email);

        return [
            'name' => $this->preferredValue($attributes['name'] ?? null, $guestProfile['name'] ?? '', ''),
            'surname' => $this->preferredValue($attributes['surname'] ?? null, $guestProfile['surname'] ?? '', ''),
            'email' => $email,
            'phone' => $this->nullIfEmpty($this->preferredValue($attributes['phone'] ?? null, $guestProfile['phone'] ?? null, null)),
            'marketing_consent_at' => $this->guestMarketingConsentAt($email),
            'email_verified_at' => now(),
        ];
    }

    private function latestGuestProfile(string $email): array
    {
        $normalizedEmail = Customer::normalizeEmail($email);

        $latestOrder = Order::query()
            ->select(['name', 'surname', 'phone', 'created_at'])
            ->whereRaw('LOWER(TRIM(email)) = ?', [$normalizedEmail])
            ->latest('created_at')
            ->first();

        $latestReservation = Reservation::query()
            ->select(['name', 'surname', 'phone', 'created_at'])
            ->whereRaw('LOWER(TRIM(email)) = ?', [$normalizedEmail])
            ->latest('created_at')
            ->first();

        $candidate = $latestOrder;

        if ($latestReservation && (!$latestOrder || $latestReservation->created_at?->gt($latestOrder->created_at))) {
            $candidate = $latestReservation;
        }

        return [
            'name' => trim((string) ($candidate?->name ?? '')),
            'surname' => trim((string) ($candidate?->surname ?? '')),
            'phone' => $this->nullIfEmpty(trim((string) ($candidate?->phone ?? ''))),
        ];
    }

    private function preferredValue($incoming, $current, $fallback)
    {
        $normalizedIncoming = is_string($incoming) ? trim($incoming) : $incoming;
        if ($normalizedIncoming !== null && $normalizedIncoming !== '') {
            return $normalizedIncoming;
        }

        $normalizedCurrent = is_string($current) ? trim($current) : $current;
        if ($normalizedCurrent !== null && $normalizedCurrent !== '') {
            return $normalizedCurrent;
        }

        return $fallback;
    }

    private function preferredProfileValue($incoming, $current, $fallback)
    {
        $normalizedCurrent = is_string($current) ? trim($current) : $current;
        if ($normalizedCurrent !== null && $normalizedCurrent !== '') {
            return $normalizedCurrent;
        }

        $normalizedIncoming = is_string($incoming) ? trim($incoming) : $incoming;
        if ($normalizedIncoming !== null && $normalizedIncoming !== '') {
            return $normalizedIncoming;
        }

        return $fallback;
    }

    private function nullIfEmpty($value)
    {
        if (!is_string($value)) {
            return $value;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function normalizeEmail(?string $email): ?string
    {
        $email = $this->nullIfEmpty($email);

        return $email === null ? null : Customer::normalizeEmail($email);
    }

    private function normalizePhone(?string $phone): ?string
    {
        $phone = $this->nullIfEmpty($phone);
        if ($phone === null) {
            return null;
        }

        $normalized = preg_replace('/\D+/', '', $phone);
        if ($normalized === '') {
            return null;
        }

        if (str_starts_with($normalized, '0039') && strlen($normalized) > 10) {
            $normalized = substr($normalized, 4);
        } elseif (str_starts_with($normalized, '39') && strlen($normalized) > 10) {
            $normalized = substr($normalized, 2);
        }

        return $normalized !== '' ? $normalized : null;
    }

    private function guestMarketingConsentAt(string $email)
    {
        $normalizedEmail = Customer::normalizeEmail($email);

        $latestOrderConsentAt = Order::query()
            ->where('news_letter', true)
            ->whereRaw('LOWER(TRIM(email)) = ?', [$normalizedEmail])
            ->max('created_at');

        $latestReservationConsentAt = Reservation::query()
            ->where('news_letter', true)
            ->whereRaw('LOWER(TRIM(email)) = ?', [$normalizedEmail])
            ->max('created_at');

        $timestamps = array_filter([$latestOrderConsentAt, $latestReservationConsentAt]);

        if ($timestamps === []) {
            return null;
        }

        rsort($timestamps);

        return $timestamps[0];
    }

    private function syncMarketingConsentFromNewsletter(Customer $customer, bool $newsletterOptIn): void
    {
        $historicalConsentAt = $this->guestMarketingConsentAt($customer->email);
        $marketingConsentAt = $customer->marketing_consent_at ?: $historicalConsentAt;

        if ($newsletterOptIn && !$marketingConsentAt) {
            $marketingConsentAt = now();
        }

        if ($marketingConsentAt !== $customer->marketing_consent_at) {
            $customer->marketing_consent_at = $marketingConsentAt;
            $customer->save();
        }
    }

    private function backfillCustomerRelations(Customer $customer): void
    {
        $normalizedEmail = Customer::normalizeEmail($customer->email);

        Order::query()
            ->whereNull('customer_id')
            ->whereRaw('LOWER(TRIM(email)) = ?', [$normalizedEmail])
            ->update(['customer_id' => $customer->id]);

        Reservation::query()
            ->whereNull('customer_id')
            ->whereRaw('LOWER(TRIM(email)) = ?', [$normalizedEmail])
            ->update(['customer_id' => $customer->id]);
    }
}
