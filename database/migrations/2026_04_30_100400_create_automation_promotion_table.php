<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('automation_promotion')) {
            return;
        }

        Schema::create('automation_promotion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automation_id')->constrained('automations')->cascadeOnDelete();
            $table->foreignId('promotion_id')->constrained('promotions')->cascadeOnDelete();
            $table->unsignedInteger('total_activation')->default(0);
            $table->unsignedInteger('total_sent')->default(0);
            $table->timestamps();

            $table->unique(['automation_id', 'promotion_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_promotion');
    }
};
