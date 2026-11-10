@extends('layouts.app')

@section('title', 'Master Titik Lokasi Jualan')

@section('content')
<div class="space-y-6">

    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
        <div>
            <h1 class="text-xl font-black text-slate-900">Titik Lokasi Geotagging</h1>
            <p class="text-xs text-slate-500 mt-1">Daftar spot jualan resmi dengan koordinat GPS dan radius geofence untuk pencocokan otomatis transaksi driver.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($locations as $loc)
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded bg-orange-100 text-orange-800">
                            Radius: {{ $loc->radius_meters }} meter
                        </span>
                        <span class="text-xs font-bold text-slate-400">
                            {{ $loc->transactions_count }} Penjualan
                        </span>
                    </div>
                    <h3 class="font-bold text-sm text-slate-900">{{ $loc->name }}</h3>
                    <p class="text-xs text-slate-500 mt-1">{{ $loc->address ?? 'Area terbuka sekitar titik koordinat' }}</p>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] font-mono text-slate-400 flex items-center justify-between">
                    <span>GPS:</span>
                    <span>{{ number_format($loc->latitude, 4) }}, {{ number_format($loc->longitude, 4) }}</span>
                </div>
            </div>
        @endforeach
    </div>

</div>
@endsection

