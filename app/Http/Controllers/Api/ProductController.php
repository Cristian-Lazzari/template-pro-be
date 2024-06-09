<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {

        $categoryId = $request->query('category');

        $query = Product::with('category', 'tags')->where('visible', '=', 0);


        if ($categoryId !== 0) {
            $query = $query->where('category_id', $categoryId);
        } 
        
        $products = $query->get();



        return response()->json([
            'success'   => true,
            'results'   => $products,
        ]);
    }
}
