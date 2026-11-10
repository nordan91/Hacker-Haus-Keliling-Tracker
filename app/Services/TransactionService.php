<?php

namespace App\Services;

use App\Models\DailyInventory;
use App\Models\Product;
use App\Models\Shift;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function __construct(
        protected LocationService $locationService,
    ) {}

    /**
     * Record a new sale transaction.
     *
     * @param Shift  $shift          The active shift.
     * @param string $paymentMethod  'cash' or 'qris'.
     * @param array  $items          Array of ['product_id' => int, 'qty' => int].
     * @param float|null $latitude   GPS latitude at sale time.
     * @param float|null $longitude  GPS longitude at sale time.
     *
     * @return Transaction The created transaction with details loaded.
     *
     * @throws \Exception If stock is insufficient or shift is closed.
     */
    public function recordSale(
        Shift $shift,
        string $paymentMethod,
        array $items,
        ?float $latitude = null,
        ?float $longitude = null,
    ): Transaction {
        if (!$shift->isOpen()) {
            throw new \Exception('Cannot record a sale on a closed shift.');
        }

        return DB::transaction(function () use ($shift, $paymentMethod, $items, $latitude, $longitude) {

            // 1. Resolve product prices and validate stock
            $lineItems = $this->resolveLineItems($shift, $items);
            $totalAmount = collect($lineItems)->sum('subtotal');

            // 2. Auto-match GPS to a known location
            $matchedLocation = null;
            $locationName = null;
            if ($latitude !== null && $longitude !== null) {
                $matchedLocation = $this->locationService->findNearestLocation($latitude, $longitude);
                $locationName = $matchedLocation?->name;
            }

            // 3. Determine payment status
            $paymentStatus = $paymentMethod === 'cash' ? 'paid' : 'pending';

            // 4. Generate invoice number
            $invoiceNumber = Transaction::generateInvoiceNumber($shift->shift_date->toDateString());

            // 5. Create the transaction
            $transaction = Transaction::create([
                'shift_id'       => $shift->id,
                'location_id'    => $matchedLocation?->id,
                'invoice_number' => $invoiceNumber,
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'total_amount'   => $totalAmount,
                'latitude'       => $latitude,
                'longitude'      => $longitude,
                'location_name'  => $locationName,
                'transacted_at'  => now(),
            ]);

            // 6. Create line items
            foreach ($lineItems as $item) {
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id'     => $item['product_id'],
                    'qty'            => $item['qty'],
                    'unit_price'     => $item['unit_price'],
                    'subtotal'       => $item['subtotal'],
                ]);
            }

            // 7. Update inventory cache
            $this->updateInventoryCache($shift, $lineItems);

            return $transaction->load('details.product', 'location');
        });
    }

    /**
     * Resolve product prices and validate stock availability.
     *
     * @return array Array of resolved line items with unit_price and subtotal.
     * @throws \Exception If stock is insufficient.
     */
    protected function resolveLineItems(Shift $shift, array $items): array
    {
        $lineItems = [];

        foreach ($items as $item) {
            $product = Product::findOrFail($item['product_id']);
            $qty = $item['qty'];

            // Check stock availability
            $inventory = DailyInventory::where('shift_id', $shift->id)
                ->where('product_id', $product->id)
                ->first();

            if (!$inventory) {
                throw new \Exception("Product '{$product->name}' is not in today's inventory.");
            }

            if ($inventory->remaining_qty < $qty) {
                throw new \Exception(
                    "Insufficient stock for '{$product->name}'. " .
                    "Available: {$inventory->remaining_qty}, Requested: {$qty}"
                );
            }

            $lineItems[] = [
                'product_id' => $product->id,
                'qty'        => $qty,
                'unit_price' => $product->price,
                'subtotal'   => $product->price * $qty,
            ];
        }

        return $lineItems;
    }

    /**
     * Update the cached sold_qty and remaining_qty in daily_inventories.
     */
    protected function updateInventoryCache(Shift $shift, array $lineItems): void
    {
        foreach ($lineItems as $item) {
            $inventory = DailyInventory::where('shift_id', $shift->id)
                ->where('product_id', $item['product_id'])
                ->first();

            if ($inventory) {
                $inventory->sold_qty += $item['qty'];
                $inventory->remaining_qty = $inventory->start_qty - $inventory->sold_qty;
                $inventory->save();
            }
        }
    }
}

