<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    public function promotionTargets()
    {
        return $this->hasMany(PromotionTarget::class, 'target_id')
            ->where('target_type', PromotionTarget::TYPE_POST);
    }

    public function activePromotionTargets()
    {
        return $this->promotionTargets()
            ->whereHas('promotion', fn ($query) => $query->where('status', 'active'));
    }
}
