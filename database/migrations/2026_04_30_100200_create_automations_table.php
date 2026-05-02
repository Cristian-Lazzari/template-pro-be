<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('automations')) {
            return;
        }

        Schema::create('automations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('trigger')->nullable()->index();
            $table->string('status')->default('draft')->index();
            $table->foreignId('model_id')->nullable()->constrained('models')->nullOnDelete();
            $table->unsignedInteger('total_activation')->default(0);
            $table->unsignedInteger('total_sent')->default(0);
            $table->timestamp('last_run_at')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automations');
    }
};
