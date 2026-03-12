<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Product;
use App\Models\Setting;
use App\Services\GoogleTranslateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    private $validations_category = [
        'name'          => 'required|string|min:2|unique:categories,name',
    ];
    private $validations_category1 = [
        'name'          => 'required|string|min:2',
    ];
    public function index()
    { 
        $categories = Category::with('products')->orderBy('updated_at', 'desc')->get();

        // $categories          = Category::orderBy('updated_at', 'desc')->get();
        return view('admin.Categories.index', compact('categories'));
    }
     
    public function create()
    {
        return view('admin.Categories.create');
    }


    public function store(Request $request)
    {
        $data = $request->all();
        $request->validate($this->validations_category);
        
        $category = new Category();
        // $category->name = $data['name'];
        // $category->description = $data['description'];
        if (isset($data['icon'])) {
            $iconPath = Storage::put('public/uploads', $data['icon']);
            $category->icon = $iconPath;
        } 
        $category->save();

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
            CategoryTranslation::create([
                'category_id' => $category->id,
                'lang' => $lang,
                'name' => $name,
                'description' => $description
            ]);
        }
        
        $m = 'La categoria "' . $data['name'] . '" è stata creata correttamente';
        return to_route('admin.categories.index')->with('category_success', $m);   
    }
    
    
    public function neworder(Request $request)
    {
        $ids = $request->input('new_order');
        $invertito = array_reverse($ids);
        $s= 0;
        foreach ($invertito as $id) {
            $category = Category::where('id', $id)->first();
            $category->updated_at = now()->addSeconds($s); // Aggiunge 5 secondi

            $category->update(); 
            $s++;
        }
        
        //dd($ids);
        $m = 'Ordine aggiornato correttamente';
    
        return to_route('admin.categories.index')->with('category_success', $m);   
    }
    
    public function new_order_products(Request $request)
    {
        $ids = $request->input('new_order_p');
        //$invertito = array_reverse($ids);
        $s= 0;
        foreach ($ids as $id) {
            $prod = Product::where('id', $id)->first();
            $prod->created_at = now()->addSeconds($s); // Aggiunge 5 secondi

            $prod->update(); 
           // dump($prod);
            $s++;
        }
        
      //  dd($ids);
        $m = 'Ordine aggiornato correttamente';
    
        return to_route('admin.categories.index')->with('category_success', $m);   
    }

    public function edit($id)
    {
        $category    = Category::where('id', $id)->firstOrFail()->load('translations');
        $translations   = $category->translations->keyBy('lang');
        $languages    = json_decode(Setting::where('name', 'Lingua')->first()->property, 1);
        return view('admin.Categories.edit', compact( 'category', 'languages', 'translations'));
    }
    public function update(Request $request, $id)
    {
        $data = $request->all();
        $request->validate($this->validations_category1);
        
        $category = Category::where('id', $id)->firstOrFail();

        /*  | TRADUZIONI PERSONALIZZATE */
        $lang_s = json_decode(Setting::where('name', 'Lingua')->first()->property, 1);
        $default_l = $lang_s['default'];

        $n_trans = $category->name !== $data['name'];
        $d_trans = $category->description !== $data['description'];

        $translator = app(GoogleTranslateService::class);

        CategoryTranslation::updateOrCreate(
            [   'category_id' => $category->id, 'lang' => $default_l   ],
            [
                'name' => $data['name'] ?? null,
                'description' => $data['description'] ?? null
            ]
        );
        if(isset($data['translations'])){
            foreach($data['translations'] as $lang => $v){
                CategoryTranslation::updateOrCreate(
                    [   'category_id' => $category->id, 'lang' => $lang   ],
                    [
                        'name' => $n_trans ? $translator->translate($data['name'], $lang) : $v['name'],
                        'description' => $n_trans ? $translator->translate($data['description'], $lang) : $v['description'],
                    ]
                );
                
            }
        }
        if (isset($data['icon'])) {
            $iconPath = Storage::put('public/uploads', $data['icon']);
            if ($category->icon) {
                Storage::delete($category->icon);
            }
            $category->icon = $iconPath;
        }
        $category->update();
        
        $m = 'La categoria "' . $data['name'] . '" è stata modificata correttamente';
        return to_route('admin.categories.index')->with('category_success', $m);
 
    }
    
    public function destroy(Category $category)
    {
        foreach ($category->product as $product) {
            $product->category_id = 1;
            $product->update();
        }
        
        $category->delete();
        $m = ' "' . $category->name . '" è stata eliminata e rimossa dai prodotti correttamente';
        return to_route('admin.categories.index')->with('delete_success', $m);
    }
    public function show($id)
    {
        $category    = category::where('id', $id)->firstOrFail();
        //return view('admin.Categories.show', compact('category'));
    }  
}
