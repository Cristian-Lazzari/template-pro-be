<?php

namespace App\Http\Controllers\Api;

use App\Models\Menu;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    public function index(Request $request)
    {

        $categoryId = $request->query('category');
        $query = Product::with('category', 'ingredients');
        $combo_query = Menu::where('fixed_menu', '!=', '0');

        $from = $request->query('from');

        if($from !== 'menu'){
            $query = $query->where('visible', 1);
            $combo_query = $combo_query->where('visible', 1);
        }

        if ($categoryId !== null && $categoryId !== 0 && $categoryId !== '0') {
            $query = $query->where('category_id', $categoryId);
            $combo_query = $combo_query->where('category_id', $categoryId);
        } 
        $combo = $combo_query->with('products.ingredients', 'category', 'products.category')->orderBy('updated_at', 'desc')->get();
        foreach ($combo as $c) {
            if($c->fixed_menu == '2'){
                $choices = [];
                foreach ($c->products as $item) {
                    $label = $item->pivot->label;
                    $f_choice = [
                        'label' => $label,
                        'name' => $label,
                        'open' => false,
                        'products' => []
                    ];
                    $product = Product::where('id', $item->id)->first();
                    $f_prod = [
                        'selcted' => false,
                        'product' => $product ? $product : null,
                        'extra_price' => $item->pivot->extra_price
                    ];
                    $check = false;
                    if(count($choices)){
                        for ($i=0; $i < count($choices); $i++) { 
                            if ($choices[$i]['label'] == $label) {
                                $check = true;
                            }
                        }
                        if (!$check) {
                            array_push($f_choice['products'], $f_prod);
                            array_push($choices, $f_choice);
                        }else{
                            array_push($choices[count($choices) - 1]['products'], $f_prod);
                        }
                    }else{
                        array_push($f_choice['products'], $f_prod);       
                        array_push($choices, $f_choice);
                    }
                   
                }
                $c->fixed_menu = $choices;
            }
            // if($c->fixed_menu !== '1'){
            //     $fixed_menu = json_decode($c->fixed_menu, 1);
            //     $f_choices = [];
            //     foreach ($fixed_menu as $choice) {
                    
            //         foreach ($choice['products'] as $p) {
                        
                        
            //             array_push($f_choice['products'], $f_prod);
            //         }
            //         array_push($f_choices, $f_choice);
            //     }
            //     $c->fixed_menu = $f_choices;
            // }
        }
        
        
        $products = $query->where('archived', 0)->orderBy('created_at', 'asc')->get();
        
        return response()->json([
            'success'   => true,
            'results'   => $products,
            'menus'   => $combo,
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
