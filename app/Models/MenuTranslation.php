<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuTranslation extends Model
{
    protected $fillable = [
        'menu_id',
        'lang',
        'name',
        'description'
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}