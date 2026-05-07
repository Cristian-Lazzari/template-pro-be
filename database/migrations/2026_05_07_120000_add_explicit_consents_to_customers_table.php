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

        $this->addColumnIfMissing('email_marketing_consent_at', function (Blueprint $table) {
            $table->timestamp('email_marketing_consent_at')->nullable()->after('marketing_consent_at');
        });

        $this->addColumnIfMissing('whatsapp_marketing_consent_at', function (Blueprint $table) {
            $table->timestamp('whatsapp_marketing_consent_at')->nullable()->after('email_marketing_consent_at');
        });

        $this->addColumnIfMissing('tracking_consent_at', function (Blueprint $table) {
            $table->timestamp('tracking_consent_at')->nullable()->after('profiling_consent_at');
        });

        $this->addColumnIfMissing('privacy_accepted_at', function (Blueprint $table) {
            $table->timestamp('privacy_accepted_at')->nullable()->after('tracking_consent_at');
        });

        $this->addColumnIfMissing('privacy_accepted_version', function (Blueprint $table) {
            $table->string('privacy_accepted_version', 50)->nullable()->after('privacy_accepted_at');
        });

        $this->addColumnIfMissing('consents_updated_at', function (Blueprint $table) {
            $table->timestamp('consents_updated_at')->nullable()->after('privacy_accepted_version');
        });

        if (
            Schema::hasColumn('customers', 'marketing_consent_at')
            && Schema::hasColumn('customers', 'email_marketing_consent_at')
        ) {
            DB::table('customers')
                ->whereNotNull('marketing_consent_at')
                ->whereNull('email_marketing_consent_at')
                ->update([
                    'email_marketing_consent_at' => DB::raw('marketing_consent_at'),
                ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('customers')) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            foreach ([
                'consents_updated_at',
                'privacy_accepted_version',
                'privacy_accepted_at',
                'tracking_consent_at',
                'whatsapp_marketing_consent_at',
                'email_marketing_consent_at',
            ] as $column) {
                if (Schema::hasColumn('customers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function addColumnIfMissing(string $column, callable $definition): void
    {
        if (Schema::hasColumn('customers', $column)) {
            return;
        }

        Schema::table('customers', $definition);
    }
};
