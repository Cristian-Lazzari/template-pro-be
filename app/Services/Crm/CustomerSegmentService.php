<?php

namespace App\Services\Crm;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class CustomerSegmentService
{
    private const CONFIRMED_ORDER_STATUSES = [1, 2, 3, 5];

    private const CONFIRMED_RESERVATION_STATUSES = [1, 2, 3, 5];

    private const LIFECYCLE_SEGMENTS = [
        'new_customers',
        'active_customers',
        'loyal_customers',
        'at_risk_customers',
        'lost_customers',
    ];

    private ?Collection $profilesCache = null;

    private ?array $customerColumnsCache = null;

    private ?array $customerMetricColumnsCache = null;

    public function getSegments(): array
    {
        return [
            'new_customers' => [
                'label' => 'Nuovi clienti',
                'description' => 'Una sola interazione confermata e ultima attivita negli ultimi 14 giorni.',
            ],
            'active_customers' => [
                'label' => 'Clienti attivi',
                'description' => 'Almeno 2 interazioni confermate con ultima attivita negli ultimi 30 giorni.',
            ],
            'loyal_customers' => [
                'label' => 'Clienti fedeli',
                'description' => 'Almeno 5 interazioni confermate oppure customer score pari o superiore a 75.',
            ],
            'at_risk_customers' => [
                'label' => 'A rischio',
                'description' => 'Ultima attivita tra 30 e 60 giorni fa.',
            ],
            'lost_customers' => [
                'label' => 'Persi',
                'description' => 'Ultima attivita oltre 60 giorni fa.',
            ],
            'high_value_customers' => [
                'label' => 'High value',
                'description' => 'Spesa totale confermata sopra la soglia CRM configurata.',
            ],
            'reservation_only' => [
                'label' => 'Solo prenotazioni',
                'description' => 'Almeno una prenotazione confermata e zero ordini confermati.',
            ],
            'order_only' => [
                'label' => 'Solo ordini',
                'description' => 'Almeno un ordine confermato e zero prenotazioni confermate.',
            ],
            'habit_customers' => [
                'label' => 'Abitudinari',
                'description' => 'Almeno 3 interazioni nello stesso giorno della settimana oppure nello stesso time slot.',
            ],
            'low_engagement' => [
                'label' => 'Basso engagement',
                'description' => 'Una sola interazione totale e customer score sotto 35.',
            ],
        ];
    }

    public function getSegmentLabel(string $key): string
    {
        return $this->getSegments()[$key]['label'] ?? $key;
    }

    public function getSegmentDescription(string $key): string
    {
        return $this->getSegments()[$key]['description'] ?? '';
    }

    public function buildBaseCustomerQuery(array $filters = []): Collection
    {
        return $this->applyFilters($this->baseProfiles(), $filters);
    }

    public function getCustomersForSegment(string $key, array $filters = []): Collection
    {
        return $this->getCustomersForSegments([$key], $filters);
    }

    public function getCustomersForSegments(array $keys, array $filters = []): Collection
    {
        return $this->buildBaseCustomerQuery(array_merge($filters, ['segments' => $keys]));
    }

    public function countCustomersForSegment(string $key): int
    {
        return $this->getCustomersForSegment($key)->count();
    }

