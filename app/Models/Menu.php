<?php

namespace App\Models;

use App\Models\Category;
use App\Models\MenuTranslation;
use App\Models\Order;
use App\Models\Product;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    use HasTranslations;

    protected $appends = ['name', 'description'];

    protected $with = ['translations'];

    public function category() {
        return $this->belongsTo(Category::class);
    }
    public function orders(){
        return $this->belongsToMany(Order::class);
    }
    // public function products(){
    //     return $this->belongsToMany(Product::class)
    //     ->withPivot('label', )
    // }
    
    public function products()
    {
        return $this->belongsToMany(Product::class)
            ->using(MenuProduct::class)
            ->withPivot('id', 'extra_price')
            ->withTimestamps()
            ->orderBy('created_at', 'asc'); // <- fondamentale!
    }

    public function translations()
    {
        return $this->hasMany(MenuTranslation::class);
    }
    
    public function getNameAttribute()
    {return $this->getTranslation('name');}

    public function getDescriptionAttribute()
    {return $this->getTranslation('description');}
    
}
