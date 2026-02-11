<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'whatsapp',
        'logo_url',
        'payment_config',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'payment_config' => 'array',
        ];
    }

    public function theme(): HasOne
    {
        return $this->hasOne(StoreTheme::class);
    }

    public function domains(): HasMany
    {
        return $this->hasMany(StoreDomain::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function primaryDomain(): HasOne
    {
        return $this->hasOne(StoreDomain::class)->where('is_primary', true);
    }

    public function owner(): HasOne
    {
        return $this->hasOne(User::class)->where('role', 'store_owner');
    }
}