    private function baseProfiles(): Collection
    {
        if ($this->profilesCache instanceof Collection) {
            return $this->profilesCache;
        }

        $customers = $this->fetchCustomers();
        $orderEvents = $this->fetchOrderEvents();
        $reservationEvents = $this->fetchReservationEvents();
        $parents = [];

        foreach ($customers as $customer) {
            $this->unionIdentifiers($parents, $this->identityTokens(
                (int) $customer->id,
                $customer->email ?? null,
                $customer->phone ?? null,
            ));
        }

        foreach ($orderEvents as $event) {
            $this->unionIdentifiers($parents, $this->identityTokens(
                $event['customer_id'],
                $event['email'],
                $event['phone'],
            ));
        }

        foreach ($reservationEvents as $event) {
            $this->unionIdentifiers($parents, $this->identityTokens(
                $event['customer_id'],
                $event['email'],
                $event['phone'],
            ));
        }

        $profiles = [];

        foreach ($customers as $customer) {
            $root = $this->resolveIdentityRoot($parents, $this->identityTokens(
                (int) $customer->id,
                $customer->email ?? null,
                $customer->phone ?? null,
            ));

            if ($root === null) {
                continue;
            }

            if (! isset($profiles[$root])) {
                $profiles[$root] = $this->emptyProfile($root);
            }

            $this->mergeCustomerRecord($profiles[$root], $customer);
        }

        foreach ($orderEvents as $event) {
            $root = $this->resolveIdentityRoot($parents, $this->identityTokens(
                $event['customer_id'],
                $event['email'],
                $event['phone'],
            ));

            if ($root === null) {
                continue;
            }

            if (! isset($profiles[$root])) {
                $profiles[$root] = $this->emptyProfile($root);
            }

            $this->mergeEvent($profiles[$root], $event);
        }

        foreach ($reservationEvents as $event) {
            $root = $this->resolveIdentityRoot($parents, $this->identityTokens(
                $event['customer_id'],
                $event['email'],
                $event['phone'],
            ));

            if ($root === null) {
                continue;
            }

            if (! isset($profiles[$root])) {
                $profiles[$root] = $this->emptyProfile($root);
            }

            $this->mergeEvent($profiles[$root], $event);
        }

        $this->profilesCache = collect($profiles)
            ->map(fn (array $profile) => $this->finalizeProfile($profile))
            ->sortByDesc(fn (object $profile) => $profile->sort_timestamp)
            ->values();

        return $this->profilesCache;
    }

    private function fetchCustomers(): Collection
    {
        $columns = [
            'id',
            'name',
            'surname',
            'email',
            'phone',
            'registered_at',
            'marketing_consent_at',
            'profiling_consent_at',
            'created_at',
            'updated_at',
        ];

        foreach ($this->metricColumns() as $column) {
            if (! in_array($column, $columns, true)) {
                $columns[] = $column;
            }
        }

        return Customer::query()
            ->select($columns)
            ->orderByDesc('created_at')
            ->get();
    }

    private function fetchOrderEvents(): Collection
    {
        return Order::query()
            ->select([
                'id',
                'customer_id',
                'name',
                'surname',
                'email',
                'phone',
                'news_letter',
                'tot_price',
                'date_slot',
                'created_at',
            ])
            ->whereIn('status', self::CONFIRMED_ORDER_STATUSES)
            ->get()
            ->map(function (Order $order) {
                $activityAt = $this->parseActivityTimestamp($order->date_slot, $order->created_at);

                return [
                    'source' => 'order',
                    'source_id' => (int) $order->id,
                    'customer_id' => $order->customer_id ? (int) $order->customer_id : null,
                    'name' => $this->trimString($order->name),
                    'surname' => $this->trimString($order->surname),
                    'email' => $this->trimString($order->email),
                    'phone' => $this->trimString($order->phone),
                    'marketing_opt_in' => (bool) $order->news_letter,
                    'activity_at' => $activityAt,
                    'time_slot_key' => $this->timeSlotKey($activityAt),
                    'weekday_key' => $activityAt?->dayOfWeekIso,
                    'amount' => (float) ($order->tot_price ?? 0),
                ];
            })
            ->values();
    }

    private function fetchReservationEvents(): Collection
    {
        return Reservation::query()
            ->select([
                'id',
                'customer_id',
                'name',
                'surname',
                'email',
                'phone',
                'news_letter',
                'date_slot',
                'created_at',
            ])
            ->whereIn('status', self::CONFIRMED_RESERVATION_STATUSES)
            ->get()
            ->map(function (Reservation $reservation) {
                $activityAt = $this->parseActivityTimestamp($reservation->date_slot, $reservation->created_at);

                return [
                    'source' => 'reservation',
                    'source_id' => (int) $reservation->id,
                    'customer_id' => $reservation->customer_id ? (int) $reservation->customer_id : null,
                    'name' => $this->trimString($reservation->name),
                    'surname' => $this->trimString($reservation->surname),
                    'email' => $this->trimString($reservation->email),
                    'phone' => $this->trimString($reservation->phone),
                    'marketing_opt_in' => (bool) $reservation->news_letter,
                    'activity_at' => $activityAt,
                    'time_slot_key' => $this->timeSlotKey($activityAt),
                    'weekday_key' => $activityAt?->dayOfWeekIso,
                    'amount' => 0.0,
                ];
            })
            ->values();
    }

