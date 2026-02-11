<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreDomain extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'domain',
        'is_primary',
        'verified',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'verified' => 'boolean',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
