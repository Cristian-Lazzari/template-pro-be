<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            ! Schema::hasTable('customers')
            || Schema::hasColumn('customers', 'soft_email_marketing_unsubscribed_at')
        ) {
            return;
        }

        $afterColumn = $this->firstExistingColumn([
            'consents_updated_at',
            'email_marketing_consent_at',
            'marketing_consent_at',
        ]);

        Schema::table('customers', function (Blueprint $table) use ($afterColumn) {
            $column = $table->timestamp('soft_email_marketing_unsubscribed_at')
                ->nullable();

            if ($afterColumn !== null) {
                $column->after($afterColumn);
            }
        });
    }

    public function down(): void
    {
        if (
            ! Schema::hasTable('customers')
            || ! Schema::hasColumn('customers', 'soft_email_marketing_unsubscribed_at')
        ) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('soft_email_marketing_unsubscribed_at');
        });
    }

    private function firstExistingColumn(array $columns): ?string
    {
        foreach ($columns as $column) {
            if (Schema::hasColumn('customers', $column)) {
                return $column;
            }
        }

        return null;
    }
};