    private function emptyProfile(string $root): array
    {
        return [
            'identity_key' => $root,
            'customer_id' => null,
            'customer_ids' => [],
            'primary_customer_updated_at' => null,
            'name' => null,
            'surname' => null,
            'email' => null,
            'phone' => null,
            'is_registered' => false,
            'registered_at' => null,
            'has_marketing_consent' => false,
            'has_profiling_consent' => false,
            'orders_count' => 0,
            'reservations_count' => 0,
            'interactions_count' => 0,
            'total_spent' => 0.0,
            'last_activity_at' => null,
            'last_source' => null,
            'last_source_id' => null,
            'created_at' => null,
            'marketing_opt_in' => false,
            'cached_customer_score' => null,
            'cached_lifecycle_segment' => null,
            'cached_orders_count' => null,
            'cached_reservations_count' => null,
            'cached_interactions_count' => null,
            'cached_total_spent' => null,
            'cached_last_activity_at' => null,
            'weekday_counts' => [],
            'time_slot_counts' => [],
            'distinct_emails' => [],
            'distinct_phones' => [],
            'field_timestamps' => [],
            'field_priorities' => [],
        ];
    }

    private function mergeCustomerRecord(array &$profile, Customer $customer): void
    {
        $customerUpdatedAt = $customer->updated_at ? Carbon::parse($customer->updated_at) : null;
        $customerCreatedAt = $customer->created_at ? Carbon::parse($customer->created_at) : null;
        $priority = $customer->isRegistered() ? 3 : 2;

        if ($this->shouldReplacePrimaryCustomer($profile, $customerUpdatedAt, $customer->isRegistered())) {
            $profile['customer_id'] = (int) $customer->id;
            $profile['primary_customer_updated_at'] = $customerUpdatedAt;
        }

        $profile['customer_ids'][(int) $customer->id] = (int) $customer->id;
        $profile['is_registered'] = $profile['is_registered'] || $customer->isRegistered();
        $profile['has_marketing_consent'] = $profile['has_marketing_consent'] || $customer->marketing_consent_at !== null;
        $profile['has_profiling_consent'] = $profile['has_profiling_consent'] || $customer->profiling_consent_at !== null;

        if (! $profile['registered_at'] && $customer->registered_at) {
            $profile['registered_at'] = Carbon::parse($customer->registered_at);
        }

        if (! $profile['created_at'] || ($customerCreatedAt && $customerCreatedAt->lt($profile['created_at']))) {
            $profile['created_at'] = $customerCreatedAt;
        }

        $this->mergeIdentityField($profile, 'name', $customer->name, $customerUpdatedAt ?? $customerCreatedAt, $priority);
        $this->mergeIdentityField($profile, 'surname', $customer->surname, $customerUpdatedAt ?? $customerCreatedAt, $priority);
        $this->mergeIdentityField($profile, 'email', $customer->email, $customerUpdatedAt ?? $customerCreatedAt, $priority);
        $this->mergeIdentityField($profile, 'phone', $customer->phone, $customerUpdatedAt ?? $customerCreatedAt, $priority);

        $normalizedEmail = $this->normalizeEmail($customer->email);
        if ($normalizedEmail !== null) {
            $profile['distinct_emails'][$normalizedEmail] = $this->trimString($customer->email);
        }

        $normalizedPhone = $this->normalizePhone($customer->phone);
        if ($normalizedPhone !== null) {
            $profile['distinct_phones'][$normalizedPhone] = $this->trimString($customer->phone);
        }

        $this->mergeCachedMetrics($profile, $customer);
    }

