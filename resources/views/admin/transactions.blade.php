@extends('layouts.app')

@section('title', 'Semua Riwayat Transaksi Penjualan')

@section('content')
<div class="space-y-6" x-data="{ selectedTrx: null }">

    <!-- Header Card -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <div>
            <div class="flex items-center space-x-2">
                <span class="bg-orange-500 text-white text-xs font-black px-2 py-0.5 rounded">ARSIP</span>
                <h1 class="text-xl font-black text-slate-900">Arsip Seluruh Transaksi Penjualan</h1>
            </div>
            <p class="text-xs text-slate-500 mt-1">Audit log penjualan dari seluruh armada gerobak keliling, jam transaksi, dan verifikasi QRIS.</p>
        </div>

        <div class="flex items-center space-x-4">
            <div class="text-right">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Total Omset Tervalidasi</span>
                <span class="text-lg font-black text-slate-900">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
            </div>
            <div class="bg-orange-100 text-orange-800 font-black text-xs px-3 py-2 rounded-xl">
                {{ $totalCount }} Transaksi
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <form action="{{ route('admin.transactions') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Tanggal</label>
                <input type="date" name="date" value="{{ $selectedDate }}"
                    class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2 font-medium outline-none focus:ring-2 focus:ring-orange-500">
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Driver</label>
                <select name="driver_id" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2 font-medium outline-none focus:ring-2 focus:ring-orange-500">
                    <option value="">Semua Driver</option>
                    @foreach($drivers as $d)
                        <option value="{{ $d->id }}" {{ $selectedDriver == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Metode Bayar</label>
                <select name="payment_method" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2 font-medium outline-none focus:ring-2 focus:ring-orange-500">
                    <option value="">Semua Metode</option>
                    <option value="cash" {{ $selectedPayment === 'cash' ? 'selected' : '' }}>Tunai (Cash)</option>
                    <option value="qris" {{ $selectedPayment === 'qris' ? 'selected' : '' }}>QRIS</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Status</label>
                <select name="status" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2 font-medium outline-none focus:ring-2 focus:ring-orange-500">
                    <option value="">Semua Status</option>
                    <option value="paid" {{ $selectedStatus === 'paid' ? 'selected' : '' }}>Sah (Tunai)</option>
                    <option value="validated" {{ $selectedStatus === 'validated' ? 'selected' : '' }}>Sah (QRIS Valid)</option>
                    <option value="pending" {{ $selectedStatus === 'pending' ? 'selected' : '' }}>Pending QRIS</option>
                    <option value="rejected" {{ $selectedStatus === 'rejected' ? 'selected' : '' }}>Ditolak (Fraud)</option>
                </select>
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 py-2 px-3 bg-slate-900 hover:bg-black text-white text-xs font-bold rounded-xl transition">
                    Filter
                </button>
                @if($selectedDate || $selectedDriver || $selectedPayment || $selectedStatus)
                    <a href="{{ route('admin.transactions') }}" class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs flex items-center justify-center" title="Reset">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider text-[10px] border-b border-slate-100">
                    <tr>
                        <th class="p-3.5">Waktu Transaksi</th>
                        <th class="p-3.5">Invoice</th>
                        <th class="p-3.5">Driver</th>
                        <th class="p-3.5">Titik Lokasi (GPS)</th>
                        <th class="p-3.5">Rincian Botol</th>
                        <th class="p-3.5">Metode</th>
                        <th class="p-3.5">Status</th>
                        <th class="p-3.5 text-right">Total</th>
                        <th class="p-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $t)
                        <tr class="hover:bg-slate-50/80 cursor-pointer transition"
                            @click="selectedTrx = {{ json_encode($t) }}">
                            <td class="p-3.5 whitespace-nowrap">
                                <span class="font-bold text-slate-900 block">{{ $t->transacted_at->format('H:i:s') }} WIB</span>
                                <span class="text-[10px] text-slate-400">{{ $t->transacted_at->format('d M Y') }}</span>
                            </td>
                            <td class="p-3.5 font-mono font-bold text-slate-700 whitespace-nowrap">{{ $t->invoice_number }}</td>
                            <td class="p-3.5 font-semibold text-slate-900 whitespace-nowrap">{{ $t->shift->user->name ?? '-' }}</td>
                            <td class="p-3.5 text-slate-600">
                                @if($t->location_name)
                                    <span class="inline-flex items-center font-medium"><i class="fa-solid fa-location-dot text-orange-600 mr-1 text-[10px]"></i>{{ $t->location_name }}</span>
                                @else
                                    <span class="text-slate-400">Area Terbuka</span>
                                @endif
                            </td>
                            <td class="p-3.5 text-slate-600 max-w-xs truncate">
                                {{ $t->details->map(fn($d) => $d->qty . 'x ' . ($d->product->name ?? 'Minuman'))->join(', ') }}
                            </td>
                            <td class="p-3.5 whitespace-nowrap">
                                <span class="uppercase font-bold text-[10px] px-2 py-0.5 rounded {{ $t->payment_method === 'cash' ? 'bg-emerald-100 text-emerald-800' : 'bg-orange-100 text-orange-800' }}">
                                    {{ $t->payment_method }}
                                </span>
                            </td>
                            <td class="p-3.5 whitespace-nowrap">
                                @if($t->payment_status === 'paid' || $t->payment_status === 'validated')
                                    <span class="text-emerald-600 font-bold text-[10px] inline-flex items-center"><i class="fa-solid fa-check mr-1"></i> Sah</span>
                                @elseif($t->payment_status === 'pending')
                                    <span class="text-amber-600 font-bold text-[10px] inline-flex items-center animate-pulse"><i class="fa-solid fa-clock mr-1"></i> Pending</span>
                                @else
                                    <span class="text-rose-600 font-bold text-[10px] inline-flex items-center"><i class="fa-solid fa-ban mr-1"></i> Fraud</span>
                                @endif
                            </td>
                            <td class="p-3.5 text-right font-black text-slate-900 whitespace-nowrap">
                                Rp {{ number_format($t->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="p-3.5 text-center whitespace-nowrap">
                                <button type="button" class="text-orange-600 hover:text-orange-800 font-bold text-[11px] underline">
                                    Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-8 text-center text-slate-400">Belum ada transaksi sesuai filter pencarian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

    <!-- Admin Detail Modal -->
    <div x-show="selectedTrx" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" @click="selectedTrx = null"></div>

        <div class="min-h-full flex items-center justify-center p-4">
            <div class="relative bg-white w-full max-w-md rounded-2xl p-6 shadow-2xl" @click.stop>
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center space-x-2">
                        <span class="bg-orange-600 text-white font-black text-xs px-2 py-0.5 rounded">HAUS!</span>
                        <h3 class="font-extrabold text-sm text-slate-900">Rincian Struk Penjualan</h3>
                    </div>
                    <button type="button" @click="selectedTrx = null" class="text-slate-400 hover:text-slate-600">
                        <i class="fa-solid fa-xmark text-base"></i>
                    </button>
                </div>

                <div class="py-4 space-y-2 text-xs border-b border-slate-100">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Nomor Invoice:</span>
                        <span class="font-mono font-bold text-slate-900" x-text="selectedTrx?.invoice_number"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Waktu Pembelian:</span>
                        <span class="font-bold text-orange-600" x-text="selectedTrx?.transacted_at ? new Date(selectedTrx.transacted_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB' : '-'"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Tanggal:</span>
                        <span class="font-semibold text-slate-800" x-text="selectedTrx?.transacted_at ? new Date(selectedTrx.transacted_at).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) : '-'"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Nama Driver:</span>
                        <span class="font-semibold text-slate-800" x-text="selectedTrx?.shift?.user?.name || '-'"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Titik Lokasi:</span>
                        <span class="font-semibold text-slate-800" x-text="selectedTrx?.location_name || 'Keliling'"></span>
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
                    <div class="flex justify-between">
                        <span class="text-slate-500">Metode & Status:</span>
                        <span class="font-bold uppercase" :class="selectedTrx?.payment_status === 'paid' || selectedTrx?.payment_status === 'validated' ? 'text-emerald-600' : 'text-amber-600'"
                            x-text="(selectedTrx?.payment_method || '') + ' (' + (selectedTrx?.payment_status || '') + ')'"></span>
                    </div>
                </div>

                <!-- Items Detail -->
                <div class="py-3 border-b border-slate-100 text-xs">
                    <p class="font-bold text-slate-700 mb-2">Item Minuman Terjual:</p>
                    <div class="space-y-1.5">
                        <template x-for="item in selectedTrx?.details" :key="item.id">
                            <div class="flex justify-between items-center bg-slate-50 p-2 rounded-lg">
                                <div>
                                    <span class="font-bold text-slate-800" x-text="item.product?.name || 'Minuman'"></span>
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

                <div class="mt-5">
                    <button type="button" @click="selectedTrx = null"
                        class="w-full py-2.5 bg-slate-900 hover:bg-black text-white font-bold text-xs rounded-xl transition">
                        Tutup Detail
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

