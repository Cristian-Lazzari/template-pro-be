<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Ingredient;
use App\Models\IngredientTranslation;
use App\Models\Menu;
use App\Models\MenuProduct;
use App\Models\MenuProductTranslation;
use App\Models\MenuTranslation;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Models\Setting;
use App\Services\GoogleTranslateService;
use Illuminate\Console\Command;
use Throwable;

class BackfillTranslationsFromDefault extends Command
{
    protected $signature = 'translations:backfill-from-default {--dry-run : Show what would be created without writing to database}';

    protected $description = 'Create missing translations using dashboard default language translations as source without overwriting existing rows';

    protected int $createdCount = 0;

    protected int $wouldCreateCount = 0;

    public function handle(): int
    {
        [$defaultLang, $languages] = $this->resolveLanguages();
        $dryRun = (bool) $this->option('dry-run');

        if (count($languages) <= 1) {
            $this->warn('Only one language configured. Nothing to backfill.');
            return self::SUCCESS;
        }

        /** @var GoogleTranslateService $translator */
        $translator = app(GoogleTranslateService::class);

        $this->info('Starting backfill from default language: ' . $defaultLang);

        if ($dryRun) {
            $this->warn('Dry run enabled: no database write will be performed.');
        }

        $this->migrateCategories($translator, $defaultLang, $languages, $dryRun);
        $this->migrateProducts($translator, $defaultLang, $languages, $dryRun);
        $this->migrateIngredients($translator, $defaultLang, $languages, $dryRun);
        $this->migrateMenus($translator, $defaultLang, $languages, $dryRun);
        $this->migrateMenuProducts($translator, $defaultLang, $languages, $dryRun);

        if ($dryRun) {
            $this->info('Dry run completed. Missing translations detected: ' . $this->wouldCreateCount);
        } else {
            $this->info('Backfill completed. New translations created: ' . $this->createdCount);
        }

        return self::SUCCESS;
    }

    protected function resolveLanguages(): array
    {
        $fallbackDefault = (string) config('configurazione.default_lang', 'it');
        $setting = Setting::where('name', 'Lingua')->first();

        if (!$setting || !$setting->property) {
            return [$fallbackDefault, [$fallbackDefault]];
        }

        $data = json_decode($setting->property, true);

        if (!is_array($data)) {
            return [$fallbackDefault, [$fallbackDefault]];
        }

        $defaultLang = isset($data['default']) && is_string($data['default'])
            ? trim($data['default'])
            : $fallbackDefault;

        $languages = [];

        if (isset($data['languages']) && is_array($data['languages'])) {
            foreach ($data['languages'] as $lang) {
                if (is_string($lang) && trim($lang) !== '') {
                    $languages[] = trim($lang);
                }
            }
        }

        if (!in_array($defaultLang, $languages, true)) {
            $languages[] = $defaultLang;
        }

        $languages = array_values(array_unique($languages));

        return [$defaultLang, $languages];
    }

    protected function migrateCategories(GoogleTranslateService $translator, string $defaultLang, array $languages, bool $dryRun): void
    {
        $this->info('Backfilling Categories...');

        Category::query()->chunkById(100, function ($categories) use ($translator, $defaultLang, $languages, $dryRun) {
            foreach ($categories as $category) {
                $translations = CategoryTranslation::query()
                    ->where('category_id', $category->id)
                    ->get()
                    ->keyBy('lang');

                /** @var CategoryTranslation|null $source */
                $source = $translations->get($defaultLang);

                if (!$source) {
                    $this->warn("Category {$category->id}: default translation '{$defaultLang}' not found, skipped.");
                    continue;
                }

                foreach ($languages as $lang) {
                    if ($translations->has($lang)) {
                        continue;
                    }

                    $payload = [
                        'category_id' => $category->id,
                        'lang' => $lang,
                        'name' => $this->translateValue($translator, $source->name, $defaultLang, $lang),
                        'description' => $this->translateValue($translator, $source->description, $defaultLang, $lang),
                    ];

                    $this->createOrCount(CategoryTranslation::class, $payload, $dryRun);
                }
            }
        });
    }

    protected function migrateProducts(GoogleTranslateService $translator, string $defaultLang, array $languages, bool $dryRun): void
    {
        $this->info('Backfilling Products...');

        Product::query()->chunkById(100, function ($products) use ($translator, $defaultLang, $languages, $dryRun) {
            foreach ($products as $product) {
                $translations = ProductTranslation::query()
                    ->where('product_id', $product->id)
                    ->get()
                    ->keyBy('lang');

                /** @var ProductTranslation|null $source */
                $source = $translations->get($defaultLang);

                if (!$source) {
                    $this->warn("Product {$product->id}: default translation '{$defaultLang}' not found, skipped.");
                    continue;
                }

                foreach ($languages as $lang) {
                    if ($translations->has($lang)) {
                        continue;
                    }

                    $payload = [
                        'product_id' => $product->id,
                        'lang' => $lang,
                        'name' => $this->translateValue($translator, $source->name, $defaultLang, $lang),
                        'description' => $this->translateValue($translator, $source->description, $defaultLang, $lang),
                    ];

                    $this->createOrCount(ProductTranslation::class, $payload, $dryRun);
                }
            }
        });
    }

