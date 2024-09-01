<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\Category;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

  //se impostato a true gli ordini vengono presi in base ai pezzi altrimenti in base al numero di ordini
class ProductController extends Controller
{

    
    private $validationsTrue = [
        'name'          => 'required|string|min:1|max:50|unique:products,name',
        'image'         => 'nullable|image',
        'price'         => 'required',
        'slot_plate'    => 'required',
    ];

    private $validationsFalse = [
        'name'          => 'required|string|min:1|max:50|unique:products,name',
        'image'         => 'nullable|image',
        'price'         => 'required',
    ];


    private $validationsTrue1 = [
        'name'          => 'required|string|min:1|max:50',
        'image'         => 'nullable|image',
        'price'         => 'required',
        'slot_plate'    => 'required',
    ];

    private $validationsFalse1 = [
        'name'          => 'required|string|min:1|max:50',
        'image'         => 'nullable|image',
        'price'         => 'required',
    ];



    private $validations_ingredient = [
        'name_ing'          => 'required|string|min:2|unique:ingredients,name',
        'price_ing'         => 'required',
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
        $categories = Category::all();
        
        return view('admin.products.archived', compact('products', 'categories'));
    }

    public function filter(Request $request){
        
        // FUNZIONE DI FILTRAGGIO INDEX
        $categories = Category::all();
        $archive = $request->input('archive');
        $visible = $request->input('visible');
        $name = $request->input('name');
        $order = $request->input('order');
        $style = $request->input('style');
        $category_id = $request->input('category_id');
        $filters = [
            'name'          => $name ,
            'visible'       => $visible ,
            'category_id'   => $category_id ,
            'order'         => $order,      
            'style'         => $style      
        ];
        
        $query = Product::query();
        
        if ($archive == 1) {
            $query->where('archived', true);
        }
        if ($name) {
            $query->where('name', 'like', '%' . $name . '%');
        } 
        if ($visible == 1) {
            $query->where('visible', '=', 1);
        } else if ($visible == 2) {
            $query->where('visible', '=', 0);
        }
        if($category_id){
            $query->where('category_id', $category_id);
        }
        if($order){
            $products = $query->orderBy('name')->get();
        }else{
            $products = $query->orderBy('updated_at', 'desc')->get();    
        }        
        if ($archive == 1) {

            return view('admin.products.archived', compact('products', 'categories', 'filters'));
        }

        return view('admin.products.index', compact('products', 'categories', 'filters'));

    }

    public function index()
    {
        
        $products    = Product::where('archived', false)->get();
        $categories  = Category::all();
        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories     = Category::all();
        $ingredients    = Ingredient::where('option', false)->orderBy('name')->get();  
        
        return view('admin.products.create', compact('categories', 'ingredients'));
    }

