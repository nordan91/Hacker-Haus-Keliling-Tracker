<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'category',
        'price',
        'image_url',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'price' => 'integer',
        'is_active' => 'boolean',
    ];

    // ──────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────

    /**
     * A product appears in many daily inventories (across shifts).
     */
    public function dailyInventories(): HasMany
    {
        return $this->hasMany(DailyInventory::class);
    }

    /**
     * A product appears in many transaction details (sale line items).
     */
    public function transactionDetails(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }

    // ──────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────

    /**
     * Scope to only active products.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

