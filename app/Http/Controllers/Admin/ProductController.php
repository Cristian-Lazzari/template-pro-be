<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    private $validations = [
        'name'          => 'required|string|min:1|max:50',
        'price'         => 'required|string|max:50000',
        'image'         => 'nullable|image',
    ];

    public function index()
    {
        $product    = Product::all();
        $categories = category::all();
        
        return view('admin.projects.index', compact('product', 'categories'));
    }

  
    public function create()
    {
        $categories     = Category::all();
        $alltag         = Tag::all();
        $tags = [];
        $tagDescription = [];
        foreach ($alltag as $tag) {
            if($tag['price'] == 0){
                array_push($tagDescription, $tag);
            }else{
                array_push($tags, $tag);
            }
        }
        
        return view('admin.projects.create', compact('categories', 'tags', 'tagDescription'));
    }

   
    public function store(Request $request)
    {
        
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
