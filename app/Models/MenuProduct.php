<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuProduct extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $table = 'menu_product';
    
    protected $fillable = [
        'product_id',
        'menu_id',
        'label',
        'extra_price',
    ];
        
    protected $with = ['translations'];


    public function translations()
    {
        return $this->hasMany(MenuProductTranslation::class);
    }

    public function getLabelAttribute()
    {
        return $this->getTranslation('label');
    }
}
