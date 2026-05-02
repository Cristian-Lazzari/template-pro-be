<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as IntModel;

class Model extends IntModel
{
    use HasFactory;

    protected $fillable = [
        'name',
        'heading',
        'object',
        'body',
        'ending',
        'sender',
        'img_1',
        'img_2',
        'type',
        'channel',
        'status',
        'body_html',
        'body_text',
        'variables',
        'preview_data',
        'last_used_at',
    ];

    protected $casts = [
        'variables' => 'array',
        'preview_data' => 'array',
        'last_used_at' => 'datetime',
    ];

    public function campaigns()
    {
        return $this->hasMany(Campaign::class, 'model_id');
    }

    public function automations()
    {
        return $this->hasMany(Automation::class, 'model_id');
    }
}
