<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuProductTranslation extends Model
{
    protected $fillable = [
        'menu_id',
        'product_id',
        'lang',
        'label'
    ];
}