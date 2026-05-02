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
            if (! Schema::hasColumn('models', 'type')) {
                $table->string('type')->default('marketing')->index();
            }

            if (! Schema::hasColumn('models', 'channel')) {
                $table->string('channel')->default('email')->index();
            }

            if (! Schema::hasColumn('models', 'status')) {
                $table->string('status')->default('draft')->index();
            }

            if (! Schema::hasColumn('models', 'body_html')) {
                $table->longText('body_html')->nullable();
            }

            if (! Schema::hasColumn('models', 'body_text')) {
                $table->longText('body_text')->nullable();
            }

            if (! Schema::hasColumn('models', 'variables')) {
                $table->json('variables')->nullable();
            }

            if (! Schema::hasColumn('models', 'preview_data')) {
                $table->json('preview_data')->nullable();
            }

            if (! Schema::hasColumn('models', 'last_used_at')) {
                $table->timestamp('last_used_at')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('models')) {
            return;
        }

        Schema::table('models', function (Blueprint $table) {
            foreach ([
                'type',
                'channel',
                'status',
                'body_html',
                'body_text',
                'variables',
                'preview_data',
                'last_used_at',
            ] as $column) {
                if (Schema::hasColumn('models', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
