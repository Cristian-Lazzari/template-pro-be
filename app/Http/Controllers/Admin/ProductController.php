<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Allergen;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\IngredientTranslation;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Models\Setting;
use App\Services\GoogleTranslateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

  //se impostato a true gli ordini vengono presi in base ai pezzi altrimenti in base al numero di ordini
class ProductController extends Controller
{

    

    private $validationsFalse = [
        'name'          => 'required|string|min:1|max:100|unique:product_translations,name',
        'image'         => 'nullable|image|max:1024',
    ];

    private $validationsFalse1 = [
        'name'          => 'required|string|min:1|max:100',
        'image'         => 'nullable|image|max:1024',
    ];
    
    
    private $validations_ingredient = [
        'name_ing'          => 'required|string|min:2|max:100|unique:ingredient_translations,name',
        'price_ing'         => 'required',
        'image_ing'         => 'nullable|image',
    ];

   
    
    public function status(Request $request){
        $a = $request->a ;
        $v = $request->v ;
        $archive = $request->archive ;
        $categories = Category::all();
        ///se uguale a 1 archivio il prodotto
        if($a == 1){
            $p = Product::where('id', $request['id'])->firstOrFail();
            $p->archived = !$p->archived;
            $p->update();
            if ($p->archived) {
                $m = '"' . $p->name . '" e\' stato archiviato correttamente';
            } else{
                $m = '"' . $p->name . '" e\' stato ripristinato correttamente';
            }
            if ($archive == 1) {
                $products = Product::where('archived', true)->get();
                return to_route('admin.products.archived', compact('products', 'categories'))->with('success', $m);
            } else{
                $products = Product::where('archived', false)->get();
                return to_route('admin.products.index', compact('products', 'categories'))->with('success', $m);
            }
            
            
        } 
        if($v == 1){
            $p = Product::where('id', $request['id'])->firstOrFail();
            $p->visible = !$p->visible;
            $p->update();
            if ($p->visible) {
                $m = '"' . $p->name . '" e\' visibile ai tuoi clienti ';
            } else{
                $m = '"' . $p->name . '" non e\' visibile ai tuoi clienti';
            }
            if ($archive == 1) {
                $products = Product::where('archived', true)->get();
                return to_route('admin.products.archived', compact('products', 'categories'))->with('success', $m);
            } else{
                $products = Product::where('archived', false)->get();
                return to_route('admin.products.index', compact('products', 'categories'))->with('success', $m);
            }
        } 
    }

    public function archived(Request $request){
        $products   = Product::where('archived', true)->get();
        
        return view('admin.Products.archived', compact('products'));
    }


    public function index()
    {
        $products    = Product::where('archived', false)->orderBy('updated_at', 'desc')->get();
        return view('admin.Products.index', compact('products'));
    }

    public function create()
    {
        $categories     = Category::all();
        $allergens      = Allergen::all();
        $lang = config('configurazione.default_lang');
        $ingredients = Ingredient::query()
            ->where('option', false)
            ->join('ingredient_translations as t', function ($join) use ($lang) {
                $join->on('ingredients.id', '=', 't.ingredient_id')
                    ->where('t.lang', $lang);
            })
            ->orderBy('t.name')
            ->select('ingredients.*')
            ->get(); 
        $adv_s = Setting::where('name', 'advanced')->first();
        $property_adv = json_decode($adv_s->property, 1);
        
        return view('admin.Products.create', compact('categories', 'ingredients', 'property_adv', 'allergens'));
    }

