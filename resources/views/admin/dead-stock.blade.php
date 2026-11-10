@extends('layouts.app')

@section('title', 'Analisis Dead Stock & Alokasi Muatan')

@section('content')
<div class="space-y-6">

    <!-- Header & Date Filter -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <div>
            <h1 class="text-xl font-black text-slate-900">Analisis Dead Stock (Stok Sisa Driver)</h1>
            <p class="text-xs text-slate-500 mt-1">Identifikasi minuman yang kurang diminati untuk menghindari pemotongan komisi/gaji driver.</p>
        </div>

        <form action="{{ route('admin.dead-stock') }}" method="GET" class="flex items-center space-x-2">
            <input type="date" name="start_date" value="{{ $startDate }}" class="text-xs bg-slate-50 border border-slate-200 rounded-lg p-2 font-medium">
            <span class="text-xs text-slate-400">s/d</span>
            <input type="date" name="end_date" value="{{ $endDate }}" class="text-xs bg-slate-50 border border-slate-200 rounded-lg p-2 font-medium">
            <button type="submit" class="px-3 py-2 bg-slate-900 hover:bg-black text-white text-xs font-bold rounded-lg transition">Filter</button>
        </form>
    </div>

    <!-- Dead Stock Performance Matrix -->
    <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden shadow-xs">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-extrabold text-sm text-slate-900">Tingkat Penjualan vs Sisa Muatan Minuman</h3>
            <span class="text-xs text-slate-400">Periode: {{ $startDate }} s/d {{ $endDate }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider text-[10px] border-b border-slate-100">
                    <tr>
                        <th class="p-3.5">Produk Minuman</th>
                        <th class="p-3.5">Kategori</th>
                        <th class="p-3.5 text-center">Total Dimuat (Bawa)</th>
                        <th class="p-3.5 text-center">Total Terjual (Laku)</th>
                        <th class="p-3.5 text-center">Sisa Unsold (Dead Stock)</th>
                        <th class="p-3.5 text-right">Sell-Through Rate (%)</th>
                        <th class="p-3.5 text-center">Status Alokasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($inventories as $inv)
                        <tr class="hover:bg-slate-50/50">
                            <td class="p-3.5 font-bold text-slate-900">{{ $inv->product->name ?? 'Minuman' }}</td>
                            <td class="p-3.5 text-slate-500">{{ $inv->product->category ?? '-' }}</td>
                            <td class="p-3.5 text-center font-bold text-slate-700">{{ $inv->total_loaded }}</td>
                            <td class="p-3.5 text-center font-bold text-emerald-600">{{ $inv->total_sold }}</td>
                            <td class="p-3.5 text-center font-black {{ $inv->total_unsold > 10 ? 'text-rose-600' : 'text-slate-700' }}">
                                {{ $inv->total_unsold }}
                            </td>
                            <td class="p-3.5 text-right">
                                <span class="font-black text-sm {{ $inv->sell_through_rate < 50 ? 'text-rose-600' : 'text-emerald-600' }}">
                                    {{ $inv->sell_through_rate }}%
                                </span>
                            </td>
                            <td class="p-3.5 text-center">
                                @if($inv->sell_through_rate >= 80)
                                    <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded">Sangat Laris (Perbanyak)</span>
                                @elseif($inv->sell_through_rate >= 50)
                                    <span class="bg-amber-100 text-amber-800 text-[10px] font-bold px-2 py-0.5 rounded">Optimal</span>
                                @else
                                    <span class="bg-rose-100 text-rose-800 text-[10px] font-bold px-2 py-0.5 rounded">⚠️ Dead Stock (Kurangi)</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-400">Belum ada data shift pada rentang tanggal ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

