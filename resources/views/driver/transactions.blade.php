@extends('layouts.app')

@section('title', 'Riwayat Semua Transaksi')

@section('content')
<div class="max-w-md mx-auto" x-data="{ selectedTrx: null }">

    <!-- Header Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 mb-4 shadow-xs">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Arsip Penjualan Driver</span>
            <span class="bg-orange-100 text-orange-800 text-[10px] font-bold px-2 py-0.5 rounded-full">
                {{ $transactions->total() }} Total Transaksi
            </span>
        </div>
        <h1 class="text-lg font-black text-slate-900">Riwayat Transaksi Lengkap</h1>
        <p class="text-xs text-slate-500 mt-0.5">Pantau seluruh riwayat transaksi per shift dan tanggal penjualan.</p>

        <!-- KPI Summary for current filter -->
        <div class="grid grid-cols-2 gap-2 mt-4 pt-3 border-t border-slate-100">
            <div class="bg-slate-50 p-2.5 rounded-xl">
                <span class="text-[10px] font-semibold text-slate-400 uppercase block">Total Omset Valid</span>
                <span class="text-sm font-black text-slate-900">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
            </div>
            <div class="bg-emerald-50 p-2.5 rounded-xl">
                <span class="text-[10px] font-semibold text-emerald-700 uppercase block">Total Bersih Insentif</span>
                <span class="text-sm font-black text-emerald-700">+Rp {{ number_format($totalAllTimeNetTakeHome, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Pilihan Arsip Shift Driver (Bisa Lihat Shift Lampau Maupun Hari Ini) -->
    @if(isset($userShifts) && $userShifts->count() > 1)
        <div class="mb-4">
            <div class="flex items-center justify-between mb-1.5 px-1">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pilih Shift yang Ingin Dilihat</span>
                <span class="text-[10px] text-slate-500 font-medium">{{ $userShifts->count() }} Shift Tercatat</span>
            </div>
            <div class="flex items-center space-x-1.5 overflow-x-auto no-scrollbar pb-1">
                @foreach($userShifts as $s)
                    <a href="{{ route('driver.transactions', ['shift_id' => $s->id]) }}"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition border flex items-center space-x-1.5 {{ (isset($featuredShift) && $featuredShift->id === $s->id) ? 'bg-slate-900 text-white border-slate-900 shadow-xs' : 'bg-white text-slate-700 border-slate-200 hover:border-slate-300' }}">
                        <span>{{ $s->shift_date->format('d M') }}</span>
                        <span class="text-[9px] px-1.5 py-0.2 rounded font-black {{ $s->isOpen() ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-700' }}">
                            {{ $s->isOpen() ? 'OPEN' : 'CLOSED' }}
                        </span>
                        @if($s->isNombok())
                            <span class="text-rose-400 text-[10px]">• Nombok</span>
                        @else
                            <span class="text-emerald-400 text-[10px]">• +{{ number_format($s->netTakeHomeIncentive(), 0, ',', '.') }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Kartu Riwayat Insentif & Status Nombok (TETAP BISA DILIHAT SETELAH TUTUP SHIFT) -->
    @if(isset($featuredShift))
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs mb-4">
            <!-- Header Kartu Shift -->
            <div class="p-3.5 bg-gradient-to-r from-orange-500 to-amber-500 text-white flex items-center justify-between">
                <div>
                    <div class="flex items-center space-x-1.5">
                        <span class="font-black text-xs">Shift: {{ $featuredShift->shift_date->translatedFormat('d M Y') }}</span>
                        <span class="text-[9px] px-2 py-0.2 rounded-full font-black uppercase {{ $featuredShift->isOpen() ? 'bg-emerald-400 text-emerald-950' : 'bg-black/30 text-white' }}">
                            {{ $featuredShift->isOpen() ? 'Aktif' : 'Tutup (' . ($featuredShift->ended_at ? $featuredShift->ended_at->format('H:i') : '') . ' WIB)' }}
                        </span>
                    </div>
                    <p class="text-[10px] text-orange-100 mt-0.5">
                        Mulai: {{ $featuredShift->started_at ? $featuredShift->started_at->format('H:i') : '-' }} WIB
                    </p>
                </div>
                <a href="{{ route('driver.recap', ['shift_id' => $featuredShift->id]) }}"
                    class="bg-white/20 hover:bg-white/30 text-white font-bold text-[10px] px-2.5 py-1.5 rounded-lg transition flex items-center space-x-1">
                    <span>Audit Stok</span>
                    <i class="fa-solid fa-arrow-right text-[9px]"></i>
                </a>
            </div>

            <!-- Target Progress Mini -->
            <div class="px-4 pt-3 pb-1">
                <div class="flex justify-between items-center text-[11px] font-bold text-slate-700 mb-1">
                    <span>Target: {{ $featuredShift->totalSoldQty() }} / {{ $featuredShift->target_qty ?? 40 }} Cup Terjual</span>
                    <span class="text-orange-600">{{ $featuredShift->targetProgressPercentage() }}%</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                    <div class="bg-orange-500 h-2 rounded-full" style="width: {{ $featuredShift->targetProgressPercentage() }}%"></div>
                </div>
            </div>

            <!-- Kalkulator Insentif vs Produk Sisa -->
            <div class="p-4 space-y-3">
                <div class="bg-slate-50 rounded-xl p-3 border border-slate-200 text-xs space-y-2">
                    <!-- 1. Total Insentif -->
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="font-bold text-slate-800 block">1. Total Insentif Terkumpul (+)</span>
                            <span class="text-[10px] text-slate-500">{{ $featuredShift->totalSoldQty() }} cup terjual × Rp 1.000</span>
                        </div>
                        <span class="text-sm font-black text-emerald-600">+Rp {{ number_format($featuredShift->totalIncentive(), 0, ',', '.') }}</span>
                    </div>

                    <!-- 2. Penggantian Produk Belum Habis -->
                    <div class="flex items-center justify-between pt-2 border-t border-slate-200/60">
                        <div>
                            <span class="font-bold text-rose-700 block">2. Penggantian Produk Belum Habis (-)</span>
                            <span class="text-[10px] text-slate-500">{{ $featuredShift->totalUnsoldQty() }} botol sisa muatan</span>
                        </div>
                        <span class="text-xs font-black text-rose-600">-Rp {{ number_format($featuredShift->totalUnsoldValue(), 0, ',', '.') }}</span>
                    </div>

                    <!-- 3. Garis Total Hasil Akhir -->
                    <div class="flex items-center justify-between pt-2 border-t-2 border-dashed border-slate-300">
                        <div>
                            <span class="font-black uppercase text-[11px] {{ $featuredShift->isNombok() ? 'text-rose-700' : 'text-emerald-700' }}">
                                {{ $featuredShift->isNombok() ? 'POSISI AKHIR: NOMBOK' : 'POSISI AKHIR: SISA INSENTIF BERSIH' }}
                            </span>
                            <span class="text-[10px] text-slate-500 block">Insentif dikurangi penggantian sisa</span>
                        </div>
                        <div class="text-right">
                            @if($featuredShift->isNombok())
                                <span class="text-base font-black text-rose-600">-Rp {{ number_format($featuredShift->nombokAmount(), 0, ',', '.') }}</span>
                                <span class="block text-[9px] font-bold text-rose-500">TANGGUNGAN NOMBOK</span>
                            @else
                                <span class="text-base font-black text-emerald-600">+Rp {{ number_format($featuredShift->netTakeHomeIncentive(), 0, ',', '.') }}</span>
                                <span class="block text-[9px] font-bold text-emerald-600">BAWA PULANG KANTONG</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Kartu Status -->
                @if(!$featuredShift->isNombok())
                    <div class="p-3 bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-200 rounded-xl">
                        <div class="flex items-center space-x-1.5 text-emerald-800 font-extrabold text-xs mb-1">
                            <i class="fa-solid fa-shield-heart text-emerald-600"></i>
                            <span>ZONA AMAN BEBAS NOMBOK! 🎉</span>
                        </div>
                        <p class="text-[11px] text-emerald-700 font-medium">
                            Insentif penjualanmu (<strong>Rp {{ number_format($featuredShift->totalIncentive(), 0, ',', '.') }}</strong>) berhasil menutup seluruh penggantian {{ $featuredShift->totalUnsoldQty() }} botol sisa (<strong>Rp {{ number_format($featuredShift->totalUnsoldValue(), 0, ',', '.') }}</strong>).
                        </p>
                        <div class="mt-2 p-2 bg-emerald-100/60 rounded-lg flex items-center justify-between">
                            <span class="text-xs text-emerald-800 font-bold">Uang Insentif Bersihmu:</span>
                            <span class="text-sm font-black text-emerald-700">Rp {{ number_format($featuredShift->netTakeHomeIncentive(), 0, ',', '.') }}</span>
                        </div>
                    </div>
                @else
                    <div class="p-3 bg-gradient-to-br from-amber-50 to-rose-50 border border-amber-300 rounded-xl">
                        <div class="flex items-center justify-between mb-1">
                            <div class="flex items-center space-x-1.5 text-rose-800 font-black text-xs">
                                <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                                <span>STATUS: TANGGUNGAN NOMBOK Rp {{ number_format($featuredShift->nombokAmount(), 0, ',', '.') }}</span>
                            </div>
                            <span class="bg-rose-100 text-rose-700 text-[9px] font-black px-1.5 py-0.5 rounded">
                                Minus
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-700 font-medium">
                            Insentif <strong>Rp {{ number_format($featuredShift->totalIncentive(), 0, ',', '.') }}</strong> memotong nilai sisa botol <strong>Rp {{ number_format($featuredShift->totalUnsoldValue(), 0, ',', '.') }}</strong>. Sisa tanggungan: <strong>Rp {{ number_format($featuredShift->nombokAmount(), 0, ',', '.') }}</strong>.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Filter Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-3.5 mb-4 shadow-xs">
        <form action="{{ route('driver.transactions') }}" method="GET" class="space-y-2.5">
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Filter Tanggal Shift</label>
                <div class="flex space-x-2">
                    <input type="date" name="date" value="{{ $selectedDate }}"
                        class="flex-1 text-xs bg-slate-50 border border-slate-200 rounded-xl p-2 font-medium outline-none focus:ring-2 focus:ring-orange-500">
                    <button type="submit"
                        class="px-4 py-2 bg-slate-900 hover:bg-black text-white text-xs font-bold rounded-xl transition">
                        Terapkan
                    </button>
                    @if($selectedDate || $selectedPayment || $selectedStatus)
                        <a href="{{ route('driver.transactions') }}"
                            class="px-2.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl flex items-center justify-center"
                            title="Reset Filter">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Quick Filter Presets -->
            <div class="flex items-center space-x-1.5 overflow-x-auto no-scrollbar pt-1">
                <a href="{{ route('driver.transactions') }}"
                    class="px-2.5 py-1 rounded-lg text-[10px] font-bold whitespace-nowrap {{ !$selectedDate && !$selectedPayment ? 'bg-orange-600 text-white' : 'bg-slate-100 text-slate-600' }}">
                    Semua Waktu
                </a>
                <a href="{{ route('driver.transactions', ['date' => now()->toDateString()]) }}"
                    class="px-2.5 py-1 rounded-lg text-[10px] font-bold whitespace-nowrap {{ $selectedDate === now()->toDateString() ? 'bg-orange-600 text-white' : 'bg-slate-100 text-slate-600' }}">
                    Hari Ini ({{ now()->format('d M') }})
                </a>
                <a href="{{ route('driver.transactions', array_merge(request()->query(), ['payment_method' => 'cash'])) }}"
                    class="px-2.5 py-1 rounded-lg text-[10px] font-bold whitespace-nowrap {{ $selectedPayment === 'cash' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600' }}">
                    💵 Tunai
                </a>
                <a href="{{ route('driver.transactions', array_merge(request()->query(), ['payment_method' => 'qris'])) }}"
                    class="px-2.5 py-1 rounded-lg text-[10px] font-bold whitespace-nowrap {{ $selectedPayment === 'qris' ? 'bg-teal-600 text-white' : 'bg-slate-100 text-slate-600' }}">
                    📱 QRIS
                </a>
            </div>
        </form>
    </div>

    <!-- Transactions List -->
    @if($transactions->isEmpty())
        <div class="bg-white border border-slate-200 rounded-2xl p-8 text-center mb-6">
            <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2 text-xl">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <h4 class="font-bold text-sm text-slate-800">Tidak Ada Transaksi</h4>
            <p class="text-xs text-slate-400 mt-1">Belum ada transaksi ditemukan untuk filter ini.</p>
        </div>
    @else
        <div class="space-y-2.5 mb-6">
            @foreach($transactions as $t)
                <div class="bg-white rounded-2xl border border-slate-200/80 p-3.5 shadow-xs hover:border-orange-300 cursor-pointer transition active:scale-[0.99]"
                    @click="selectedTrx = {{ json_encode($t) }}">
                    
                    <div class="flex items-center justify-between mb-1.5">
                        <div class="flex items-center space-x-1.5">
                            <span class="font-mono text-xs font-black text-slate-800">{{ $t->invoice_number }}</span>
                            <span class="bg-orange-50 text-orange-700 font-bold text-[10px] px-1.5 py-0.2 rounded border border-orange-200">
                                <i class="fa-regular fa-clock mr-0.5"></i> {{ $t->transacted_at->format('H:i:s') }} WIB
                            </span>
                        </div>
                        <span class="text-[10px] text-slate-400 font-medium">
                            {{ $t->transacted_at->format('d M Y') }}
                        </span>
                    </div>

                    <!-- Items Summary -->
                    <p class="text-xs text-slate-700 font-medium line-clamp-1 mb-2">
                        {{ $t->details->map(fn($d) => $d->qty . 'x ' . ($d->product->name ?? 'Minuman'))->join(', ') }}
                    </p>

                    <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-xs">
                        <div class="flex items-center space-x-2">
                            @if($t->payment_method === 'cash')
                                <span class="bg-emerald-100 text-emerald-800 font-bold text-[10px] px-2 py-0.5 rounded">
                                    TUNAI
                                </span>
                            @else
                                <span class="font-bold text-[10px] px-2 py-0.5 rounded {{ $t->payment_status === 'validated' ? 'bg-teal-100 text-teal-800' : ($t->payment_status === 'pending' ? 'bg-amber-100 text-amber-800 animate-pulse' : 'bg-rose-100 text-rose-800') }}">
                                    QRIS ({{ strtoupper($t->payment_status) }})
                                </span>
                            @endif

                            @if($t->location_name)
                                <span class="text-[10px] text-slate-500 truncate max-w-[120px]">
                                    <i class="fa-solid fa-location-dot text-orange-600 mr-0.5"></i> {{ $t->location_name }}
                                </span>
                            @endif
                        </div>

                        <span class="font-black text-sm text-slate-900">
                            Rp {{ number_format($t->total_amount, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        @if($transactions->hasPages())
            <div class="mb-6">
                {{ $transactions->links() }}
            </div>
        @endif
    @endif

    <!-- Digital Receipt Detail Modal -->
    <div x-show="selectedTrx" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" @click="selectedTrx = null"></div>

        <div class="min-h-full flex items-center justify-center p-4">
            <div class="relative bg-white w-full max-w-sm rounded-2xl p-5 shadow-2xl" @click.stop>
                <div class="text-center pb-3 border-b border-dashed border-slate-200">
                    <img src="{{ asset('images/hacker-logo-hd.png') }}" alt="HACKER" class="w-32 h-auto mx-auto object-contain mb-1">
                    <h4 class="font-black text-sm text-slate-900 mt-1">Struk Penjualan Digital</h4>
                    <p class="font-mono text-xs text-slate-500 mt-0.5" x-text="selectedTrx?.invoice_number"></p>
                </div>

                <div class="py-3 text-xs space-y-1.5 border-b border-dashed border-slate-200">
                    <div class="flex justify-between text-slate-500">
                        <span>Waktu Pembelian:</span>
                        <span class="font-bold text-slate-800" x-text="selectedTrx?.transacted_at ? new Date(selectedTrx.transacted_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB' : '-'"></span>
                    </div>
                    <div class="flex justify-between text-slate-500">
                        <span>Tanggal:</span>
                        <span class="font-semibold text-slate-800" x-text="selectedTrx?.transacted_at ? new Date(selectedTrx.transacted_at).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) : '-'"></span>
                    </div>
                    <div class="flex justify-between text-slate-500">
                        <span>Titik Lokasi:</span>
                        <span class="font-semibold text-slate-800" x-text="selectedTrx?.location_name || 'Area Penjualan Keliling'"></span>
                    </div>
                    <template x-if="selectedTrx?.latitude && selectedTrx?.longitude">
                        <div class="flex justify-between items-center bg-slate-50 p-2 rounded-lg">
                            <span class="text-slate-500 text-[10px]">Peta GPS:</span>
                            <a :href="'https://www.google.com/maps?q=' + selectedTrx?.latitude + ',' + selectedTrx?.longitude"
                                target="_blank" class="text-orange-600 font-bold text-[10px] hover:underline flex items-center space-x-1">
                                <i class="fa-solid fa-map-pin"></i>
                                <span>Buka Google Maps &rarr;</span>
                            </a>
                        </div>
                    </template>
                    <div class="flex justify-between text-slate-500">
                        <span>Metode Bayar:</span>
                        <span class="font-bold uppercase" :class="selectedTrx?.payment_method === 'cash' ? 'text-emerald-600' : 'text-orange-600'" x-text="selectedTrx?.payment_method"></span>
                    </div>
                    <div class="flex justify-between text-slate-500">
                        <span>Status Bayar:</span>
                        <span class="font-bold uppercase" :class="selectedTrx?.payment_status === 'paid' || selectedTrx?.payment_status === 'validated' ? 'text-emerald-600' : (selectedTrx?.payment_status === 'pending' ? 'text-amber-600' : 'text-rose-600')" x-text="selectedTrx?.payment_status"></span>
                    </div>
                </div>

                <!-- Items Breakdown List -->
                <div class="py-3 border-b border-dashed border-slate-200 text-xs space-y-1.5">
                    <template x-for="item in selectedTrx?.details" :key="item.id">
                        <div class="flex justify-between items-center">
                            <div>
                                <span class="font-bold text-slate-800" x-text="item.product?.name || 'Minuman'"></span>
                                <span class="text-slate-400 block text-[10px]" x-text="item.qty + ' × Rp ' + (item.unit_price || 0).toLocaleString('id-ID')"></span>
                            </div>
                            <span class="font-bold text-slate-900" x-text="'Rp ' + (item.subtotal || 0).toLocaleString('id-ID')"></span>
                        </div>
                    </template>
                </div>

                <div class="pt-3 flex justify-between items-center text-sm font-black text-slate-900">
                    <span>TOTAL DITERIMA:</span>
                    <span class="text-base text-orange-600" x-text="'Rp ' + (selectedTrx?.total_amount || 0).toLocaleString('id-ID')"></span>
                </div>

                <div class="mt-5">
                    <button type="button" @click="selectedTrx = null"
                        class="w-full py-2.5 bg-slate-900 hover:bg-black text-white font-bold text-xs rounded-xl transition">
                        Tutup Struk
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

