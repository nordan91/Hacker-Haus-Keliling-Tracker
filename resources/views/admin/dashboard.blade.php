@extends('layouts.app')

@section('title', 'Admin Analytics Dashboard')

@section('content')
<div class="space-y-4 sm:space-y-6" x-data="{ 
    selectedTrx: null, 
    mobileChartTab: 'payment',
    mobilePanelTab: 'deadstock'
}">

    <!-- Top Hero Header (Mobile-Optimized) -->
    <div class="relative overflow-hidden bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white rounded-2xl sm:rounded-3xl p-4 sm:p-7 shadow-lg border border-slate-700/50">
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-orange-500/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-3 sm:gap-4">
            <div>
                <div class="flex items-center space-x-2 mb-1.5">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-orange-500 text-white tracking-wide shadow-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-white animate-ping mr-1"></span>
                        LIVE
                    </span>
                    <span class="text-[11px] text-slate-400 font-medium">Haus Keliling POS & Analytics</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white">
                    Dashboard Eksekutif Huling
                </h1>
                <p class="text-xs text-slate-300 mt-0.5 leading-relaxed hidden sm:block">
                    Monitoring gerobak bergerak real-time, audit keamanan QRIS, dan mitigasi <span class="text-orange-400 font-bold">dead stock</span> harian.
                </p>
            </div>

            <div class="flex items-center space-x-2 text-xs">
                <div class="bg-slate-800/90 border border-slate-700 px-3 py-1.5 rounded-xl flex items-center space-x-1.5">
                    <i class="fa-solid fa-motorcycle text-orange-400 text-xs"></i>
                    <span class="text-slate-300 text-[11px]">Armada:</span>
                    <span class="font-black text-white bg-orange-600 px-1.5 py-0.2 rounded-md text-[11px]">{{ $activeDriversCount }} Aktif</span>
                </div>

                <div class="bg-slate-800/90 border border-slate-700 px-3 py-1.5 rounded-xl flex items-center space-x-1.5">
                    <i class="fa-regular fa-calendar text-teal-400 text-xs"></i>
                    <span class="font-bold text-slate-200 text-[11px]">{{ now()->format('d M Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 4 Key Performance Indicators (Sleek 2x2 Grid on Mobile, 4-col on Desktop) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4">
        
        <!-- Total Valid Revenue -->
        <div class="bg-white p-3.5 sm:p-5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-1.5 sm:mb-2">
                    <span class="text-[10px] sm:text-[11px] uppercase font-extrabold text-slate-400 tracking-wider">Omset Sah</span>
                    <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-xs sm:text-sm">
                        <i class="fa-solid fa-coins"></i>
                    </div>
                </div>
                <p class="text-lg sm:text-2xl font-black text-slate-900 tracking-tight leading-tight">
                    Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                </p>
            </div>
            <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-500">
                <span>{{ $totalTransactions }} Transaksi</span>
                <span class="text-emerald-700 font-bold bg-emerald-50 px-1.5 py-0.2 rounded">Lunas</span>
            </div>
        </div>

        <!-- Cash In Hand -->
        <div class="bg-white p-3.5 sm:p-5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-1.5 sm:mb-2">
                    <span class="text-[10px] sm:text-[11px] uppercase font-extrabold text-slate-400 tracking-wider">Uang Tunai</span>
                    <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs sm:text-sm">
                        <i class="fa-solid fa-money-bill-wave"></i>
                    </div>
                </div>
                <p class="text-lg sm:text-2xl font-black text-emerald-600 tracking-tight leading-tight">
                    Rp {{ number_format($totalCash, 0, ',', '.') }}
                </p>
            </div>
            <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-500">
                <span>Uang Fisik</span>
                <span class="text-slate-800 font-black">Wajib Setor</span>
            </div>
        </div>

        <!-- QRIS Validated -->
        <div class="bg-white p-3.5 sm:p-5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-1.5 sm:mb-2">
                    <span class="text-[10px] sm:text-[11px] uppercase font-extrabold text-slate-400 tracking-wider">QRIS Sah</span>
                    <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-xs sm:text-sm">
                        <i class="fa-solid fa-qrcode"></i>
                    </div>
                </div>
                <p class="text-lg sm:text-2xl font-black text-teal-700 tracking-tight leading-tight">
                    Rp {{ number_format($totalQris, 0, ',', '.') }}
                </p>
            </div>
            <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-500">
                <span>{{ $qrisValidatedCount }} Sah</span>
                <span class="text-teal-700 font-bold bg-teal-50 px-1.5 py-0.2 rounded">Di Rekening</span>
            </div>
        </div>

        <!-- Fraud Shield & Pending QRIS -->
        <div class="bg-white p-3.5 sm:p-5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-1.5 sm:mb-2">
                    <span class="text-[10px] sm:text-[11px] uppercase font-extrabold text-slate-400 tracking-wider">Fraud QRIS</span>
                    <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xs sm:text-sm">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                </div>
                <div class="flex items-baseline space-x-1.5">
                    <span class="text-lg sm:text-2xl font-black text-rose-600 tracking-tight leading-tight">{{ $qrisRejectedCount }}</span>
                    <span class="text-[10px] font-bold text-rose-700 bg-rose-50 px-1.5 py-0.2 rounded">Ditolak</span>
                </div>
            </div>
            <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-[10px]">
                <span class="text-amber-700 font-bold truncate">
                    {{ $qrisPendingCount }} Pending
                </span>
                <a href="{{ route('admin.transactions', ['status' => 'pending']) }}" class="text-orange-600 font-bold hover:underline">Cek &rarr;</a>
            </div>
        </div>

    </div>

    <!-- Charts Row (Desktop: Side-by-Side, Mobile: Tab Switcher) -->
    <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs">
        
        <!-- Mobile Chart Switcher Pills -->
        <div class="flex md:hidden items-center justify-between mb-3 pb-2 border-b border-slate-100">
            <span class="text-[11px] font-black text-slate-400 uppercase tracking-wider">Visualisasi Grafik</span>
            <div class="flex items-center space-x-1 bg-slate-100 p-1 rounded-xl text-xs">
                <button type="button" @click="mobileChartTab = 'payment'"
                    :class="mobileChartTab === 'payment' ? 'bg-white text-slate-900 font-black shadow-xs' : 'text-slate-500 font-medium'"
                    class="px-2.5 py-1 rounded-lg text-[11px] transition">
                    Pembayaran
                </button>
                <button type="button" @click="mobileChartTab = 'location'"
                    :class="mobileChartTab === 'location' ? 'bg-white text-slate-900 font-black shadow-xs' : 'text-slate-500 font-medium'"
                    class="px-2.5 py-1 rounded-lg text-[11px] transition">
                    Lokasi GPS
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Payment Donut Chart -->
            <div :class="mobileChartTab === 'payment' ? 'block' : 'hidden md:block'" class="flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <h3 class="font-extrabold text-sm text-slate-900">Metode Bayar</h3>
                            <p class="text-[11px] text-slate-400">Tunai vs QRIS</p>
                        </div>
                        <span class="w-7 h-7 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 text-xs">
                            <i class="fa-solid fa-chart-pie"></i>
                        </span>
                    </div>

                    <div class="relative h-44 sm:h-48 flex items-center justify-center my-1">
                        <canvas id="paymentMethodChart"></canvas>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100 text-xs">
                    <div class="flex items-center space-x-1.5 text-[11px]">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <span class="text-slate-600 font-medium">Tunai:</span>
                        <span class="font-bold text-slate-900">{{ $totalRevenue > 0 ? round(($totalCash / $totalRevenue) * 100) : 0 }}%</span>
                    </div>
                    <div class="flex items-center space-x-1.5 text-[11px] justify-end">
                        <span class="w-2.5 h-2.5 rounded-full bg-teal-500"></span>
                        <span class="text-slate-600 font-medium">QRIS:</span>
                        <span class="font-bold text-slate-900">{{ $totalRevenue > 0 ? round(($totalQris / $totalRevenue) * 100) : 0 }}%</span>
                    </div>
                </div>
            </div>

            <!-- Location Bar Chart -->
            <div :class="mobileChartTab === 'location' ? 'block' : 'hidden md:block'" class="md:col-span-2 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <h3 class="font-extrabold text-sm text-slate-900">Performa Spot Lokasi (GPS Geotag)</h3>
                            <p class="text-[11px] text-slate-400">Omset per titik penjualan gerobak</p>
                        </div>
                        <a href="{{ route('admin.locations') }}" class="text-xs font-bold text-orange-600 hover:text-orange-700 bg-orange-50 px-2.5 py-1 rounded-xl">
                            Spot GPS &rarr;
                        </a>
                    </div>

                    <div class="relative h-48 sm:h-52">
                        <canvas id="locationSalesChart"></canvas>
                    </div>
                </div>

                <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                    <span class="flex items-center truncate">
                        <i class="fa-solid fa-map-pin text-orange-600 mr-1 text-xs"></i>
                        Radius geofence aktif
                    </span>
                    <span class="font-medium text-slate-700">Terekam otomatis</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Dead Stock & Best Sellers (Desktop: 2 Columns, Mobile: Switchable Tabs) -->
    <div>
        <!-- Mobile Section Switcher -->
        <div class="flex md:hidden items-center justify-between mb-3 bg-white p-2 rounded-2xl border border-slate-200 shadow-xs">
            <button type="button" @click="mobilePanelTab = 'deadstock'"
                :class="mobilePanelTab === 'deadstock' ? 'bg-orange-600 text-white font-black shadow-xs' : 'text-slate-600 font-bold'"
                class="flex-1 py-2 rounded-xl text-xs flex items-center justify-center space-x-1.5 transition">
                <i class="fa-solid fa-triangle-exclamation text-amber-300"></i>
                <span>Dead Stock</span>
            </button>
            <button type="button" @click="mobilePanelTab = 'bestsellers'"
                :class="mobilePanelTab === 'bestsellers' ? 'bg-orange-600 text-white font-black shadow-xs' : 'text-slate-600 font-bold'"
                class="flex-1 py-2 rounded-xl text-xs flex items-center justify-center space-x-1.5 transition">
                <i class="fa-solid fa-fire text-amber-300"></i>
                <span>Top 5 Terlaris</span>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">

            <!-- Panel 1: Dead Stock Mitigation -->
            <div :class="mobilePanelTab === 'deadstock' ? 'block' : 'hidden md:block'"
                class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                            </div>
                            <div>
                                <h3 class="font-black text-sm text-slate-900">Mitigasi Dead Stock</h3>
                                <p class="text-[11px] text-slate-400">Minuman sering bersisa di gerobak</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.dead-stock') }}" class="text-xs font-bold text-orange-600 hover:text-orange-700">Detail &rarr;</a>
                    </div>

                    <!-- Bottle Counters Summary Pill -->
                    <div class="bg-slate-50 p-2.5 rounded-xl mb-3 grid grid-cols-3 gap-1 text-center text-xs">
                        <div>
                            <span class="text-[9px] text-slate-400 uppercase font-bold block">Dimuat</span>
                            <span class="font-black text-slate-800 text-xs sm:text-sm">{{ $totalBottlesLoaded }} Btl</span>
                        </div>
                        <div>
                            <span class="text-[9px] text-emerald-600 uppercase font-bold block">Terjual</span>
                            <span class="font-black text-emerald-600 text-xs sm:text-sm">{{ $totalBottlesSold }} Btl</span>
                        </div>
                        <div>
                            <span class="text-[9px] text-rose-600 uppercase font-bold block">Sisa (Unsold)</span>
                            <span class="font-black text-rose-600 text-xs sm:text-sm">{{ $totalBottlesUnsold }} Btl</span>
                        </div>
                    </div>

                    <div class="space-y-2.5">
                        @forelse($deadStockRanking->take(4) as $ds)
                            <div class="p-3 bg-slate-50/70 border border-slate-100 rounded-xl flex items-center justify-between">
                                <div class="flex-1 pr-3">
                                    <div class="flex items-center justify-between mb-1">
                                        <h5 class="font-bold text-xs text-slate-900 truncate max-w-[140px]">{{ $ds->product->name ?? 'Minuman' }}</h5>
                                        <span class="text-[10px] font-black {{ $ds->sell_through_rate < 50 ? 'text-rose-600' : 'text-slate-700' }}">
                                            {{ $ds->sell_through_rate }}% Laku
                                        </span>
                                    </div>
                                    <div class="w-full bg-slate-200 h-1.5 rounded-full overflow-hidden">
                                        <div class="h-1.5 rounded-full {{ $ds->sell_through_rate < 40 ? 'bg-rose-500' : ($ds->sell_through_rate < 70 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                                            style="width: {{ $ds->sell_through_rate }}%"></div>
                                    </div>
                                    <div class="flex justify-between text-[9px] text-slate-400 mt-0.5">
                                        <span>Bawa: {{ $ds->total_loaded }}</span>
                                        <span class="font-bold text-rose-600">Sisa: {{ $ds->total_unsold }} botol</span>
                                    </div>
                                </div>

                                <div>
                                    @if($ds->sell_through_rate < 40)
                                        <span class="bg-rose-100 text-rose-800 font-extrabold text-[9px] px-2 py-0.5 rounded-lg whitespace-nowrap block text-center">
                                            Kurangi Muatan
                                        </span>
                                    @else
                                        <span class="bg-amber-100 text-amber-800 font-bold text-[9px] px-2 py-0.5 rounded-lg whitespace-nowrap block text-center">
                                            Optimal
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 text-center py-4">Belum ada inventori shift tercatat.</p>
                        @endforelse
                    </div>
                </div>

                <div class="mt-3 pt-2.5 border-t border-slate-100 text-[11px] text-slate-500 flex items-center space-x-1.5">
                    <i class="fa-solid fa-lightbulb text-amber-500"></i>
                    <span>Kurangi alokasi produk bersisa banyak agar driver tidak kena potongan gaji.</span>
                </div>
            </div>

            <!-- Panel 2: Best Selling Beverages -->
            <div :class="mobilePanelTab === 'bestsellers' ? 'block' : 'hidden md:block'"
                class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-sm">
                                <i class="fa-solid fa-fire"></i>
                            </div>
                            <div>
                                <h3 class="font-black text-sm text-slate-900">Produk Terlaris (Best Sellers)</h3>
                                <p class="text-[11px] text-slate-400">Penyumbang omset terbesar armada</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.products') }}" class="text-xs font-bold text-orange-600 hover:text-orange-700">Katalog &rarr;</a>
                    </div>

                    <div class="space-y-2.5">
                        @forelse($topProducts as $tp)
                            <div class="p-3 bg-slate-50/70 border border-slate-100 rounded-xl flex items-center justify-between">
                                <div class="flex items-center space-x-2.5">
                                    <div class="w-7 h-7 rounded-lg {{ $loop->first ? 'bg-orange-500 text-white font-black' : 'bg-slate-200 text-slate-700 font-bold' }} flex items-center justify-center text-xs">
                                        #{{ $loop->iteration }}
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-xs text-slate-900">{{ $tp->product->name ?? 'Minuman' }}</h4>
                                        <span class="text-[10px] text-slate-400">Rp {{ number_format($tp->product->price ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                </div>

                                <div class="text-right">
                                    <span class="font-black text-xs text-slate-900 block">{{ $tp->total_qty }} Botol Laku</span>
                                    <span class="text-[10px] font-bold text-orange-600">Rp {{ number_format($tp->total_sales, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 text-center py-4">Belum ada transaksi penjualan yang sah.</p>
                        @endforelse
                    </div>
                </div>

                <div class="mt-3 pt-2.5 border-t border-slate-100 text-[11px] text-slate-500 flex items-center space-x-1.5">
                    <i class="fa-solid fa-arrow-trend-up text-emerald-600"></i>
                    <span>Jaga stok botol best seller selalu penuh di setiap gerobak sebelum berangkat.</span>
                </div>
            </div>

        </div>
    </div>

    <!-- Active Drivers Armada Today (Compact Mobile Cards) -->
    @if($todayShifts->isNotEmpty())
        <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <h3 class="font-black text-sm text-slate-900">Armada Gerobak Aktif Hari Ini ({{ $todayShifts->count() }})</h3>
                </div>
                <a href="{{ route('admin.shifts') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800">
                    Semua &rarr;
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 sm:gap-3">
                @foreach($todayShifts as $sh)
                    <div class="p-3 sm:p-4 bg-slate-50 rounded-xl sm:rounded-2xl border border-slate-200/60 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-xs font-black text-slate-900">{{ $sh->user->name ?? 'Driver' }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold {{ $sh->isOpen() ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' }}">
                                    {{ strtoupper($sh->status) }}
                                </span>
                            </div>
                            <div class="flex justify-between text-[11px] text-slate-500">
                                <span>Mulai: {{ $sh->started_at ? $sh->started_at->format('H:i') : '-' }} WIB</span>
                                <span>Muatan: {{ $sh->dailyInventories->sum('start_qty') }} Botol</span>
                            </div>
                        </div>

                        <div class="mt-2.5 pt-2 border-t border-slate-200 flex items-center justify-between text-xs font-black">
                            <span class="text-slate-400 text-[10px]">Omset Shift:</span>
                            <span class="text-orange-600 font-extrabold">Rp {{ number_format($sh->totalCash() + $sh->totalQrisValidated(), 0, ',', '.') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Recent Transactions: Desktop Table vs Mobile Cards -->
    <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 overflow-hidden shadow-xs">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-clock-rotate-left text-orange-600 text-sm"></i>
                    <h3 class="font-black text-sm text-slate-900">Transaksi Terakhir</h3>
                </div>
                <p class="text-[11px] text-slate-400 mt-0.5">Audit log jam transaksi dan status verifikasi</p>
            </div>

            <a href="{{ route('admin.transactions') }}" class="text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-xl transition">
                Arsip &rarr;
            </a>
        </div>

        <!-- 1. MOBILE TRANSACTIONS VIEW: Clean Cards (< md screens) -->
        <div class="block md:hidden divide-y divide-slate-100">
            @forelse($recentTransactions as $t)
                <div class="p-3.5 hover:bg-slate-50 cursor-pointer active:scale-[0.99] transition flex flex-col space-y-1.5"
                    @click="selectedTrx = {{ json_encode($t) }}">
                    
                    <div class="flex items-center justify-between text-xs">
                        <div class="flex items-center space-x-1.5">
                            <span class="font-mono font-bold text-slate-800 text-[11px]">{{ $t->invoice_number }}</span>
                            <span class="bg-orange-50 text-orange-700 font-bold text-[9px] px-1.5 py-0.2 rounded border border-orange-200">
                                {{ $t->transacted_at->format('H:i:s') }} WIB
                            </span>
                        </div>
                        <span class="font-black text-slate-900 text-sm">
                            Rp {{ number_format($t->total_amount, 0, ',', '.') }}
                        </span>
                    </div>

                    <!-- Items Summary -->
                    <p class="text-xs text-slate-600 font-medium line-clamp-1">
                        {{ $t->details->map(fn($d) => $d->qty . 'x ' . ($d->product->name ?? 'Minuman'))->join(', ') }}
                    </p>

                    <div class="flex items-center justify-between text-[10px] text-slate-400 pt-1">
                        <div class="flex items-center space-x-2">
                            <span><i class="fa-solid fa-user text-slate-400 mr-0.5"></i>{{ $t->shift->user->name ?? 'Driver' }}</span>
                            @if($t->location_name)
                                <span>•</span>
                                <span class="text-slate-600 font-medium truncate max-w-[110px]">
                                    <i class="fa-solid fa-location-dot text-orange-600 mr-0.5"></i>{{ $t->location_name }}
                                </span>
                            @endif
                        </div>

                        <div>
                            @if($t->payment_status === 'paid' || $t->payment_status === 'validated')
                                <span class="text-emerald-700 font-bold bg-emerald-50 px-1.5 py-0.2 rounded">
                                    {{ strtoupper($t->payment_method) }}: LUNAS
                                </span>
                            @elseif($t->payment_status === 'pending')
                                <span class="text-amber-700 font-bold bg-amber-50 px-1.5 py-0.2 rounded animate-pulse">
                                    QRIS PENDING
                                </span>
                            @else
                                <span class="text-rose-700 font-bold bg-rose-50 px-1.5 py-0.2 rounded">
                                    FRAUD DITOLAK
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-xs text-slate-400">Belum ada transaksi terekam.</div>
            @endforelse
        </div>

        <!-- 2. DESKTOP TRANSACTIONS VIEW: Wide Table (>= md screens) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider text-[10px] border-b border-slate-100">
                    <tr>
                        <th class="p-3.5">Waktu Pembelian</th>
                        <th class="p-3.5">Invoice</th>
                        <th class="p-3.5">Driver</th>
                        <th class="p-3.5">Titik Lokasi (GPS)</th>
                        <th class="p-3.5">Item Botol</th>
                        <th class="p-3.5">Metode</th>
                        <th class="p-3.5">Status</th>
                        <th class="p-3.5 text-right">Total Transaksi</th>
                        <th class="p-3.5 text-center">Struk</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentTransactions as $t)
                        <tr class="hover:bg-slate-50/80 cursor-pointer transition"
                            @click="selectedTrx = {{ json_encode($t) }}">
                            
                            <td class="p-3.5 whitespace-nowrap">
                                <span class="font-extrabold text-slate-900 block text-xs">{{ $t->transacted_at->format('H:i:s') }} WIB</span>
                                <span class="text-[10px] text-slate-400">{{ $t->transacted_at->format('d M Y') }}</span>
                            </td>

                            <td class="p-3.5 font-mono font-bold text-slate-700 whitespace-nowrap">
                                <span class="bg-slate-100 px-2 py-1 rounded-lg">{{ $t->invoice_number }}</span>
                            </td>

                            <td class="p-3.5 font-semibold text-slate-900 whitespace-nowrap">
                                {{ $t->shift->user->name ?? '-' }}
                            </td>

                            <td class="p-3.5 text-slate-600">
                                @if($t->location_name)
                                    <span class="inline-flex items-center font-medium bg-orange-50 text-orange-800 px-2 py-0.5 rounded-lg text-[11px]">
                                        <i class="fa-solid fa-location-dot text-orange-600 mr-1 text-[10px]"></i>
                                        {{ $t->location_name }}
                                    </span>
                                @else
                                    <span class="text-slate-400 italic">Area Terbuka</span>
                                @endif
                            </td>

                            <td class="p-3.5 text-slate-700 max-w-xs truncate">
                                {{ $t->details->map(fn($d) => $d->qty . 'x ' . ($d->product->name ?? 'Minuman'))->join(', ') }}
                            </td>

                            <td class="p-3.5 whitespace-nowrap">
                                <span class="uppercase font-extrabold text-[10px] px-2 py-0.5 rounded-lg {{ $t->payment_method === 'cash' ? 'bg-emerald-100 text-emerald-800' : 'bg-orange-100 text-orange-800' }}">
                                    {{ $t->payment_method }}
                                </span>
                            </td>

                            <td class="p-3.5 whitespace-nowrap">
                                @if($t->payment_status === 'paid' || $t->payment_status === 'validated')
                                    <span class="bg-emerald-100 text-emerald-800 font-extrabold text-[10px] px-2 py-0.5 rounded-lg inline-flex items-center">
                                        <i class="fa-solid fa-check mr-1"></i> Sah
                                    </span>
                                @elseif($t->payment_status === 'pending')
                                    <span class="bg-amber-100 text-amber-800 font-extrabold text-[10px] px-2 py-0.5 rounded-lg inline-flex items-center animate-pulse">
                                        <i class="fa-solid fa-clock mr-1"></i> Pending QRIS
                                    </span>
                                @else
                                    <span class="bg-rose-100 text-rose-800 font-extrabold text-[10px] px-2 py-0.5 rounded-lg inline-flex items-center">
                                        <i class="fa-solid fa-ban mr-1"></i> Fraud Ditolak
                                    </span>
                                @endif
                            </td>

                            <td class="p-3.5 text-right font-black text-slate-900 whitespace-nowrap">
                                Rp {{ number_format($t->total_amount, 0, ',', '.') }}
                            </td>

                            <td class="p-3.5 text-center whitespace-nowrap">
                                <button type="button" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-orange-600 hover:text-white text-slate-600 flex items-center justify-center transition mx-auto">
                                    <i class="fa-regular fa-file-lines text-xs"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-8 text-center text-slate-400">Belum ada transaksi terekam di sistem.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Digital Receipt Detail Modal (Touch-Friendly) -->
    <div x-show="selectedTrx" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" @click="selectedTrx = null"></div>

        <div class="min-h-full flex items-center justify-center p-3 sm:p-4">
            <div class="relative bg-white w-full max-w-sm sm:max-w-md rounded-2xl sm:rounded-3xl p-5 sm:p-6 shadow-2xl max-h-[90vh] overflow-y-auto no-scrollbar" @click.stop>
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center space-x-2">
                        <span class="bg-orange-600 text-white font-black text-xs px-2.5 py-0.5 rounded-lg tracking-wider">HAUS!</span>
                        <h3 class="font-extrabold text-sm text-slate-900">Rincian Struk Pembelian</h3>
                    </div>
                    <button type="button" @click="selectedTrx = null" class="w-7 h-7 rounded-full bg-slate-100 text-slate-400 hover:text-slate-700 flex items-center justify-center">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                </div>

                <div class="py-3.5 space-y-2 text-xs border-b border-slate-100">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Nomor Invoice:</span>
                        <span class="font-mono font-bold text-slate-900" x-text="selectedTrx?.invoice_number"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Waktu Pembelian:</span>
                        <span class="font-bold text-orange-600" x-text="selectedTrx?.transacted_at ? new Date(selectedTrx.transacted_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB' : '-'"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Tanggal:</span>
                        <span class="font-semibold text-slate-800" x-text="selectedTrx?.transacted_at ? new Date(selectedTrx.transacted_at).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) : '-'"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Driver Pelayan:</span>
                        <span class="font-semibold text-slate-800" x-text="selectedTrx?.shift?.user?.name || '-'"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Titik Lokasi:</span>
                        <span class="font-semibold text-slate-800" x-text="selectedTrx?.location_name || 'Area Terbuka'"></span>
                    </div>
                    <template x-if="selectedTrx?.latitude && selectedTrx?.longitude">
                        <div class="flex justify-between items-center bg-slate-50 p-2 rounded-xl border border-slate-100">
                            <span class="text-slate-400 text-[10px]">Titik GPS HP:</span>
                            <a :href="'https://www.google.com/maps?q=' + selectedTrx?.latitude + ',' + selectedTrx?.longitude"
                                target="_blank" class="text-orange-600 font-bold text-[10px] hover:underline flex items-center space-x-1">
                                <i class="fa-solid fa-map-location-dot"></i>
                                <span>Buka Google Maps &rarr;</span>
                            </a>
                        </div>
                    </template>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Metode & Status:</span>
                        <span class="font-extrabold uppercase" :class="selectedTrx?.payment_status === 'paid' || selectedTrx?.payment_status === 'validated' ? 'text-emerald-600' : 'text-amber-600'"
                            x-text="(selectedTrx?.payment_method || '') + ' • ' + (selectedTrx?.payment_status || '')"></span>
                    </div>
                </div>

                <!-- Items Breakdown -->
                <div class="py-3 border-b border-slate-100 text-xs">
                    <p class="font-bold text-slate-700 mb-2">Item Minuman Terjual:</p>
                    <div class="space-y-1.5">
                        <template x-for="item in selectedTrx?.details" :key="item.id">
                            <div class="flex justify-between items-center bg-slate-50 p-2 rounded-xl border border-slate-100">
                                <div>
                                    <span class="font-bold text-slate-900" x-text="item.product?.name || 'Minuman'"></span>
                                    <span class="text-slate-400 block text-[10px]" x-text="item.qty + ' botol × Rp ' + (item.unit_price || 0).toLocaleString('id-ID')"></span>
                                </div>
                                <span class="font-black text-slate-900" x-text="'Rp ' + (item.subtotal || 0).toLocaleString('id-ID')"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="pt-3 flex justify-between items-center text-sm font-black text-slate-900">
                    <span>TOTAL TRANSAKSI:</span>
                    <span class="text-base text-orange-600" x-text="'Rp ' + (selectedTrx?.total_amount || 0).toLocaleString('id-ID')"></span>
                </div>

                <div class="mt-4">
                    <button type="button" @click="selectedTrx = null"
                        class="w-full py-2.5 bg-slate-900 hover:bg-black text-white font-black text-xs rounded-xl shadow-xs transition">
                        Tutup Rincian
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Payment Method Donut Chart
        const paymentCtx = document.getElementById('paymentMethodChart');
        if (paymentCtx) {
            new Chart(paymentCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Tunai (Cash)', 'QRIS Sah', 'QRIS Pending'],
                    datasets: [{
                        data: [
                            {{ $totalCash }},
                            {{ $totalQris }},
                            {{ \App\Models\Transaction::where('payment_method', 'qris')->where('payment_status', 'pending')->sum('total_amount') }}
                        ],
                        backgroundColor: ['#10b981', '#14b8a6', '#f59e0b'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    cutout: '70%'
                }
            });
        }

        // 2. Sales by Location Horizontal Bar Chart
        const locationCtx = document.getElementById('locationSalesChart');
        if (locationCtx) {
            const locNames = {!! json_encode($locationSales->pluck('location_name')) !!};
            const locRevenues = {!! json_encode($locationSales->pluck('total_revenue')) !!};

            new Chart(locationCtx, {
                type: 'bar',
                data: {
                    labels: locNames.length ? locNames : ['Belum ada lokasi'],
                    datasets: [{
                        label: 'Total Omset (Rp)',
                        data: locRevenues.length ? locRevenues : [0],
                        backgroundColor: '#ea580c',
                        borderRadius: 6,
                        barThickness: 14
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                font: { size: 9 },
                                callback: function(value) {
                                    return 'Rp ' + (value / 1000) + 'k';
                                }
                            }
                        },
                        y: {
                            grid: { display: false },
                            ticks: { font: { size: 10, weight: 'bold' } }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
@endsection
