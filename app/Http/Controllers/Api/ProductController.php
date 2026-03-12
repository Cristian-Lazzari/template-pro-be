<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // public function index(Request $request)
    // {
    //     $query = Product::with('category', 'ingredients', 'ingredients.allergens', 'directAllergens',);
    //     $combo_query = Menu::where('fixed_menu', '!=', '0');

    //     $from = $request->query('from');

    //     if($from !== 'menu'){
    //         $query = $query->where('visible', true);
    //         $combo_query = $combo_query->where('visible', true);
    //     }


    //     $combo = $combo_query->with('products.ingredients', 'category', 'products.category')->orderBy('updated_at', 'desc')->get();
    //     foreach ($combo as $c) {
    //         if($c->fixed_menu == '2'){
    //             $choices = [];
    //             foreach ($c->products as $item) {
    //                 $label = $item->pivot->label;
    //                 $f_choice = [
    //                     'label' => $label,
    //                     'name' => $label,
    //                     'open' => false,
    //                     'products' => []
    //                 ];
    //                 $product = Product::where('id', $item->id)->with('ingredients', 'ingredients.allergens')->first();
    //                 $f_prod = [
    //                     'selected' => false,
    //                     'product' => $product ? $product : null,
    //                     'extra_price' => $item->pivot->extra_price
    //                 ];
    //                 $check = false;
    //                 if(count($choices)){
    //                     for ($i=0; $i < count($choices); $i++) { 
    //                         if ($choices[$i]['label'] == $label) {
    //                             $check = $i;
    //                         }
    //                     }
    //                     if ($check === false) {
    //                         array_push($f_choice['products'], $f_prod);
    //                         array_push($choices, $f_choice);
    //                     }else{
    //                         array_push($choices[$check]['products'], $f_prod);
    //                     }
    //                 }else{
    //                     array_push($f_choice['products'], $f_prod);       
    //                     array_push($choices, $f_choice);
    //                 }
                   
    //             }
    //             $c->fixed_menu = $choices;
    //         }
           
    //     }
        
    //     $products = $query->where('archived', 0)->orderBy('created_at', 'asc')->get();
        
    //     return response()->json([
    //         'success'   => true,
    //         'results'   => $products,
    //         'menus'   => $combo,
    //         'allergens'   => config('configurazione.allergens'),
    //     ]);
    // }
public function index(Request $request)
{
    $lang = $request->query('lang', config('app.locale'));
    $from = $request->query('from');

    $categories = Category::with([

        'translation' => fn($q) => $q->where('lang',$lang),

        /*
        |--------------------------------------------------------------------------
        | PRODUCTS
        |--------------------------------------------------------------------------
        */

        'products' => function ($q) use ($from,$lang){

            if($from !== 'menu'){
                $q->where('visible',true);
            }

            $q->where('archived',0)
              ->orderBy('updated_at','desc')
              ->with([
                    'translation' => fn($q)=>$q->where('lang',$lang),
                    'ingredients.translation' => fn($q)=>$q->where('lang',$lang),
                    'ingredients.allergens.translation' => fn($q)=>$q->where('lang',$lang),
                    'directAllergens.translation' => fn($q)=>$q->where('lang',$lang),
              ]);
        },

        /*
        |--------------------------------------------------------------------------
        | MENUS (fixed_menu != 0)
        |--------------------------------------------------------------------------
        */

        'menus' => function ($q) use ($from,$lang){

            if($from !== 'menu'){
                $q->where('visible',true);
            }

            $q->where('fixed_menu','!=',0)
              ->orderBy('updated_at','desc')
              ->with([
                    'translation' => fn($q)=>$q->where('lang',$lang),
                    'products.ingredients.allergens',
                    'products.translation' => fn($q)=>$q->where('lang',$lang),
                    'products.ingredients.translation' => fn($q)=>$q->where('lang',$lang),
                    'products.ingredients.allergens.translation' => fn($q)=>$q->where('lang',$lang),
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

                    $product = [
                        'selected' => false,
                        'product' => $item,
                        'extra_price' => $item->pivot->extra_price
                    ];

                    if(!isset($choices[$label])){

                        $choices[$label] = [
                            'label' => $label,
                            'name' => $label,
                            'open' => false,
                            'products' => []
                        ];

                    }

                    $choices[$label]['products'][] = $product;

                }

                $menu->fixed_menu = array_values($choices);

            }

        }

    }


    return response()->json([
        'success' => true,
        'categories' => $categories,
        'allergens' => config('configurazione.allergens'),
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
