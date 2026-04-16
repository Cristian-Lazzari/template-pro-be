<?php

namespace App\Models;

use App\Models\Allergen;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\ProductTranslation;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    use HasTranslations;

    protected $casts = [
        'price' => 'float',
        'old_price' => 'float',
    ];

    protected $appends = ['allergens', 'name', 'description'];

    protected $with = ['directAllergens.translations', 'translations', 'ingredients.allergens'];

    public function category()
    { return $this->belongsTo(Category::class); }

    public function ingredients()
    { return $this->belongsToMany(Ingredient::class)->orderBy('sort_order', 'asc');}

    public function orders()
    { return $this->belongsToMany(Order::class); }

    public function directAllergens()
    { return $this->belongsToMany(Allergen::class, 'product_allergen'); }    

    public function translations()
    {return $this->hasMany(ProductTranslation::class);}

    public function getNameAttribute()
    {return $this->getTranslation('name');}

    public function getDescriptionAttribute()
    {return $this->getTranslation('description');}
    

    public function getAllergensAttribute()
    {
        $productAllergens = $this->directAllergens;
        $ingredientAllergens = $this->ingredients->flatMap(function ($ingredient) {
            return $ingredient->allergens;
        });

        $allergens = $productAllergens
            ->merge($ingredientAllergens)
            ->unique('id')
            ->values();
        if ($allergens->contains('id', 4)) {
            $allergens = $allergens
                ->reject(fn($a) => $a->id == 1)
                ->values();
        }
        return $allergens;
    }
   

}
