<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DailyInventory;
use App\Models\Location;
use App\Models\Product;
use App\Models\Shift;
use App\Models\Transaction;
use App\Services\LocationService;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DriverController extends Controller
{
    public function __construct(
        protected TransactionService $transactionService,
        protected LocationService $locationService,
    ) {}

    /**
     * Get or require current active shift.
     */
    protected function getCurrentShift(): ?Shift
    {
        return Shift::where('user_id', Auth::id())
            ->where('status', 'open')
            ->with(['dailyInventories.product'])
            ->latest('shift_date')
            ->first();
    }

    /**
     * View shift initialization / starting stock screen.
     */
    public function shift(): View|RedirectResponse
    {
        $currentShift = $this->getCurrentShift();
        if ($currentShift && $currentShift->dailyInventories->isNotEmpty()) {
            return redirect()->route('driver.pos');
        }

        $products = Product::active()->orderBy('category')->orderBy('name')->get();

        return view('driver.shift', compact('currentShift', 'products'));
    }

    /**
     * Start a new shift and initialize starting stock.
     */
    public function startShift(Request $request): RedirectResponse
    {
        $request->validate([
            'target_qty' => ['nullable', 'integer', 'min:1', 'max:200'],
            'notes'      => ['nullable', 'string', 'max:500'],
            'stocks'     => ['required', 'array'],
            'stocks.*'   => ['nullable', 'integer', 'min:0'],
        ]);

        $today = now()->toDateString();
        $user = Auth::user();
        $targetQty = (int) ($request->target_qty ?? 40);

        DB::transaction(function () use ($user, $today, $targetQty, $request) {
            // Find existing shift for today (robust date comparison)
            $shift = Shift::where('user_id', $user->id)
                ->whereDate('shift_date', $today)
                ->first();

            if (!$shift) {
                $shift = Shift::create([
                    'user_id'    => $user->id,
                    'shift_date' => $today,
                    'target_qty' => $targetQty,
                    'status'     => 'open',
                    'notes'      => $request->notes,
                    'started_at' => now(),
                ]);
            } else {
                $shift->update([
                    'status'     => 'open',
                    'target_qty' => $targetQty,
                    'notes'      => $request->notes ?? $shift->notes,
                    'ended_at'   => null,
                ]);
            }

            // Save or update starting inventory
            foreach ($request->stocks as $productId => $qty) {
                $startQty = (int) $qty;
                if ($startQty > 0) {
                    $inventory = DailyInventory::where('shift_id', $shift->id)
                        ->where('product_id', $productId)
                        ->first();

                    if ($inventory) {
                        $inventory->start_qty = $startQty;
                        $inventory->remaining_qty = max(0, $startQty - $inventory->sold_qty);
                        $inventory->save();
                    } else {
                        DailyInventory::create([
                            'shift_id'      => $shift->id,
                            'product_id'    => $productId,
                            'start_qty'     => $startQty,
                            'sold_qty'      => 0,
                            'remaining_qty' => $startQty,
                        ]);
                    }
                }
            }
        });

        return redirect()->route('driver.pos')->with('success', "Target hari ini: {$targetQty} cup berhasil dipasang! Semangat berburu insentif.");
    }

    /**
     * Main Mobile POS screen.
     */
    public function pos(): View|RedirectResponse
    {
        $currentShift = $this->getCurrentShift();
        if (!$currentShift || $currentShift->dailyInventories->isEmpty()) {
            return redirect()->route('driver.shift')->with('warning', 'Silakan input stok awal terlebih dahulu sebelum berjualan.');
        }

        // Get inventories with only active products
        $inventories = DailyInventory::where('shift_id', $currentShift->id)
            ->whereHas('product', fn($q) => $q->where('is_active', true))
            ->with('product')
            ->get();

        // Get pending QRIS count for alert badge
        $pendingQrisCount = Transaction::where('shift_id', $currentShift->id)
            ->where('payment_method', 'qris')
            ->where('payment_status', 'pending')
            ->count();

        // Known locations for manual or auto match
        $locations = Location::all();

        // Gamification & Incentive metrics for POS
        $gamification = [
            'sold_qty' => $currentShift->totalSoldQty(),
            'target_qty' => $currentShift->target_qty ?? 40,
            'progress_percent' => $currentShift->targetProgressPercentage(),
            'incentive' => $currentShift->totalIncentive(),
            'incentive_formatted' => 'Rp ' . number_format($currentShift->totalIncentive(), 0, ',', '.'),
            'cups_to_target' => $currentShift->cupsToTarget(),
            'is_safe_zone' => $currentShift->isSafeZone(),
            'is_nombok' => $currentShift->isNombok(),
            'nombok_amount' => $currentShift->nombokAmount(),
            'net_balance' => $currentShift->netIncentiveBalance(),
            'net_take_home' => $currentShift->netTakeHomeIncentive(),
            'unsold_qty' => $currentShift->totalUnsoldQty(),
            'unsold_value' => $currentShift->totalUnsoldValue(),
            'badge' => $currentShift->motivationalBadge(),
        ];

        return view('driver.pos', compact('currentShift', 'inventories', 'pendingQrisCount', 'locations', 'gamification'));
    }

    /**
     * Process checkout transaction from POS (AJAX / JSON).
     */
    public function checkout(Request $request): JsonResponse
    {
        $request->validate([
            'payment_method' => ['required', 'in:cash,qris'],
            'latitude'       => ['nullable', 'numeric'],
            'longitude'      => ['nullable', 'numeric'],
            'items'          => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.qty'        => ['required', 'integer', 'min:1'],
        ]);

        $shift = $this->getCurrentShift();
        if (!$shift) {
            return response()->json(['success' => false, 'message' => 'Tidak ada shift aktif.'], 422);
        }

        try {
            $transaction = $this->transactionService->recordSale(
                shift: $shift,
                paymentMethod: $request->payment_method,
                items: $request->items,
                latitude: $request->latitude ? (float) $request->latitude : null,
                longitude: $request->longitude ? (float) $request->longitude : null,
            );

            // Reload inventory data
            $inventories = DailyInventory::where('shift_id', $shift->id)
                ->with('product')
                ->get();

            $pendingQrisCount = Transaction::where('shift_id', $shift->id)
                ->where('payment_method', 'qris')
                ->where('payment_status', 'pending')
                ->count();

            $shift->refresh();
            $soldQty = $shift->totalSoldQty();
            $incentive = $shift->totalIncentive();
            $target = $shift->target_qty ?? 40;
            $progress = $shift->targetProgressPercentage();
            $badge = $shift->motivationalBadge();
            $isSafeZone = $shift->isSafeZone();
            $earnedIncentive = collect($request->items)->sum('qty') * 1000;

            return response()->json([
                'success' => true,
                'message' => $request->payment_method === 'qris'
                    ? 'Transaksi QRIS tercatat. Status: PENDING (menunggu konfirmasi dana masuk).'
                    : "Transaksi Tunai berhasil! +Rp " . number_format($earnedIncentive, 0, ',', '.') . " Insentif Masuk 🎉",
                'transaction' => $transaction,
                'inventories' => $inventories,
                'pending_qris_count' => $pendingQrisCount,
                'gamification' => [
                    'sold_qty' => $soldQty,
                    'target_qty' => $target,
                    'progress_percent' => $progress,
                    'incentive' => $incentive,
                    'incentive_formatted' => 'Rp ' . number_format($incentive, 0, ',', '.'),
                    'badge' => $badge,
                    'is_safe_zone' => $isSafeZone,
                    'is_nombok' => $shift->isNombok(),
                    'nombok_amount' => $shift->nombokAmount(),
                    'net_balance' => $shift->netIncentiveBalance(),
                    'net_take_home' => $shift->netTakeHomeIncentive(),
                    'unsold_qty' => $shift->totalUnsoldQty(),
                    'unsold_value' => $shift->totalUnsoldValue(),
                    'cups_to_target' => $shift->cupsToTarget(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * QRIS Anti-Fraud Validation Center.
     */
    public function qrisPending(): View|RedirectResponse
    {
        $currentShift = $this->getCurrentShift();
        if (!$currentShift) {
            return redirect()->route('driver.shift');
        }

        $pendingTransactions = Transaction::where('shift_id', $currentShift->id)
            ->where('payment_method', 'qris')
            ->where('payment_status', 'pending')
            ->with(['details.product'])
            ->orderByDesc('transacted_at')
            ->get();

        $historyTransactions = Transaction::where('shift_id', $currentShift->id)
            ->where('payment_method', 'qris')
            ->whereIn('payment_status', ['validated', 'rejected'])
            ->with(['details.product'])
            ->orderByDesc('updated_at')
            ->take(10)
            ->get();

        return view('driver.qris-pending', compact('currentShift', 'pendingTransactions', 'historyTransactions'));
    }

    /**
     * Validate a pending QRIS transaction.
     */
    public function validateQris(Transaction $transaction): RedirectResponse|JsonResponse
    {
        if ($transaction->isPendingQris()) {
            $transaction->validatePayment();
        }

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Pembayaran QRIS berhasil divalidasi!']);
        }

        return back()->with('success', "Pembayaran {$transaction->invoice_number} berhasil divalidasi!");
    }

    /**
     * Reject a suspected fraud QRIS transaction.
     */
    public function rejectQris(Transaction $transaction): RedirectResponse|JsonResponse
    {
        if ($transaction->isPendingQris()) {
            $transaction->rejectPayment();

            // Restore inventory quantities
            foreach ($transaction->details as $detail) {
                $inventory = DailyInventory::where('shift_id', $transaction->shift_id)
                    ->where('product_id', $detail->product_id)
                    ->first();

                if ($inventory) {
                    $inventory->sold_qty = max(0, $inventory->sold_qty - $detail->qty);
                    $inventory->remaining_qty = $inventory->start_qty - $inventory->sold_qty;
                    $inventory->save();
                }
            }
        }

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Transaksi ditolak (indikasi fraud). Stok dikembalikan.']);
        }

        return back()->with('warning', "Transaksi {$transaction->invoice_number} ditolak! Stok telah dikembalikan.");
    }

    /**
     * End of Day Recap screen.
     */
    public function recap(Request $request): View|RedirectResponse
    {
        $user = Auth::user();

        // Check if specific shift_id requested
        $shiftId = $request->query('shift_id');
        if ($shiftId) {
            $currentShift = Shift::where('user_id', $user->id)->where('id', $shiftId)->first();
        } else {
            // First check if there is an active open shift
            $currentShift = Shift::where('user_id', $user->id)
                ->where('status', 'open')
                ->latest('shift_date')
                ->first();

            // If no open shift, show the latest shift (even if closed) so driver can still review recap!
            if (!$currentShift) {
                $currentShift = Shift::where('user_id', $user->id)
                    ->latest('shift_date')
                    ->latest('id')
                    ->first();
            }
        }

        if (!$currentShift) {
            return redirect()->route('driver.shift')->with('info', 'Belum ada data shift. Silakan inisialisasi shift terlebih dahulu.');
        }

        $currentShift->load(['dailyInventories.product']);

        $transactions = $currentShift->transactions()
            ->with(['details.product', 'location'])
            ->orderByDesc('transacted_at')
            ->get();

        $totalCash = $currentShift->totalCash();
        $totalQrisValidated = $currentShift->totalQrisValidated();
        $totalQrisPending = $currentShift->totalQrisPending();

        $hasPendingQris = $transactions
            ->where('payment_method', 'qris')
            ->where('payment_status', 'pending')
            ->count() > 0;

        // Psychological & Incentive Metrics
        $totalSoldQty = $currentShift->totalSoldQty();
        $totalLoadedQty = $currentShift->totalLoadedQty();
        $totalUnsoldQty = $currentShift->totalUnsoldQty();
        $totalIncentive = $currentShift->totalIncentive();
        $totalUnsoldValue = $currentShift->totalUnsoldValue();
        $netIncentiveBalance = $currentShift->netIncentiveBalance();
        $isSafeZone = $currentShift->isSafeZone();
        $isNombok = $currentShift->isNombok();
        $nombokAmount = $currentShift->nombokAmount();
        $netTakeHomeIncentive = $currentShift->netTakeHomeIncentive();
        $unsoldBreakdown = $currentShift->unsoldItemsBreakdown();
        $targetProgress = $currentShift->targetProgressPercentage();
        $cupsToTarget = $currentShift->cupsToTarget();
        $badge = $currentShift->motivationalBadge();

        return view('driver.recap', compact(
            'currentShift',
            'transactions',
            'totalCash',
            'totalQrisValidated',
            'totalQrisPending',
            'hasPendingQris',
            'totalSoldQty',
            'totalLoadedQty',
            'totalUnsoldQty',
            'totalIncentive',
            'totalUnsoldValue',
            'netIncentiveBalance',
            'isSafeZone',
            'isNombok',
            'nombokAmount',
            'netTakeHomeIncentive',
            'unsoldBreakdown',
            'targetProgress',
            'cupsToTarget',
            'badge'
        ));
    }

    /**
     * Driver All Transactions History (across current and past shifts).
     */
    public function transactions(Request $request): View
    {
        $user = Auth::user();

        // Get past shift dates for filter dropdown
        $shiftDates = Shift::where('user_id', $user->id)
            ->orderByDesc('shift_date')
            ->pluck('shift_date')
            ->map(fn($d) => \Carbon\Carbon::parse($d)->format('Y-m-d'))
            ->unique()
            ->values();

        $selectedDate = $request->input('date');
        $selectedPayment = $request->input('payment_method');
        $selectedStatus = $request->input('status');
        $selectedShiftId = $request->input('shift_id');

        $query = Transaction::whereHas('shift', fn($q) => $q->where('user_id', $user->id))
            ->with(['details.product', 'location', 'shift']);

        if ($selectedDate) {
            $query->whereDate('transacted_at', $selectedDate);
        }

        if ($selectedPayment) {
            $query->where('payment_method', $selectedPayment);
        }

        if ($selectedStatus) {
            $query->where('payment_status', $selectedStatus);
        }

        if ($selectedShiftId) {
            $query->where('shift_id', $selectedShiftId);
        }

        // Summary metrics based on current filter
        $totalRevenue = (clone $query)->whereIn('payment_status', ['paid', 'validated'])->sum('total_amount');
        $totalTransactionsCount = (clone $query)->count();
        $totalCash = (clone $query)->where('payment_method', 'cash')->where('payment_status', 'paid')->sum('total_amount');
        $totalQris = (clone $query)->where('payment_method', 'qris')->where('payment_status', 'validated')->sum('total_amount');

        $transactions = $query->orderByDesc('transacted_at')->paginate(20)->withQueryString();

        // Retrieve all user shifts (open & closed) for the shift archive list
        $userShifts = Shift::where('user_id', $user->id)
            ->with(['dailyInventories.product'])
            ->orderByDesc('shift_date')
            ->orderByDesc('id')
            ->get();

        // Active shift to showcase in the Incentive Recap card:
        $featuredShift = null;
        if ($selectedShiftId) {
            $featuredShift = $userShifts->firstWhere('id', (int) $selectedShiftId);
        } elseif ($selectedDate) {
            $featuredShift = $userShifts->first(fn($s) => $s->shift_date->format('Y-m-d') === $selectedDate);
        }

        if (!$featuredShift) {
            $featuredShift = $userShifts->first();
        }

        // Aggregate All-Time Incentive Metrics
        $totalAllTimeCups = 0;
        $totalAllTimeIncentive = 0;
        $totalAllTimeNetTakeHome = 0;
        foreach ($userShifts as $s) {
            $totalAllTimeCups += $s->totalSoldQty();
            $totalAllTimeIncentive += $s->totalIncentive();
            $totalAllTimeNetTakeHome += $s->netTakeHomeIncentive();
        }

        return view('driver.transactions', compact(
            'transactions',
            'shiftDates',
            'selectedDate',
            'selectedPayment',
            'selectedStatus',
            'selectedShiftId',
            'totalRevenue',
            'totalTransactionsCount',
            'totalCash',
            'totalQris',
            'userShifts',
            'featuredShift',
            'totalAllTimeCups',
            'totalAllTimeIncentive',
            'totalAllTimeNetTakeHome'
        ));
    }

    /**
     * Close the shift.
     */
    public function closeShift(Request $request): RedirectResponse
    {
        $shift = $this->getCurrentShift();
        if (!$shift) {
            return redirect()->route('driver.shift');
        }

        // Recalculate all inventory
        foreach ($shift->dailyInventories as $inventory) {
            $inventory->recalculate();
        }

        $shift->close();

        return redirect()->route('driver.recap')->with('success', 'Shift berhasil ditutup! Rekap penjualan harian telah disimpan.');
    }
}

