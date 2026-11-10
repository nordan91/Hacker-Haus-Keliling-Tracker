@extends('layouts.app')

@section('title', 'Mulai Shift & Target Harian')

@section('content')
<div class="max-w-md mx-auto" x-data="shiftSetup({{ json_encode($products) }})">

    <!-- Header Card -->
    <div class="bg-gradient-to-r from-orange-600 to-amber-600 text-white rounded-2xl p-5 mb-4 shadow-sm">
        <div class="flex items-center justify-between mb-1">
            <span class="text-xs uppercase tracking-wider font-extrabold opacity-95">HACKER • Shift Harian</span>
            <span class="text-[10px] bg-white/20 px-2.5 py-0.5 rounded-full font-bold">Retas Target, Bawa Pulang Cuan!</span>
        </div>
        <h1 class="text-xl font-black">Bawa Berapa Botol Hari Ini?</h1>
        <p class="text-xs text-orange-100 mt-1">
            Tentukan targetmu sendiri dan bawa muatan yang pas. Bonus <strong>Rp 1.000/cup</strong> menunggumu!
        </p>

        <div class="mt-4 pt-3 border-t border-white/20 flex justify-between items-center text-xs">
            <span>Total Muatan Gerobak:</span>
            <span class="text-lg font-black" x-text="totalBottles() + ' Botol'">0 Botol</span>
        </div>
    </div>

    <!-- Form Input Shift & Target -->
    <form action="{{ route('driver.shift.start') }}" method="POST">
        @csrf

        <!-- 1. Tentukan Target Harian Sendiri (Gamified & Autonomy) -->
        <div class="bg-white p-4 rounded-2xl border-2 border-orange-200 shadow-xs mb-4">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center space-x-2">
                    <span class="w-7 h-7 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center text-sm font-black">
                        🎯
                    </span>
                    <div>
                        <h3 class="font-black text-sm text-slate-900">Target Jualanmu Hari Ini</h3>
                        <p class="text-[11px] text-slate-400">Tentukan sendiri target yang ingin kamu capai!</p>
                    </div>
                </div>
            </div>

            <!-- Preset Target Buttons -->
            <div class="grid grid-cols-4 gap-1.5 my-3">
                <button type="button" @click="targetQty = 30"
                    :class="targetQty === 30 ? 'bg-orange-600 text-white font-black shadow-xs' : 'bg-slate-100 text-slate-700 font-bold'"
                    class="py-2 rounded-xl text-xs flex flex-col items-center justify-center transition">
                    <span>30 Cup</span>
                    <span class="text-[9px] opacity-80 font-normal">Santai</span>
                </button>
                <button type="button" @click="targetQty = 40"
                    :class="targetQty === 40 ? 'bg-orange-600 text-white font-black shadow-xs' : 'bg-slate-100 text-slate-700 font-bold'"
                    class="py-2 rounded-xl text-xs flex flex-col items-center justify-center transition">
                    <span>40 Cup</span>
                    <span class="text-[9px] opacity-80 font-normal">Standar</span>
                </button>
                <button type="button" @click="targetQty = 50"
                    :class="targetQty === 50 ? 'bg-orange-600 text-white font-black shadow-xs' : 'bg-slate-100 text-slate-700 font-bold'"
                    class="py-2 rounded-xl text-xs flex flex-col items-center justify-center transition">
                    <span>50 Cup</span>
                    <span class="text-[9px] opacity-80 font-normal">Pro 🔥</span>
                </button>
                <button type="button" @click="targetQty = 60"
                    :class="targetQty === 60 ? 'bg-orange-600 text-white font-black shadow-xs' : 'bg-slate-100 text-slate-700 font-bold'"
                    class="py-2 rounded-xl text-xs flex flex-col items-center justify-center transition">
                    <span>60 Cup</span>
                    <span class="text-[9px] opacity-80 font-normal">Sultan 👑</span>
                </button>
            </div>

            <!-- Custom Input & Potential Earnings Card -->
            <div class="flex items-center space-x-2 bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                <span class="text-xs text-slate-500 font-medium">Atau kustom:</span>
                <input type="number" name="target_qty" x-model.number="targetQty" min="1" max="200"
                    class="w-16 text-center font-black text-sm bg-white border border-slate-300 rounded-lg py-1 outline-none focus:ring-2 focus:ring-orange-500">
                <span class="text-xs text-slate-500 font-bold">Cup</span>
                
                <div class="flex-1 text-right">
                    <span class="text-[10px] text-slate-400 block">Potensi Insentif:</span>
                    <span class="text-xs font-black text-emerald-600" x-text="'Rp ' + (targetQty * 1000).toLocaleString('id-ID')"></span>
                </div>
            </div>

            <div class="mt-2.5 text-[11px] text-emerald-700 bg-emerald-50 p-2 rounded-xl flex items-center space-x-1.5">
                <i class="fa-solid fa-gift text-emerald-600"></i>
                <span><strong>Bonus Pasti:</strong> Tiap 1 cup laku = <strong>Rp 1.000</strong> langsung jadi hakmu!</span>
            </div>
        </div>

        <!-- 2. Muatan Botol Awal -->
        <div class="flex items-center justify-between mb-2 px-1">
            <h4 class="font-extrabold text-xs uppercase tracking-wider text-slate-400">Muatan Botol Minuman Gerobak</h4>
            <span class="text-[10px] text-amber-600 font-bold bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200">
                <i class="fa-solid fa-lightbulb mr-1"></i>Bawa secukupnya agar bebas nombok
            </span>
        </div>
        <div class="space-y-3 mb-6">
            @foreach($products as $product)
                <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs flex items-center justify-between">
                    <div class="flex-1 pr-2">
                        <div class="flex items-center space-x-2">
                            <span class="text-[10px] font-bold uppercase tracking-wider px-1.5 py-0.5 bg-slate-100 text-slate-600 rounded">
                                {{ $product->category ?? 'Minuman' }}
                            </span>
                            <span class="text-xs text-orange-600 font-bold">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                        </div>
                        <h3 class="font-bold text-sm text-slate-800 mt-0.5">{{ $product->name }}</h3>
                    </div>

                    <!-- Counter Controls -->
                    <div class="flex items-center space-x-1">
                        <button type="button" @click="decrement({{ $product->id }})"
                            class="w-9 h-9 rounded-lg bg-slate-100 active:bg-slate-200 text-slate-700 font-bold text-base flex items-center justify-center transition">
                            -
                        </button>
                        
                        <input type="number" name="stocks[{{ $product->id }}]"
                            x-model.number="stocks[{{ $product->id }}]" min="0"
                            class="w-14 h-9 text-center font-black text-sm bg-slate-50 border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-orange-500">
                        
                        <button type="button" @click="increment({{ $product->id }})"
                            class="w-9 h-9 rounded-lg bg-orange-100 active:bg-orange-200 text-orange-700 font-bold text-base flex items-center justify-center transition">
                            +
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Optional Notes -->
        <div class="bg-white p-3.5 rounded-xl border border-slate-200 mb-6">
            <label class="block text-xs font-semibold text-slate-600 mb-1">Catatan Rute / Cuaca (Opsional)</label>
            <textarea name="notes" rows="2"
                class="w-full text-xs p-2.5 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-orange-500"
                placeholder="Misal: Rute area sekolah SMAN 71 & halte, cuaca cerah..."></textarea>
        </div>

        <!-- Submit Button -->
        <div class="sticky bottom-16 sm:bottom-4 pb-2 bg-gradient-to-t from-slate-50 via-slate-50 pt-2">
            <button type="submit"
                class="w-full py-3.5 px-4 bg-orange-600 hover:bg-orange-700 active:bg-orange-800 text-white font-extrabold text-sm rounded-xl shadow-lg shadow-orange-600/30 flex items-center justify-center space-x-2 transition">
                <i class="fa-solid fa-flag-checkered"></i>
                <span>Mulai Jualan & Capai Target!</span>
            </button>
        </div>
    </form>

</div>

<script>
    function shiftSetup(products) {
        let initialStocks = {};
        products.forEach(p => {
            // Start dari 0, driver isi sendiri sesuai kebutuhan
            initialStocks[p.id] = 0;
        });

        return {
            targetQty: 40,
            stocks: initialStocks,
            increment(id) {
                this.stocks[id] = (this.stocks[id] || 0) + 1;
            },
            decrement(id) {
                if (this.stocks[id] > 0) {
                    this.stocks[id]--;
                }
            },
            totalBottles() {
                return Object.values(this.stocks).reduce((acc, val) => acc + (parseInt(val) || 0), 0);
            }
        }
    }
</script>
@endsection
