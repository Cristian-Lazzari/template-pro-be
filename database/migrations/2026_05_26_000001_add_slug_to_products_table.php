<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Passo 1: colonna nullable per consentire il backfill
        Schema::table('products', function (Blueprint $table) {
            $table->string('slug', 120)->nullable()->after('name');
        });

        // Passo 2: backfill — genera slug univoci per i prodotti esistenti
        $usedSlugs = [];
        DB::table('products')->orderBy('id')->each(function ($product) use (&$usedSlugs) {
            $base = Str::slug($product->name);
            if ($base === '') {
                $base = 'prodotto';
            }

            $slug    = $base;
            $counter = 1;
            while (in_array($slug, $usedSlugs, true)) {
                $slug = $base . '-' . (++$counter);
            }

            $usedSlugs[] = $slug;
            DB::table('products')->where('id', $product->id)->update(['slug' => $slug]);
        });

        // Passo 3: indice unique dopo il backfill
        Schema::table('products', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
