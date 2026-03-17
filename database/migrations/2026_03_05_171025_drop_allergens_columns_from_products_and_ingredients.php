<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// return new class extends Migration
// {
//     /**
//      * Run the migrations.
//      *
//      * @return void
//      */
//     public function up()
//     {
//         Schema::table('products_and_ingredients', function (Blueprint $table) {
//             //
//         });
//     }

//     /**
//      * Reverse the migrations.
//      *
//      * @return void
//      */
//     public function down()
//     {
//         Schema::table('products_and_ingredients', function (Blueprint $table) {
//             //
//         });
//     }
// };


return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'allergens')) {
                $table->dropColumn('allergens');
            }
        });

        Schema::table('ingredients', function (Blueprint $table) {
            if (Schema::hasColumn('ingredients', 'allergens')) {
                $table->dropColumn('allergens');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // rimetti la colonna com’era (string o json), scegli tu:
            $table->text('allergens')->nullable();
        });

        Schema::table('ingredients', function (Blueprint $table) {
            $table->text('allergens')->nullable();
        });
    }
};