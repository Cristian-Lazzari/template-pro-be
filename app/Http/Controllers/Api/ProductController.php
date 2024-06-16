<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    public function index(Request $request)
    {

        $categoryId = $request->query('category');

        $query = Product::with('category', 'ingredients')->where('visible', 1);
        $query = $query->where('archived', 0);


        if ($categoryId !== null && $categoryId !== 0) {
            $query = $query->where('category_id', $categoryId);
        } 
        
        $products = $query->get();
        



        return response()->json([
            'success'   => true,
            'results'   => $products,
            'allergiens'   => config('configurazione.allergiens'),
        ]);
    }
}