    protected function migrateIngredients(GoogleTranslateService $translator, string $defaultLang, array $languages, bool $dryRun): void
    {
        $this->info('Backfilling Ingredients...');

        Ingredient::query()->chunkById(100, function ($ingredients) use ($translator, $defaultLang, $languages, $dryRun) {
            foreach ($ingredients as $ingredient) {
                $translations = IngredientTranslation::query()
                    ->where('ingredient_id', $ingredient->id)
                    ->get()
                    ->keyBy('lang');

                /** @var IngredientTranslation|null $source */
                $source = $translations->get($defaultLang);

                if (!$source) {
                    $this->warn("Ingredient {$ingredient->id}: default translation '{$defaultLang}' not found, skipped.");
                    continue;
                }

                foreach ($languages as $lang) {
                    if ($translations->has($lang)) {
                        continue;
                    }

                    $payload = [
                        'ingredient_id' => $ingredient->id,
                        'lang' => $lang,
                        'name' => $this->translateValue($translator, $source->name, $defaultLang, $lang),
                    ];

                    $this->createOrCount(IngredientTranslation::class, $payload, $dryRun);
                }
            }
        });
    }

    protected function migrateMenus(GoogleTranslateService $translator, string $defaultLang, array $languages, bool $dryRun): void
    {
        $this->info('Backfilling Menus...');

        Menu::query()->chunkById(100, function ($menus) use ($translator, $defaultLang, $languages, $dryRun) {
            foreach ($menus as $menu) {
                $translations = MenuTranslation::query()
                    ->where('menu_id', $menu->id)
                    ->get()
                    ->keyBy('lang');

                /** @var MenuTranslation|null $source */
                $source = $translations->get($defaultLang);

                if (!$source) {
                    $this->warn("Menu {$menu->id}: default translation '{$defaultLang}' not found, skipped.");
                    continue;
                }

                foreach ($languages as $lang) {
                    if ($translations->has($lang)) {
                        continue;
                    }

                    $payload = [
                        'menu_id' => $menu->id,
                        'lang' => $lang,
                        'name' => $this->translateValue($translator, $source->name, $defaultLang, $lang),
                        'description' => $this->translateValue($translator, $source->description, $defaultLang, $lang),
                    ];

                    $this->createOrCount(MenuTranslation::class, $payload, $dryRun);
                }
            }
        });
    }

    protected function migrateMenuProducts(GoogleTranslateService $translator, string $defaultLang, array $languages, bool $dryRun): void
    {
        $this->info('Backfilling MenuProduct labels...');

        MenuProduct::query()->chunkById(100, function ($menuProducts) use ($translator, $defaultLang, $languages, $dryRun) {
            foreach ($menuProducts as $menuProduct) {
                $translations = MenuProductTranslation::query()
                    ->where('menu_product_id', $menuProduct->id)
                    ->get()
                    ->keyBy('lang');

                /** @var MenuProductTranslation|null $source */
                $source = $translations->get($defaultLang);

                if (!$source) {
                    $this->warn("MenuProduct {$menuProduct->id}: default translation '{$defaultLang}' not found, skipped.");
                    continue;
                }

                if ($source->label === null || trim((string) $source->label) === '') {
                    continue;
                }

                foreach ($languages as $lang) {
                    if ($translations->has($lang)) {
                        continue;
                    }

                    $payload = [
                        'menu_product_id' => $menuProduct->id,
                        'lang' => $lang,
                        'label' => $this->translateValue($translator, $source->label, $defaultLang, $lang),
                    ];

                    $this->createOrCount(MenuProductTranslation::class, $payload, $dryRun);
                }
            }
        });
    }

    protected function createOrCount(string $modelClass, array $payload, bool $dryRun): void
    {
        if ($dryRun) {
            $this->wouldCreateCount++;
            return;
        }

        $modelClass::create($payload);
        $this->createdCount++;
    }

    protected function translateValue(
        GoogleTranslateService $translator,
        $value,
        string $sourceLang,
        string $targetLang
    ): ?string {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return $value;
        }

        if ($targetLang === $sourceLang) {
            return $value;
        }

        try {
            $translated = $translator->translate($value, $targetLang);

            return $translated ?: $value;
        } catch (Throwable $e) {
            $this->warn("Translation failed [{$sourceLang}->{$targetLang}] for text: {$value}");
            $this->warn($e->getMessage());

            return $value;
        }
    }
}