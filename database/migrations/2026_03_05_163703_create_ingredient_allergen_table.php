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
        Schema::create('ingredient_allergen', function (Blueprint $table) {
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('allergen_id')->constrained()->cascadeOnDelete();

            $table->primary(['ingredient_id','allergen_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ingredient_allergen');
    }
};
