@extends('layouts.app')

@section('title', 'Rekap Penjualan Akhir Hari')

@section('content')
<div class="max-w-md mx-auto">

    <!-- Shift Status Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 mb-4 shadow-xs">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Rekap Shift Driver</span>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-black {{ $currentShift->isOpen() ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                {{ strtoupper($currentShift->status) }}
            </span>
        </div>
        <h2 class="text-lg font-black text-slate-900">{{ auth()->user()->name }}</h2>
        <div class="flex items-center space-x-4 text-xs text-slate-500 mt-1">
            <span><i class="fa-regular fa-calendar mr-1"></i>{{ $currentShift->shift_date->translatedFormat('l, d F Y') }}</span>
            <span><i class="fa-regular fa-clock mr-1"></i>Mulai: {{ $currentShift->started_at ? $currentShift->started_at->format('H:i') : '-' }}</span>
        </div>
    </div>

    <!-- Gamifikasi Target Harian & Badge Motivasi -->
    <div class="bg-gradient-to-r from-orange-500 via-amber-500 to-orange-600 text-white rounded-2xl p-4 mb-4 shadow-md relative overflow-hidden">
        <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
        <div class="flex items-center justify-between mb-2">
            <div class="inline-flex items-center space-x-1.5 bg-black/25 backdrop-blur-xs px-2.5 py-1 rounded-full text-[11px] font-bold">
                <i class="fa-solid {{ $badge['icon'] }}"></i>
                <span>{{ $badge['title'] }}</span>
            </div>
            <span class="text-xs font-bold bg-white/20 px-2 py-0.5 rounded-md">
                Target: {{ $currentShift->target_qty ?? 40 }} Cup
            </span>
        </div>

        <div class="mt-2">
            <div class="flex items-baseline justify-between mb-1">
                <div>
                    <span class="text-2xl font-black">{{ $totalSoldQty }}</span>
                    <span class="text-xs opacity-90 font-semibold"> / {{ $currentShift->target_qty ?? 40 }} Cup Terjual</span>
                </div>
                <span class="text-sm font-black">{{ $targetProgress }}%</span>
            </div>
            <div class="w-full bg-black/20 rounded-full h-2.5 overflow-hidden">
                <div class="bg-white h-2.5 rounded-full transition-all duration-500" style="width: {{ $targetProgress }}%"></div>
            </div>
        </div>

        <p class="text-[11px] text-orange-100 font-medium mt-2.5 flex items-center">
            <i class="fa-regular fa-circle-check mr-1.5 text-xs"></i>
            @if($totalSoldQty >= ($currentShift->target_qty ?? 40))
                <span><strong>Target Tercapai!</strong> Performa hebat, kamu luar biasa hari ini! 🎉</span>
            @else
                <span>Sisa <strong>{{ $cupsToTarget }} cup</strong> dari target mandiri. Setiap langkah adalah rezeki!</span>
            @endif
        </p>
    </div>

    <!-- Kalkulator Insentif Rp 1.000 / Cup & Simulasi Nombok -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs mb-4">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <div class="flex items-center space-x-2">
                <div class="w-7 h-7 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center text-xs font-black">
                    <i class="fa-solid fa-calculator"></i>
                </div>
                <div>
                    <h3 class="font-black text-xs text-slate-800 uppercase tracking-wider">Kalkulator Insentif vs Produk Sisa</h3>
                    <p class="text-[10px] text-slate-500">Perhitungan komisi bersih & tanggungan nombok</p>
                </div>
            </div>
            <span class="text-[10px] bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded-full">
                Rp 1.000 / Cup
            </span>
        </div>

        <div class="p-4 space-y-3">
            <!-- Rincian Persamaan -->
            <div class="bg-slate-50 rounded-xl p-3 border border-slate-200 text-xs space-y-2">
                <!-- 1. Total Insentif -->
                <div class="flex items-center justify-between">
                    <div>
                        <span class="font-bold text-slate-800 block">1. Total Insentif Terkumpul (+)</span>
                        <span class="text-[10px] text-slate-500">{{ $totalSoldQty }} cup terjual × Rp 1.000</span>
                    </div>
                    <span class="text-sm font-black text-emerald-600">+Rp {{ number_format($totalIncentive, 0, ',', '.') }}</span>
                </div>

                <!-- 2. Penggantian Produk Belum Habis -->
                <div class="flex items-center justify-between pt-2 border-t border-slate-200/60">
                    <div>
                        <span class="font-bold text-rose-700 block">2. Penggantian Produk Belum Habis (-)</span>
                        <span class="text-[10px] text-slate-500">{{ $totalUnsoldQty }} botol sisa muatan</span>
                    </div>
                    <span class="text-xs font-black text-rose-600">-Rp {{ number_format($totalUnsoldValue, 0, ',', '.') }}</span>
                </div>

                <!-- 3. Garis Total Hasil Akhir -->
                <div class="flex items-center justify-between pt-2 border-t-2 border-dashed border-slate-300">
                    <div>
                        <span class="font-black uppercase text-[11px] {{ $isNombok ? 'text-rose-700' : 'text-emerald-700' }}">
                            {{ $isNombok ? 'Posisi Akhir: NOMBOK' : 'Posisi Akhir: SISA INSENTIF BERSIH' }}
                        </span>
                        <span class="text-[10px] text-slate-500 block">Insentif dikurangi penggantian sisa</span>
                    </div>
                    <div class="text-right">
                        @if($isNombok)
                            <span class="text-base font-black text-rose-600">-Rp {{ number_format($nombokAmount, 0, ',', '.') }}</span>
                            <span class="block text-[9px] font-bold text-rose-500">TANGGUNGAN NOMBOK</span>
                        @else
                            <span class="text-base font-black text-emerald-600">+Rp {{ number_format($netTakeHomeIncentive, 0, ',', '.') }}</span>
                            <span class="block text-[9px] font-bold text-emerald-600">BAWA PULANG KANTONG</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Kartu Status Psikologis & Solusi Lapangan -->
            @if(!$isNombok)
                <!-- KONDISI SURPLUS (BEBAS NOMBOK) -->
                <div class="p-3.5 bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-200 rounded-xl">
                    <div class="flex items-center space-x-2 text-emerald-800 font-extrabold text-xs mb-1">
                        <i class="fa-solid fa-shield-heart text-emerald-600"></i>
                        <span>ZONA AMAN BEBAS NOMBOK! 🎉</span>
                    </div>
                    <p class="text-[11px] text-emerald-700 leading-relaxed font-medium">
                        Insentif penjualanmu (<strong>Rp {{ number_format($totalIncentive, 0, ',', '.') }}</strong>) berhasil menutup seluruh penggantian {{ $totalUnsoldQty }} botol sisa (<strong>Rp {{ number_format($totalUnsoldValue, 0, ',', '.') }}</strong>).
                    </p>
                    <div class="mt-2 p-2 bg-emerald-100/60 rounded-lg flex items-center justify-between">
                        <span class="text-xs text-emerald-800 font-bold">Uang Insentif Bersihmu:</span>
                        <span class="text-sm font-black text-emerald-700">Rp {{ number_format($netTakeHomeIncentive, 0, ',', '.') }}</span>
                    </div>
                    <p class="text-[10px] text-emerald-600 mt-1.5">
                        ✨ Mantap Nanda! Tidak ada potongan gaji, bonus bersih ini langsung jadi hak milikmu.
                    </p>
                </div>
            @else
                <!-- KONDISI NOMBOK (ASPEK PSIKOLOGIS: MOTIVATIF & SOLUTIF, BUKAN TEKANAN) -->
                <div class="p-3.5 bg-gradient-to-br from-amber-50 via-rose-50 to-orange-50 border border-amber-300 rounded-xl">
                    <div class="flex items-center justify-between mb-1">
                        <div class="flex items-center space-x-2 text-rose-800 font-black text-xs">
                            <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                            <span>STATUS: TANGGUNGAN NOMBOK Rp {{ number_format($nombokAmount, 0, ',', '.') }}</span>
                        </div>
                        <span class="bg-rose-100 text-rose-700 text-[10px] font-black px-1.5 py-0.5 rounded">
                            Minus
                        </span>
                    </div>

                    <!-- Penjelasan Transparan -->
                    <p class="text-[11px] text-slate-700 leading-relaxed font-medium mt-1">
                        Insentif penjualanmu sebesar <strong>Rp {{ number_format($totalIncentive, 0, ',', '.') }}</strong> otomatis dipakai seluruhnya untuk meringankan penggantian sisa botol (<strong>Rp {{ number_format($totalUnsoldValue, 0, ',', '.') }}</strong>). Sisa kekurangan yang perlu diganti adalah <strong>Rp {{ number_format($nombokAmount, 0, ',', '.') }}</strong>.
                    </p>

                    <!-- Aspek Psikologis & Solusi Lapangan -->
                    <div class="mt-2.5 space-y-1.5 bg-white/80 p-2.5 rounded-lg border border-amber-200/80 text-[11px]">

                        <div class="flex items-start space-x-1.5 text-emerald-800 font-semibold">
                            <i class="fa-solid fa-bullseye text-emerald-600 text-xs mt-0.5"></i>
                            <span>
                                <strong>Strategi Besok Pagi:</strong> Jual sisa {{ $totalUnsoldQty }} botol ini di spot ramai pertama besok (misal halte/sekolah). Hasil penjualannya (Rp {{ number_format($totalUnsoldValue, 0, ',', '.') }}) <strong>100% langsung balik modal uangmu!</strong>
                            </span>
                        </div>

                        <div class="flex items-start space-x-1.5 text-blue-800 font-semibold">
                            <i class="fa-solid fa-sliders text-blue-600 text-xs mt-0.5"></i>
                            <span>
                                <strong>Tips Muatan:</strong> Besok pagi kamu bisa kurangi jumlah bawaan botol di menu input stok awal agar tidak berlebihan dan lebih pas dengan rute kelilingmu.
                            </span>
                        </div>
                    </div>

                    <p class="text-[10px] text-slate-500 mt-2 text-center font-medium">
                        💪 Jangan berkecil hati Nanda! Semua pejuang keliling pernah mengalami hari sepi, besok kita gaspol lagi!
                    </p>
                </div>
            @endif

            <!-- Daftar Rincian Botol Sisa Yang Harus Diganti -->
            @if($unsoldBreakdown->isNotEmpty())
                <div class="border border-slate-200 rounded-xl overflow-hidden mt-2" x-data="{ openDetail: false }">
                    <button type="button" @click="openDetail = !openDetail"
                        class="w-full p-2.5 bg-slate-50 hover:bg-slate-100 flex items-center justify-between text-left transition">
                        <span class="text-[11px] font-bold text-slate-700 flex items-center space-x-1.5">
                            <i class="fa-solid fa-list-check text-slate-500"></i>
                            <span>Rincian Produk Sisa yang Diganti ({{ $totalUnsoldQty }} Botol)</span>
                        </span>
                        <div class="flex items-center space-x-1 text-slate-400 text-xs">
                            <span class="text-[10px] font-bold text-rose-600">Rp {{ number_format($totalUnsoldValue, 0, ',', '.') }}</span>
                            <i class="fa-solid fa-chevron-down transition-transform" :class="openDetail ? 'rotate-180' : ''"></i>
                        </div>
                    </button>

                    <div x-show="openDetail" class="divide-y divide-slate-100 bg-white" style="display: none;">
                        @foreach($unsoldBreakdown as $item)
                            <div class="p-2.5 flex items-center justify-between text-xs">
                                <div>
                                    <span class="font-bold text-slate-800 block">{{ $item['product_name'] }}</span>
                                    <span class="text-[10px] text-slate-500">
                                        {{ $item['remaining_qty'] }} botol × Rp {{ number_format($item['unit_price'], 0, ',', '.') }}
                                    </span>
                                </div>
                                <span class="font-bold text-rose-600 text-xs">
                                    Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>

    <!-- Financial Summary Cards -->
    <div class="grid grid-cols-2 gap-2.5 mb-4">
        <!-- Cash Card -->
        <div class="bg-gradient-to-br from-emerald-500 to-teal-600 text-white rounded-2xl p-3.5 shadow-sm">
            <div class="flex items-center space-x-1.5 opacity-90 text-[10px] uppercase font-bold tracking-wider">
                <i class="fa-solid fa-money-bill-wave"></i>
                <span>Uang Tunai (Wajib Setor)</span>
            </div>
            <p class="text-lg font-black mt-1">Rp {{ number_format($totalCash, 0, ',', '.') }}</p>
            <p class="text-[10px] text-emerald-100 mt-0.5">Uang fisik yang harus ada di kantong</p>
        </div>

        <!-- Validated QRIS Card -->
        <div class="bg-gradient-to-br from-orange-500 to-amber-600 text-white rounded-2xl p-3.5 shadow-sm">
            <div class="flex items-center space-x-1.5 opacity-90 text-[10px] uppercase font-bold tracking-wider">
                <i class="fa-solid fa-qrcode"></i>
                <span>QRIS Tervalidasi</span>
            </div>
            <p class="text-lg font-black mt-1">Rp {{ number_format($totalQrisValidated, 0, ',', '.') }}</p>
            <p class="text-[10px] text-orange-100 mt-0.5">Masuk ke rekening perusahaan</p>
        </div>
    </div>

    <!-- Grand Total & Pending Alert -->
    <div class="bg-slate-900 text-white p-4 rounded-2xl mb-4 flex items-center justify-between shadow-sm">
        <div>
            <p class="text-[10px] uppercase tracking-wider font-semibold text-slate-400">Total Omset Shift Ini</p>
            <p class="text-xl font-black text-white">Rp {{ number_format($totalCash + $totalQrisValidated, 0, ',', '.') }}</p>
        </div>
        <div class="text-right">
            <p class="text-[10px] uppercase tracking-wider font-semibold text-slate-400">Total Transaksi</p>
            <p class="text-base font-black text-orange-400">{{ $currentShift->transactions->count() }} Transaksi</p>
        </div>
    </div>

    @if($totalQrisPending > 0)
        <div class="bg-rose-50 border border-rose-200 rounded-xl p-3 mb-4 text-xs text-rose-800 flex items-start space-x-2">
            <i class="fa-solid fa-triangle-exclamation text-rose-600 text-base mt-0.5"></i>
            <div>
                <p class="font-bold">Perhatian: Masih ada QRIS PENDING sebesar Rp {{ number_format($totalQrisPending, 0, ',', '.') }}!</p>
                <p class="text-[11px] text-rose-700 mt-0.5">
                    Harap selesaikan validasi atau tolak transaksi mencurigakan di <a href="{{ route('driver.qris.pending') }}" class="underline font-bold">Menu Validasi QRIS</a> sebelum menutup shift.
                </p>
            </div>
        </div>
    @endif

    <!-- Stock Balance Table -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs mb-6">
        <div class="p-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <h3 class="font-extrabold text-xs text-slate-800 uppercase tracking-wider">Audit Sisa Stok Minuman</h3>
            <span class="text-[10px] text-slate-500 font-medium">Bawa vs Laku vs Sisa</span>
        </div>

        <div class="divide-y divide-slate-100">
            @foreach($currentShift->dailyInventories as $inv)
                <div class="p-3 flex items-center justify-between text-xs">
                    <div class="flex-1 pr-2">
                        <h5 class="font-bold text-slate-900">{{ $inv->product->name }}</h5>
                        <p class="text-[10px] text-slate-500">Kategori: {{ $inv->product->category ?? '-' }}</p>
                    </div>

                    <div class="flex items-center space-x-4 text-right">
                        <div>
                            <span class="text-[10px] text-slate-400 block">Bawa</span>
                            <span class="font-bold text-slate-700">{{ $inv->start_qty }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-emerald-600 font-semibold block">Laku</span>
                            <span class="font-black text-emerald-600">{{ $inv->sold_qty }}</span>
                        </div>
                        <div class="bg-slate-100 px-2 py-1 rounded-lg min-w-10 text-center">
                            <span class="text-[9px] text-slate-400 block">Sisa</span>
                            <span class="font-black {{ $inv->remaining_qty > 5 ? 'text-amber-600' : 'text-slate-800' }}">
                                {{ $inv->remaining_qty }}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Rincian Transaksi Shift Ini (Detail & Jam Beli) -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs mb-6" x-data="{ selectedTrx: null }">
        <div class="p-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-clock-rotate-left text-orange-600"></i>
                <h3 class="font-extrabold text-xs text-slate-800 uppercase tracking-wider">Riwayat Transaksi Shift Ini ({{ $transactions->count() }})</h3>
            </div>
            <span class="text-[10px] text-slate-400">Klik untuk struk digital</span>
        </div>

        @if($transactions->isEmpty())
            <div class="p-6 text-center text-xs text-slate-400">
                Belum ada transaksi penjualan pada shift ini.
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach($transactions as $t)
                    <div class="p-3 hover:bg-slate-50/80 cursor-pointer transition flex items-center justify-between"
                        @click="selectedTrx = {{ json_encode($t) }}">
                        <div class="flex-1 pr-2">
                            <div class="flex items-center space-x-2 mb-0.5">
                                <span class="font-mono text-[11px] font-bold text-slate-800">{{ $t->invoice_number }}</span>
                                <span class="bg-orange-50 text-orange-700 text-[10px] font-bold px-1.5 py-0.2 rounded border border-orange-200">
                                    <i class="fa-regular fa-clock mr-0.5"></i> {{ $t->transacted_at->format('H:i:s') }} WIB
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-600 font-medium line-clamp-1">
                                {{ $t->details->map(fn($d) => $d->qty . 'x ' . ($d->product->name ?? 'Minuman'))->join(', ') }}
                            </p>
                            <div class="flex items-center space-x-2 text-[10px] text-slate-400 mt-0.5">
                                <span><i class="fa-solid fa-location-dot text-slate-400 mr-0.5"></i>{{ $t->location_name ?? 'Area Keliling' }}</span>
                                <span>•</span>
                                <span>{{ $t->transacted_at->diffForHumans() }}</span>
                            </div>
                        </div>

                        <div class="text-right pl-2">
                            <span class="font-black text-xs text-slate-900 block">Rp {{ number_format($t->total_amount, 0, ',', '.') }}</span>
                            @if($t->payment_method === 'cash')
                                <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded">TUNAI</span>
                            @else
                                <span class="text-[10px] font-bold {{ $t->payment_status === 'validated' ? 'text-teal-700 bg-teal-50' : ($t->payment_status === 'pending' ? 'text-amber-700 bg-amber-50 animate-pulse' : 'text-rose-700 bg-rose-50') }} px-1.5 py-0.2 rounded">
                                    QRIS: {{ strtoupper($t->payment_status) }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Struk Digital Detail Modal -->
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
                            <span class="font-semibold text-slate-800" x-text="selectedTrx?.transacted_at ? new Date(selectedTrx.transacted_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-'"></span>
                        </div>
                        <div class="flex justify-between text-slate-500">
                            <span>Titik Lokasi:</span>
                            <span class="font-semibold text-slate-800" x-text="selectedTrx?.location_name || 'Area Penjualan'"></span>
                        </div>
                        <div class="flex justify-between text-slate-500">
                            <span>Metode Bayar:</span>
                            <span class="font-bold uppercase" :class="selectedTrx?.payment_method === 'cash' ? 'text-emerald-600' : 'text-orange-600'" x-text="selectedTrx?.payment_method"></span>
                        </div>
                        <div class="flex justify-between text-slate-500">
                            <span>Status Bayar:</span>
                            <span class="font-bold uppercase" :class="selectedTrx?.payment_status === 'paid' || selectedTrx?.payment_status === 'validated' ? 'text-emerald-600' : (selectedTrx?.payment_status === 'pending' ? 'text-amber-600' : 'text-rose-600')" x-text="selectedTrx?.payment_status"></span>
                        </div>
                    </div>

                    <!-- Items List -->
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
                            class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                            Tutup Struk
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Close Shift Action -->
    @if($currentShift->isOpen())
        <form action="{{ route('driver.shift.close') }}" method="POST"
            onsubmit="return confirm('Apakah Anda yakin ingin MENUTUP SHIFT HARI INI?\n\nPastikan uang tunai dan sisa botol sudah cocok.')">
            @csrf
            <button type="submit"
                class="w-full py-4 bg-slate-900 hover:bg-black text-white font-extrabold text-sm rounded-xl shadow-lg flex items-center justify-center space-x-2 transition">
                <i class="fa-solid fa-door-closed"></i>
                <span>TUTUP SHIFT HARI INI</span>
            </button>
        </form>
    @else
        <div class="p-4 bg-slate-100 rounded-2xl text-center text-xs text-slate-700 font-medium border border-slate-200">
            <p class="font-bold text-slate-900 mb-1 flex items-center justify-center">
                <i class="fa-solid fa-circle-check text-emerald-600 text-sm mr-1.5"></i>
                <span>Shift Ini Telah Resmi Ditutup pada {{ $currentShift->ended_at ? $currentShift->ended_at->format('H:i') : '' }} WIB</span>
            </p>
            <p class="text-[11px] text-slate-500">
                Seluruh rekap penjualan, perolehan insentif, dan status nombok di atas tersimpan rapi dan dapat selalu ditinjau kembali di tab <a href="{{ route('driver.transactions') }}" class="text-orange-600 font-bold underline">Riwayat Trx</a>.
            </p>
        </div>
        <div class="mt-3">
            <a href="{{ route('driver.shift') }}"
                class="w-full py-3.5 bg-orange-600 hover:bg-orange-700 active:bg-orange-800 text-white font-black text-xs rounded-xl shadow-md flex items-center justify-center space-x-2 transition">
                <i class="fa-solid fa-play"></i>
                <span>MULAI SHIFT BARU</span>
            </a>
        </div>
    @endif

</div>
@endsection

