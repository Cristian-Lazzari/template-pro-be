<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('promotions')) {
            Schema::create('promotions', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('slug')->unique();
                $table->string('status')->default('draft')->index();
                $table->string('case_use')->nullable()->index();
                $table->decimal('discount', 12, 2)->nullable();
                $table->string('type_discount')->nullable()->index();
                $table->decimal('minimum_pretest', 12, 2)->nullable();
                $table->string('cta')->nullable();
                $table->boolean('permanent')->default(false)->index();
                $table->boolean('default_active')->default(false)->index();
                $table->timestamp('schedule_at')->nullable()->index();
                $table->timestamp('expiring_at')->nullable()->index();
                $table->json('valid_weekdays')->nullable();
                $table->time('valid_from_time')->nullable();
                $table->time('valid_to_time')->nullable();
                $table->unsignedInteger('total_activation')->default(0);
                $table->unsignedInteger('total_sent')->default(0);
                $table->unsignedInteger('total_used')->default(0);
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('campaigns')) {
            Schema::create('campaigns', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('status')->default('draft')->index();
                $table->string('campaign_type', 50)->default('explicit_email_marketing')->index();
                $table->string('channel', 30)->default('email')->index();
                $table->string('consent_basis', 50)->default('explicit_email_marketing');
                $table->string('segment')->nullable()->index();
                $table->foreignId('model_id')->nullable()->constrained('models')->nullOnDelete();
                $table->timestamp('scheduled_at')->nullable()->index();
                $table->timestamp('sent_at')->nullable()->index();
                $table->unsignedInteger('total_activation')->default(0);
                $table->unsignedInteger('total_sent')->default(0);
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('automations')) {
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

        if (! Schema::hasTable('campaign_promotion')) {
            Schema::create('campaign_promotion', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campaign_id')->constrained('campaigns')->cascadeOnDelete();
                $table->foreignId('promotion_id')->constrained('promotions')->cascadeOnDelete();
                $table->unsignedInteger('total_activation')->default(0);
                $table->unsignedInteger('total_sent')->default(0);
                $table->timestamps();

                $table->unique(['campaign_id', 'promotion_id']);
            });
        }

        if (! Schema::hasTable('automation_promotion')) {
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

        if (! Schema::hasTable('customer_promotion')) {
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

        if (! Schema::hasTable('promotion_targets')) {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_targets');
        Schema::dropIfExists('customer_promotion');
        Schema::dropIfExists('automation_promotion');
        Schema::dropIfExists('campaign_promotion');
        Schema::dropIfExists('automations');
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('promotions');
    }
};
