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


            $table->string('title', 100);
            $table->text('description');
            $table->tinyInteger('path');
            $table->smallInteger('order');
            $table->string('image');
            $table->text('hashtag')->nullable();;
            $table->string('link')->nullable();
            
            $table->boolean  ('visible')->default(false);
            $table->boolean  ('archived')->default(false);

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
