<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreTheme extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'primary_color',
        'secondary_color',
        'button_color',
        'text_color',
        'background_color',
        'seo_title',
        'seo_description',
        'custom_css',
    ];

    protected function casts(): array
    {
        return [
            'custom_css' => 'array',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
