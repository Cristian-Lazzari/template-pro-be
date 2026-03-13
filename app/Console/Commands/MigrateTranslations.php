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

class MigrateTranslations extends Command
{
    protected $signature = 'translations:migrate';

    protected $description = 'Move old translatable columns into translation tables and auto-translate them';

    public function handle()
    {
        $defaultLang = 'it';

        // Metti qui le lingue che vuoi creare
        $languages = ['it', 'en', 'de', 'es'];
        $set_lang = Setting::where('name', 'Lingua')->first();


        if(!$set_lang){
            $set_lang = new Setting;
            $set_lang->name = 'Lingua';
            $set_lang->property = json_encode(
                [
                    'default' => 'it',
                    'languages' => $languages,
                ]
            );
            $set_lang->save();
        }

        /** @var GoogleTranslateService $translator */
        $translator = app(GoogleTranslateService::class);

        $this->info('Starting translations migration...');

        $this->migrateCategories($translator, $defaultLang, $languages);
        $this->migrateProducts($translator, $defaultLang, $languages);
        $this->migrateIngredients($translator, $defaultLang, $languages);
        $this->migrateMenus($translator, $defaultLang, $languages);
        $this->migrateMenuProducts($translator, $defaultLang, $languages);

        $this->info('Done!');
        return self::SUCCESS;
    }

    protected function migrateCategories(GoogleTranslateService $translator, string $defaultLang, array $languages): void
    {
        $this->info('Migrating Categories...');

        Category::query()->chunkById(100, function ($categories) use ($translator, $defaultLang, $languages) {
            foreach ($categories as $category) {
                $name = $category->getRawOriginal('name');
                $description = $category->getRawOriginal('description');

                foreach ($languages as $lang) {
                    CategoryTranslation::updateOrCreate(
                        [
                            'category_id' => $category->id,
                            'lang' => $lang,
                        ],
                        [
                            'name' => $this->translateValue($translator, $name, $defaultLang, $lang),
                            'description' => $this->translateValue($translator, $description, $defaultLang, $lang),
                        ]
                    );
                }
            }
        });
    }

    protected function migrateProducts(GoogleTranslateService $translator, string $defaultLang, array $languages): void
    {
        $this->info('Migrating Products...');

        Product::query()->chunkById(100, function ($products) use ($translator, $defaultLang, $languages) {
            foreach ($products as $product) {
                $name = $product->getRawOriginal('name');
                $description = $product->getRawOriginal('description');

                foreach ($languages as $lang) {
                    ProductTranslation::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'lang' => $lang,
                        ],
                        [
                            'name' => $this->translateValue($translator, $name, $defaultLang, $lang),
                            'description' => $this->translateValue($translator, $description, $defaultLang, $lang),
                        ]
                    );
                }
            }
        });
    }

    protected function migrateIngredients(GoogleTranslateService $translator, string $defaultLang, array $languages): void
    {
        $this->info('Migrating Ingredients...');

        Ingredient::query()->chunkById(100, function ($ingredients) use ($translator, $defaultLang, $languages) {
            foreach ($ingredients as $ingredient) {
                $name = $ingredient->getRawOriginal('name');

                foreach ($languages as $lang) {
                    IngredientTranslation::updateOrCreate(
                        [
                            'ingredient_id' => $ingredient->id,
                            'lang' => $lang,
                        ],
                        [
                            'name' => $this->translateValue($translator, $name, $defaultLang, $lang),
                        ]
                    );
                }
            }
        });
    }

    protected function migrateMenus(GoogleTranslateService $translator, string $defaultLang, array $languages): void
    {
        $this->info('Migrating Menus...');

        Menu::query()->chunkById(100, function ($menus) use ($translator, $defaultLang, $languages) {
            foreach ($menus as $menu) {
                $name = $menu->getRawOriginal('name');
                $description = $menu->getRawOriginal('description');

                foreach ($languages as $lang) {
                    MenuTranslation::updateOrCreate(
                        [
                            'menu_id' => $menu->id,
                            'lang' => $lang,
                        ],
                        [
                            'name' => $this->translateValue($translator, $name, $defaultLang, $lang),
                            'description' => $this->translateValue($translator, $description, $defaultLang, $lang),
                        ]
                    );
                }
            }
        });
    }

    protected function migrateMenuProducts(GoogleTranslateService $translator, string $defaultLang, array $languages): void
    {
        $this->info('Migrating MenuProduct labels...');

        MenuProduct::query()->chunkById(100, function ($menuProducts) use ($translator, $defaultLang, $languages) {
            foreach ($menuProducts as $menuProduct) {
                $label = $menuProduct->getRawOriginal('label');

                // se la label è nulla o vuota non creo traduzioni inutili
                if ($label === null || trim($label) === '') {
                    continue;
                }

                foreach ($languages as $lang) {
                    MenuProductTranslation::updateOrCreate(
                        [
                            'menu_product_id' => $menuProduct->id,
                            'lang' => $lang,
                        ],
                        [
                            'label' => $this->translateValue($translator, $label, $defaultLang, $lang),
                        ]
                    );
                }
            }
        });
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
            /**
             * Se nel tuo servizio il metodo si chiama in modo diverso
             * cambia solo questa riga.
             *
             * Esempi possibili:
             * - $translator->translate($value, $targetLang, $sourceLang)
             * - $translator->translateText($value, $targetLang, $sourceLang)
             */
            return $translator->translate($value, $targetLang, $sourceLang);
        } catch (Throwable $e) {
            $this->warn("Translation failed [{$sourceLang}->{$targetLang}] for text: {$value}");
            $this->warn($e->getMessage());

            // fallback: salvo l'originale invece di perdere il dato
            return $value;
        }
    }
}