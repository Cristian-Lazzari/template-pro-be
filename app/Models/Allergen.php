<?php

namespace App\Models;

use App\Models\AllergenTranslation;
use App\Models\Ingredient;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTranslations;

class Allergen extends Model
{
    use HasFactory;
    use HasTranslations;

    // Se vuoi che "name" compaia anche quando converti in JSON:
    protected $appends = ['name'];
    protected $fillable = ['special','img'];

    public function translations()
    {
        return $this->hasMany(AllergenTranslation::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_allergen');
    }

    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'ingredient_allergen');
    }

    public function getNameAttribute()
    {return $this->getTranslation('name');}

}
