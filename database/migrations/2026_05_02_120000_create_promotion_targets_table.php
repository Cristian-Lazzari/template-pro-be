<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('promotion_targets')) {
            return;
        }

        Schema::create('promotion_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained('promotions')->cascadeOnDelete();
            $table->string('target_type', 50);
            $table->unsignedBigInteger('target_id')->nullable();
            $table->decimal('discount', 12, 2)->nullable();
            $table->string('type_discount', 30)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('promotion_id');
            $table->index(['target_type', 'target_id']);
            $table->unique(
                ['promotion_id', 'target_type', 'target_id'],
                'promotion_targets_promotion_target_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_targets');
    }
};
