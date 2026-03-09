<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IngredientTranslation extends Model
{
    protected $fillable = [
        'ingredient_id',
        'lang',
        'name'
    ];

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
}