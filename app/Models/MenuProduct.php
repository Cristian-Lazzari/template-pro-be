<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class MenuProduct extends Pivot
{
    use HasFactory;
    use HasTranslations;

    protected $table = 'menu_product';

    protected $casts = [
        'extra_price' => 'float',
    ];
    
    protected $fillable = [
        'product_id',
        'menu_id',
        'label',
        'extra_price',
    ];
        
    protected $with = ['translations'];


    public function translations()
    {
        return $this->hasMany(MenuProductTranslation::class, 'menu_product_id');
    }

    public function getLabelAttribute()
    {
        return $this->getTranslation('label');
    }
}
