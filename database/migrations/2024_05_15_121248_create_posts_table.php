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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();

            $table->string('date', 50);
            $table->string('place', 150);
            $table->string('title', 150);
            $table->text('description');
            $table->text('links');
            
            $table->smallInteger('order');
            $table->string('img_1')->nullable();
            $table->string('img_2')->nullable();
            
            $table->boolean('visible')->default(false);
            $table->boolean('archived')->default(false);

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
        Schema::dropIfExists('posts');
    }
};
