<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('models')) {
            return;
        }

        Schema::table('models', function (Blueprint $table) {
            if (! Schema::hasColumn('models', 'has_promotion')) {
                $table->boolean('has_promotion')->default(false)->after('status');
            }

            if (! Schema::hasColumn('models', 'cta_label')) {
                $table->string('cta_label', 100)->nullable()->after('has_promotion');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('models')) {
            return;
        }

        Schema::table('models', function (Blueprint $table) {
            $cols = array_filter([
                Schema::hasColumn('models', 'has_promotion') ? 'has_promotion' : null,
                Schema::hasColumn('models', 'cta_label')     ? 'cta_label'     : null,
            ]);
            if ($cols) {
                $table->dropColumn(array_values($cols));
            }
        });
    }
};
