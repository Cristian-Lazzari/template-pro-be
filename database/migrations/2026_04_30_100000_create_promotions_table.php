<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('promotions')) {
            return;
        }

        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('status')->default('draft')->index();
            $table->string('case_use')->nullable()->index();
            $table->decimal('discount', 12, 2)->nullable();
            $table->string('type_discount')->nullable()->index();
            $table->decimal('minimum_pretest', 12, 2)->nullable();
            $table->string('cta')->nullable();
            $table->boolean('permanent')->default(false)->index();
            $table->timestamp('schedule_at')->nullable()->index();
            $table->timestamp('expiring_at')->nullable()->index();
            $table->unsignedInteger('total_activation')->default(0);
            $table->unsignedInteger('total_sent')->default(0);
            $table->unsignedInteger('total_used')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