    private function mergeEvent(array &$profile, array $event): void
    {
        $activityAt = $event['activity_at'];

        if ($event['source'] === 'order') {
            $profile['orders_count']++;
            $profile['total_spent'] += (float) $event['amount'];
        } else {
            $profile['reservations_count']++;
        }

        $profile['interactions_count']++;
        $profile['marketing_opt_in'] = $profile['marketing_opt_in'] || (bool) $event['marketing_opt_in'];

        if ($activityAt && (! $profile['last_activity_at'] || $activityAt->gt($profile['last_activity_at']))) {
            $profile['last_activity_at'] = $activityAt;
            $profile['last_source'] = $event['source'];
            $profile['last_source_id'] = $event['source_id'];
        }

        if (! $profile['created_at'] && $activityAt) {
            $profile['created_at'] = $activityAt;
        }

        if ($event['weekday_key']) {
            $profile['weekday_counts'][$event['weekday_key']] = ($profile['weekday_counts'][$event['weekday_key']] ?? 0) + 1;
        }

        if ($event['time_slot_key']) {
            $profile['time_slot_counts'][$event['time_slot_key']] = ($profile['time_slot_counts'][$event['time_slot_key']] ?? 0) + 1;
        }

        $this->mergeIdentityField($profile, 'name', $event['name'], $activityAt, 1);
        $this->mergeIdentityField($profile, 'surname', $event['surname'], $activityAt, 1);
        $this->mergeIdentityField($profile, 'email', $event['email'], $activityAt, 1);
        $this->mergeIdentityField($profile, 'phone', $event['phone'], $activityAt, 1);

        $normalizedEmail = $this->normalizeEmail($event['email']);
        if ($normalizedEmail !== null) {
            $profile['distinct_emails'][$normalizedEmail] = $event['email'];
        }

        $normalizedPhone = $this->normalizePhone($event['phone']);
        if ($normalizedPhone !== null) {
            $profile['distinct_phones'][$normalizedPhone] = $event['phone'];
        }
    }

    private function finalizeProfile(array $profile): object
    {
        $this->applyCachedMetricsFallback($profile);

        $profile['total_spent'] = round((float) $profile['total_spent'], 2);
        $profile['account_state'] = $profile['is_registered'] ? 'registered' : 'guest';
        $profile['marketing_state'] = $profile['has_profiling_consent']
            ? 'full'
            : (($profile['has_marketing_consent'] || $profile['marketing_opt_in']) ? 'soft_marketing' : 'no_marketing');

        $profile['customer_score'] = $this->resolveCustomerScore($profile);
        $profile['segments'] = $this->resolveSegments($profile);
        $profile['segment_labels'] = collect($profile['segments'])
            ->mapWithKeys(fn (string $segment) => [$segment => $this->getSegmentLabel($segment)])
            ->all();
        $profile['lifecycle_segment'] = $this->resolveLifecycleSegment($profile['segments'], $profile['cached_lifecycle_segment']);
        $profile['lifecycle_label'] = $profile['lifecycle_segment']
            ? $this->getSegmentLabel($profile['lifecycle_segment'])
            : null;
        $profile['highlight_segment'] = $this->resolveHighlightSegment($profile['segments']);
        $profile['highlight_label'] = $profile['highlight_segment']
            ? $this->getSegmentLabel($profile['highlight_segment'])
            : null;
        $profile['detail_url'] = $this->resolveDetailUrl($profile);
        $profile['search_text'] = $this->buildSearchText($profile);
        $profile['sort_timestamp'] = $profile['last_activity_at']?->timestamp
            ?? $profile['created_at']?->timestamp
            ?? 0;

        unset(
            $profile['customer_ids'],
            $profile['primary_customer_updated_at'],
            $profile['has_marketing_consent'],
            $profile['has_profiling_consent'],
            $profile['marketing_opt_in'],
            $profile['cached_customer_score'],
            $profile['cached_lifecycle_segment'],
            $profile['cached_orders_count'],
            $profile['cached_reservations_count'],
            $profile['cached_interactions_count'],
            $profile['cached_total_spent'],
            $profile['cached_last_activity_at'],
            $profile['weekday_counts'],
            $profile['time_slot_counts'],
            $profile['distinct_emails'],
            $profile['distinct_phones'],
            $profile['field_timestamps'],
            $profile['field_priorities']
        );

        return (object) $profile;
    }

