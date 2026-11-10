<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'shift_id',
        'location_id',
        'invoice_number',
        'payment_method',
        'payment_status',
        'total_amount',
        'latitude',
        'longitude',
        'location_name',
        'transacted_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'total_amount' => 'integer',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'transacted_at' => 'datetime',
    ];

    // ──────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────

    /**
     * A transaction belongs to a shift.
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * A transaction may belong to a known location (auto-matched via geofence).
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * A transaction has many line items (details).
     */
    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }

    // ──────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────

    /**
     * Scope: only cash transactions.
     */
    public function scopeCash($query)
    {
        return $query->where('payment_method', 'cash');
    }

    /**
     * Scope: only QRIS transactions.
     */
    public function scopeQris($query)
    {
        return $query->where('payment_method', 'qris');
    }

    /**
     * Scope: only pending QRIS transactions (awaiting validation).
     */
    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    /**
     * Scope: only confirmed transactions (paid or validated).
     */
    public function scopeConfirmed($query)
    {
        return $query->whereIn('payment_status', ['paid', 'validated']);
    }

    // ──────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────

    /**
     * Check if this is a QRIS transaction that is still pending.
     */
    public function isPendingQris(): bool
    {
        return $this->payment_method === 'qris' && $this->payment_status === 'pending';
    }

    /**
     * Validate a pending QRIS transaction (driver confirmed funds received).
     */
    public function validatePayment(): void
    {
        if ($this->payment_status === 'pending') {
            $this->update(['payment_status' => 'validated']);
        }
    }

    /**
     * Reject a pending QRIS transaction (suspected fraud).
     */
    public function rejectPayment(): void
    {
        if ($this->payment_status === 'pending') {
            $this->update(['payment_status' => 'rejected']);
        }
    }

    /**
     * Generate the next invoice number for a given date.
     * Format: HT-YYYYMMDD-XXXX (e.g. HT-20261108-0001)
     */
    public static function generateInvoiceNumber(string $date): string
    {
        $prefix = 'HT-' . str_replace('-', '', $date);

        $lastInvoice = static::where('invoice_number', 'like', $prefix . '%')
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}

