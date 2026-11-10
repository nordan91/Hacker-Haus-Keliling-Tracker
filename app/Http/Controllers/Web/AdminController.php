<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DailyInventory;
use App\Models\Location;
use App\Models\Product;
use App\Models\Shift;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    /**
     * Main Admin Analytics Dashboard.
     */
    public function dashboard(): View
    {
        // 1. Overall stats
        $totalRevenue = Transaction::whereIn('payment_status', ['paid', 'validated'])->sum('total_amount');
        $totalCash = Transaction::where('payment_method', 'cash')->where('payment_status', 'paid')->sum('total_amount');
        $totalQris = Transaction::where('payment_method', 'qris')->where('payment_status', 'validated')->sum('total_amount');
        $totalTransactions = Transaction::count();

        // 2. Dead Stock Overview (Products with highest unsold / remaining qty)
        $deadStockRanking = DailyInventory::select('product_id',
                DB::raw('SUM(start_qty) as total_loaded'),
                DB::raw('SUM(sold_qty) as total_sold'),
                DB::raw('SUM(remaining_qty) as total_unsold'),
                DB::raw('ROUND((SUM(sold_qty) * 100.0 / NULLIF(SUM(start_qty), 0)), 1) as sell_through_rate')
            )
            ->with('product')
            ->groupBy('product_id')
            ->orderBy('total_unsold', 'desc')
            ->get();

        // 3. QRIS Fraud Security Metrics
        $qrisPendingCount = Transaction::where('payment_method', 'qris')->where('payment_status', 'pending')->count();
        $qrisValidatedCount = Transaction::where('payment_method', 'qris')->where('payment_status', 'validated')->count();
        $qrisRejectedCount = Transaction::where('payment_method', 'qris')->where('payment_status', 'rejected')->count();

        // 4. Sales by Location
        $locationSales = Transaction::select('location_name',
                DB::raw('COUNT(id) as total_orders'),
                DB::raw('SUM(total_amount) as total_revenue')
            )
            ->whereNotNull('location_name')
            ->whereIn('payment_status', ['paid', 'validated'])
            ->groupBy('location_name')
            ->orderByDesc('total_revenue')
            ->take(6)
            ->get();

        // 5. Recent Transactions
        $recentTransactions = Transaction::with(['shift.user', 'details.product', 'location'])
            ->orderByDesc('transacted_at')
            ->take(10)
            ->get();

        // 6. Active Drivers / Shifts today
        $todayShifts = Shift::whereDate('shift_date', now()->toDateString())
            ->with(['user', 'dailyInventories.product', 'transactions'])
            ->get();

        // 7. Top Selling Products
        $topProducts = TransactionDetail::select('product_id',
                DB::raw('SUM(qty) as total_qty'),
                DB::raw('SUM(subtotal) as total_sales')
            )
            ->whereHas('transaction', fn($q) => $q->whereIn('payment_status', ['paid', 'validated']))
            ->groupBy('product_id')
            ->with('product')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        $activeDriversCount = $todayShifts->where('status', 'open')->count();
        $totalBottlesLoaded = $deadStockRanking->sum('total_loaded');
        $totalBottlesSold = $deadStockRanking->sum('total_sold');
        $totalBottlesUnsold = $deadStockRanking->sum('total_unsold');

        return view('admin.dashboard', compact(
            'totalRevenue',
            'totalCash',
            'totalQris',
            'totalTransactions',
            'deadStockRanking',
            'qrisPendingCount',
            'qrisValidatedCount',
            'qrisRejectedCount',
            'locationSales',
            'recentTransactions',
            'todayShifts',
            'topProducts',
            'activeDriversCount',
            'totalBottlesLoaded',
            'totalBottlesSold',
            'totalBottlesUnsold'
        ));
    }

    /**
     * Detailed Dead Stock Analytics.
     */
    public function deadStock(Request $request): View
    {
        $startDate = $request->input('start_date', now()->subDays(7)->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $inventories = DailyInventory::whereHas('shift', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('shift_date', [$startDate, $endDate]);
            })
            ->select('product_id',
                DB::raw('SUM(start_qty) as total_loaded'),
                DB::raw('SUM(sold_qty) as total_sold'),
                DB::raw('SUM(remaining_qty) as total_unsold'),
                DB::raw('ROUND((SUM(sold_qty) * 100.0 / NULLIF(SUM(start_qty), 0)), 1) as sell_through_rate')
            )
            ->with('product')
            ->groupBy('product_id')
            ->orderBy('sell_through_rate', 'asc')
            ->get();

        // Dead stock hotspots by location
        $locationDeadStock = TransactionDetail::join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->select('transactions.location_name', 'transaction_details.product_id',
                DB::raw('SUM(transaction_details.qty) as units_sold')
            )
            ->whereNotNull('transactions.location_name')
            ->whereIn('transactions.payment_status', ['paid', 'validated'])
            ->groupBy('transactions.location_name', 'transaction_details.product_id')
            ->with('product')
            ->get();

        return view('admin.dead-stock', compact('inventories', 'startDate', 'endDate', 'locationDeadStock'));
    }

    /**
     * Shifts List and Audit.
     */
    public function shifts(): View
    {
        $shifts = Shift::with(['user', 'dailyInventories.product'])
            ->orderByDesc('shift_date')
            ->paginate(15);

        return view('admin.shifts', compact('shifts'));
    }

    /**
     * Complete Transactions Audit View for Admin.
     */
    public function transactions(Request $request): View
    {
        $selectedDate = $request->input('date');
        $selectedDriver = $request->input('driver_id');
        $selectedPayment = $request->input('payment_method');
        $selectedStatus = $request->input('status');

        $query = Transaction::with(['shift.user', 'details.product', 'location']);

        if ($selectedDate) {
            $query->whereDate('transacted_at', $selectedDate);
        }

        if ($selectedDriver) {
            $query->whereHas('shift', fn($q) => $q->where('user_id', $selectedDriver));
        }

        if ($selectedPayment) {
            $query->where('payment_method', $selectedPayment);
        }

        if ($selectedStatus) {
            $query->where('payment_status', $selectedStatus);
        }

        $drivers = User::where('role', 'driver')->get();
        $totalRevenue = (clone $query)->whereIn('payment_status', ['paid', 'validated'])->sum('total_amount');
        $totalCount = (clone $query)->count();

        $transactions = $query->orderByDesc('transacted_at')->paginate(25)->withQueryString();

        return view('admin.transactions', compact(
            'transactions',
            'drivers',
            'selectedDate',
            'selectedDriver',
            'selectedPayment',
            'selectedStatus',
            'totalRevenue',
            'totalCount'
        ));
    }

    /**
     * Locations Management View.
     */
    public function locations(): View
    {
        $locations = Location::withCount('transactions')->get();
        return view('admin.locations', compact('locations'));
    }

    /**
     * Products Management View.
     */
    public function products(): View
    {
        $products = Product::orderBy('category')->orderBy('name')->get();
        return view('admin.products', compact('products'));
    }

    // ──────────────────────────────────────
    // Driver Management (CRUD)
    // ──────────────────────────────────────

    /**
     * Driver Management View.
     */
    public function drivers(): View
    {
        $drivers = User::where('role', 'driver')
            ->withCount('shifts')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.drivers', compact('drivers'));
    }

    /**
     * Store a new driver.
     */
    public function storeDriver(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
        ]);

        User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => 'driver',
            'phone'    => $validated['phone'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return back()->with('success', 'Driver berhasil ditambahkan.');
    }

    /**
     * Update driver details.
     */
    public function updateDriver(Request $request, User $driver): RedirectResponse
    {
        if ($driver->role !== 'driver') {
            return back()->withErrors(['driver' => 'User bukan driver.']);
        }

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email,' . $driver->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
        ]);

        $driver->name = $validated['name'];
        $driver->email = $validated['email'];
        $driver->phone = $validated['phone'] ?? null;
        $driver->is_active = $validated['is_active'] ?? true;

        if ($request->filled('password')) {
            $driver->password = Hash::make($validated['password']);
        }

        $driver->save();

        return back()->with('success', 'Driver berhasil diperbarui.');
    }

    /**
     * Delete a driver.
     */
    public function destroyDriver(User $driver): RedirectResponse
    {
        if ($driver->role !== 'driver') {
            return back()->withErrors(['driver' => 'User bukan driver.']);
        }

        // Prevent deleting if driver has shifts
        if ($driver->shifts()->exists()) {
            return back()->withErrors(['driver' => 'Tidak bisa menghapus driver yang sudah memiliki data shift. Nonaktifkan saja.']);
        }

        $driver->delete();

        return back()->with('success', 'Driver berhasil dihapus.');
    }

    /**
     * Toggle driver active status.
     */
    public function toggleDriverStatus(User $driver): JsonResponse
    {
        if ($driver->role !== 'driver') {
            return response()->json(['success' => false, 'message' => 'User bukan driver.'], 422);
        }

        $driver->is_active = !$driver->is_active;
        $driver->save();

        return response()->json([
            'success' => true,
            'message' => $driver->is_active ? 'Driver diaktifkan.' : 'Driver dinonaktifkan.',
            'is_active' => $driver->is_active,
        ]);
    }
}

