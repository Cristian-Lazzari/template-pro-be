<?php

namespace App\Models;

use App\Models\Customer;
use App\Models\Menu;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $casts = [
        'tot_price' => 'float',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function customerPromotions()
    {
        return $this->hasMany(CustomerPromotion::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class)
        ->withPivot('quantity', 'remove', 'add', 'option'); // Aggiungi qui tutti i campi che vuoi accedere

    }
    public function menus()
    {
        return $this->belongsToMany(Menu::class)
        ->withPivot('choices', 'quantity'); // Aggiungi qui tutti i campi che vuoi accedere

    }
}
