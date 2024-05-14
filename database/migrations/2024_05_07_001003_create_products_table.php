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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->default('1'); 
            $table->foreign     ('category_id')->references('id')->on('categories'); 

            $table->string      ('name', 100);
            $table->bigInteger  ('price');
            $table->string      ('image')->nullable();
            $table->text        ('description')->nullable(); 
            
            $table->text        ('allergiens')->nullable(); 
            
            $table->tinyInteger  ('slot_plate'); //indica se è di tipo fq ft o altro e quanti slot occupa es: a-1, a-3, b-1, c ( -0 )
            $table->string      ('type_plate', 4); //indica se è di tipo fq ft o altro e quanti slot occupa es: a-1, a-3, b-1, c ( -0 )
            $table->tinyInteger ('tag_set'); //indica se questo prodotto puo subire modifiche 0 nessuna, 1 togli ingredienti, 2 aggiungi e togli
            $table->boolean     ('visible')->default(true);
            $table->boolean      ('archived')->default(false);
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
        Schema::dropIfExists('products');
    }
};
