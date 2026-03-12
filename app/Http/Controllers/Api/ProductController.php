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

    /*
    |--------------------------------------------------------------------------
    | IMPOSTAZIONE LINGUA REQUEST-SCOPED
    |--------------------------------------------------------------------------
    |
    | Se i tuoi accessor leggono app()->getLocale() o config('app.locale'),
    | così dovrebbe bastare.
    | Se invece il tuo service usa una chiave custom tipo config('translatable.lang')
    | o simili, qui devi settare QUELLA chiave.
    |
    */

    app()->setLocale($lang);
    \Illuminate\Support\Facades\App::setLocale($lang);
    config(['app.locale' => $lang]);

    /*
    |--------------------------------------------------------------------------
    | SCOPE TRADUZIONI
    |--------------------------------------------------------------------------
    */

    $translationScope = function ($q) use ($lang, $defaultLang) {
        $q->whereIn('lang', [$lang, $defaultLang])
          ->orderByRaw("CASE WHEN lang = ? THEN 0 ELSE 1 END", [$lang]);
    };

    /*
    |--------------------------------------------------------------------------
    | FILTRI PRODOTTI E MENU
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

        $q->where('fixed_menu', '!=', 0);
    };

    /*
    |--------------------------------------------------------------------------
    | CATEGORIE NORMALI
    |--------------------------------------------------------------------------
    |
    | Mostra solo categorie che hanno almeno 1 prodotto valido.
    |
    */

    $categories = Category::query()
        ->whereHas('product', $productFilter)
        ->with([
            'translations' => $translationScope,

            'product' => function ($q) use ($productFilter, $translationScope) {
                $productFilter($q);

                $q->orderBy('updated_at', 'desc')
                  ->with([
                      'translations' => $translationScope,
                      'ingredients.translations' => $translationScope,
                      'ingredients.allergens.translations' => $translationScope,
                      'directAllergens.translations' => $translationScope,
                  ]);
            },

            'menu' => function ($q) use ($menuFilter, $translationScope) {
                $menuFilter($q);

                $q->orderBy('updated_at', 'desc')
                  ->with([
                      'translations' => $translationScope,
                      'products.translations' => $translationScope,
                      'products.ingredients.translations' => $translationScope,
                      'products.ingredients.allergens.translations' => $translationScope,
                      'products.directAllergens.translations' => $translationScope,
                  ]);
            },
        ])
        ->orderBy('updated_at', 'desc')
        ->get();

    /*
    |--------------------------------------------------------------------------
    | LOGICA FIXED MENU == 2
    |--------------------------------------------------------------------------
    */

    foreach ($categories as $category) {
        foreach ($category->menu as $menu) {
            if ((string) $menu->fixed_menu === '2') {
                $choices = [];

                foreach ($menu->products as $item) {
                    $label = $item->pivot->label;

                    if (!isset($choices[$label])) {
                        $choices[$label] = [
                            'label' => $label,
                            'name' => $label,
                            'open' => false,
                            'products' => [],
                        ];
                    }

                    $choices[$label]['products'][] = [
                        'selected' => false,
                        'product' => $item,
                        'extra_price' => $item->pivot->extra_price,
                    ];
                }

                $menu->fixed_menu = array_values($choices);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CATEGORIA "PRODOTTI IN EVIDENZA"
    |--------------------------------------------------------------------------
    */

    $featuredProducts = Product::query()
        ->where('promotion', 1)
        ->where(function ($q) use ($from) {
            if ($from !== 'menu') {
                $q->where('visible', true);
            }
        })
        ->where('archived', 0)
        ->orderBy('updated_at', 'desc')
        ->with([
            'translations' => $translationScope,
            'ingredients.translations' => $translationScope,
            'ingredients.allergens.translations' => $translationScope,
            'directAllergens.translations' => $translationScope,
            'category.translations' => $translationScope,
        ])
        ->get();

    $featuredMenus = Menu::query()
        ->where('promo', 1)
        ->where('fixed_menu', '!=', 0)
        ->where(function ($q) use ($from) {
            if ($from !== 'menu') {
                $q->where('visible', true);
            }
        })
        ->orderBy('updated_at', 'desc')
        ->with([
            'translations' => $translationScope,
            'products.translations' => $translationScope,
            'products.ingredients.translations' => $translationScope,
            'products.ingredients.allergens.translations' => $translationScope,
            'products.directAllergens.translations' => $translationScope,
            'category.translations' => $translationScope,
        ])
        ->get();

    foreach ($featuredMenus as $menu) {
        if ((string) $menu->fixed_menu === '2') {
            $choices = [];

            foreach ($menu->products as $item) {
                $label = $item->pivot->label;

                if (!isset($choices[$label])) {
                    $choices[$label] = [
                        'label' => $label,
                        'name' => $label,
                        'open' => false,
                        'products' => [],
                    ];
                }

                $choices[$label]['products'][] = [
                    'selected' => false,
                    'product' => $item,
                    'extra_price' => $item->pivot->extra_price,
                ];
            }

            $menu->fixed_menu = array_values($choices);
        }
    }

    $featuredCategory = null;

    if ($featuredProducts->count() || $featuredMenus->count()) {
        $featuredCategory = (object) [
            'id' => 'featured',
            'name' => 'Prodotti in evidenza',
            'slug' => 'prodotti-in-evidenza',
            'product' => $featuredProducts,
            'menu' => $featuredMenus,
            'featured' => true,
        ];
    }

    if ($featuredCategory) {
        $categories->prepend($featuredCategory);
    }

    return response()->json([
        'success' => true,
        'categories' => $categories->values(),
        'allergens' => config('configurazione.allergens'),
        'lang' => $lang,
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
