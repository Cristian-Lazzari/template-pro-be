<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customers')) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'last_order_at')) {
                $table->timestamp('last_order_at')->nullable()->index();
            }

            if (! Schema::hasColumn('customers', 'last_booking_at')) {
                $table->timestamp('last_booking_at')->nullable()->index();
            }

            if (! Schema::hasColumn('customers', 'first_order_at')) {
                $table->timestamp('first_order_at')->nullable()->index();
            }

            if (! Schema::hasColumn('customers', 'first_booking_at')) {
                $table->timestamp('first_booking_at')->nullable()->index();
            }

            if (! Schema::hasColumn('customers', 'average_order_value')) {
                $table->decimal('average_order_value', 12, 2)->nullable();
            }
        });

        // Backfill orders stats.
        // NOTE: orders.status is a tinyInteger with no documented constants in the codebase.
        // From OrderController: status 0 can be both "pending new" and "cancelled" depending on
        // the transition. Status 6 = rimborso/refund. We include ALL orders (no status filter)
        // to avoid misclassifying valid orders. If the project later adds explicit status constants,
        // revisit this to restrict to confirmed/valid statuses only.
        if (
            Schema::hasTable('orders') &&
            Schema::hasColumn('orders', 'customer_id') &&
            Schema::hasColumn('orders', 'created_at')
        ) {
            DB::statement('
                UPDATE customers
                SET
                    first_order_at = COALESCE(first_order_at, (
                        SELECT MIN(created_at)
                        FROM orders
                        WHERE orders.customer_id = customers.id
                    )),
                    last_order_at = COALESCE(last_order_at, (
                        SELECT MAX(created_at)
                        FROM orders
                        WHERE orders.customer_id = customers.id
                    ))
                WHERE EXISTS (
                    SELECT 1
                    FROM orders
                    WHERE orders.customer_id = customers.id
                )
            ');
        }

        // Backfill reservations stats.
        // Same rationale as orders above: no status filter to avoid misclassifying.
        if (
            Schema::hasTable('reservations') &&
            Schema::hasColumn('reservations', 'customer_id') &&
            Schema::hasColumn('reservations', 'created_at')
        ) {
            DB::statement('
                UPDATE customers
                SET
                    first_booking_at = COALESCE(first_booking_at, (
                        SELECT MIN(created_at)
                        FROM reservations
                        WHERE reservations.customer_id = customers.id
                    )),
                    last_booking_at = COALESCE(last_booking_at, (
                        SELECT MAX(created_at)
                        FROM reservations
                        WHERE reservations.customer_id = customers.id
                    ))
                WHERE EXISTS (
                    SELECT 1
                    FROM reservations
                    WHERE reservations.customer_id = customers.id
                )
            ');
        }

        // Backfill average_order_value = total_spent / orders_count.
        if (
            Schema::hasColumn('customers', 'total_spent') &&
            Schema::hasColumn('customers', 'orders_count') &&
            Schema::hasColumn('customers', 'average_order_value')
        ) {
            DB::statement('
                UPDATE customers
                SET average_order_value = ROUND(total_spent / orders_count, 2)
                WHERE orders_count > 0
                  AND total_spent IS NOT NULL
                  AND total_spent > 0
                  AND average_order_value IS NULL
            ');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('customers')) {
            return;
        }

        $toDrop = array_values(array_filter(
            ['last_order_at', 'last_booking_at', 'first_order_at', 'first_booking_at', 'average_order_value'],
            fn ($col) => Schema::hasColumn('customers', $col)
        ));

        if (! empty($toDrop)) {
            Schema::table('customers', fn (Blueprint $table) => $table->dropColumn($toDrop));
        }
    }
};
