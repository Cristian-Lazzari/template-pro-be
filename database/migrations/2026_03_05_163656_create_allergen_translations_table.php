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
       Schema::create('allergen_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('allergen_id')->constrained()->cascadeOnDelete();
            $table->string('locale',5);
            $table->string('name');
            $table->timestamps();

            $table->unique(['allergen_id','locale']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('allergen_translations');
    }
};
