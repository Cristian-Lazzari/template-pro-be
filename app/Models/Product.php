<?php

namespace App\Models;

use App\Models\Allergen;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\ProductTranslation;
use App\Models\PromotionTarget;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    use HasTranslations;

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Product $product): void {
            if (empty($product->slug)) {
                $product->slug = static::generateUniqueSlug($product->name);
            }
        });

        static::updating(function (Product $product): void {
            if ($product->isDirty('name') && empty($product->slug)) {
                $product->slug = static::generateUniqueSlug($product->name, $product->id);
            }
        });
    }

    public static function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = 'prodotto';
        }

        $slug    = $base;
        $counter = 1;
        while (
            static::where('slug', $slug)
                ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = $base . '-' . (++$counter);
        }

        return $slug;
    }

    protected $casts = [
        'price' => 'float',
        'old_price' => 'float',
    ];

    protected $appends = ['allergens', 'name', 'description'];

    protected $with = ['directAllergens.translations', 'translations', 'ingredients.allergens'];

    public function category()
    { return $this->belongsTo(Category::class); }

    public function ingredients()
    { return $this->belongsToMany(Ingredient::class)->orderBy('sort_order', 'asc');}

    public function orders()
    { return $this->belongsToMany(Order::class); }

    public function directAllergens()
    { return $this->belongsToMany(Allergen::class, 'product_allergen'); }    

    public function translations()
    {return $this->hasMany(ProductTranslation::class);}

    public function promotionTargets()
    {
        return $this->hasMany(PromotionTarget::class, 'target_id')
            ->where('target_type', PromotionTarget::TYPE_PRODUCT);
    }

    public function activePromotionTargets()
    {
        return $this->promotionTargets()
            ->whereHas('promotion', fn ($query) => $query->where('status', 'active'));
    }

    public function getNameAttribute()
    {return $this->getTranslation('name');}

    public function getDescriptionAttribute()
    {return $this->getTranslation('description');}
    

    public function getAllergensAttribute()
    {
        $productAllergens = $this->directAllergens;
        $ingredientAllergens = $this->ingredients->flatMap(function ($ingredient) {
            return $ingredient->allergens;
        });

        $allergens = $productAllergens
            ->merge($ingredientAllergens)
            ->unique('id')
            ->values();
        if ($allergens->contains('id', 4)) {
            $allergens = $allergens
                ->reject(fn($a) => $a->id == 1)
                ->values();
        }
        return $allergens;
    }
   

}
