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
        Schema::table('ingredient_product', function (Blueprint $table) {
            $table->integer('sort_order')->default(0);
        });
    }

    public function down()
    {
        Schema::table('ingredient_product', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }

};
