<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\Category;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

 $typeOfOrdering = true; //se impostato a true gli ordini vengono presi in base ai pezzi altrimenti in base al numero di ordini
class ProductController extends Controller
{

    
        private $validationsTrue = [
            'name'          => 'required|string|min:1|max:50',
            'image'         => 'nullable|image',
            'price'         => 'required',
            'description'   => 'required',
            'slot_plate'    => 'required',
            'type_plate'    => 'required',
            'tag_set'       => 'required'
        ];
 
        private $validationsFalse = [
            'name'          => 'required|string|min:1|max:50',
            'image'         => 'nullable|image',
            'price'         => 'required',
            'description'   => 'required',
            'tag_set'       => 'required'
        ];
    

    public function index()
    {
        $product    = Product::all();
        $categories = Category::all();
        
        
        return view('admin.products.index', compact('product', 'categories'));
    }
    
    public function special(Request $request){
        
        // FUNZIONE DI FILTRAGGIO INDEX
        $categories = Category::all();
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
        ];;
        
        $query = Product::query();
        
        if ($name) {
            $query->where('name', 'like', '%' . $name . '%');
        } 
        if ($visible == 1) {
            $query->where('visible', '=', 0);
        } else if ($visible == 2) {
            $query->where('visible', '=', 1);
        }
        if($category_id){
            $query->where('category_id', $category_id);
        }
        if($name){
            $products = $query->orderBy('name')->get();
        }else{
            $products = $query->orderBy('updated_at', 'desc')->get();    
        }        

        return view('admin.products.index', compact('products', 'categories', 'filters'));
        
    }

  
    public function create()
    {
        $categories     = Category::all();
        $ingredient     = Ingredient::all(); 
        
        return view('admin.products.create', compact('categories', 'ingredients'));
    }

    private $validations_ingredient = [
        'name_ing'          => 'required|string|min:2',
        'price_ing'         => 'required',
    ];
    public function store(Request $request)
    {
        $data = $request->all();
        $newi = $data['newi'];
        if (isset($newi)) {
            $ingredient_allergiens = $data['allergiens'];
            $request->validate($this->validations_ingredient);
            
            $new_ing = new Ingredient();
            $new_ing->name = $data['name_ing'];
            $new_ing->price = $data['price_ing'];

            if($ingredient_allergiens){
                $new_ing->allergiens = json_encode($ingredient_allergiens);
            }
            $new_ing->save();
            
            return redirect()->route('admin.products.create')->with('ingredient_success', 'data');     
        }
        if ($typeOfOrdering) {        
            $request->validate($this->validationsTrue);
        }else{
            $request->validate($this->validationsFalse);
        }
        
        $newproduct = new Product();
        
        $allergiens = [];
        if(isset($data['ingredients'])){     
            foreach ($data['ingredients'] as $i) {
                $all = Ingredient::where('id', $i)->firstOrFail();
                $isall = json_decode($all['allergiens']);
                foreach($isall as $ia){
                    array_push($allergiens, $ia);
                }  
            }
        }
        

       
        $prezzo_stringa = str_replace(',', '.', $data['price']);
        $prezzo_stringa = preg_replace('/[^0-9.]/', '', $prezzo_stringa);
        $prezzo_float = floatval($prezzo_stringa);

        if (isset($data['image'])) {
            $imagePath = Storage::put('public/uploads', $data['image']);
            $newproduct->image = $imagePath;
        } 
        $newproduct->category_id   = $data['category_id'];

        $newproduct->name          = $data['name'];
        $newproduct->price         = intval(round($prezzo_float * 100));       
        $newproduct->description   = $data['description'];

        $newproduct->allergiens    = $allergiens;
        
        $newproduct->type_plate    = $data['type_plate'];     
        $newproduct->slot_plate    = $data['slot_plate'];     
        $newproduct->tag_set       = $data['tag_set'];
    
        
        $newproduct->save();
        
        $ingredients = [];
        if(isset($data['ingredients'])){     
            foreach ($data['ingredients'] as $v) {
                array_push($ingredients, $v);
            }
            $newproduct->ingredients()->sync($ingredients ?? []);
        }
        
        return to_route('admin.products.show', ['product' => $newproduct]);
        
    }

   
    public function show($id)
    {
        
    }

    public function edit($id)
    {
        
    }

    public function update(Request $request, $id)
    {
        
    }

   
    public function destroy($id)
    {
        
    }
}