    private function resolveSegments(array $profile): array
    {
        $segments = [];
        $interactions = (int) $profile['interactions_count'];
        $orders = (int) $profile['orders_count'];
        $reservations = (int) $profile['reservations_count'];
        $lastActivityAt = $profile['last_activity_at'];
        $daysSinceActivity = $lastActivityAt instanceof Carbon
            ? max(0, $lastActivityAt->diffInDays(now()))
            : null;
        $customerScore = $profile['customer_score'];
        $highValueThreshold = (float) config('crm.high_value_threshold', 100);

        if ($interactions === 1 && $daysSinceActivity !== null && $daysSinceActivity <= 14) {
            $segments[] = 'new_customers';
        }

        if ($interactions >= 2 && $daysSinceActivity !== null && $daysSinceActivity <= 30) {
            $segments[] = 'active_customers';
        }

        if ($interactions >= 5 || ($customerScore !== null && $customerScore >= 75)) {
            $segments[] = 'loyal_customers';
        }

        if ($daysSinceActivity !== null && $daysSinceActivity >= 30 && $daysSinceActivity <= 60) {
            $segments[] = 'at_risk_customers';
        }

        if ($daysSinceActivity !== null && $daysSinceActivity > 60) {
            $segments[] = 'lost_customers';
        }

        if ((float) $profile['total_spent'] > $highValueThreshold) {
            $segments[] = 'high_value_customers';
        }

        if ($reservations >= 1 && $orders === 0) {
            $segments[] = 'reservation_only';
        }

        if ($orders >= 1 && $reservations === 0) {
            $segments[] = 'order_only';
        }

        if ($this->isHabitCustomer($profile)) {
            $segments[] = 'habit_customers';
        }

        if ($interactions === 1 && $customerScore !== null && $customerScore < 35) {
            $segments[] = 'low_engagement';
        }

        return array_values(array_unique($segments));
    }

    private function resolveLifecycleSegment(array $segments, ?string $cachedLifecycle): ?string
    {
        if ($cachedLifecycle !== null) {
            return $cachedLifecycle;
        }

        $priority = [
            'lost_customers',
            'at_risk_customers',
            'loyal_customers',
            'active_customers',
            'new_customers',
        ];

        foreach ($priority as $segment) {
            if (in_array($segment, $segments, true)) {
                return $segment;
            }
        }

        return null;
    }

    private function resolveHighlightSegment(array $segments): ?string
    {
        $priority = [
            'high_value_customers',
            'habit_customers',
            'order_only',
            'reservation_only',
            'low_engagement',
        ];

        foreach ($priority as $segment) {
            if (in_array($segment, $segments, true)) {
                return $segment;
            }
        }

        return null;
    }

    private function resolveCustomerScore(array $profile): ?int
    {
        $derivedScore = $this->deriveCustomerScore($profile);
        $cachedScore = $profile['cached_customer_score'];

        if ($cachedScore === null) {
            return $derivedScore;
        }

        if ($profile['interactions_count'] > 0 && (float) $cachedScore <= 0) {
            return $derivedScore;
        }

        return max(0, min(100, (int) round((float) $cachedScore)));
    }

    private function deriveCustomerScore(array $profile): ?int
    {
        if ((int) $profile['interactions_count'] === 0 && (float) $profile['total_spent'] <= 0) {
            return null;
        }

        $threshold = max(1.0, (float) config('crm.high_value_threshold', 100));
        $interactionPoints = min(50, (int) $profile['interactions_count'] * 10);
        $spendPoints = min(25, ((float) $profile['total_spent'] / $threshold) * 25);
        $daysSinceActivity = $profile['last_activity_at'] instanceof Carbon
            ? max(0, $profile['last_activity_at']->diffInDays(now()))
            : null;

        $recencyPoints = match (true) {
            $daysSinceActivity === null => 0,
            $daysSinceActivity <= 14 => 18,
            $daysSinceActivity <= 30 => 14,
            $daysSinceActivity <= 60 => 8,
            $daysSinceActivity <= 90 => 4,
            default => 0,
        };

        $channelPoints = match (true) {
            $profile['orders_count'] > 0 && $profile['reservations_count'] > 0 => 7,
            $profile['interactions_count'] > 0 => 2,
            default => 0,
        };

        return max(0, min(100, (int) round($interactionPoints + $spendPoints + $recencyPoints + $channelPoints)));
    }

