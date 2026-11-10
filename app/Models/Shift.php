<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Shift extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'shift_date',
        'target_qty',
        'status',
        'notes',
        'started_at',
        'ended_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'shift_date' => 'date:Y-m-d',
        'target_qty' => 'integer',
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];

    /**
     * Ensure shift_date is always saved in Y-m-d format (prevents SQLite/MySQL datetime mismatch).
     */
    public function setShiftDateAttribute($value): void
    {
        $this->attributes['shift_date'] = \Carbon\Carbon::parse($value)->format('Y-m-d');
    }

    // ──────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────

    /**
     * A shift belongs to a user (driver).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Alias for the user relationship — more readable.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * A shift has many daily inventory entries (one per product).
     */
    public function dailyInventories(): HasMany
    {
        return $this->hasMany(DailyInventory::class);
    }

    /**
     * A shift has many transactions.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    // ──────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────

    /**
     * Check if this shift is still open (active).
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Close this shift and set the end time.
     */
    public function close(): void
    {
        $this->update([
            'status' => 'closed',
            'ended_at' => now(),
        ]);
    }

    /**
     * Get total cash sales for this shift.
     */
    public function totalCash(): int
    {
        return (int) $this->transactions()
            ->where('payment_method', 'cash')
            ->where('payment_status', 'paid')
            ->sum('total_amount');
    }

    /**
     * Get total validated QRIS sales for this shift.
     */
    public function totalQrisValidated(): int
    {
        return (int) $this->transactions()
            ->where('payment_method', 'qris')
            ->where('payment_status', 'validated')
            ->sum('total_amount');
    }

    /**
     * Get total pending QRIS sales for this shift.
     */
    public function totalQrisPending(): int
    {
        return (int) $this->transactions()
            ->where('payment_method', 'qris')
            ->where('payment_status', 'pending')
            ->sum('total_amount');
    }

    // ──────────────────────────────────────
    // Gamification & Incentive Calculations (Rp 1.000 / Cup)
    // ──────────────────────────────────────

    /**
     * Total cups sold during this shift.
     */
    public function totalSoldQty(): int
    {
        return (int) $this->dailyInventories()->sum('sold_qty');
    }

    /**
     * Total bottles loaded onto the cart.
     */
    public function totalLoadedQty(): int
    {
        return (int) $this->dailyInventories()->sum('start_qty');
    }

    /**
     * Total unsold bottles.
     */
    public function totalUnsoldQty(): int
    {
        return (int) $this->dailyInventories()->sum('remaining_qty');
    }

    /**
     * Total financial value of unsold inventory (potential dead stock burden).
     */
    public function totalUnsoldValue(): int
    {
        $value = 0;
        foreach ($this->dailyInventories()->with('product')->get() as $inv) {
            $value += $inv->remaining_qty * ($inv->product->price ?? 0);
        }
        return $value;
    }

    /**
     * Driver incentive earned: Rp 1.000 per cup sold.
     */
    public function totalIncentive(): int
    {
        return $this->totalSoldQty() * 1000;
    }

    /**
     * Net Incentive Balance: Total Incentive minus Unsold Value burden.
     * Positive: Surplus bonus!
     * Negative: Nombok gap.
     */
    public function netIncentiveBalance(): int
    {
        return $this->totalIncentive() - $this->totalUnsoldValue();
    }

    /**
     * Check if driver has reached the "Zona Aman / Bebas Nombok" (Incentive >= Unsold value).
     */
    public function isSafeZone(): bool
    {
        return $this->netIncentiveBalance() >= 0;
    }

    /**
     * Check if driver is in 'nombok' position (net balance < 0).
     */
    public function isNombok(): bool
    {
        return $this->netIncentiveBalance() < 0;
    }

    /**
     * The exact nombok amount the driver has to cover out-of-pocket (0 if safe/surplus).
     */
    public function nombokAmount(): int
    {
        return $this->isNombok() ? abs($this->netIncentiveBalance()) : 0;
    }

    /**
     * Net surplus incentive the driver actually takes home after replacing unsold products.
     */
    public function netTakeHomeIncentive(): int
    {
        return max(0, $this->netIncentiveBalance());
    }

    /**
     * Breakdown of unsold products that incur replacement cost.
     */
    public function unsoldItemsBreakdown(): \Illuminate\Support\Collection
    {
        return $this->dailyInventories()
            ->where('remaining_qty', '>', 0)
            ->with('product')
            ->get()
            ->map(function ($inv) {
                $price = $inv->product->price ?? 0;
                return [
                    'product_name' => $inv->product->name ?? 'Minuman',
                    'category' => $inv->product->category ?? '-',
                    'remaining_qty' => $inv->remaining_qty,
                    'unit_price' => $price,
                    'subtotal' => $inv->remaining_qty * $price,
                ];
            });
    }

    /**
     * Percentage progress toward the driver's self-determined target.
     */
    public function targetProgressPercentage(): int
    {
        $target = max(1, $this->target_qty ?? 40);
        return (int) min(100, round(($this->totalSoldQty() / $target) * 100));
    }

    /**
     * Cups remaining to reach daily target.
     */
    public function cupsToTarget(): int
    {
        $target = $this->target_qty ?? 40;
        return max(0, $target - $this->totalSoldQty());
    }

    /**
     * Driver motivational badge based on cups sold.
     */
    public function motivationalBadge(): array
    {
        $sold = $this->totalSoldQty();

        if ($sold >= 60) {
            return ['title' => 'Legend Huling 👑', 'color' => 'bg-amber-500 text-white', 'icon' => 'fa-crown', 'desc' => 'Performa Luar Biasa!'];
        } elseif ($sold >= 40) {
            return ['title' => 'Bintang Lapangan ⭐', 'color' => 'bg-purple-600 text-white', 'icon' => 'fa-star', 'desc' => 'Target Utama Terlampaui!'];
        } elseif ($sold >= 25) {
            return ['title' => 'Jagoan Keliling 🔥', 'color' => 'bg-orange-500 text-white', 'icon' => 'fa-fire', 'desc' => 'Laju Penjualan Mantap!'];
        } elseif ($sold >= 10) {
            return ['title' => 'Pejuang Haus ⚡', 'color' => 'bg-blue-600 text-white', 'icon' => 'fa-bolt', 'desc' => 'Awal yang Bagus, Gas Terus!'];
        }

        return ['title' => 'Semangat Mulai 🚀', 'color' => 'bg-slate-700 text-white', 'icon' => 'fa-play', 'desc' => 'Siap Taklukkan Hari Ini!'];
    }
}

