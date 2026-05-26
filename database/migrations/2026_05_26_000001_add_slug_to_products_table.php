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
        //
        // Il nome reale è in product_translations, non nella colonna products.name.
        // Strategia di risoluzione del nome per ogni prodotto:
        //   1. traduzione nella lingua di default di sistema (DEFAULT_LANG / 'it')
        //   2. traduzione in 'en' (fallback del trait HasTranslations)
        //   3. prima traduzione disponibile qualsiasi lingua
        //   4. colonna products.name (legacy, prodotti pre-sistema traduzioni)
        //   5. 'prodotto' (sicurezza assoluta)

        $defaultLang = config('configurazione.default_lang') ?: env('DEFAULT_LANG') ?: 'it';

        $usedSlugs = [];

        DB::table('products')->orderBy('id')->each(function ($product) use (&$usedSlugs, $defaultLang) {
            $name = $this->resolveProductName($product->id, $defaultLang, $product->name ?? '');
            $base = Str::slug($name);
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

    private function resolveProductName(int $productId, string $defaultLang, string $legacyName): string
    {
        $translations = DB::table('product_translations')
            ->where('product_id', $productId)
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->get(['lang', 'name'])
            ->keyBy('lang');

        // 1. lingua di default del sistema
        if (isset($translations[$defaultLang])) {
            return (string) $translations[$defaultLang]->name;
        }

        // 2. fallback 'en' (coerente con HasTranslations)
        if (isset($translations['en'])) {
            return (string) $translations['en']->name;
        }

        // 3. prima traduzione disponibile
        if ($translations->isNotEmpty()) {
            return (string) $translations->first()->name;
        }

        // 4. colonna legacy products.name (prodotti pre-sistema traduzioni)
        if ($legacyName !== '') {
            return $legacyName;
        }

        // 5. sicurezza assoluta
        return 'prodotto';
    }
};
