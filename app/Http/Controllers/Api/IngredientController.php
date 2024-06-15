<?php

namespace App\Http\Controllers\Api;

use App\Models\Ingredient;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class IngredientController extends Controller
{
    public function index() {
        $ingredients = Ingredient::all();

        return response()->json([
            'success' => true,
            'results' => $ingredients,
        ]);
    }
}