    public function store(Request $request){   
        // Recupera le configurazioni
        $adv_s = Setting::where('name', 'advanced')->first();
        $property_adv = json_decode($adv_s->property, 1);  
            
        $double = $property_adv['dt'];
        $pack = $property_adv['services'];

        $data = $request->all();
     
        if (isset($data['newi'])){
            $request->validate($this->validations_ingredient);
            
            $newi = $data['newi'];
            $ingredient_allergens = $data['allergens_ing'] ?? [];
            $type_ing = $data['type_ing'] ?? [];

            $price = (float) str_replace(',', '.', $data['price_ing']);

            $new_ing = new Ingredient();
            $new_ing->option = 0;
            $new_ing->price = $price * 100;
            $new_ing->type = json_encode($type_ing);

            if (!empty($data['image_ing'])) {
                $new_ing->icon = Storage::put('public/uploads', $data['image_ing']);
            }

            $new_ing->save();

            $new_ing->allergens()->sync($ingredient_allergens);

            $data['ingredients'] = $data['ingredients'] ?? [];
            $data['ingredients'][] = $new_ing->id;

            $translator = app(GoogleTranslateService::class);

            $languages_set = json_decode(Setting::where('name', 'Lingua')->first()->property, 1);
            $languages = $languages_set['languages'];
            $default = $languages_set['default'];

            foreach ($languages as $lang) {
                if ($lang === $default) {
                    $name = $data['name_ing'];
                } else {
                    $name = $translator->translate($data['name_ing'], $lang);
                }
                IngredientTranslation::create([
                    'ingredient_id' => $new_ing->id,
                    'lang' => $lang,
                    'name' => $name,
                ]);
            }

            unset( $data['image_ing']);
            return to_route('admin.products.create')->with('ingredient_success', $data);     
        }
  
        $request->validate($this->validationsFalse);
        
        $product = new Product();
        
        $price = (float) str_replace(',', '.', $data['price']);

        if (isset($data['image'])) {
            $imagePath = Storage::put('public/uploads', $data['image']);
            $product->image = $imagePath;
        } 
        $product->category_id   = $data['category_id'];

        $product->price         = $price * 100;       


        $product->promotion   = isset($data['promotion']) ? true : false;
        $product->old_price   = isset($data['old_price']) ? (float) str_replace(',', '.', $data['old_price']) * 100 : null;
        
        if($pack > 2){
            $product->tag_set       = $data['tag_set'];
        }
    
        $product->save();
        
        $ingredients = [];
        if(isset($data['ingredients'])){     
            foreach ($data['ingredients'] as $v) {
                array_push($ingredients, $v);
            }
            $product->ingredients()->sync($ingredients ?? []);  
        }
        // controllo se l utente ha inserito gli allergens 
        if (isset($data['allergens'])){
            $allergens = $data['allergens'];
            $allergens = array_map('intval', array_values($data['allergens']));
            $product->directAllergens()->sync($allergens);
        }
        $products = Product::all();

        $translator = app(GoogleTranslateService::class);

        $languages_set = json_decode(Setting::where('name', 'Lingua')->first()->property, 1);
        $languages = $languages_set['languages'];
        $default = $languages_set['default'];

        foreach ($languages as $lang) {
            if ($lang === $default) {
                $name = $data['name'];
                $description = $data['description'];
            } else {
                $name = $translator->translate($data['name'], $lang);
                $description = $translator->translate($data['description'], $lang);
            }
            ProductTranslation::create([
                'product_id' => $product->id,
                'lang' => $lang,
                'name' => $name,
                'description' => $description
            ]);
        }

        return to_route('admin.products.index', compact('products'))->with('success', 'Prodotto "' . $product->name . '" creato correttamente');
    }
    
    
    public function show($id)
    {
        $product = Product::where('id', $id)->firstOrFail();
        $adv_s = Setting::where('name', 'advanced')->first();
        $property_adv = json_decode($adv_s->property, 1);
        return view('admin.Products.show', compact( 'product', 'property_adv'));      
    }
    
    public function edit($id)
    {
        $product        = Product::where('id', $id)->firstOrFail()->load('translations');
        $translations   = $product->translations->keyBy('lang');
        $categories     = Category::all();
        $allergens      = Allergen::all();
        $lang = config('configurazione.default_lang');
        $ingredients = Ingredient::query()
            ->where('option', false)
            ->join('ingredient_translations as t', function ($join) use ($lang) {
                $join->on('ingredients.id', '=', 't.ingredient_id')
                    ->where('t.lang', $lang);
            })
            ->orderBy('t.name')
            ->select('ingredients.*')
            ->get(); 

        $languages    = json_decode(Setting::where('name', 'Lingua')->first()->property, 1);
        $property_adv = json_decode(Setting::where('name', 'advanced')->first()->property, 1);

        return view('admin.Products.edit', compact( 'product', 'categories', 'ingredients', 'property_adv', 'allergens', 'translations', 'languages'));        
    }
    
