<?php

namespace App\Http\Controllers\Admin;

use App\Models\Menu;
use App\Models\Product;
use App\Models\Category;
use App\Models\MenuProduct;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MenuController extends Controller
{
    private $validation = [
        'name'          => 'required|string|min:1|max:150|unique:menus,name',
        'price'         => 'required',
        'image'         => 'nullable|image',
    ];
    private $validation_edit = [
        'name'          => 'required|string|min:1|max:150',
        'price'         => 'required',
        'image'         => 'nullable|image',
    ];
    
    public function index()
    {
        $fix = Menu::where('fixed_menu', '0')->with('products', 'category')->orderBy('updated_at', 'desc')->get();
        $combo = Menu::where('fixed_menu', '!=', '0')->with('products', 'category')->orderBy('updated_at', 'desc')->get();
        foreach ($combo as $c) {
            if($c->fixed_menu == '2'){
                $choices = [];
                foreach ($c->products as $item) {
                    $label = $item->pivot->label;
                    if (!isset($choices[$label])) {
                        $choices[$label] = [];
                    }
                    $choices[$label][] = $item;
                }
                $c->fixed_menu = $choices;
            }
        }
        return view('admin.Menus.index', compact('fix', 'combo'));
    }


    public function create()
    {
        $categories = Category::all();
        $products = Category::where('id', '!=', 1)->with('product')->get();
        return view('admin.Menus.create', compact('categories', 'products'));
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $data = $request->all();
        $request->validate($this->validation);     
        
        //ci arriva choice 
        $choices = [];
        $menu = new Menu();
        if (isset($data['image'])) {
            $imagePath = Storage::put('public/uploads', $data['image']);
            $menu->image = $imagePath;
        } 
        $prezzo_stringa = str_replace(',', '.', $data['price']);
        $prezzo_stringa = preg_replace('/[^0-9.]/', '', $prezzo_stringa);
        $prezzo_float = floatval($prezzo_stringa);
        $menu->price         = intval(round($prezzo_float * 100));  
        if($data['old_price'] == null){
            $data['old_price'] = 0;
        }
        $prezzo_stringa = str_replace(',', '.', $data['old_price']);
        $prezzo_stringa = preg_replace('/[^0-9.]/', '', $prezzo_stringa);
        $prezzo_float = floatval($prezzo_stringa);
        $menu->old_price         = intval(round($prezzo_float * 100));  

        $menu->category_id   = $data['category_id'];
        $menu->name          = $data['name'];
        $menu->description   = $data['description'];
        
        if($data['radio_choice'] == 2){
            $menu->fixed_menu = 2;
            $menu->save();
            foreach ($data['choice'] as $c) {
                foreach ($c['products'] as $prod) {       
                    if($prod['extra_price'] == null){
                        $prod['extra_price'] = 0;
                    }
                    $p_stringa = str_replace(',', '.', $prod['extra_price'] ? $prod['extra_price'] : 0);
                    $p_stringa = preg_replace('/[^0-9.]/', '', $p_stringa);
                    $p = intval(round(floatval($p_stringa) * 100));
                    $f_product = [
                        'id' => $prod['id'],
                        'label' => $c['label'],
                        'extra_price' => $p
                    ];     

                    $menu_prod = new MenuProduct();
                    $menu_prod->menu_id = $menu->id;
                    $menu_prod->product_id = $f_product['id'];
                    $menu_prod->extra_price = $f_product['extra_price'];
                    $menu_prod->label = $f_product['label'];
                    $menu_prod->save();
                } 
            } 
            
        }else{
            //dd($data['radio_choice']);
            $request->validate([ 'products' => 'required',]);
            $menu->fixed_menu = $data['radio_choice'];
            $product = [];
            
            foreach ($data['products'] as $v) {
                array_push($product, $v);
            }
            $menu->products()->sync($product ?? []);  
        }
        
        
        //dd('top');
       

        $m = ' "' . $menu['name'] . '" è stato creato correttamente';
        return to_route('admin.menus.index')->with('store_success', $m);    
    }

    public function edit($id)
    {
        return view('admin.Menus.create');
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        $request->validate($this->validation_edit);      
        
        $menu = new Menu();

        if (isset($data['image'])) {
            $imagePath = Storage::put('public/uploads', $data['image']);
            if ($menu->image) {
                Storage::delete($menu->image);
            }
            $menu->image = $imagePath;
        }
        $prezzo_stringa = str_replace(',', '.', $data['price']); $prezzo_stringa = preg_replace('/[^0-9.]/', '', $prezzo_stringa); $prezzo_float = floatval($prezzo_stringa);
        $menu->price         = intval(round($prezzo_float * 100));  
        $menu->category_id   = $data['category_id'];
        $menu->name          = $data['name'];
        $menu->description   = $data['description'];
        $menu->fixed_menu    = isset($data['fixed_menu']) ? true : false;
        
        $menu->update();
        $m = ' "' . $menu['name'] . '" è stato modificato correttamente';
        return to_route('admin.Menus.index')->with('store_success', $m);    
    }


    public function destroy($id)
    {
        $menu->delete();
        return to_route('admin.Menus.index')->with('delete_success', $menu);
    }
}
