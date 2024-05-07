<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();

            $table->string      ('name', 100);
            $table->bigInteger  ('price');
            $table->tinyInteger ('type'); //indica se deve essere aggiunto a
            $table->text        ('allergiens')->nullable() ;
            $table->string      ('icon')->nullable();
            
            $table->timestamps();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('ingredients');
    }
};