    public function store(Request $request)
    {   
        //funzione del cazzo di chat per controllare la questione glutine e senza glutine
        function cleanArray($array) {
            $hasGluten = false;
            $hasNoGluten = false;
            foreach ($array as $item) {     
                if ($item == 1) {
                    $hasGluten = true;
                } elseif ($item == 4) {
                    $hasNoGluten = true;
                }     
            } 
            if ($hasGluten && $hasNoGluten) {
                $filteredArray = array_filter($array, function($value) {
                    return $value !== 1;
                });
            }else{
                $filteredArray = $array;
            }  
            return array_values($filteredArray); // re-indicizzare l'array
        }
        //end---funzione del cazzo di chat per controllare la questione glutine e senza glutine
        $data = $request->all();
     
        if (isset($data['newi'])){
            $newi = $data['newi'];
            $request->validate($this->validations_ingredient);

            $prezzo_stringa = str_replace(',', '.', $data['price_ing']);
            $prezzo_stringa = preg_replace('/[^0-9.]/', '', $prezzo_stringa);
            $prezzo_float = floatval($prezzo_stringa);

            if (isset($data['allergens_ing'])){
                $ingredient_allergens = $data['allergens_ing'];
            }else{
                $ingredient_allergens = '[]';
            }
            if (isset($data['type_ing'])){
                $type_ing = $data['type_ing'];
            }else{
                $type_ing = '[]';
            }
            
            $new_ing = new Ingredient();
            if (isset($data['image_ing'])) {
                $imagePath = Storage::put('public/uploads', $data['image_ing']);
                
                $new_ing->icon = $imagePath;
            }
            $new_ing->name = $data['name_ing'];
            if (isset($data['option_ing'])) {
                $new_ing->option = true;
            }else{
                $new_ing->option = false;
            }
            $new_ing->price = intval(round($prezzo_float * 100));
            $new_ing->type = json_encode($type_ing);

            if($ingredient_allergens !== '[]'){
                $rightall = array_map('intval', array_values($ingredient_allergens));
                $new_ing->allergens = json_encode($rightall);
            }else{
                $new_ing->allergens = '[]';
            }
            $new_ing->save();
            if(isset($data['ingredients'])){
                array_push($data['ingredients'], $new_ing->id);
            }else{
                $data['ingredients'] = [$new_ing->id];
            }
           unset( $data['image_ing']);
            return to_route('admin.products.create')->with('ingredient_success', $data);     
        }

        if (config('configurazione.typeOfOrdering') && config('configurazione.pack') > 2) {        
            $request->validate($this->validationsTrue);
        }else{
            $request->validate($this->validationsFalse);
        }
        
        $product = new Product();
        
        if(isset($data['ingredients'])){     
            $allergens = [];
            foreach ($data['ingredients'] as $i) {
                $all = Ingredient::where('id', $i)->firstOrFail();
                $isall = json_decode($all['allergens']);
                if($isall !== "[]" && $isall !== NULL ){
                    foreach($isall as $ia){
                        array_push($allergens, $ia);
                    }  
                }
            }

            if (count($allergens) > 0) {
                $alldclen = array_unique($allergens);
                $rightall = array_map('intval', array_values($alldclen));   
                $cleanallergens = cleanArray($rightall);          
                $allergens = json_encode($cleanallergens);
            }else{
                $allergens = '[]';   
            }
        }else{
            $allergens = '[]';   
        }
        

       
        $prezzo_stringa = str_replace(',', '.', $data['price']);
        $prezzo_stringa = preg_replace('/[^0-9.]/', '', $prezzo_stringa);
        $prezzo_float = floatval($prezzo_stringa);

        if (isset($data['image'])) {
            $imagePath = Storage::put('public/uploads', $data['image']);
            $product->image = $imagePath;
        } 
        $product->category_id   = $data['category_id'];

        $product->name          = $data['name'];
        $product->price         = intval(round($prezzo_float * 100));       
        $product->description   = $data['description'];

        $product->allergens    = $allergens;
        
        
        
        if(config('configurazione.pack') > 2){
            $product->tag_set       = $data['tag_set'];
            if( config('configurazione.typeOfOrdering') ){
                $product->type_plate    = $data['type_plate'];     
                $product->slot_plate    = $data['slot_plate'];     
            }
        }
    
        
        $product->save();
        
        $ingredients = [];
        if(isset($data['ingredients'])){     
            foreach ($data['ingredients'] as $v) {
                array_push($ingredients, $v);
            }
            $product->ingredients()->sync($ingredients ?? []);  
        }
        
        return view('admin.products.show', compact( 'product'));
        
    }
    
    
    public function show($id)
    {
        $product = Product::where('id', $id)->firstOrFail();
        return view('admin.products.show', ['product' => $product]);      
    }
    
    public function edit($id)
    {
        $product = Product::where('id', $id)->firstOrFail();
        $categories     = Category::all();
        $ingredients    = Ingredient::where('option', false)->orderBy('name')->get();  
        
        return view('admin.products.edit', compact( 'product', 'categories', 'ingredients'));        
    }
    
