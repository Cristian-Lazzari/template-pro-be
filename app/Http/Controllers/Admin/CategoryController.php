<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    private $validations_ingredient = [
        'name_ing'          => 'required|string|min:2',
        'price_ing'         => 'required',
    ];
    public function index()
    { 
        $categories     = Category::all();
        return view('admin.categories.index', compact('categories'));
    }
     
    public function create()
    {
    $categories     = Category::all();
    return view('admin.ingredients.create', compact('categories'));
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
        $new_ing->name = $data['name_ing'];
        if (isset($data['option_ing'])) {
            $new_ing->option = true;
        }else{
            $new_ing->option = false;
        }

        $new_ing->price = intval(round($prezzo_float * 100));
        $new_ing->type = json_encode($type_ing);

        if($ingredient_allergiens){
            $new_ing->allergiens = json_encode($ingredient_allergiens);
        }
        $new_ing->save();
        
        $m = ' "' . $new_ing['name'] . '" è stato creato correttamente';
        return to_route('admin.ingredients.index')->with('ingredient_success', $m);   
    }
    
    
    public function edit($id)
    {
        $ingredient    = Ingredient::where('id', $id)->firstOrFail();; 
        $categories    = Category::all();
        return view('admin.ingredients.edit', compact('categories', 'ingredient'));
    }
    public function update(Request $request, $id)
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
        
        $new_ing = Ingredient::where('id', $id)->firstOrFail();; 
        $new_ing->name = $data['name_ing'];
        if (isset($data['option_ing'])) {
            $new_ing->option = true;
        }else{
            $new_ing->option = false;
        }
        $new_ing->price = intval(round($prezzo_float * 100));
        $new_ing->type = json_encode($type_ing);
    
        if($ingredient_allergiens){
            $new_ing->allergiens = json_encode($ingredient_allergiens);
        }
        $new_ing->update();
        $m = ' "' . $new_ing['name'] . '" è stato modificato correttamente';
        return to_route('admin.ingredients.index')->with('ingredient_success', $m);
 
    }
    
    public function destroy($id)
    {
        //
    }
    public function show($id)
    {
        $ingredient    = Ingredient::where('id', $id)->firstOrFail();; 
        $categories    = Category::all();
        return view('admin.ingredients.show', compact('categories', 'ingredient'));
    }  
}
