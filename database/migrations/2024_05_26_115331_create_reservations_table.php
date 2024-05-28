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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('date_slot', 16);
            $table->string('status', 10);

            $table->string('name', 50);
            $table->string('surname', 50);
            $table->string('email', 100);
            $table->string('phone', 20);
            $table->boolean('news_letter');
            
            $table->string('n_person', 10);
            $table->string('message', 500)->nullable();
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
        Schema::dropIfExists('reservations');
    }
};
