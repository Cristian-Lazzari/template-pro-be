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

    protected $appends = ['allergens', 'name', 'description'];

    protected $with = ['directAllergens.translations', 'translation'];

    public function category()
    { return $this->belongsTo(Category::class); }
    public function ingredients()
    { return $this->belongsToMany(Ingredient::class)->orderBy('sort_order', 'asc');}
    public function orders()
    { return $this->belongsToMany(Order::class); }
    public function directAllergens()
    { return $this->belongsToMany(Allergen::class, 'product_allergen'); }

    public function translations()
    { return $this->hasMany(ProductTranslation::class); }
    
    public function getAllergensAttribute()
    {
        $productAllergens = $this->directAllergens;
        $ingredientAllergens = $this->ingredients
            ->pluck('allergens')
            ->flatten();
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
    public function getNameAttribute()
    {
        $default_l = json_decode(Setting::where('name', 'Lingua')->first()->property, 1)['default'];
        $t = $this->translations->firstWhere('lang', $default_l);
        return $t?->name ?? null;
    }
    public function getDescriptionAttribute()
    {
        $default_l = json_decode(Setting::where('name', 'Lingua')->first()->property, 1)['default'];
        $t = $this->translations->firstWhere('lang', $default_l);
        return $t?->description ?? null;
    }

}