<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_product_translations', function (Blueprint $table) {

            $table->id();

            $table->foreignId('menu_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('lang',5);

            $table->string('label');

            $table->timestamps();

            $table->unique(['menu_id','product_id','lang']);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_product_translations');
    }
};