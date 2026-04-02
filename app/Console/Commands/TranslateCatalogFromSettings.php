<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Ingredient;
use App\Models\IngredientTranslation;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Models\Setting;
use App\Services\GoogleTranslateService;
use Illuminate\Console\Command;
use Throwable;

class TranslateCatalogFromSettings extends Command
{
    protected $signature = 'catalog:translate
        {--dry-run : Show what would be translated without writing to database}
        {--force : Overwrite existing translations for non-default languages}';

    protected $description = 'Translate products, categories and ingredients into all languages configured in settings';

    protected int $createdCount = 0;

    protected int $updatedCount = 0;

    protected int $wouldCreateCount = 0;

    protected int $wouldUpdateCount = 0;

    public function handle(): int
    {
        [$defaultLang, $languages] = $this->resolveLanguages();
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        if (count($languages) <= 1) {
            $this->warn('Only one language configured. Nothing to translate.');

            return self::SUCCESS;
        }

        /** @var GoogleTranslateService $translator */
        $translator = app(GoogleTranslateService::class);

        $this->info("Starting catalog translation from default language: {$defaultLang}");

        if ($dryRun) {
            $this->warn('Dry run enabled: no database write will be performed.');
        }

        if ($force) {
            $this->warn('Force mode enabled: existing non-default translations will be overwritten.');
        }

        $this->translateCategories($translator, $defaultLang, $languages, $dryRun, $force);
        $this->translateProducts($translator, $defaultLang, $languages, $dryRun, $force);
        $this->translateIngredients($translator, $defaultLang, $languages, $dryRun, $force);

        if ($dryRun) {
            $this->info(
                'Dry run completed. Rows to create: '
                . $this->wouldCreateCount
                . '. Rows to update: '
                . $this->wouldUpdateCount
            );

            return self::SUCCESS;
        }

        $this->info(
            'Translation completed. Rows created: '
            . $this->createdCount
            . '. Rows updated: '
            . $this->updatedCount
        );

        return self::SUCCESS;
    }

    protected function resolveLanguages(): array
    {
        $fallbackDefault = (string) config('configurazione.default_lang', 'it');
        $setting = Setting::query()->where('name', 'Lingua')->first();

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

        foreach (($data['languages'] ?? []) as $lang) {
            if (is_string($lang) && trim($lang) !== '') {
                $languages[] = trim($lang);
            }
        }

        if (!in_array($defaultLang, $languages, true)) {
            $languages[] = $defaultLang;
        }

        return [$defaultLang, array_values(array_unique($languages))];
    }

    protected function translateCategories(
        GoogleTranslateService $translator,
        string $defaultLang,
        array $languages,
        bool $dryRun,
        bool $force
    ): void {
        $this->info('Translating Categories...');

        Category::query()->chunkById(100, function ($categories) use ($translator, $defaultLang, $languages, $dryRun, $force) {
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
                    if ($lang === $defaultLang) {
                        continue;
                    }

                    $payload = [
                        'name' => $this->translateValue($translator, $source->name, $defaultLang, $lang),
                        'description' => $this->translateValue($translator, $source->description, $defaultLang, $lang),
                    ];

                    $this->syncTranslation(
                        $translations->get($lang),
                        CategoryTranslation::class,
                        ['category_id' => $category->id, 'lang' => $lang],
                        $payload,
                        $dryRun,
                        $force
                    );
                }
            }
        });
    }

    protected function translateProducts(
        GoogleTranslateService $translator,
        string $defaultLang,
        array $languages,
        bool $dryRun,
        bool $force
    ): void {
        $this->info('Translating Products...');

        Product::query()->chunkById(100, function ($products) use ($translator, $defaultLang, $languages, $dryRun, $force) {
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
                    if ($lang === $defaultLang) {
                        continue;
                    }

                    $payload = [
                        'name' => $this->translateValue($translator, $source->name, $defaultLang, $lang),
                        'description' => $this->translateValue($translator, $source->description, $defaultLang, $lang),
                    ];

                    $this->syncTranslation(
                        $translations->get($lang),
                        ProductTranslation::class,
                        ['product_id' => $product->id, 'lang' => $lang],
                        $payload,
                        $dryRun,
                        $force
                    );
                }
            }
        });
    }

    protected function translateIngredients(
        GoogleTranslateService $translator,
        string $defaultLang,
        array $languages,
        bool $dryRun,
        bool $force
    ): void {
        $this->info('Translating Ingredients...');

        Ingredient::query()->chunkById(100, function ($ingredients) use ($translator, $defaultLang, $languages, $dryRun, $force) {
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
                    if ($lang === $defaultLang) {
                        continue;
                    }

                    $payload = [
                        'name' => $this->translateValue($translator, $source->name, $defaultLang, $lang),
                    ];

                    $this->syncTranslation(
                        $translations->get($lang),
                        IngredientTranslation::class,
                        ['ingredient_id' => $ingredient->id, 'lang' => $lang],
                        $payload,
                        $dryRun,
                        $force
                    );
                }
            }
        });
    }

    protected function syncTranslation(
        ?object $existingTranslation,
        string $modelClass,
        array $identity,
        array $payload,
        bool $dryRun,
        bool $force
    ): void {
        if ($existingTranslation) {
            if (!$force) {
                return;
            }

            if ($dryRun) {
                $this->wouldUpdateCount++;
                return;
            }

            $existingTranslation->update($payload);
            $this->updatedCount++;

            return;
        }

        if ($dryRun) {
            $this->wouldCreateCount++;
            return;
        }

        $modelClass::create(array_merge($identity, $payload));
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

        if ($sourceLang === $targetLang) {
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