    public function update(Request $request, $id){
        // Recupera le configurazioni
        $adv_s = Setting::where('name', 'advanced')->first();
        $property_adv = json_decode($adv_s->property, 1);  
            
        $pack = $property_adv['services'];

        $product = Product::where('id', $id)->firstOrFail();
        $data = $request->all();
        if (isset($data['newi'])){
            $request->validate($this->validations_ingredient);

            $newi = $data['newi'];
            $ingredient_allergens = $data['allergens_ing'] ?? [];
            $type_ing = $data['type_ing'] ?? [];

            $price = (float) str_replace(',', '.', $data['price_ing']);

            $new_ing = new Ingredient();
            $new_ing->option = 0;
            $new_ing->price = $price * 100;
            $new_ing->type = json_encode($type_ing);

            if (!empty($data['image_ing'])) {
                $new_ing->icon = Storage::put('public/uploads', $data['image_ing']);
            }

            $new_ing->save();

            $new_ing->allergens()->sync($ingredient_allergens);

            $data['ingredients'] = $data['ingredients'] ?? [];
            $data['ingredients'][] = $new_ing->id;

            $translator = app(GoogleTranslateService::class);

            $languages_set = json_decode(Setting::where('name', 'Lingua')->first()->property, 1);
            $languages = $languages_set['languages'];
            $default = $languages_set['default'];

            foreach ($languages as $lang) {
                if ($lang === $default) {
                    $name = $data['name_ing'];
                } else {
                    $name = $translator->translate($data['name_ing'], $lang);
                }
                IngredientTranslation::create([
                    'ingredient_id' => $new_ing->id,
                    'lang' => $lang,
                    'name' => $name,
                ]);
            }
            unset( $data['image_ing']);
            return to_route('admin.products.edit', ['product' =>$product])->with('ingredient_success', $data);     
        }

        $request->validate($this->validationsFalse1);

        $price = (float) str_replace(',', '.', $data['price']);
        $oldprice = (float) str_replace(',', '.', $data['old_price']);

        if (isset($data['image'])) {
            $imagePath = Storage::put('public/uploads', $data['image']);
            if ($product->image) {
                Storage::delete($product->image);
            }
            $product->image = $imagePath;
        }elseif (isset($data['img_off'])) {
            if ($product->image) {
                Storage::delete($product->image);
            }
            $product->image = null;
        } 
        
        $product->category_id   = $data['category_id'];
        $product->price         = $price * 100;       
        $product->promotion   = isset($data['promotion']) ? true : false;
        $product->old_price   = $oldprice * 100;
        
        if($pack > 2){
            $product->tag_set       = $data['tag_set'];
        }
    
        $product->save();
        
        $ingredients = [];
        if (isset($data['ingredients']) && is_array($data['ingredients'])) {
            $syncData = [];
            foreach ($data['ingredients'] as $index => $ingredientId) {
                $syncData[$ingredientId] = ['sort_order' => $index];
            }
            $product->ingredients()->sync($syncData);
        } else {
            // Nessun ingrediente selezionato
            $product->ingredients()->sync([]);
        }
        // controllo se l utente ha inserito gli allergens 
        if (isset($data['allergens'])){
            $allergens = $data['allergens'];
            $allergens = array_map('intval', array_values($data['allergens']));
            $product->directAllergens()->sync($allergens);
        }


        /*  | TRADUZIONI PERSONALIZZATE */
        $lang_s = json_decode(Setting::where('name', 'Lingua')->first()->property, 1);
        $default_l = $lang_s['default'];


        $n_trans = $product->name !== $data['name'];
        $d_trans = $product->description !== $data['description'];

        $translator = app(GoogleTranslateService::class);

        ProductTranslation::updateOrCreate(
            [   'product_id' => $product->id, 'lang' => $default_l   ],
            [
                'name' => $data['name'] ?? null,
                'description' => $data['description'] ?? null
            ]
        );
        if(isset($data['translations'])){
            foreach($data['translations'] as $lang => $v){
                //dd($n_trans);
                ProductTranslation::updateOrCreate(
                    [   'product_id' => $product->id, 'lang' => $lang   ],
                    [
                        'name' => ($n_trans || blank($v['name'])) ? $translator->translate($data['name'], $lang) : $v['name'],
                        'description' => ($d_trans || blank($v['description'])) ? $translator->translate($data['description'], $lang) : $v['description'],
                    ]
                );
                
            }
        }

        $products = Product::all();        
        return to_route('admin.products.index', compact('products'))->with('success', 'Prodotto "' . $data['name'] . '" modificato correttamente');

    }

    public function destroy(Product $product)
    {     
        $product->ingredients()->detach();
        $product->orders()->detach();
        $product->forceDelete();
        return to_route('admin.products.index')->with('delete_success', $product);

    }
}
