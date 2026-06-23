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
        Schema::table('posts', function (Blueprint $table) {
            // Etichetta facoltativa del bottone associato al link
            $table->string('link_label')->nullable()->after('link');
        });

        // Foto aggiuntive (galleria) di un post: news (path 1) e storia/about (path 2)
        Schema::create('post_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->string('image');
            $table->smallInteger('order')->default(0);
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
        Schema::dropIfExists('post_images');

        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('link_label');
        });
    }
};
