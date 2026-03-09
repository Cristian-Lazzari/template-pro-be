<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Allergen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AllergenController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $allergens = Allergen::orderBy('special', 'asc')->get();

        return view('admin.Allergens.index', compact('allergens'));
    }

 
    public function create()
    {
        return view('admin.Allergens.create');
    }
    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|string|min:2|unique:allergen_translations,name',
            'special' => 'nullable',
            'img' => 'nullable|string'
        ]);
        $img = null;
        if (isset($data['img'])) {
            $iconPath = Storage::put('public/uploads', $data['img']);
            $img= $iconPath;
        } 
        $allergen = Allergen::create([
            'special' => $request->special,
            'img' => $request->img
        ]);

        $allergen->translations()->create([
            'lang' => 'it',
            'name' => $request->name
        ]);   
        
        $m = ' "' . $allergen['name'] . '" è stato creato correttamente';
        return to_route('admin.allergens.index')->with('success', $m);
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        $allergen = Allergen::where('id', $id)->firstOrFail(); 
    
        return view('admin.Allergens.edit', compact('allergen'));
    }

    public function update(Request $request, Allergen $allergen)
    {
        $translation = $allergen->translations()
            ->firstOrCreate(['lang' => 'it']);

        $request->validate([
            'name' => [
                'required',
                'string',
                'min:2',
                Rule::unique('allergen_translations', 'name')
                    ->where('lang', 'it')
                    ->ignore($translation->id)
            ],
            'special' => 'nullable',
            'img' => 'nullable|string'
        ]);

        /* update tabella allergens */
        $img = $allergen->img;
        if (isset($data['img'])) {
            $iconPath = Storage::put('public/uploads', $data['img']);
            if ($allergen->img) {
                Storage::delete($allergen->img);
            }
            $img = $img;
        }
        $allergen->update([
            'special' => $request->special,
            'img' => $img
        ]);

        /* update traduzione */
        $translation->update([
            'name' => $request->name
        ]);
        
        $m = ' "' . $allergen->name . '" è stato creato correttamente';
        return to_route('admin.allergens.index')->with('success', $m);
    }

    public function destroy(Allergen $allergen)
    {
        $allergen->products()->detach();
        $allergen->delete();
        $m = ' "' . $allergen->name . '" è stato eliminato e rimosso dai prodotti correttamente';
        return to_route('admin.allergens.index')->with('delete_success', $m);
    }   
}
