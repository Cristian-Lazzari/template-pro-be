<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Allergen;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\IngredientTranslation;
use App\Models\Setting;
use App\Services\GoogleTranslateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IngredientController extends Controller
{
    private $validations_ingredient = [
        'name_ing'          => 'required|string|min:2',
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
        $options = Ingredient::query()
            ->where('option', true)
            ->join('ingredient_translations as t', function ($join) use ($lang) {
                $join->on('ingredients.id', '=', 't.ingredient_id')
                    ->where('t.lang', $lang);
            })
            ->orderBy('t.name')
            ->select('ingredients.*')
            ->get();         
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

        $ingredient = new Ingredient();
        // $ingredient->name = $data['name_ing'];
        $ingredient->option = !empty($data['option_ing']);
        $ingredient->price = (int) round($price * 100);
        $ingredient->type = json_encode($type_ing);

        // upload immagine
        if (!empty($data['image_ing'])) {
            $ingredient->icon = Storage::put('public/uploads', $data['image_ing']);
        }

        $ingredient->save();
        $ingredient->allergens()->sync($ingredient_allergens);

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
                'ingredient_id' => $ingredient->id,
                'lang' => $lang,
                'name' => $name,
            ]);
        }
        
        $m = ' "' . $ingredient['name'] . '" è stato creato correttamente';
        return to_route('admin.ingredients.index')->with('ingredient_success', $m);   
    }
    
    
    public function edit($id)
    {
        $ingredient    = Ingredient::where('id', $id)->firstOrFail()->load('translations');
        $translations  = $ingredient->translations->keyBy('lang');
        $categories    = Category::all();
        $allergens     = Allergen::all();
        $languages     = json_decode(Setting::where('name', 'Lingua')->first()->property, 1);
        return view('admin.Ingredients.edit', compact('categories', 'ingredient', 'allergens', 'translations', 'languages'));
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

        $ingredient = Ingredient::findOrFail($id);


        $ingredient->option = !empty($data['option_ing']);
        $ingredient->price = (int) round($price * 100);
        $ingredient->type = json_encode($type_ing);

        // upload immagine
        if (!empty($data['image_ing'])) {
            $imagePath = Storage::put('public/uploads', $data['image_ing']);

            if ($ingredient->icon) {
                Storage::delete($ingredient->icon);
            }

            $ingredient->icon = $imagePath;
        }

        $ingredient->update();
        $ingredient->allergens()->sync($ingredient_allergens);
    

        /*  | TRADUZIONI PERSONALIZZATE */
        $lang_s = json_decode(Setting::where('name', 'Lingua')->first()->property, 1);
        $default_l = $lang_s['default'];


        $n_trans = $ingredient->name !== $data['name_ing'];

        $translator = app(GoogleTranslateService::class);

        IngredientTranslation::updateOrCreate(
            [   'product_id' => $ingredient->id, 'lang' => $default_l   ],
            [
                'name' => $data['name_ing'] ?? null,
            ]
        );
        if(isset($data['translations'])){
            foreach($data['translations'] as $lang => $v){
                IngredientTranslation::updateOrCreate(
                    [   'product_id' => $ingredient->id, 'lang' => $lang   ],
                    [
                        'name' => $n_trans ? $translator->translate($data['name_ing'], $lang) : $v['name'],
                    ]
                );
                
            }
        }


        $m = ' "' . $ingredient['name'] . '" è stato modificato correttamente';
        return to_route('admin.ingredients.index')->with('ingredient_success', $m);
 
    }
    
    public function destroy(Ingredient $ingredient)
    {
        $ingredient->products()->detach();
        $ingredient->delete();
        $m = ' "' . $ingredient->name . '" è stato eliminato e rimosso dai prodotti correttamente';
        return to_route('admin.ingredients.index')->with('delete_success', $m);
    }
   
    

    
}
    