    private function isHabitCustomer(array $profile): bool
    {
        $weekdayPeak = collect($profile['weekday_counts'])->max() ?? 0;
        $timeSlotPeak = collect($profile['time_slot_counts'])->max() ?? 0;

        return $weekdayPeak >= 3 || $timeSlotPeak >= 3;
    }

    private function resolveDetailUrl(array $profile): ?string
    {
        if ($profile['customer_id']) {
            return route('admin.customers.show', $profile['customer_id']);
        }

        if (count($profile['distinct_emails']) === 1) {
            $email = collect($profile['distinct_emails'])->filter()->first();

            if ($email) {
                return route('admin.customers.show_guest', ['email' => $email]);
            }
        }

        return null;
    }

    private function buildSearchText(array $profile): string
    {
        $values = array_filter([
            $profile['name'],
            $profile['surname'],
            $profile['email'],
            $profile['phone'],
            ...array_values($profile['distinct_emails']),
            ...array_values($profile['distinct_phones']),
        ]);

        return mb_strtolower(trim(implode(' ', array_unique($values))));
    }

    private function applyFilters(Collection $customers, array $filters): Collection
    {
        $search = mb_strtolower(trim((string) ($filters['search'] ?? '')));
        $type = trim((string) ($filters['type'] ?? 'all'));
        $segments = $this->normalizeSegmentKeys($filters);

        if ($search !== '') {
            $customers = $customers
                ->filter(fn (object $customer) => str_contains($customer->search_text, $search))
                ->values();
        }

        if ($type === 'orders') {
            $customers = $customers->filter(fn (object $customer) => $customer->orders_count > 0)->values();
        } elseif ($type === 'reservations') {
            $customers = $customers->filter(fn (object $customer) => $customer->reservations_count > 0)->values();
        } elseif ($type === 'both') {
            $customers = $customers
                ->filter(fn (object $customer) => $customer->orders_count > 0 && $customer->reservations_count > 0)
                ->values();
        }

        if ($segments !== []) {
            $customers = $customers
                ->filter(function (object $customer) use ($segments) {
                    return count(array_intersect($segments, $customer->segments)) > 0;
                })
                ->values();
        }

        return $customers->values();
    }

