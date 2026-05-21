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

        if (! Schema::hasColumn('customers', 'birthday')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->date('birthday')->nullable()->after('gender');
            });
        }

        if (Schema::hasColumn('customers', 'age')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn('age');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('customers')) {
            return;
        }

        if (! Schema::hasColumn('customers', 'age')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->unsignedTinyInteger('age')->nullable()->after('gender');
            });
        }

        if (Schema::hasColumn('customers', 'birthday')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn('birthday');
            });
        }
    }
};
