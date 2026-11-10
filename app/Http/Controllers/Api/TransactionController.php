<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Shift;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TransactionController extends Controller
{
    public function __construct(
        protected TransactionService $transactionService,
    ) {}

    /**
     * POST /api/transactions
     *
     * Record a new sale transaction on the driver's current open shift.
     */
    public function store(StoreTransactionRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        // Find the driver's current open shift
        $shift = Shift::where('user_id', $user->id)
            ->where('status', 'open')
            ->latest('shift_date')
            ->first();

        if (!$shift) {
            return response()->json([
                'message' => 'Tidak ada shift aktif. Mulai shift terlebih dahulu.',
            ], 422);
        }

        try {
            $transaction = $this->transactionService->recordSale(
                shift: $shift,
                paymentMethod: $validated['payment_method'],
                items: $validated['items'],
                latitude: $validated['latitude'] ?? null,
                longitude: $validated['longitude'] ?? null,
            );

            return response()->json([
                'message' => 'Transaksi berhasil dicatat.',
                'data'    => new TransactionResource($transaction),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * GET /api/transactions
     *
     * List transactions for the driver's current shift.
     * Supports query params: ?payment_method=qris&payment_status=pending
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Admins can view all, drivers see their own shift
        if ($user->isAdmin()) {
            $query = Transaction::with('details.product', 'location', 'shift.user');

            if ($request->has('shift_id')) {
                $query->where('shift_id', $request->shift_id);
            }

            if ($request->has('date')) {
                $query->whereHas('shift', fn ($q) => $q->where('shift_date', $request->date));
            }
        } else {
            $shift = Shift::where('user_id', $user->id)
                ->where('status', 'open')
                ->latest('shift_date')
                ->first();

            if (!$shift) {
                return response()->json([
                    'message' => 'Tidak ada shift aktif.',
                    'data'    => [],
                ]);
            }

            $query = Transaction::where('shift_id', $shift->id)
                ->with('details.product', 'location');
        }

        // Optional filters
        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $transactions = $query->orderByDesc('transacted_at')->get();

        return response()->json([
            'data' => TransactionResource::collection($transactions),
        ]);
    }

    /**
     * GET /api/transactions/{transaction}
     *
     * Show a single transaction with details.
     */
    public function show(Request $request, Transaction $transaction): JsonResponse
    {
        $user = $request->user();

        // Drivers can only see their own transactions
        if ($user->isDriver() && $transaction->shift->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $transaction->load('details.product', 'location');

        return response()->json([
            'data' => new TransactionResource($transaction),
        ]);
    }

    /**
     * PATCH /api/transactions/{transaction}/validate
     *
     * Validate a pending QRIS transaction (driver confirms funds received).
     */
    public function validateQris(Request $request, Transaction $transaction): JsonResponse
    {
        $user = $request->user();

        if ($user->isDriver() && $transaction->shift->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (!$transaction->isPendingQris()) {
            return response()->json([
                'message' => 'Transaksi ini bukan QRIS pending.',
            ], 422);
        }

        $transaction->validatePayment();

        return response()->json([
            'message' => 'Pembayaran QRIS berhasil divalidasi.',
            'data'    => new TransactionResource($transaction->fresh('details.product', 'location')),
        ]);
    }

    /**
     * PATCH /api/transactions/{transaction}/reject
     *
     * Reject a pending QRIS transaction (suspected fraud).
     */
    public function rejectQris(Request $request, Transaction $transaction): JsonResponse
    {
        $user = $request->user();

        if ($user->isDriver() && $transaction->shift->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (!$transaction->isPendingQris()) {
            return response()->json([
                'message' => 'Transaksi ini bukan QRIS pending.',
            ], 422);
        }

        $transaction->rejectPayment();

        // Restore inventory since the sale is rejected
        $this->restoreInventory($transaction);

        return response()->json([
            'message' => 'Pembayaran QRIS ditolak (fraud).',
            'data'    => new TransactionResource($transaction->fresh('details.product', 'location')),
        ]);
    }

    /**
     * GET /api/transactions/pending-qris
     *
     * List all pending QRIS transactions for the driver's current shift.
     */
    public function pendingQris(Request $request): JsonResponse
    {
        $user = $request->user();

        $shift = Shift::where('user_id', $user->id)
            ->where('status', 'open')
            ->latest('shift_date')
            ->first();

        if (!$shift) {
            return response()->json([
                'data'  => [],
                'count' => 0,
            ]);
        }

        $pending = Transaction::where('shift_id', $shift->id)
            ->where('payment_method', 'qris')
            ->where('payment_status', 'pending')
            ->with('details.product')
            ->orderByDesc('transacted_at')
            ->get();

        return response()->json([
            'data'  => TransactionResource::collection($pending),
            'count' => $pending->count(),
            'total_amount' => $pending->sum('total_amount'),
            'total_formatted' => 'Rp ' . number_format($pending->sum('total_amount'), 0, ',', '.'),
        ]);
    }

    /**
     * Restore inventory quantities when a QRIS transaction is rejected.
     */
    protected function restoreInventory(Transaction $transaction): void
    {
        $transaction->load('details');

        foreach ($transaction->details as $detail) {
            $inventory = $transaction->shift->dailyInventories()
                ->where('product_id', $detail->product_id)
                ->first();

            if ($inventory) {
                $inventory->sold_qty -= $detail->qty;
                $inventory->remaining_qty = $inventory->start_qty - $inventory->sold_qty;
                $inventory->save();
            }
        }
    }
}

