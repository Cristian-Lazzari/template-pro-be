<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Ingredient;
use App\Models\IngredientTranslation;
use App\Models\Product;
use App\Models\ProductTranslation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Ecce35MenuSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $menu = $this->menu();
            $categoryNames = array_keys($menu);
            $productNames = collect($menu)
                ->flatMap(fn (array $products) => array_column($products, 'name'))
                ->values()
                ->all();

            foreach ($menu as $categoryName => $products) {
                $category = $this->upsertCategory($categoryName);

                foreach ($products as $productData) {
                    $this->createProduct($category, $productData);
                }
            }

            $this->deleteProductsNotInMenu($productNames);
            $this->deleteUnusedCategories($categoryNames);
            $this->deleteUnusedIngredients();
        });
    }

    private function upsertCategory(string $name): Category
    {
        $translation = CategoryTranslation::query()
            ->where('lang', 'it')
            ->where('name', $name)
            ->first();

        $category = $translation?->category ?? Category::create([
            'icon' => null,
        ]);

        CategoryTranslation::updateOrCreate(
            [
                'category_id' => $category->id,
                'lang' => 'it',
            ],
            [
                'name' => $name,
                'description' => null,
            ]
        );

        $category->translations()->where('lang', '!=', 'it')->delete();

        return $category->fresh();
    }

    private function createProduct(Category $category, array $data): void
    {
        $product = new Product();

        $product->category_id = $category->id;
        $product->price = $data['price'];
        $product->old_price = null;
        $product->slot_plate = 1;
        $product->type_plate = 1;
        $product->tag_set = 0;
        $product->visible = true;
        $product->archived = false;
        $product->promotion = false;
        $product->save();

        ProductTranslation::create(
            [
                'product_id' => $product->id,
                'lang' => 'it',
                'name' => $data['name'],
                'description' => $data['description'],
            ]
        );

        $syncIngredients = [];

        foreach ($data['ingredients'] as $index => $ingredientData) {
            $ingredient = $this->upsertIngredient($ingredientData);
            $syncIngredients[$ingredient->id] = ['sort_order' => $index];
        }

        $product->ingredients()->sync($syncIngredients);
        $product->directAllergens()->sync($this->mapMenuAllergensToSystemIds($data['allergens']));
    }

    private function upsertIngredient(string|array $ingredientData): Ingredient
    {
        $name = is_array($ingredientData) ? $ingredientData['name'] : $ingredientData;

        $translation = IngredientTranslation::query()
            ->where('lang', 'it')
            ->where('name', $name)
            ->first();

        $ingredient = $translation?->ingredient ?? Ingredient::create([
            'price' => 0,
            'type' => json_encode([]),
            'icon' => null,
            'option' => false,
        ]);

        IngredientTranslation::updateOrCreate(
            [
                'ingredient_id' => $ingredient->id,
                'lang' => 'it',
            ],
            [
                'name' => $name,
            ]
        );

        $ingredient->translations()->where('lang', '!=', 'it')->delete();

        // Gli allergeni degli ingredienti vivono solo nella pivot `ingredient_allergen`.
        // Se sono presenti nel seed li sincronizziamo, altrimenti lasciamo invariati quelli esistenti.
        if (is_array($ingredientData) && array_key_exists('allergens', $ingredientData)) {
            $ingredient->allergens()->sync(
                $this->mapMenuAllergensToSystemIds($ingredientData['allergens'] ?? [])
            );
        }

        return $ingredient->fresh();
    }

    private function deleteProductsNotInMenu(array $productNames): void
    {
        Product::query()
            ->whereDoesntHave('translations', function ($query) use ($productNames) {
                $query->where('lang', 'it')->whereIn('name', $productNames);
            })
            ->get()
            ->each(function (Product $product) {
                $this->deleteProductSafely($product);
            });
    }

    private function deleteUnusedCategories(array $categoryNames): void
    {
        Category::query()
            ->whereDoesntHave('translations', function ($query) use ($categoryNames) {
                $query->where('lang', 'it')->whereIn('name', $categoryNames);
            })
            ->orWhereDoesntHave('products')
            ->get()
            ->unique('id')
            ->each
            ->delete();
    }

    private function deleteUnusedIngredients(): void
    {
        Ingredient::query()
            ->doesntHave('products')
            ->get()
            ->each
            ->delete();
    }

    private function deleteProductSafely(Product $product): void
    {
        $product->ingredients()->detach();
        $product->directAllergens()->detach();
        $product->orders()->detach();

        DB::table('menu_product')
            ->where('product_id', $product->id)
            ->delete();

        $product->delete();
    }

    private function mapMenuAllergensToSystemIds(array $menuAllergens): array
    {
        $map = [
            1 => 4,
            2 => 14,
            3 => 11,
            4 => 13,
            5 => 6,
            6 => 7,
            7 => 12,
            8 => 15,
            9 => 9,
            10 => 10,
            11 => 5,
            12 => 16,
            13 => 8,
            14 => 18,
        ];

        return collect($menuAllergens)
            ->map(fn ($id) => $map[(int) $id] ?? null)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function menu(): array
    {
        return [
            'Brusch-Ecce' => [
                [
                    'name' => 'La Tricolore',
                    'description' => 'Dalla professionalita storica di Rovagnati il miglior crudo, stracciatella home made, pomodori secchi, basilico, olio.',
                    'price' => 1300,
                    'allergens' => [1, 7, 9, 12],
                    'ingredients' => ['Prosciutto crudo Rovagnati', 'Stracciatella home made', 'Pomodori secchi', 'Basilico', 'Olio'],
                ],
                [
                    'name' => "L'Affumicata",
                    'description' => "Salmone norvegese affumicato a freddo, rucola fresca, stracciatella, scorsetta d'arancia, basilico, olio.",
                    'price' => 1500,
                    'allergens' => [1, 7],
                    'ingredients' => ['Salmone norvegese affumicato', 'Rucola fresca', 'Stracciatella', "Scorzetta d'arancia", 'Basilico', 'Olio'],
                ],
                [
                    'name' => 'Martina & Franca',
                    'description' => "Da Martina Franca il miglior capocollo, caciocavallo stagionato in grotta fuso, carciofo sott'olio home made, basilico, olio.",
                    'price' => 1500,
                    'allergens' => [1, 7, 9, 12],
                    'ingredients' => ['Capocollo', 'Caciocavallo stagionato', "Carciofo sott'olio home made", 'Basilico', 'Olio'],
                ],
                [
                    'name' => 'Made In Sud',
                    'description' => 'Cremoso di pomodoro e basilico home made, rucola fresca, friarello, grana 24 mesi, basilico, olio.',
                    'price' => 1200,
                    'allergens' => [1, 7],
                    'ingredients' => ['Cremoso di pomodoro e basilico home made', 'Rucola fresca', 'Friarello', 'Grana 24 mesi', 'Basilico', 'Olio'],
                ],
                [
                    'name' => 'La Nordica',
                    'description' => 'Prosciutto cotto di Praga, formaggio brie, funghi sottolio, basilico, olio.',
                    'price' => 1200,
                    'allergens' => [1, 7, 9, 12],
                    'ingredients' => ['Prosciutto cotto di Praga', 'Brie', 'Funghi sottolio', 'Basilico', 'Olio'],
                ],
                [
                    'name' => 'Mortazza Chic',
                    'description' => 'Mortadella Rovagnati, stracciatella home made, granella di pistacchio, basilico, olio.',
                    'price' => 1100,
                    'allergens' => [1, 7, 8],
                    'ingredients' => ['Mortadella Rovagnati', 'Stracciatella home made', 'Granella di pistacchio', 'Basilico', 'Olio'],
                ],
            ],
            'Pinse Gourmet' => [
                [
                    'name' => 'La Giallo Rossa',
                    'description' => 'Mozzarella, pomodoro datterino giallo e rosso, basilico, olio.',
                    'price' => 1400,
                    'allergens' => [1, 7],
                    'ingredients' => ['Mozzarella', 'Pomodoro datterino giallo', 'Pomodoro datterino rosso', 'Basilico', 'Olio'],
                ],
                [
                    'name' => 'La Fresca',
                    'description' => "Salmone norvegese affumicato a freddo, crema guacamole, scorsetta d'arancia, erbetta cipollina, olio.",
                    'price' => 1650,
                    'allergens' => [1, 3, 9, 11, 12],
                    'ingredients' => ['Salmone norvegese affumicato', 'Crema guacamole', "Scorsetta d'arancia", 'Erbetta cipollina', 'Olio'],
                ],
                [
                    'name' => 'Il Bosco Urbano',
                    'description' => 'Fonduta di gorgonzola dolce, crema ai funghi porcini, noci, cips di parmigiano e olio.',
                    'price' => 1650,
                    'allergens' => [1, 7, 8],
                    'ingredients' => ['Fonduta di gorgonzola dolce', 'Crema ai funghi porcini', 'Noci', 'Cips di parmigiano', 'Olio'],
                ],
                [
                    'name' => 'La Pistacchiosa',
                    'description' => 'Mortadella Rovagnati, stracciatella home made, granella di pistacchio, crumble di tarallo e olio.',
                    'price' => 1550,
                    'allergens' => [1, 7, 8],
                    'ingredients' => ['Mortadella Rovagnati', 'Stracciatella home made', 'Granella di pistacchio', 'Crumble di tarallo', 'Olio'],
                ],
                [
                    'name' => 'La Regina',
                    'description' => "Prosciutto crudo Rovagnati, stracciatella home made, pomodori secchi, funghi sott'olio, basilico, olio.",
                    'price' => 1500,
                    'allergens' => [1, 7, 12],
                    'ingredients' => ['Prosciutto crudo Rovagnati', 'Stracciatella home made', 'Pomodori secchi', "Funghi sott'olio", 'Basilico', 'Olio'],
                ],
                [
                    'name' => 'La Cacio-Pepe',
                    'description' => 'Mozzarella, fonduta di cacio pepe, bacon croccante, basilico, olio.',
                    'price' => 1600,
                    'allergens' => [1, 7],
                    'ingredients' => ['Mozzarella', 'Fonduta di cacio pepe', 'Bacon croccante', 'Basilico', 'Olio'],
                ],
            ],
            'Crunchy Buns' => [
                [
                    'name' => 'Ecce35',
                    'description' => 'Buns artigianale, pulled pork, fonduta di caciocavallo, rucola fresca e mayo.',
                    'price' => 1550,
                    'allergens' => [1, 3, 7],
                    'ingredients' => ['Buns artigianale', 'Pulled pork', 'Fonduta di caciocavallo', 'Rucola fresca', 'Mayo'],
                ],
                [
                    'name' => 'Norcia Crunch',
                    'description' => 'Buns artigianale, salsiccia di Norcia, fonduta di scamorza, cavolo viola e cipolla caramellata.',
                    'price' => 1550,
                    'allergens' => [1, 7, 12],
                    'ingredients' => ['Buns artigianale', 'Salsiccia di Norcia', 'Fonduta di scamorza', 'Cavolo viola', 'Cipolla caramellata'],
                ],
                [
                    'name' => 'Smoky Crunch',
                    'description' => 'Buns artigianale, hamburger di scottona 180 gr, cipolla crispy, bacon croccante, cheddar e salsa smoky.',
                    'price' => 1500,
                    'allergens' => [1, 6, 7, 10, 12],
                    'ingredients' => ['Buns artigianale', 'Hamburger di scottona 180 gr', 'Cipolla crispy', 'Bacon croccante', 'Cheddar', 'Salsa smoky'],
                ],
                [
                    'name' => 'Chicken Crunch',
                    'description' => 'Buns artigianale, cotoletta di pollo panata, cheddar e bacon croccante, pomodoro e insalata.',
                    'price' => 1450,
                    'allergens' => [1, 7],
                    'ingredients' => ['Buns artigianale', 'Cotoletta di pollo panata', 'Cheddar', 'Bacon croccante', 'Pomodoro', 'Insalata'],
                ],
                [
                    'name' => 'Italian Crunch',
                    'description' => 'Buns artigianale, hamburger di scottona 180 gr, mortadella crunch, stracciatella home made, granella di pistacchio, pomodoro secco e insalata.',
                    'price' => 1550,
                    'allergens' => [1, 7, 8, 12],
                    'ingredients' => ['Buns artigianale', 'Hamburger di scottona 180 gr', 'Mortadella crunch', 'Stracciatella home made', 'Granella di pistacchio', 'Pomodoro secco', 'Insalata'],
                ],
                [
                    'name' => 'Veggy Crunch',
                    'description' => 'Buns artigianale, hamburger di verdure CBT, pomodoro, cavolo viola e vegan mayo.',
                    'price' => 1400,
                    'allergens' => [1, 12],
                    'ingredients' => ['Buns artigianale', 'Hamburger di verdure CBT', 'Pomodoro', 'Cavolo viola', 'Vegan mayo'],
                ],
            ],
            'Insalat-Ecce' => [
                [
                    'name' => 'La Caprese',
                    'description' => 'Insalata iceberg, mozzarella, pomodoro datterino, basilico, olio e crostone di pane.',
                    'price' => 1050,
                    'allergens' => [1, 7],
                    'ingredients' => ['Insalata iceberg', 'Mozzarella', 'Pomodoro datterino', 'Basilico', 'Olio', 'Crostone di pane'],
                ],
                [
                    'name' => 'La Guaka Chichen',
                    'description' => 'Insalata iceberg, cotoletta di pollo panata, crema guacamole, pomodoro datterino, scaglie di grana, basilico, olio e crostone di pane.',
                    'price' => 1250,
                    'allergens' => [1, 3, 7, 10, 11, 12],
                    'ingredients' => ['Insalata iceberg', 'Cotoletta di pollo panata', 'Crema guacamole', 'Pomodoro datterino', 'Scaglie di grana', 'Basilico', 'Olio', 'Crostone di pane'],
                ],
                [
                    'name' => 'La Tuna Tay',
                    'description' => "Insalata iceberg, tonno sott'olio, stick di carote, cetriolo, semi di chia, salsa soia, olio e crostone di pane.",
                    'price' => 1250,
                    'allergens' => [1, 6, 8, 11],
                    'ingredients' => ['Insalata iceberg', "Tonno sott'olio", 'Stick di carote', 'Cetriolo', 'Semi di chia', 'Salsa soia', 'Olio', 'Crostone di pane'],
                ],
                [
                    'name' => 'La Norvegese',
                    'description' => "Insalata iceberg, salmone norvegese affumicato a freddo, cavolo viola, stracciatella home made, scorsetta d'arancia, semi di chia, basilico, olio e crostone di pane.",
                    'price' => 1400,
                    'allergens' => [1, 7, 8, 11],
                    'ingredients' => ['Insalata iceberg', 'Salmone norvegese affumicato', 'Cavolo viola', 'Stracciatella home made', "Scorsetta d'arancia", 'Semi di chia', 'Basilico', 'Olio', 'Crostone di pane'],
                ],
                [
                    'name' => 'Praga Zola Salad',
                    'description' => 'Insalata iceberg, prosciutto cotto di Praga, gorgonzola, cavolo viola, noci, basilico, olio e crostone di pane.',
                    'price' => 1250,
                    'allergens' => [1, 7, 8],
                    'ingredients' => ['Insalata iceberg', 'Prosciutto cotto di Praga', 'Gorgonzola', 'Cavolo viola', 'Noci', 'Basilico', 'Olio', 'Crostone di pane'],
                ],
            ],
            'Piatti Speciali' => [
                [
                    'name' => 'Tartare Di Fassona',
                    'description' => 'Tartare di manzo, cetriolo, senape, pomodoro, stracciatella, basilico, olio e crostone di pane.',
                    'price' => 1600,
                    'allergens' => [1, 7, 10],
                    'ingredients' => ['Tartare di manzo', 'Cetriolo', 'Senape', 'Pomodoro', 'Stracciatella', 'Basilico', 'Olio', 'Crostone di pane'],
                ],
                [
                    'name' => 'Carpaccio Di Manzo Iberico',
                    'description' => 'Carpaccio di manzo iberico, senape, salsa worcester, tabasco, rucola, cips di parmigiano, olio e crostone di pane.',
                    'price' => 1650,
                    'allergens' => [1, 7, 10],
                    'ingredients' => ['Carpaccio di manzo iberico', 'Senape', 'Salsa worcester', 'Tabasco', 'Rucola', 'Cips di parmigiano', 'Olio', 'Crostone di pane'],
                ],
                [
                    'name' => 'Carpaccio Black Angus Affumicato',
                    'description' => 'Carpaccio di black angus affumicato, stracciatella home made, rucola, olio e crostone di pane.',
                    'price' => 1650,
                    'allergens' => [1, 7],
                    'ingredients' => ['Carpaccio di black angus affumicato', 'Stracciatella home made', 'Rucola', 'Olio', 'Crostone di pane'],
                ],
                [
                    'name' => 'Polpetta Di Carne Al Sugo',
                    'description' => 'Polpetta di carne al sugo, scaglie di parmigiano, basilico, olio e crostone di pane.',
                    'price' => 1250,
                    'allergens' => [1, 7],
                    'ingredients' => ['Polpetta di carne al sugo', 'Scaglie di parmigiano', 'Basilico', 'Olio', 'Crostone di pane'],
                ],
                [
                    'name' => 'Costine Di Maiale BBQ',
                    'description' => 'Costolette di maiale BBQ, rucola, pomodoro, basilico, olio e patate.',
                    'price' => 2000,
                    'allergens' => [1, 6, 7, 8, 10, 12],
                    'ingredients' => ['Costolette di maiale BBQ', 'Rucola', 'Pomodoro', 'Basilico', 'Olio', 'Patate'],
                ],
                [
                    'name' => 'Bresaola Rucola & Grana',
                    'description' => 'Bresaola, rucola e grana.',
                    'price' => 1650,
                    'allergens' => [7],
                    'ingredients' => ['Bresaola', 'Rucola', 'Grana'],
                ],
                [
                    'name' => 'Fave & Cicorie',
                    'description' => 'Fave e cicorie.',
                    'price' => 1000,
                    'allergens' => [1, 7],
                    'ingredients' => ['Fave', 'Cicorie'],
                ],
                [
                    'name' => 'Salsiccia & Patate',
                    'description' => 'Salsiccia e patate.',
                    'price' => 1000,
                    'allergens' => [1, 7, 9],
                    'ingredients' => ['Salsiccia', 'Patate'],
                ],
            ],
            'Tapas' => [
                [
                    'name' => 'Barchetta Di Patata',
                    'description' => 'Con mortadella, stracciatella e pistacchio, 2 pezzi.',
                    'price' => 700,
                    'allergens' => [7, 8],
                    'ingredients' => ['Mortadella', 'Stracciatella', 'Pistacchio'],
                ],
                [
                    'name' => 'Baccala In Tempura',
                    'description' => 'Con crema ai funghi porcini, 2 pezzi.',
                    'price' => 800,
                    'allergens' => [1, 4],
                    'ingredients' => ['Baccala in tempura', 'Crema ai funghi porcini'],
                ],
                [
                    'name' => 'Polpetta Di Melanzane',
                    'description' => 'Tartare di melanzane e cips di parmigiano, 4 pezzi.',
                    'price' => 800,
                    'allergens' => [1, 4, 6, 7, 8, 10],
                    'ingredients' => ['Polpetta di melanzane', 'Tartare di melanzane', 'Cips di parmigiano'],
                ],
                [
                    'name' => 'Polpetta Di Carne',
                    'description' => 'Con crema cacio pepe ed erbetta cipollina, 4 pezzi.',
                    'price' => 800,
                    'allergens' => [1, 7],
                    'ingredients' => ['Polpetta di carne', 'Crema cacio pepe', 'Erbetta cipollina'],
                ],
                [
                    'name' => 'Pork Smoke',
                    'description' => 'Con salsa mango e habanero e bacon croccante, 6 pezzi.',
                    'price' => 1200,
                    'allergens' => [1, 3, 4, 6, 7, 8, 10],
                    'ingredients' => ['Salsa mango e habanero', 'Bacon croccante'],
                ],
                [
                    'name' => 'Spiedino Di Pollo Spicy',
                    'description' => 'Con salsa smoky, 4 pezzi.',
                    'price' => 1000,
                    'allergens' => [1, 7, 10, 12],
                    'ingredients' => ['Spiedino di pollo spicy', 'Salsa smoky'],
                ],
                [
                    'name' => 'Fagotto Di Patata',
                    'description' => 'Ripieno con erbetta cipollina e cremoso avvolto da prosciutto crudo, cipolla crispy e mayo, 2 pezzi.',
                    'price' => 800,
                    'allergens' => [1, 7, 10, 12],
                    'ingredients' => ['Erbetta cipollina', 'Cremoso', 'Prosciutto crudo', 'Cipolla crispy', 'Mayo'],
                ],
                [
                    'name' => 'Pittula Gourmet',
                    'description' => 'Con capocollo, cremoso di gorgonzola dolce e basilico, 4 pezzi.',
                    'price' => 800,
                    'allergens' => [1, 7, 12],
                    'ingredients' => ['Capocollo', 'Cremoso di gorgonzola dolce', 'Basilico'],
                ],
                [
                    'name' => 'Arancino Chorizo & Cheddar',
                    'description' => '2 pezzi.',
                    'price' => 800,
                    'allergens' => [1, 7, 12],
                    'ingredients' => ['Chorizo', 'Cheddar'],
                ],
                [
                    'name' => 'Polpetta Di Carciofi',
                    'description' => 'Con crema di gorgonzola e noci, 4 pezzi.',
                    'price' => 800,
                    'allergens' => [1, 7, 12],
                    'ingredients' => ['Polpetta di carciofi', 'Crema di gorgonzola', 'Noci'],
                ],
            ],
            'Aperi-Ecce' => [
                [
                    'name' => 'Apericena Ecce 35',
                    'description' => 'Selezione di tapas, focaccia con prosciutto crudo Rovagnati, stracciatella, crema guacamole e basilico, tagliere di salumi formaggi e latticini con pinsa condita olio e origano. Per 2 persone.',
                    'price' => 4000,
                    'allergens' => [1, 3, 7, 9, 10, 11, 12],
                    'ingredients' => ['Selezione di tapas', 'Focaccia', 'Prosciutto crudo Rovagnati', 'Stracciatella', 'Crema guacamole', 'Basilico', 'Tagliere di salumi', 'Formaggi', 'Latticini', 'Pinsa', 'Olio', 'Origano'],
                ],
                [
                    'name' => 'Aperiti-Amo',
                    'description' => 'Selezione di tapas dello chef. Prezzo a persona, minimo 2 persone.',
                    'price' => 1000,
                    'allergens' => [1, 3, 7, 9, 10, 11, 12],
                    'ingredients' => ['Selezione di tapas dello chef'],
                ],
            ],
            'Taglieri e Patate' => [
                [
                    'name' => 'Tagliere Maxi',
                    'description' => 'Tagliere di salumi, formaggi e latticini con pinsa condita con olio e origano. Per 2 persone.',
                    'price' => 3000,
                    'allergens' => [1, 7, 12],
                    'ingredients' => ['Tagliere di salumi', 'Formaggi', 'Latticini', 'Pinsa', 'Olio', 'Origano'],
                ],
                [
                    'name' => 'Focaccione Dello Chef',
                    'description' => '4 pezzi.',
                    'price' => 1200,
                    'allergens' => [],
                    'ingredients' => ['Focaccia'],
                ],
                [
                    'name' => 'Patate Al Rosmarino',
                    'description' => 'Patate al rosmarino.',
                    'price' => 700,
                    'allergens' => [],
                    'ingredients' => ['Patate', 'Rosmarino'],
                ],
                [
                    'name' => 'Patate Cacio-Pepe',
                    'description' => 'Patate cacio e pepe.',
                    'price' => 1000,
                    'allergens' => [],
                    'ingredients' => ['Patate', 'Cacio', 'Pepe'],
                ],
                [
                    'name' => 'Patate Cheddar & Bacon',
                    'description' => 'Patate con cheddar e bacon.',
                    'price' => 1200,
                    'allergens' => [],
                    'ingredients' => ['Patate', 'Cheddar', 'Bacon'],
                ],
                [
                    'name' => 'Patate Zola Speck & Noci',
                    'description' => 'Patate con gorgonzola, speck e noci.',
                    'price' => 1200,
                    'allergens' => [],
                    'ingredients' => ['Patate', 'Gorgonzola', 'Speck', 'Noci'],
                ],
            ],
            'Dolci' => [
                [
                    'name' => 'Tiramisu Dello Chef',
                    'description' => 'Tiramisu dello chef.',
                    'price' => 700,
                    'allergens' => [],
                    'ingredients' => ['Tiramisu'],
                ],
                [
                    'name' => 'Cheesecake Ai Frutti Di Bosco',
                    'description' => 'Cheesecake ai frutti di bosco.',
                    'price' => 600,
                    'allergens' => [],
                    'ingredients' => ['Cheesecake', 'Frutti di bosco'],
                ],
                [
                    'name' => 'Flan Al Cioccolato',
                    'description' => 'Flan al cioccolato.',
                    'price' => 600,
                    'allergens' => [],
                    'ingredients' => ['Flan al cioccolato'],
                ],
                [
                    'name' => 'Semifreddo Al Pistacchio',
                    'description' => 'Semifreddo al pistacchio.',
                    'price' => 600,
                    'allergens' => [],
                    'ingredients' => ['Semifreddo al pistacchio'],
                ],
                [
                    'name' => 'Spumone Nocciola & Cioccolato',
                    'description' => 'Spumone nocciola e cioccolato.',
                    'price' => 600,
                    'allergens' => [],
                    'ingredients' => ['Spumone nocciola e cioccolato'],
                ],
            ],
        ];
    }
}
