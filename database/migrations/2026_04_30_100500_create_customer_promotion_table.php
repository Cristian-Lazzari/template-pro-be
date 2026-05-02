<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('customer_promotion')) {
            return;
        }

        Schema::create('customer_promotion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('promotion_id')->constrained('promotions')->cascadeOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained('campaigns')->nullOnDelete();
            $table->foreignId('automation_id')->nullable()->constrained('automations')->nullOnDelete();
            $table->timestamp('email_sent_at')->nullable()->index();
            $table->timestamp('email_click_at')->nullable();
            $table->timestamp('email_open_at')->nullable();
            $table->timestamp('promo_used')->nullable()->index();
            $table->uuid('tracking_token')->nullable()->unique();
            $table->string('status')->default('assigned')->index();
            $table->decimal('discount_amount', 12, 2)->nullable();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('reservation_id')->nullable()->constrained('reservations')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'promotion_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_promotion');
    }
};
