<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Allergen;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IngredientController extends Controller
{
    private $validations_ingredient = [
        'name_ing'          => 'required|string|min:2|unique:ingredients,name',
        'price_ing'         => 'required',
        'image_ing'         => 'nullable|image',
    ];
    private $validations_ingredient1 = [
        'name_ing'          => 'required|string|min:2',
        'price_ing'         => 'required',
        'image_ing'         => 'nullable|image',
    ];
    public function index()
    {
        $ingredients    = Ingredient::where('option', false)->orderBy('name')->get(); 
        $options        = Ingredient::where('option', true)->orderBy('name')->get(); 
        return view('admin.Ingredients.index', compact('ingredients', 'options'));
    }
     
    public function create()
    {
    $categories     = Category::all();
    $allergens      = Allergen::all();
    return view('admin.Ingredients.create', compact('categories', 'allergens'));
    }


    public function store(Request $request)
    {
        $data = $request->all();
        $request->validate($this->validations_ingredient);

        // normalizza prezzo
        $price = str_replace(',', '.', $data['price_ing']);
        $price = preg_replace('/[^0-9.]/', '', $price);
        $price = (float) $price;

        // dati opzionali
        $ingredient_allergens = $data['allergens_ing'] ?? [];
        $type_ing = $data['type_ing'] ?? [];

        $new_ing = new Ingredient();
        $new_ing->name = $data['name_ing'];
        $new_ing->option = !empty($data['option_ing']);
        $new_ing->price = (int) round($price * 100);
        $new_ing->type = json_encode($type_ing);

        // upload immagine
        if (!empty($data['image_ing'])) {
            $new_ing->icon = Storage::put('public/uploads', $data['image_ing']);
        }

        $new_ing->save();
        $new_ing->allergens()->sync($ingredient_allergens);
        
        $m = ' "' . $new_ing['name'] . '" è stato creato correttamente';
        return to_route('admin.ingredients.index')->with('ingredient_success', $m);   
    }
    
    
    public function edit($id)
    {
        $ingredient    = Ingredient::where('id', $id)->firstOrFail();
        $categories    = Category::all();
        $allergens     = Allergen::all();
        return view('admin.Ingredients.edit', compact('categories', 'ingredient', 'allergens'));
    }
    protected function cleanArray($array) {
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
    public function update(Request $request, $id)
    {
        $data = $request->all();
        $request->validate($this->validations_ingredient1);

        // normalizza prezzo
        $price = str_replace(',', '.', $data['price_ing']);
        $price = preg_replace('/[^0-9.]/', '', $price);
        $price = (float) $price;

        // dati opzionali
        $ingredient_allergens = $data['allergens_ing'] ?? [];
        $type_ing = $data['type_ing'] ?? [];

        $new_ing = Ingredient::findOrFail($id);

        $new_ing->name = $data['name_ing'];
        $new_ing->option = !empty($data['option_ing']);
        $new_ing->price = (int) round($price * 100);
        $new_ing->type = json_encode($type_ing);

        // upload immagine
        if (!empty($data['image_ing'])) {
            $imagePath = Storage::put('public/uploads', $data['image_ing']);

            if ($new_ing->icon) {
                Storage::delete($new_ing->icon);
            }

            $new_ing->icon = $imagePath;
        }

        $new_ing->update();
        $new_ing->allergens()->sync($ingredient_allergens);
        
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
    