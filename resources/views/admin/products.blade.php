@extends('layouts.app')

@section('title', 'Katalog Minuman Haus!')

@section('content')
<div class="space-y-6" x-data="{ filter: 'all' }">

    <!-- Header Card -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs">
        <div>
            <div class="flex items-center space-x-2">
                <span class="bg-orange-600 text-white font-black text-xs px-2.5 py-0.5 rounded-lg tracking-wider">HAUS!</span>
                <h1 class="text-xl font-black text-slate-900">Katalog Minuman Haus Keliling</h1>
            </div>
            <p class="text-xs text-slate-500 mt-1">Daftar menu resmi gerobak keliling sesuai spanduk fisik lapangan.</p>
        </div>

        <!-- Filter Pills -->
        <div class="flex items-center space-x-2 bg-slate-100 p-1.5 rounded-2xl text-xs">
            <button type="button" @click="filter = 'all'"
                :class="filter === 'all' ? 'bg-white text-slate-900 font-black shadow-xs' : 'text-slate-500 font-medium'"
                class="px-3 py-1.5 rounded-xl transition">
                Semua ({{ $products->count() }})
            </button>
            <button type="button" @click="filter = 'active'"
                :class="filter === 'active' ? 'bg-white text-slate-900 font-black shadow-xs' : 'text-slate-500 font-medium'"
                class="px-3 py-1.5 rounded-xl transition">
                Aktif Diproduksi ({{ $products->where('is_active', true)->count() }})
            </button>
            <button type="button" @click="filter = 'inactive'"
                :class="filter === 'inactive' ? 'bg-white text-slate-900 font-black shadow-xs' : 'text-slate-500 font-medium'"
                class="px-3 py-1.5 rounded-xl transition">
                Tidak Diproduksi ({{ $products->where('is_active', false)->count() }})
            </button>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($products as $prod)
            <div x-show="filter === 'all' || (filter === 'active' && {{ $prod->is_active ? 'true' : 'false' }}) || (filter === 'inactive' && {{ !$prod->is_active ? 'true' : 'false' }})"
                class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col justify-between transition-all hover:shadow-md {{ !$prod->is_active ? 'opacity-60 grayscale-[40%]' : '' }}">
                
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[10px] uppercase font-extrabold tracking-wider px-2.5 py-1 rounded-xl
                            {{ $prod->category === 'Teh' ? 'bg-amber-100 text-amber-800' : ($prod->category === 'Kopi' ? 'bg-amber-900/10 text-amber-900' : ($prod->category === 'Matcha' ? 'bg-emerald-100 text-emerald-800' : 'bg-orange-100 text-orange-800')) }}">
                            {{ $prod->category ?? 'Minuman' }}
                        </span>

                        @if($prod->is_active)
                            <span class="text-[10px] font-extrabold px-2.5 py-1 rounded-xl bg-emerald-100 text-emerald-800 flex items-center">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                                Aktif
                            </span>
                        @else
                            <span class="text-[10px] font-extrabold px-2.5 py-1 rounded-xl bg-rose-100 text-rose-800">
                                Sudah Tidak Produksi
                            </span>
                        @endif
                    </div>

                    <h3 class="font-black text-base text-slate-900 leading-snug">{{ $prod->name }}</h3>
                </div>

                <div class="mt-5 pt-3.5 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs text-slate-400 font-medium">Harga Resmi:</span>
                    <span class="font-black text-base text-orange-600">
                        Rp {{ number_format($prod->price, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        @endforeach
    </div>

</div>
@endsection
