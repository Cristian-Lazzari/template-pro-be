<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Menu;
use App\Models\MenuProduct;
use App\Models\MenuProductTranslation;
use App\Models\MenuTranslation;
use App\Models\Setting;
use App\Services\GoogleTranslateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        $fix   = Menu::where('fixed_menu', '0')->with('products', 'category')->orderBy('updated_at', 'desc')->get();
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
        $menu->promo         = $data['promo'] ?? 0;

        $translator = app(GoogleTranslateService::class);

        $languages_set = json_decode(Setting::where('name', 'Lingua')->first()->property, 1);
        $languages = $languages_set['languages'];
        $default = $languages_set['default'];
        
        if($data['radio_choice'] == 2){
            $menu->fixed_menu = 2;
            $menu->save();
            foreach ($data['choice'] as $c) {
                foreach ($c['products'] as $prod) {       
                    if($prod['extra_price'] == null){
                        $prod['extra_price'] = 0;
                    }
                    $p_stringa = preg_replace('/[^0-9.]/', '', str_replace(',', '.', $prod['extra_price'] ? $prod['extra_price'] : 0));
                    $p = intval(round(floatval($p_stringa) * 100));
                    $f_product = [
                        'id' => $prod['id'],
                        'label' => $c['label'],
                        'extra_price' => $p
                    ];     
                    $pivot = MenuProduct::create([
                        'menu_id' => $$menu->id,
                        'product_id' => $f_product['id'],
                        'extra_price' => $f_product['extra_price'] ?? null
                    ]);
                    foreach ($languages as $lang) {
                        if ($lang === $default) {
                            $name = $f_product['label'];
                        } else {
                            $name = $translator->translate($f_product['label'], $lang);
                        }
                        MenuProductTranslation::create([
                            'menu_product_id' => $pivot->id,
                            'lang' => $lang,
                            'label' => $name
                        ]);
                    }
                } 
            } 
            
        }else{
            //dd($data['radio_choice']);
            $request->validate([ 'products' => 'required',]);
            $menu->fixed_menu = $data['radio_choice'];
            $menu->save();
            $product = [];
            
            foreach ($data['products'] as $v) {
                array_push($product, $v);
            }
            $menu->products()->sync($product ?? []);  
        }


        

        foreach ($languages as $lang) {
            if ($lang === $default) {
                $name = $data['name'];
                $description = $data['description'];
            } else {
                $name = $translator->translate($data['name'], $lang);
                $description = $translator->translate($data['description'], $lang);
            }
            MenuTranslation::create([
                'menu_id' => $menu->id,
                'lang' => $lang,
                'name' => $name,
                'description' => $description
            ]);
        }

        $m = ' "' . $data['name'] . '" è stato creato correttamente';
        return to_route('admin.menus.index')->with('success', $m);    
    }

    public function edit($id)
    {
        $menu = Menu::findOrFail($id)->load('translations');
        $translations   = $menu->translations->keyBy('lang');
        $categories = Category::all();
        $products = Category::where('id', '!=', 1)->with('product')->get();
        $languages    = json_decode(Setting::where('name', 'Lingua')->first()->property, 1);

        return view('admin.Menus.edit', compact('categories', 'products', 'menu', 'translations', 'languages'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        $request->validate($this->validation_edit);      
        
        $menu = Menu::findOrFail($id);

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
        $menu->promo         = $data['promo'] ??  0;
        $menu->description   = $data['description'];
        
        $menu->update();

    /*  | TRADUZIONI PERSONALIZZATE */
        $lang_s = json_decode(Setting::where('name', 'Lingua')->first()->property, 1);
        $default_l = $lang_s['default'];


        $n_trans = $menu->name !== $data['name'];
        $d_trans = $menu->description !== $data['description'];

        $translator = app(GoogleTranslateService::class);

        MenuTranslation::updateOrCreate(
            [   'menu_id' => $menu->id, 'lang' => $default_l   ],
            [
                'name' => $data['name'] ?? null,
                'description' => $data['description'] ?? null
            ]
        );
        if(isset($data['translations'])){
            foreach($data['translations'] as $lang => $v){
                MenuTranslation::updateOrCreate(
                    [   'menu_id' => $menu->id, 'lang' => $lang   ],
                    [
                        'name' => $n_trans ? $translator->translate($data['name'], $lang) : $v['name'],
                        'description' => $n_trans ? $translator->translate($data['description'], $lang) : $v['description'],
                    ]
                );
                
            }
        }       
        $m = ' "' . $data['name'] . '" è stato modificato correttamente';
        return to_route('admin.menus.index')->with('success', $m);    
    }


    public function destroy($id)
    {
        $menu = Menu::findOrFail($id);
        $menu->products()->detach();
        $menu->orders()->detach();
        $menu->delete();
        return to_route('admin.menus.index')->with('delete_success', $menu);
    }
}
