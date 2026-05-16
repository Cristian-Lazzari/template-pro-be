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
            if (! Schema::hasColumn('customers', 'email_marketing_consent_at')) {
                $after = Schema::hasColumn('customers', 'marketing_consent_at')
                    ? 'marketing_consent_at'
                    : 'registered_at';
                $table->timestamp('email_marketing_consent_at')->nullable()->after($after);
            }

            if (! Schema::hasColumn('customers', 'whatsapp_marketing_consent_at')) {
                $table->timestamp('whatsapp_marketing_consent_at')->nullable()->after('email_marketing_consent_at');
            }

            if (! Schema::hasColumn('customers', 'tracking_consent_at')) {
                $table->timestamp('tracking_consent_at')->nullable()->after('profiling_consent_at');
            }

            if (! Schema::hasColumn('customers', 'privacy_accepted_at')) {
                $table->timestamp('privacy_accepted_at')->nullable()->after('tracking_consent_at');
            }

            if (! Schema::hasColumn('customers', 'privacy_accepted_version')) {
                $table->string('privacy_accepted_version', 50)->nullable()->after('privacy_accepted_at');
            }

            if (! Schema::hasColumn('customers', 'consents_updated_at')) {
                $table->timestamp('consents_updated_at')->nullable()->after('privacy_accepted_version');
            }

            if (! Schema::hasColumn('customers', 'soft_email_marketing_unsubscribed_at')) {
                $table->timestamp('soft_email_marketing_unsubscribed_at')->nullable()->after('consents_updated_at');
            }

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

        // Propaga marketing_consent_at → email_marketing_consent_at per i clienti esistenti
        if (
            Schema::hasColumn('customers', 'marketing_consent_at') &&
            Schema::hasColumn('customers', 'email_marketing_consent_at')
        ) {
            DB::table('customers')
                ->whereNotNull('marketing_consent_at')
                ->whereNull('email_marketing_consent_at')
                ->update(['email_marketing_consent_at' => DB::raw('marketing_consent_at')]);
        }

        // Elimina il vecchio campo ora sostituito da email_marketing_consent_at
        if (Schema::hasColumn('customers', 'marketing_consent_at')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn('marketing_consent_at');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('customers')) {
            return;
        }

        // Ripristina marketing_consent_at e ricopia i dati
        if (! Schema::hasColumn('customers', 'marketing_consent_at')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->timestamp('marketing_consent_at')->nullable()->after('registered_at');
            });

            if (Schema::hasColumn('customers', 'email_marketing_consent_at')) {
                DB::table('customers')
                    ->whereNotNull('email_marketing_consent_at')
                    ->whereNull('marketing_consent_at')
                    ->update(['marketing_consent_at' => DB::raw('email_marketing_consent_at')]);
            }
        }

        Schema::table('customers', function (Blueprint $table) {
            $toDrop = array_values(array_filter([
                'soft_email_marketing_unsubscribed_at',
                'consents_updated_at',
                'privacy_accepted_version',
                'privacy_accepted_at',
                'tracking_consent_at',
                'whatsapp_marketing_consent_at',
                'email_marketing_consent_at',
                'customer_score',
                'lifecycle_segment',
                'last_activity_at',
                'last_marketing_contact_at',
                'orders_count',
                'reservations_count',
                'interactions_count',
                'total_spent',
            ], fn ($col) => Schema::hasColumn('customers', $col)));

            if (! empty($toDrop)) {
                $table->dropColumn($toDrop);
            }
        });
    }
};
