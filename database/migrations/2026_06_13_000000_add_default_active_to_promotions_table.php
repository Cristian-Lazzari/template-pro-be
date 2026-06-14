<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('promotions') || Schema::hasColumn('promotions', 'default_active')) {
            return;
        }

        Schema::table('promotions', function (Blueprint $table) {
            $table->boolean('default_active')->default(false)->index();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('promotions') || ! Schema::hasColumn('promotions', 'default_active')) {
            return;
        }

        Schema::table('promotions', function (Blueprint $table) {
            $table->dropColumn('default_active');
        });
    }
};
