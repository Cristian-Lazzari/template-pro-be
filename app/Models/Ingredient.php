<?php

namespace App\Models;

use App\Models\Allergen;
use App\Models\Product;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    use HasFactory;

    use HasTranslations;

    protected $appends  = ['name'];
    protected $with     = ['translations'];
    
    public function products() {
        return $this->belongsToMany(Product::class);
    }
    public function allergens()
    {
        return $this->belongsToMany(Allergen::class, 'ingredient_allergen');
    }
    public function translations()
    {
        return $this->hasMany(IngredientTranslation::class);
    }
    public function getNameAttribute()
    {return $this->getTranslation('name');}

}
