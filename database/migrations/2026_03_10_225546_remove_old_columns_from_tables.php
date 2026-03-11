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
        Schema::table('tables', function (Blueprint $table) {
            //
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tables', function (Blueprint $table) {
            //
        });
    }
};


// return new class extends Migration
// {
//     /**
//      * Run the migrations.
//      */
//     public function up(): void
//     {
//         // categories
//         Schema::table('categories', function (Blueprint $table) {
//             if (Schema::hasColumn('categories', 'name')) {
//                 $table->dropColumn('name');
//             }

//             if (Schema::hasColumn('categories', 'description')) {
//                 $table->dropColumn('description');
//             }
//         });

//         // products
//         Schema::table('products', function (Blueprint $table) {
//             if (Schema::hasColumn('products', 'name')) {
//                 $table->dropColumn('name');
//             }

//             if (Schema::hasColumn('products', 'description')) {
//                 $table->dropColumn('description');
//             }
//         });

//         // ingredients
//         Schema::table('ingredients', function (Blueprint $table) {
//             if (Schema::hasColumn('ingredients', 'name')) {
//                 $table->dropColumn('name');
//             }
//         });

//         // menus
//         Schema::table('menus', function (Blueprint $table) {
//             if (Schema::hasColumn('menus', 'name')) {
//                 $table->dropColumn('name');
//             }

//             if (Schema::hasColumn('menus', 'description')) {
//                 $table->dropColumn('description');
//             }
//         });

//         // pivot menu_product
//         Schema::table('menu_product', function (Blueprint $table) {
//             if (Schema::hasColumn('menu_product', 'label')) {
//                 $table->dropColumn('label');
//             }
//         });
//     }

//     /**
//      * Reverse the migrations.
//      */
//     public function down(): void
//     {
//         // categories
//         Schema::table('categories', function (Blueprint $table) {
//             $table->string('name')->nullable();
//             $table->text('description')->nullable();
//         });

//         // products
//         Schema::table('products', function (Blueprint $table) {
//             $table->string('name')->nullable();
//             $table->text('description')->nullable();
//         });

//         // ingredients
//         Schema::table('ingredients', function (Blueprint $table) {
//             $table->string('name')->nullable();
//         });

//         // menus
//         Schema::table('menus', function (Blueprint $table) {
//             $table->string('name')->nullable();
//             $table->text('description')->nullable();
//         });

//         // pivot
//         Schema::table('menu_product', function (Blueprint $table) {
//             $table->string('label')->nullable();
//         });
//     }
// };