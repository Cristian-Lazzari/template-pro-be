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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->string('date_slot', 16); 
            $table->tinyInteger('status'); //annullata confermata o in elaborazione

            $table->string('name', 50);
            $table->string('surname', 50);
            $table->string('email', 100);
            $table->string('phone', 20);

            $table->string('address', 120);
            $table->string('address_n', 4);
            $table->string('comune', 30);
            
            $table->smallInteger('tot_price');
            $table->string('message', 500)->nullable();
            
            $table->boolean('news_letter');

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
        Schema::dropIfExists('orders');
    }
};