    public function update(Request $request, $id){

        //funzione del cazzo di chat per controllare la questione glutine e senza glutine
        function cleanArray($array) {
            $hasGluten = false;
            $hasNoGluten = false;
            foreach ($array as $item) {     
                if ($item == 1) {
                    $hasGluten = true;
                } elseif ($item == 4) {
                    $hasNoGluten = true;
                }     
            } 
            if ($hasGluten && $hasNoGluten) {
                $filteredArray = array_filter($array, function($value) {
                    return $value !== 1;
                });
            }else{
                $filteredArray = $array;
            }  
            return array_values($filteredArray); // re-indicizzare l'array
        }
        //end---funzione del cazzo di chat per controllare la questione glutine e senza glutine


        $product = Product::where('id', $id)->firstOrFail();
        $data = $request->all();
        if (isset($data['newi'])){
            $newi = $data['newi'];
            if (isset($data['allergens_ing'])){
                $ingredient_allergens = $data['allergens_ing'];
            }else{
                $ingredient_allergens = '[]';
            }
            if (isset($data['type_ing'])){
                $type_ing = $data['type_ing'];
            }else{
                $type_ing = '[]';
            }
            $request->validate($this->validations_ingredient);
            
            $new_ing = new Ingredient();
            $new_ing->name = $data['name_ing'];
            if (isset($data['image_ing'])) {
                $imagePath = Storage::put('public/uploads', $data['image_ing']);
                
                $new_ing->icon = $imagePath;
            }
            $new_ing->option = 0;
            $new_ing->price = $data['price_ing'];
            $new_ing->type = json_encode($type_ing);

            if($ingredient_allergens !== '[]'){
                $rightall = array_map('intval', array_values($ingredient_allergens));
                $new_ing->allergens = json_encode($rightall);
            }else{
                $new_ing->allergens = '[]';
            }
            $new_ing->save();
            if(isset($data['ingredients'])){
                array_push($data['ingredients'], $new_ing->id);
            }else{
                $data['ingredients'] = [$new_ing->id];
            }
            unset( $data['image_ing']);
            return to_route('admin.products.edit', ['product' =>$product])->with('ingredient_success', $data);     
        }
        if (config('configurazione.typeOfOrdering') && config('configurazione.pack') > 2) {        
            $request->validate($this->validationsTrue1);
        }else{
            $request->validate($this->validationsFalse1);
        }    
        $allergens = [];
        if(isset($data['ingredients'])){     
            foreach ($data['ingredients'] as $i) {
                $all = Ingredient::where('id', $i)->firstOrFail();
                $isall = json_decode($all['allergens']);
                if($isall !== "[]" && $isall !== NULL ){
                    foreach($isall as $ia){
                        array_push($allergens, $ia);
                    }  
                }
            }
            if (count($allergens) > 0) {
                $alldclen = array_unique($allergens);
                $rightall = array_map('intval', array_values($alldclen));   
                $cleanallergens = cleanArray($rightall);          
                $allergens = json_encode($cleanallergens);
            }else{
                $allergens = '[]';   
            }
        }else{
            $allergens = '[]';   
        }
        
        $prezzo_stringa = str_replace(',', '.', $data['price']);
        $prezzo_stringa = preg_replace('/[^0-9.]/', '', $prezzo_stringa);
        $prezzo_float = floatval($prezzo_stringa);

        $prezzo_stringa1 = str_replace(',', '.', $data['old_price']);
        $prezzo_stringa1 = preg_replace('/[^0-9.]/', '', $prezzo_stringa1);
        $prezzo_float1 = floatval($prezzo_stringa1);

        if (isset($data['image'])) {
            $imagePath = Storage::put('public/uploads', $data['image']);
            if ($product->image) {
                Storage::delete($product->image);
            }
            $product->image = $imagePath;
        } 
        $product->category_id   = $data['category_id'];

        $product->name          = $data['name'];
        $product->price         = intval(round($prezzo_float * 100));       
        $product->description   = $data['description'];
        
        $product->allergens    = $allergens;
        
        $product->promotion   = isset($data['promotion']) ? true : false;
        $product->old_price   = intval(round($prezzo_float1 * 100));
        
        if(config('configurazione.pack') > 2){
            $product->tag_set       = $data['tag_set'];
            if( config('configurazione.typeOfOrdering') ){
                $product->type_plate    = $data['type_plate'];     
                $product->slot_plate    = $data['slot_plate'];     
            }
        }
    
        
        $product->save();
        
        $ingredients = [];
        if(isset($data['ingredients'])){     
            foreach ($data['ingredients'] as $v) {
                array_push($ingredients, $v);
            }
            $product->ingredients()->sync($ingredients ?? []);  
        }else{
            $product->ingredients()->sync([] ?? []);  
        }
        
        return view('admin.products.show', compact( 'product'));     
    }

    public function destroy(Product $product)
    {     
        $product->ingredients()->detach();
        $product->orders()->detach();
        $product->forceDelete();
        return to_route('admin.products.index')->with('delete_success', $product);

    }
}
