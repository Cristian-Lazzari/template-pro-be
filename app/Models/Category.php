<?php

namespace App\Models;

use App\Models\Menu;
use App\Models\Product;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $with = ['translations'];
    
    public function product() {    
        return $this->hasMany(Product::class)->orderBy('created_at', 'asc');
    }
    public function menu() {    
        return $this->hasMany(Menu::class);
    }
    public function translations()
    {
        return $this->hasMany(CategoryTranslation::class);
    }
    
    public function getNameAttribute()
    {return $this->getTranslation('name');}

    public function getDescriptionAttribute()
    {return $this->getTranslation('description');}
    
}
