<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyInventory extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'daily_inventories';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'shift_id',
        'product_id',
        'start_qty',
        'sold_qty',
        'remaining_qty',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'start_qty' => 'integer',
        'sold_qty' => 'integer',
        'remaining_qty' => 'integer',
    ];

    // ──────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────

    /**
     * This inventory entry belongs to a shift.
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * This inventory entry tracks a specific product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ──────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────

    /**
     * Recalculate sold_qty and remaining_qty from actual transaction data.
     * Call this after recording a new sale or correcting a transaction.
     */
    public function recalculate(): void
    {
        $this->sold_qty = TransactionDetail::whereHas('transaction', function ($query) {
            $query->where('shift_id', $this->shift_id)
                  ->whereIn('payment_status', ['paid', 'validated']);
        })->where('product_id', $this->product_id)->sum('qty');

        $this->remaining_qty = $this->start_qty - $this->sold_qty;

        $this->save();
    }
}

