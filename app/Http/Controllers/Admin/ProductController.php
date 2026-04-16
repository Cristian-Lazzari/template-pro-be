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
use App\Support\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

   
    
    public function status(Request $request)
    {
        $archiveContext = (int) $request->input('archive', 0);
        $toggleArchive = (int) $request->input('a', 0) === 1;
        $toggleVisible = (int) $request->input('v', 0) === 1;

        $product = Product::where('id', $request->input('id'))->firstOrFail();
        $message = null;
        $action = null;

        if ($toggleArchive) {
            $product->archived = !$product->archived;
            $product->save();
            $action = 'archived';
            $message = $product->archived
                ? '"' . $product->name . '" e\' stato archiviato correttamente'
                : '"' . $product->name . '" e\' stato ripristinato correttamente';
        }

        if ($toggleVisible) {
            $product->visible = !$product->visible;
            $product->save();
            $action = 'visible';
            $message = $product->visible
                ? '"' . $product->name . '" e\' visibile ai tuoi clienti '
                : '"' . $product->name . '" non e\' visibile ai tuoi clienti';
        }

        if (is_null($message)) {
            $message = 'Nessuna modifica applicata.';
        }

        if ($request->expectsJson() || $request->ajax()) {
            $shouldRemoveFromList = ($archiveContext === 1 && !$product->archived)
                || ($archiveContext !== 1 && $product->archived);

            return response()->json([
                'success' => true,
                'message' => $message,
                'action' => $action,
                'should_remove' => $shouldRemoveFromList,
                'product' => [
                    'id' => $product->id,
                    'visible' => (bool) $product->visible,
                    'archived' => (bool) $product->archived,
                ],
            ]);
        }

        if ($archiveContext === 1) {
            return to_route('admin.products.archived')->with('success', $message);
        }

        return to_route('admin.products.index')->with('success', $message);
    }

    public function archived(Request $request){
        $products   = Product::where('archived', true)->get();
        
        return view('admin.Products.archived', compact('products'));
    }


    public function index()
    {
        $locale = app()->getLocale();
        $defaultLocale = config('configurazione.default_lang', 'en');

        $products = Product::query()
            ->without(['directAllergens.translations', 'translations', 'ingredients.allergens'])
            ->leftJoin('product_translations as pt_locale', function ($join) use ($locale) {
                $join->on('products.id', '=', 'pt_locale.product_id')
                    ->where('pt_locale.lang', $locale);
            })
            ->leftJoin('product_translations as pt_default', function ($join) use ($defaultLocale) {
                $join->on('products.id', '=', 'pt_default.product_id')
                    ->where('pt_default.lang', $defaultLocale);
            })
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->leftJoin('category_translations as ct_locale', function ($join) use ($locale) {
                $join->on('categories.id', '=', 'ct_locale.category_id')
                    ->where('ct_locale.lang', $locale);
            })
            ->leftJoin('category_translations as ct_default', function ($join) use ($defaultLocale) {
                $join->on('categories.id', '=', 'ct_default.category_id')
                    ->where('ct_default.lang', $defaultLocale);
            })
            ->where('products.archived', false)
            ->orderBy('products.updated_at', 'desc')
            ->select([
                'products.id',
                'products.image',
                'products.price',
                'products.visible',
                DB::raw("COALESCE(NULLIF(pt_locale.name, ''), pt_default.name) as display_name"),
                DB::raw("COALESCE(NULLIF(ct_locale.name, ''), ct_default.name) as category_name"),
            ])
            ->simplePaginate(60);

        $categories = Category::query()
            ->leftJoin('category_translations as ct_locale', function ($join) use ($locale) {
                $join->on('categories.id', '=', 'ct_locale.category_id')
                    ->where('ct_locale.lang', $locale);
            })
            ->leftJoin('category_translations as ct_default', function ($join) use ($defaultLocale) {
                $join->on('categories.id', '=', 'ct_default.category_id')
                    ->where('ct_default.lang', $defaultLocale);
            })
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('products')
                    ->whereColumn('products.category_id', 'categories.id')
                    ->where('products.archived', false);
            })
            ->orderByRaw("COALESCE(NULLIF(ct_locale.name, ''), ct_default.name) asc")
            ->select([
                'categories.id',
                DB::raw("COALESCE(NULLIF(ct_locale.name, ''), ct_default.name) as name"),
            ])
            ->get();

        return view('admin.Products.index', compact('products', 'categories'));
    }

    public function search(Request $request)
    {
        $locale = app()->getLocale();
        $defaultLocale = config('configurazione.default_lang', 'en');
        $search = trim((string) $request->query('q', ''));
        $categoryId = $request->query('category_id');
        $sort = $request->query('sort', 'recent') === 'alpha' ? 'alpha' : 'recent';

        $query = Product::query()
            ->without(['directAllergens.translations', 'translations', 'ingredients.allergens'])
            ->leftJoin('product_translations as pt_locale', function ($join) use ($locale) {
                $join->on('products.id', '=', 'pt_locale.product_id')
                    ->where('pt_locale.lang', $locale);
            })
            ->leftJoin('product_translations as pt_default', function ($join) use ($defaultLocale) {
                $join->on('products.id', '=', 'pt_default.product_id')
                    ->where('pt_default.lang', $defaultLocale);
            })
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->leftJoin('category_translations as ct_locale', function ($join) use ($locale) {
                $join->on('categories.id', '=', 'ct_locale.category_id')
                    ->where('ct_locale.lang', $locale);
            })
            ->leftJoin('category_translations as ct_default', function ($join) use ($defaultLocale) {
                $join->on('categories.id', '=', 'ct_default.category_id')
                    ->where('ct_default.lang', $defaultLocale);
            })
            ->where('products.archived', false);

        if ($search !== '') {
            $query->where(function ($nested) use ($search) {
                $nested->where('pt_locale.name', 'like', "%{$search}%")
                    ->orWhere('pt_default.name', 'like', "%{$search}%");
            });
        }

        if (is_numeric($categoryId)) {
            $query->where('products.category_id', (int) $categoryId);
        }

        if ($sort === 'alpha') {
            $query->orderByRaw("COALESCE(NULLIF(pt_locale.name, ''), pt_default.name) asc");
        } else {
            $query->orderBy('products.updated_at', 'desc');
        }

        $products = $query
            ->limit(400)
            ->select([
                'products.id',
                'products.image',
                'products.price',
                'products.visible',
                DB::raw("COALESCE(NULLIF(pt_locale.name, ''), pt_default.name) as display_name"),
                DB::raw("COALESCE(NULLIF(ct_locale.name, ''), ct_default.name) as category_name"),
            ])
            ->get();

        return response()->json([
            'html' => view('admin.Products.partials.index_cards', compact('products'))->render(),
        ]);
    }

    public function quickView(Product $product)
    {
        $product->load(['category', 'ingredients.allergens', 'directAllergens']);

        return view('admin.Products.partials.quick_view_modal_body', compact('product'));
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

            $price = Currency::parseInput($data['price_ing']);

            $new_ing = new Ingredient();
            $new_ing->option = 0;
            $new_ing->price = $price;
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
        
        $price = Currency::parseInput($data['price']);

        if (isset($data['image'])) {
            $imagePath = Storage::put('public/uploads', $data['image']);
            $product->image = $imagePath;
        } 
        $product->category_id   = $data['category_id'];

        $product->price         = $price;


        $product->promotion   = isset($data['promotion']) ? true : false;
        $product->old_price   = filled($data['old_price'] ?? null)
            ? Currency::parseInput($data['old_price'])
            : null;
        
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

            $price = Currency::parseInput($data['price_ing']);

            $new_ing = new Ingredient();
            $new_ing->option = 0;
            $new_ing->price = $price;
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

        $price = Currency::parseInput($data['price']);
        $oldprice = filled($data['old_price'] ?? null)
            ? Currency::parseInput($data['old_price'])
            : null;

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
        $product->price         = $price;
        $product->promotion   = isset($data['promotion']) ? true : false;
        $product->old_price   = $oldprice;
        
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
