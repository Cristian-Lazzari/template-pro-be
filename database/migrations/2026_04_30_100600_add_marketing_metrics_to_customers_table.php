<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customers')) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'customer_score')) {
                $table->unsignedTinyInteger('customer_score')->nullable()->index();
            }

            if (! Schema::hasColumn('customers', 'lifecycle_segment')) {
                $table->string('lifecycle_segment')->nullable()->index();
            }

            if (! Schema::hasColumn('customers', 'last_activity_at')) {
                $table->timestamp('last_activity_at')->nullable()->index();
            }

            if (! Schema::hasColumn('customers', 'last_marketing_contact_at')) {
                $table->timestamp('last_marketing_contact_at')->nullable()->index();
            }

            if (! Schema::hasColumn('customers', 'orders_count')) {
                $table->unsignedInteger('orders_count')->default(0);
            }

            if (! Schema::hasColumn('customers', 'reservations_count')) {
                $table->unsignedInteger('reservations_count')->default(0);
            }

            if (! Schema::hasColumn('customers', 'interactions_count')) {
                $table->unsignedInteger('interactions_count')->default(0);
            }

            if (! Schema::hasColumn('customers', 'total_spent')) {
                $table->decimal('total_spent', 12, 2)->default(0);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('customers')) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            foreach ([
                'customer_score',
                'lifecycle_segment',
                'last_activity_at',
                'last_marketing_contact_at',
                'orders_count',
                'reservations_count',
                'interactions_count',
                'total_spent',
            ] as $column) {
                if (Schema::hasColumn('customers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
