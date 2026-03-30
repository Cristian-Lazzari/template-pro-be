<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $defaultLang = config('app.locale');
        $lang = $request->query('lang', $defaultLang);
        $from = $request->query('from');

        app()->setLocale($lang);
        // config(['app.locale' => $lang]);

        $toCollection = function ($value) {
            if ($value instanceof \Illuminate\Support\Collection) {
                return $value;
            }

            if (is_array($value)) {
                return collect($value);
            }

            return collect();
        };

        $hideProductTranslations = function ($product) use ($toCollection) {
            $product->makeHidden(['translations']);

            $ingredients = $product->relationLoaded('ingredients')
                ? $product->getRelation('ingredients')
                : $product->ingredients()->get();

            $toCollection($ingredients)->each(function ($ingredient) use ($toCollection) {
                $ingredient->makeHidden(['translations']);

                $allergens = $ingredient->relationLoaded('allergens')
                    ? $ingredient->getRelation('allergens')
                    : $ingredient->allergens()->get();

                $toCollection($allergens)->each(function ($allergen) {
                    $allergen->makeHidden(['translations']);
                });
            });

            $directAllergens = $product->relationLoaded('directAllergens')
                ? $product->getRelation('directAllergens')
                : $product->directAllergens()->get();

            $toCollection($directAllergens)->each(function ($allergen) {
                $allergen->makeHidden(['translations']);
            });
        };

        $hideMenuTranslations = function ($menu) use ($hideProductTranslations, $toCollection) {
            $menu->makeHidden(['translations']);

            $products = $menu->relationLoaded('products')
                ? $menu->getRelation('products')
                : $menu->products()->get();

            $toCollection($products)->each(function ($product) use ($hideProductTranslations) {
                $hideProductTranslations($product);
            });
        };

        $hideCategoryTranslations = function ($category) use ($hideProductTranslations, $hideMenuTranslations, $toCollection) {
            $category->makeHidden(['translations']);

            $products = $category->relationLoaded('products')
                ? $category->getRelation('products')
                : $category->products()->get();

            $toCollection($products)->each(function ($product) use ($hideProductTranslations) {
                $hideProductTranslations($product);
            });

            $menus = $category->relationLoaded('menus')
                ? $category->getRelation('menus')
                : $category->menus()->get();

            $toCollection($menus)->each(function ($menu) use ($hideMenuTranslations) {
                $hideMenuTranslations($menu);
            });
        };

        /*
        |--------------------------------------------------------------------------
        | TRADUZIONI
        |--------------------------------------------------------------------------
        */

        $translationScope = function ($q) use ($lang, $defaultLang) {
            $q->whereIn('lang', [$lang, $defaultLang])
            ->orderByRaw("CASE WHEN lang = ? THEN 0 ELSE 1 END", [$lang]);
        };

        /*
        |--------------------------------------------------------------------------
        | FILTRI
        |--------------------------------------------------------------------------
        */

        $productFilter = function ($q) use ($from) {

            if ($from !== 'menu') {
                $q->where('visible', true);
            }

            $q->where('archived', 0);
        };

        $menuFilter = function ($q) use ($from) {

            if ($from !== 'menu') {
                $q->where('visible', true);
            }

            $q->where('fixed_menu','!=',0);
        };

        /*
        |--------------------------------------------------------------------------
        | CATEGORIE
        |--------------------------------------------------------------------------
        */

        $categories = Category::query()

            ->whereHas('products', $productFilter)

            ->with([

                'translations' => $translationScope,

                'products' => function ($q) use ($productFilter,$translationScope){

                    $productFilter($q);

                    $q->orderBy('updated_at','desc')
                    ->with([
                            'translations' => $translationScope,
                            'ingredients.translations' => $translationScope,
                            'ingredients.allergens.translations' => $translationScope,
                            'directAllergens.translations' => $translationScope
                    ]);
                },

                'menus' => function ($q) use ($menuFilter,$translationScope){

                    $menuFilter($q);

                    $q->orderBy('updated_at','desc')
                    ->with([
                            'translations' => $translationScope,
                            'products.translations' => $translationScope,
                            'products.ingredients.translations' => $translationScope,
                            'products.ingredients.allergens.translations' => $translationScope,
                            'products.directAllergens.translations' => $translationScope
                    ]);
                }

            ])

            ->orderBy('updated_at','desc')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | FIXED MENU LOGIC
        |--------------------------------------------------------------------------
        */

        foreach ($categories as $category) {

            foreach ($category->menus as $menu) {

                if($menu->fixed_menu == 2){

                    $choices = [];

                    foreach ($menu->products as $item) {

                        $label = $item->pivot->label;

                        if(!isset($choices[$label])){

                            $choices[$label] = [
                                'label' => $label,
                                'name' => $label,
                                'open' => false,
                                'products' => []
                            ];

                        }

                        $choices[$label]['products'][] = [
                            'selected' => false,
                            'product' => $item,
                            'extra_price' => $item->pivot->extra_price
                        ];
                    }

                    $menu->fixed_menu = array_values($choices);
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | PRODOTTI IN EVIDENZA
        |--------------------------------------------------------------------------
        */

        $featuredProducts = Product::query()

            ->where('promotion',1)

            ->where(function($q) use ($from){
                if($from !== 'menu'){
                    $q->where('visible',true);
                }
            })

            ->where('archived',0)

            ->orderBy('updated_at','desc')

            ->with([
                'translations' => $translationScope,
                'ingredients.translations' => $translationScope,
                'ingredients.allergens.translations' => $translationScope,
                'directAllergens.translations' => $translationScope
            ])

            ->get();


        $featuredMenus = Menu::query()

            ->where('promo',1)
            ->where('fixed_menu','!=',0)

            ->where(function($q) use ($from){
                if($from !== 'menu'){
                    $q->where('visible',true);
                }
            })

            ->orderBy('updated_at','desc')

            ->with([
                'translations' => $translationScope,
                'products.translations' => $translationScope,
                'products.ingredients.translations' => $translationScope,
                'products.ingredients.allergens.translations' => $translationScope
            ])

            ->get();


        foreach ($featuredMenus as $menu) {

            if($menu->fixed_menu == 2){

                $choices = [];

                foreach ($menu->products as $item) {

                    $label = $item->pivot->label;

                    if(!isset($choices[$label])){

                        $choices[$label] = [
                            'label' => $label,
                            'name' => $label,
                            'open' => false,
                            'products' => []
                        ];
                    }

                    $choices[$label]['products'][] = [
                        'selected' => false,
                        'product' => $item,
                        'extra_price' => $item->pivot->extra_price
                    ];
                }

                $menu->fixed_menu = array_values($choices);
            }
        }

        $featuredCategory = null;

        $categories->each(function ($category) use ($hideCategoryTranslations) {
            $hideCategoryTranslations($category);
        });

        $featuredProducts->each(function ($product) use ($hideProductTranslations) {
            $hideProductTranslations($product);
        });

        $featuredMenus->each(function ($menu) use ($hideMenuTranslations) {
            $hideMenuTranslations($menu);
        });

        if($featuredProducts->count() || $featuredMenus->count()){

            $featuredCategory = (object)[
                'id' => 'featured',
                'name' => 'Prodotti in evidenza',
                'products' => $featuredProducts,
                'menus' => $featuredMenus,
                'featured' => true
            ];
        }

        if($featuredCategory){
            $categories->prepend($featuredCategory);
        }

        return response()->json([
            'success' => true,
            'categories' => $categories->values(),
            'allergens' => config('configurazione.allergens'),
            'lang' => $lang
        ]);
    }
    public function menuFissi(){

        $query =  Menu::where('fixed_menu',  '0')->where('visible', 1);
        $menu = $query->with('products.ingredients', 'category', 'products.category')->orderBy('updated_at', 'desc')->get();
        return response()->json([
            'success'   => true,
            'results'   => $menu,
            'allergens'   => config('configurazione.allergens'),
        ]);
    }
    public function promoHome()
    {
        
        $products = Product::with('category', 'ingredients')->where('promotion', 1)->orderBy('updated_at')->get();
        
        return response()->json([
            'success'   => true,
            'results'   => $products,
        ]);
    }
}
