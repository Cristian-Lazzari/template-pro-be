<?php

namespace App\Models;

use App\Models\Allergen;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    public function category() {
        return $this->belongsTo(Category::class);
    }
    public function ingredients() {
        return $this->belongsToMany(Ingredient::class)->orderBy('sort_order', 'asc');
    }
    public function orders(){
        return $this->belongsToMany(Order::class);
    }
    public function directAllergens()
    {
        return $this->belongsToMany(Allergen::class, 'product_allergen');
    }
    /*
    |--------------------------------------------------------------------------
    | ACCESSOR -> allergens
    |--------------------------------------------------------------------------
    */

    public function getAllergensAttribute()
    {

        // allergeni del prodotto
        $productAllergens = $this->directAllergens;

        // allergeni degli ingredienti
        $ingredientAllergens = $this->ingredients
            ->pluck('allergens')
            ->flatten();

        // merge
        $allergens = $productAllergens
            ->merge($ingredientAllergens)
            ->unique('id')
            ->values();

        /*
        |--------------------------------------------------------------------------
        | REGOLA GLUTINE
        |--------------------------------------------------------------------------
        | id 1 = senza glutine
        | id 4 = glutine
        */

        if ($allergens->contains('id', 4)) {
            $allergens = $allergens
                ->reject(fn($a) => $a->id == 1)
                ->values();
        }

        return $allergens;
    }


}
