<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionDetail extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'transaction_id',
        'product_id',
        'qty',
        'unit_price',
        'subtotal',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'qty' => 'integer',
        'unit_price' => 'integer',
        'subtotal' => 'integer',
    ];

    // ──────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────

    /**
     * This detail line belongs to a transaction.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * This detail line references a product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

