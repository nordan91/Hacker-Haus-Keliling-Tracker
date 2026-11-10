<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shift\StartShiftRequest;
use App\Http\Requests\Shift\StoreInventoryRequest;
use App\Http\Resources\DailyInventoryResource;
use App\Http\Resources\ShiftResource;
use App\Models\DailyInventory;
use App\Models\Shift;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    /**
     * POST /api/shifts/start
     *
     * Start a new shift for the authenticated driver.
     * Creates the shift record; inventory is set separately.
     */
    public function start(StartShiftRequest $request): JsonResponse
    {
        $user = $request->user();
        $shiftDate = $request->validated()['shift_date'];

        // Check if a shift already exists for this driver on this date
        $existingShift = Shift::where('user_id', $user->id)
            ->whereDate('shift_date', $shiftDate)
            ->first();

        if ($existingShift) {
            return response()->json([
                'message' => 'Shift sudah ada untuk tanggal ini.',
                'data'    => new ShiftResource($existingShift->load('dailyInventories.product')),
            ], 409); // Conflict
        }

        $shift = Shift::create([
            'user_id'    => $user->id,
            'shift_date' => $shiftDate,
            'status'     => 'open',
            'notes'      => $request->validated()['notes'] ?? null,
            'started_at' => now(),
        ]);

        return response()->json([
            'message' => 'Shift berhasil dimulai.',
            'data'    => new ShiftResource($shift),
        ], 201);
    }

    /**
     * GET /api/shifts/current
     *
     * Get the driver's current open shift with inventory and transactions.
     */
    public function current(Request $request): JsonResponse
    {
        $shift = Shift::where('user_id', $request->user()->id)
            ->where('status', 'open')
            ->with(['dailyInventories.product', 'transactions.details.product'])
            ->latest('shift_date')
            ->first();

        if (!$shift) {
            return response()->json([
                'message' => 'Tidak ada shift aktif. Silakan mulai shift baru.',
                'data'    => null,
            ], 404);
        }

        return response()->json([
            'data' => new ShiftResource($shift),
        ]);
    }

    /**
     * POST /api/shifts/{shift}/inventories
     *
     * Set starting stock for a shift (bulk upsert).
     * Called right after starting a shift.
     */
    public function storeInventory(StoreInventoryRequest $request, Shift $shift): JsonResponse
    {
        // Ensure the shift belongs to the authenticated driver
        if ($shift->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (!$shift->isOpen()) {
            return response()->json(['message' => 'Shift sudah ditutup.'], 422);
        }

        $inventories = $request->validated()['inventories'];

        foreach ($inventories as $item) {
            DailyInventory::updateOrCreate(
                [
                    'shift_id'   => $shift->id,
                    'product_id' => $item['product_id'],
                ],
                [
                    'start_qty'     => $item['start_qty'],
                    'sold_qty'      => 0,
                    'remaining_qty' => $item['start_qty'],
                ]
            );
        }

        $shift->load('dailyInventories.product');

        return response()->json([
            'message' => 'Stok awal berhasil disimpan.',
            'data'    => DailyInventoryResource::collection($shift->dailyInventories),
        ]);
    }

    /**
     * GET /api/shifts/{shift}/inventories
     *
     * Get current inventory status for a shift.
     */
    public function getInventory(Request $request, Shift $shift): JsonResponse
    {
        if ($shift->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $shift->load('dailyInventories.product');

        return response()->json([
            'data' => DailyInventoryResource::collection($shift->dailyInventories),
        ]);
    }

    /**
     * POST /api/shifts/{shift}/close
     *
     * Close a shift and generate the end-of-day recap.
     */
    public function close(Request $request, Shift $shift): JsonResponse
    {
        if ($shift->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (!$shift->isOpen()) {
            return response()->json(['message' => 'Shift sudah ditutup.'], 422);
        }

        // Recalculate all inventory caches before closing
        foreach ($shift->dailyInventories as $inventory) {
            $inventory->recalculate();
        }

        $shift->close();

        return response()->json([
            'message' => 'Shift berhasil ditutup.',
            'data'    => $this->buildRecap($shift),
        ]);
    }

    /**
     * GET /api/shifts/{shift}/recap
     *
     * Get the end-of-day recap for a shift.
     */
    public function recap(Request $request, Shift $shift): JsonResponse
    {
        if ($shift->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        return response()->json([
            'data' => $this->buildRecap($shift),
        ]);
    }

    /**
     * GET /api/shifts/history
     *
     * List past shifts for the authenticated driver (paginated).
     */
    public function history(Request $request): JsonResponse
    {
        $shifts = Shift::where('user_id', $request->user()->id)
            ->with('dailyInventories.product')
            ->orderByDesc('shift_date')
            ->paginate(15);

        return response()->json([
            'data' => ShiftResource::collection($shifts),
            'meta' => [
                'current_page' => $shifts->currentPage(),
                'last_page'    => $shifts->lastPage(),
                'total'        => $shifts->total(),
            ],
        ]);
    }

    /**
     * Build the end-of-day recap data structure.
     */
    protected function buildRecap(Shift $shift): array
    {
        $shift->load(['dailyInventories.product', 'transactions.details.product', 'user']);

        $inventorySummary = $shift->dailyInventories->map(fn ($inv) => [
            'product_id'    => $inv->product_id,
            'product_name'  => $inv->product->name,
            'start_qty'     => $inv->start_qty,
            'sold_qty'      => $inv->sold_qty,
            'remaining_qty' => $inv->remaining_qty,
        ]);

        $totalCash = $shift->totalCash();
        $totalQrisValidated = $shift->totalQrisValidated();
        $totalQrisPending = $shift->totalQrisPending();

        $pendingTransactions = $shift->transactions
            ->where('payment_status', 'pending')
            ->values()
            ->map(fn ($t) => [
                'id'             => $t->id,
                'invoice_number' => $t->invoice_number,
                'total_amount'   => $t->total_amount,
                'location_name'  => $t->location_name,
                'transacted_at'  => $t->transacted_at->toIso8601String(),
            ]);

        return [
            'shift' => [
                'id'         => $shift->id,
                'driver'     => $shift->user->name,
                'shift_date' => $shift->shift_date->toDateString(),
                'status'     => $shift->status,
                'started_at' => $shift->started_at?->toIso8601String(),
                'ended_at'   => $shift->ended_at?->toIso8601String(),
            ],
            'inventory' => $inventorySummary,
            'sales_summary' => [
                'total_transactions'   => $shift->transactions->count(),
                'total_cash'           => $totalCash,
                'total_cash_formatted' => 'Rp ' . number_format($totalCash, 0, ',', '.'),
                'total_qris_validated'           => $totalQrisValidated,
                'total_qris_validated_formatted' => 'Rp ' . number_format($totalQrisValidated, 0, ',', '.'),
                'total_qris_pending'             => $totalQrisPending,
                'total_qris_pending_formatted'   => 'Rp ' . number_format($totalQrisPending, 0, ',', '.'),
                'grand_total'           => $totalCash + $totalQrisValidated,
                'grand_total_formatted' => 'Rp ' . number_format($totalCash + $totalQrisValidated, 0, ',', '.'),
            ],
            'pending_transactions' => $pendingTransactions,
        ];
    }
}

