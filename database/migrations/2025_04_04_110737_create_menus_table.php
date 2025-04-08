<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->default(1); 
            $table->foreign('category_id')->references('id')->on('categories');
            $table->string('name', 150);
            $table->string('image')->nullable();
            $table->bigInteger('price');
            $table->bigInteger('old_price');
            $table->text('description', 500)->nullable();
            $table->text('fixed_menu');
            $table->boolean('visible')->default(true);
            $table->boolean('promo')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('menus');
    }
};
