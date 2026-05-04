<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionTarget extends Model
{
    use HasFactory;

    public const TYPE_PRODUCT = 'product';
    public const TYPE_MENU = 'menu';
    public const TYPE_CATEGORY = 'category';
    public const TYPE_POST = 'post';
    public const TYPE_GENERIC = 'generic';

    protected $fillable = [
        'promotion_id',
        'target_type',
        'target_id',
        'discount',
        'type_discount',
        'metadata',
    ];

    protected $casts = [
        'discount' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    public function target(): ?Model
    {
        if ($this->isGenericTarget() || ! $this->target_id) {
            return null;
        }

        $targetClass = match ($this->target_type) {
            self::TYPE_PRODUCT => Product::class,
            self::TYPE_MENU => Menu::class,
            self::TYPE_CATEGORY => Category::class,
            self::TYPE_POST => Post::class,
            default => null,
        };

        if (! $targetClass || ! class_exists($targetClass)) {
            return null;
        }

        return $targetClass::query()->find($this->target_id);
    }

    public function isProductTarget(): bool
    {
        return $this->target_type === self::TYPE_PRODUCT;
    }

    public function isMenuTarget(): bool
    {
        return $this->target_type === self::TYPE_MENU;
    }

    public function isCategoryTarget(): bool
    {
        return $this->target_type === self::TYPE_CATEGORY;
    }

    public function isPostTarget(): bool
    {
        return $this->target_type === self::TYPE_POST;
    }

    public function isGenericTarget(): bool
    {
        return $this->target_type === self::TYPE_GENERIC;
    }
}
