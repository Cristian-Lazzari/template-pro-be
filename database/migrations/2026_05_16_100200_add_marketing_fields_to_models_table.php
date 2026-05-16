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
            $table->string('type')->default('marketing')->index();
            $table->string('channel')->default('email')->index();
            $table->string('status')->default('draft')->index();
            $table->longText('body_html')->nullable();
            $table->longText('body_text')->nullable();
            $table->json('variables')->nullable();
            $table->json('preview_data')->nullable();
            $table->timestamp('last_used_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('models')) {
            return;
        }

        Schema::table('models', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'channel',
                'status',
                'body_html',
                'body_text',
                'variables',
                'preview_data',
                'last_used_at',
            ]);
        });
    }
};
