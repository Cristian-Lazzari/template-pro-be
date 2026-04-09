<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('id')->constrained('customers')->nullOnDelete();
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('id')->constrained('customers')->nullOnDelete();
        });

        $eventQuery = DB::table('orders')
            ->selectRaw("
                LOWER(TRIM(email)) as email_key,
                TRIM(email) as email,
                NULLIF(TRIM(name), '') as name,
                NULLIF(TRIM(surname), '') as surname,
                NULLIF(TRIM(phone), '') as phone,
                news_letter,
                created_at
            ")
            ->whereNotNull('email')
            ->whereRaw("TRIM(email) <> ''")
            ->unionAll(
                DB::table('reservations')
                    ->selectRaw("
                        LOWER(TRIM(email)) as email_key,
                        TRIM(email) as email,
                        NULLIF(TRIM(name), '') as name,
                        NULLIF(TRIM(surname), '') as surname,
                        NULLIF(TRIM(phone), '') as phone,
                        news_letter,
                        created_at
                    ")
                    ->whereNotNull('email')
                    ->whereRaw("TRIM(email) <> ''")
            );

        $events = collect(
            DB::query()
                ->fromSub($eventQuery, 'customer_events')
                ->orderByDesc('created_at')
                ->get()
        )->groupBy('email_key');

        foreach ($events as $emailKey => $rows) {
            if (!is_string($emailKey) || $emailKey === '') {
                continue;
            }

            $latest = $rows->first();
            $marketingConsentAt = optional($rows->first(function ($row) {
                return (bool) $row->news_letter;
            }))->created_at;

            $customerId = DB::table('customers')
                ->whereRaw('LOWER(email) = ?', [$emailKey])
                ->value('id');

            if (!$customerId) {
                $customerId = DB::table('customers')->insertGetId([
                    'name' => $latest->name ?? '',
                    'surname' => $latest->surname ?? '',
                    'email' => $emailKey,
                    'phone' => $latest->phone,
                    'gender' => null,
                    'age' => null,
                    'profile_answers' => null,
                    'registered_at' => null,
                    'marketing_consent_at' => $marketingConsentAt,
                    'profiling_consent_at' => null,
                    'email_verified_at' => null,
                    'created_at' => $latest->created_at ?? now(),
                    'updated_at' => $latest->created_at ?? now(),
                ]);
            } else {
                $customer = DB::table('customers')
                    ->select(['name', 'surname', 'phone', 'marketing_consent_at'])
                    ->where('id', $customerId)
                    ->first();

                DB::table('customers')
                    ->where('id', $customerId)
                    ->update([
                        'name' => $customer->name ?: ($latest->name ?? ''),
                        'surname' => $customer->surname ?: ($latest->surname ?? ''),
                        'phone' => $customer->phone ?: $latest->phone,
                        'marketing_consent_at' => $customer->marketing_consent_at ?: $marketingConsentAt,
                        'updated_at' => now(),
                    ]);
            }

            DB::table('orders')
                ->whereNull('customer_id')
                ->whereRaw('LOWER(TRIM(email)) = ?', [$emailKey])
                ->update(['customer_id' => $customerId]);

            DB::table('reservations')
                ->whereNull('customer_id')
                ->whereRaw('LOWER(TRIM(email)) = ?', [$emailKey])
                ->update(['customer_id' => $customerId]);
        }
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_id');
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_id');
        });
    }
};
