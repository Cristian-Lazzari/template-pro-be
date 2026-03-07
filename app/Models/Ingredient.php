<?php

namespace App\Models;

use App\Models\Allergen;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    use HasFactory;
    
    public function products() {
        return $this->belongsToMany(Product::class);
    }
    public function allergens()
    {
        return $this->belongsToMany(Allergen::class, 'ingredient_allergen');
    }

}
