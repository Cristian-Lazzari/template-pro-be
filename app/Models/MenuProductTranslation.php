<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuProductTranslation extends Model
{

    protected $fillable = [
        'menu_product_id',
        'lang',
        'label'
    ];

    public function menuProduct()
    {
        return $this->belongsTo(MenuProduct::class);
    }
}