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
        Schema::create('menu_order', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('menu_id');
            $table->unsignedBigInteger('order_id');
            
            $table->foreign('menu_id')->references('id')->on('menus');
            $table->foreign('order_id')->references('id')->on('orders');
            $table->string('choices');
            $table->tinyInteger('quantity');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('menu_order');
    }
};
