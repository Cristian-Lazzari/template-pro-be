<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Allergen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        $data = $request->all();
        $request->validate([
            'name' => 'required|string|min:2|unique:allergens,name',
        ]);
        $allergen = new Allergen();
        $allergen->name = $data['name'];
        $allergen->description = $data['description'];
        if (isset($data['icon'])) {
            $iconPath = Storage::put('public/uploads', $data['icon']);
            $allergen->icon = $iconPath;
        } 
        $allergen->save();
        
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

    public function update(Request $request, $id)
    {
        $data = $request->all();
        $request->validate([
            'name' => 'required|string|min:2',
        ]);

        $allergen = Allergen::where('id', $id)->firstOrFail();
        $allergen->name = $data['name'];
        if (isset($data['img'])) {
            $iconPath = Storage::put('public/uploads', $data['img']);
            if ($allergen->img) {
                Storage::delete($allergen->img);
            }
            $allergen->img = $iconPath;
        }
        $allergen->update();
        
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