    private function normalizeSegmentKeys(array $filters): array
    {
        $values = [];

        if (isset($filters['segment']) && $filters['segment'] !== '') {
            $values[] = $filters['segment'];
        }

        if (isset($filters['segments']) && is_array($filters['segments'])) {
            $values = array_merge($values, $filters['segments']);
        }

        $allowed = array_keys($this->getSegments());

        return collect($values)
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => in_array($value, $allowed, true))
            ->unique()
            ->values()
            ->all();
    }

    private function mergeCachedMetrics(array &$profile, Customer $customer): void
    {
        $columns = $this->resolvedMetricColumns();

        if ($columns['customer_score']) {
            $score = $customer->{$columns['customer_score']};
            if ($score !== null) {
                $profile['cached_customer_score'] = $profile['cached_customer_score'] === null
                    ? (float) $score
                    : max((float) $profile['cached_customer_score'], (float) $score);
            }
        }

        if ($columns['lifecycle_segment']) {
            $mapped = $this->mapLifecycleSegment($customer->{$columns['lifecycle_segment']});
            if ($mapped !== null) {
                $profile['cached_lifecycle_segment'] = $mapped;
            }
        }

        if ($columns['orders_count']) {
            $value = $customer->{$columns['orders_count']};
            if ($value !== null) {
                $profile['cached_orders_count'] = max((int) ($profile['cached_orders_count'] ?? 0), (int) $value);
            }
        }

        if ($columns['reservations_count']) {
            $value = $customer->{$columns['reservations_count']};
            if ($value !== null) {
                $profile['cached_reservations_count'] = max((int) ($profile['cached_reservations_count'] ?? 0), (int) $value);
            }
        }

        if ($columns['interactions_count']) {
            $value = $customer->{$columns['interactions_count']};
            if ($value !== null) {
                $profile['cached_interactions_count'] = max((int) ($profile['cached_interactions_count'] ?? 0), (int) $value);
            }
        }

        if ($columns['total_spent']) {
            $value = $customer->{$columns['total_spent']};
            if ($value !== null) {
                $profile['cached_total_spent'] = max((float) ($profile['cached_total_spent'] ?? 0), (float) $value);
            }
        }

        if ($columns['last_activity_at']) {
            $value = $customer->{$columns['last_activity_at']};
            $parsed = $this->parseMetricTimestamp($value);
            if ($parsed && (! $profile['cached_last_activity_at'] || $parsed->gt($profile['cached_last_activity_at']))) {
                $profile['cached_last_activity_at'] = $parsed;
            }
        }
    }

    private function applyCachedMetricsFallback(array &$profile): void
    {
        if ($profile['orders_count'] === 0 && $profile['cached_orders_count'] !== null) {
            $profile['orders_count'] = (int) $profile['cached_orders_count'];
        }

        if ($profile['reservations_count'] === 0 && $profile['cached_reservations_count'] !== null) {
            $profile['reservations_count'] = (int) $profile['cached_reservations_count'];
        }

        if ($profile['interactions_count'] === 0 && $profile['cached_interactions_count'] !== null) {
            $profile['interactions_count'] = (int) $profile['cached_interactions_count'];
        }

        if ($profile['interactions_count'] === 0) {
            $profile['interactions_count'] = (int) $profile['orders_count'] + (int) $profile['reservations_count'];
        }

        if ((float) $profile['total_spent'] <= 0 && $profile['cached_total_spent'] !== null) {
            $profile['total_spent'] = (float) $profile['cached_total_spent'];
        }

        if (! $profile['last_activity_at'] && $profile['cached_last_activity_at']) {
            $profile['last_activity_at'] = $profile['cached_last_activity_at'];
        }
    }

    private function mergeIdentityField(array &$profile, string $field, $value, ?Carbon $timestamp, int $priority): void
    {
        $value = $this->trimString($value);
        if ($value === null) {
            return;
        }

        $currentValue = $profile[$field];
        $currentTimestamp = $profile['field_timestamps'][$field] ?? null;
        $currentPriority = $profile['field_priorities'][$field] ?? -1;
        $candidateTimestamp = $timestamp?->timestamp ?? 0;
        $existingTimestamp = $currentTimestamp?->timestamp ?? -1;

        if (
            $currentValue === null
            || $priority > $currentPriority
            || ($priority === $currentPriority && $candidateTimestamp >= $existingTimestamp)
        ) {
            $profile[$field] = $value;
            $profile['field_timestamps'][$field] = $timestamp;
            $profile['field_priorities'][$field] = $priority;
        }
    }

    private function shouldReplacePrimaryCustomer(array $profile, ?Carbon $updatedAt, bool $isRegistered): bool
    {
        if ($profile['customer_id'] === null) {
            return true;
        }

        if ($isRegistered && ! $profile['is_registered']) {
            return true;
        }

        $currentTimestamp = $profile['primary_customer_updated_at']?->timestamp ?? 0;
        $candidateTimestamp = $updatedAt?->timestamp ?? 0;

        return $candidateTimestamp >= $currentTimestamp;
    }

    private function parseActivityTimestamp(?string $dateSlot, $createdAt): ?Carbon
    {
        $raw = $this->trimString($dateSlot);
        if ($raw !== null) {
            foreach (['d/m/Y H:i', 'Y-m-d H:i', 'd/m/Y H:i:s', 'Y-m-d H:i:s'] as $format) {
                try {
                    return Carbon::createFromFormat($format, $raw);
                } catch (\Throwable $exception) {
                }
            }

            try {
                return Carbon::parse($raw);
            } catch (\Throwable $exception) {
            }
        }

        if ($createdAt) {
            try {
                return Carbon::parse($createdAt);
            } catch (\Throwable $exception) {
            }
        }

        return null;
    }

    private function parseMetricTimestamp($value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable $exception) {
            return null;
        }
    }

    private function timeSlotKey(?Carbon $activityAt): ?string
    {
        if (! $activityAt) {
            return null;
        }

        return $activityAt->format('H:00');
    }

    private function identityTokens(?int $customerId, ?string $email, ?string $phone): array
    {
        $tokens = [];

        if ($customerId) {
            $tokens[] = 'customer:'.$customerId;
        }

        $normalizedEmail = $this->normalizeEmail($email);
        if ($normalizedEmail !== null) {
            $tokens[] = 'email:'.$normalizedEmail;
        }

        $normalizedPhone = $this->normalizePhone($phone);
        if ($normalizedPhone !== null) {
            $tokens[] = 'phone:'.$normalizedPhone;
        }

        return array_values(array_unique($tokens));
    }

    private function unionIdentifiers(array &$parents, array $tokens): void
    {
        if ($tokens === []) {
            return;
        }

        foreach ($tokens as $token) {
            if (! isset($parents[$token])) {
                $parents[$token] = $token;
            }
        }

        $first = $tokens[0];
        foreach (array_slice($tokens, 1) as $token) {
            $this->union($parents, $first, $token);
        }
    }

    private function resolveIdentityRoot(array &$parents, array $tokens): ?string
    {
        if ($tokens === []) {
            return null;
        }

        return $this->find($parents, $tokens[0]);
    }

    private function union(array &$parents, string $left, string $right): void
    {
        $leftRoot = $this->find($parents, $left);
        $rightRoot = $this->find($parents, $right);

        if ($leftRoot !== $rightRoot) {
            $parents[$rightRoot] = $leftRoot;
        }
    }

    private function find(array &$parents, string $token): string
    {
        if (! isset($parents[$token])) {
            $parents[$token] = $token;
        }

        if ($parents[$token] !== $token) {
            $parents[$token] = $this->find($parents, $parents[$token]);
        }

        return $parents[$token];
    }

    private function normalizeEmail(?string $email): ?string
    {
        $email = $this->trimString($email);

        return $email === null ? null : Customer::normalizeEmail($email);
    }

    private function normalizePhone(?string $phone): ?string
    {
        $phone = $this->trimString($phone);
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

    private function trimString($value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function customerColumns(): array
    {
        if ($this->customerColumnsCache !== null) {
            return $this->customerColumnsCache;
        }

        return $this->customerColumnsCache = Schema::hasTable('customers')
            ? Schema::getColumnListing('customers')
            : [];
    }

    private function metricColumns(): array
    {
        return array_values(array_filter($this->resolvedMetricColumns()));
    }

    private function resolvedMetricColumns(): array
    {
        if ($this->customerMetricColumnsCache !== null) {
            return $this->customerMetricColumnsCache;
        }

        return $this->customerMetricColumnsCache = [
            'customer_score' => $this->resolveMetricColumn(['customer_score', 'crm_score']),
            'lifecycle_segment' => $this->resolveMetricColumn(['lifecycle_segment', 'crm_lifecycle', 'customer_lifecycle']),
            'orders_count' => $this->resolveMetricColumn(['orders_count', 'crm_orders_count']),
            'reservations_count' => $this->resolveMetricColumn(['reservations_count', 'crm_reservations_count']),
            'interactions_count' => $this->resolveMetricColumn(['interactions_count', 'crm_interactions_count']),
            'total_spent' => $this->resolveMetricColumn(['total_spent', 'total_spent_cents', 'crm_total_spent']),
            'last_activity_at' => $this->resolveMetricColumn(['last_activity_at', 'crm_last_activity_at']),
        ];
    }

    private function resolveMetricColumn(array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if (in_array($candidate, $this->customerColumns(), true)) {
                return $candidate;
            }
        }

        return null;
    }

    private function mapLifecycleSegment($value): ?string
    {
        $normalized = trim(mb_strtolower((string) $value));
        if ($normalized === '') {
            return null;
        }

        $map = [
            'new' => 'new_customers',
            'new_customer' => 'new_customers',
            'new_customers' => 'new_customers',
            'active' => 'active_customers',
            'active_customer' => 'active_customers',
            'active_customers' => 'active_customers',
            'loyal' => 'loyal_customers',
            'loyal_customer' => 'loyal_customers',
            'loyal_customers' => 'loyal_customers',
            'at_risk' => 'at_risk_customers',
            'at_risk_customer' => 'at_risk_customers',
            'at_risk_customers' => 'at_risk_customers',
            'lost' => 'lost_customers',
            'lost_customer' => 'lost_customers',
            'lost_customers' => 'lost_customers',
        ];

        return $map[$normalized] ?? null;
    }
}
