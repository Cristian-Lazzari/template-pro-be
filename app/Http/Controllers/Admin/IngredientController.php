<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\Category;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class IngredientController extends Controller
{
    private $validations_ingredient = [
        'name_ing'          => 'required|string|min:2|unique:ingredients,name',
        'price_ing'         => 'required',
    ];
    private $validations_ingredient1 = [
        'name_ing'          => 'required|string|min:2',
        'price_ing'         => 'required',
    ];
    public function index()
    {
        $ingredients    = Ingredient::all(); 
        return view('admin.Ingredients.index', compact('ingredients'));
    }
     
    public function create()
    {
    $categories     = Category::all();
    return view('admin.Ingredients.create', compact('categories'));
    }


    public function store(Request $request)
    {
        $data = $request->all();
        $request->validate($this->validations_ingredient);
        
        $prezzo_stringa = str_replace(',', '.', $data['price_ing']);
        $prezzo_stringa = preg_replace('/[^0-9.]/', '', $prezzo_stringa);
        $prezzo_float = floatval($prezzo_stringa);
        if (isset($data['allergiens_ing'])){
            $ingredient_allergiens = $data['allergiens_ing'];
        }else{
            $ingredient_allergiens = '[]';
        }

        if (isset($data['type_ing'])){
            $type_ing = $data['type_ing'];
        }else{
            $type_ing = '[]';
        }
        
        $new_ing = new Ingredient();
        if (isset($data['image'])) {
            $imagePath = Storage::put('public/uploads', $data['image_ing']);
            $new_ing->image = $imagePath;
        }
        $new_ing->name = $data['name_ing'];

        if (isset($data['option_ing'])) {
            $new_ing->option = true;
        }else{
            $new_ing->option = false;
        }

        $new_ing->price = intval(round($prezzo_float * 100));
        $new_ing->type = json_encode($type_ing);

         if($ingredient_allergiens !== '[]'){
            $rightall = array_map('intval', array_values($ingredient_allergiens));
            $new_ing->allergiens = json_encode($rightall);
        }
        $new_ing->save();
        
        $m = ' "' . $new_ing['name'] . '" è stato creato correttamente';
        return to_route('admin.ingredients.index')->with('ingredient_success', $m);   
    }
    
    
    public function edit($id)
    {
        $ingredient    = Ingredient::where('id', $id)->firstOrFail();
        $categories    = Category::all();
        return view('admin.Ingredients.edit', compact('categories', 'ingredient'));
    }
    public function update(Request $request, $id)
    {
        $data = $request->all();
        $request->validate($this->validations_ingredient1);
        
        $prezzo_stringa = str_replace(',', '.', $data['price_ing']);
        $prezzo_stringa = preg_replace('/[^0-9.]/', '', $prezzo_stringa);
        $prezzo_float = floatval($prezzo_stringa);
        if (isset($data['allergiens_ing'])){
            $ingredient_allergiens = $data['allergiens_ing'];
        }else{
            $ingredient_allergiens = '[]';
        }
        if (isset($data['type_ing'])){
            $type_ing = $data['type_ing'];
        }else{
            $type_ing = '[]';
        }
        
        $new_ing = Ingredient::where('id', $id)->firstOrFail();
        $new_ing->name = $data['name_ing'];
        if (isset($data['image'])) {
            $imagePath = Storage::put('public/uploads', $data['image_ing']);
            if ($new_ing->image) {
                Storage::delete($new_ing->image);
            }
            $new_ing->image = $imagePath;
        }
        if (isset($data['option_ing'])) {
            $new_ing->option = true;
        }else{
            $new_ing->option = false;
        }
        $new_ing->price = intval(round($prezzo_float * 100));
        $new_ing->type = json_encode($type_ing);
    
         if($ingredient_allergiens !== '[]'){
            $rightall = array_map('intval', array_values($ingredient_allergiens));
            $new_ing->allergiens = json_encode($rightall);
        }
        $new_ing->update();
        $m = ' "' . $new_ing['name'] . '" è stato modificato correttamente';
        return to_route('admin.ingredients.index')->with('ingredient_success', $m);
 
    }
    
    public function destroy(Ingredient $ingredient)
    {
        $ingredient->products()->detach();
        $ingredient->delete();
        $m = ' "' . $ingredient->name . '" è stato eliminato e rimosso dai prodotti correttamente';
        return to_route('admin.ingredients.index')->with('delete_success', $m);
    }
    public function show($id)
    {
        $ingredient    = Ingredient::where('id', $id)->firstOrFail();; 
        $categories    = Category::all();
        return view('admin.Ingredients.show', compact('categories', 'ingredient'));
    }
     
   
    

    
}
    