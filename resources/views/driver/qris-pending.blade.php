@extends('layouts.app')

@section('title', 'Pusat Validasi QRIS (Anti-Fraud)')

@section('content')
<div class="max-w-md mx-auto">

    <!-- Anti-Fraud Awareness Banner -->
    <div class="bg-gradient-to-r from-rose-600 to-red-700 text-white p-4 rounded-2xl shadow-sm mb-4">
        <div class="flex items-start space-x-3">
            <div class="bg-white/20 p-2 rounded-xl">
                <i class="fa-solid fa-triangle-exclamation text-xl"></i>
            </div>
            <div>
                <h3 class="font-black text-sm uppercase tracking-wide">Pencegahan Penipuan QRIS</h3>
                <p class="text-xs text-rose-100 mt-1 leading-relaxed">
                    Jangan percaya screenshot bukti transfer pembeli! <strong>Selalu pastikan saldo/notifikasi bank Anda benar-benar bertambah</strong> sebelum menekan tombol validasi dan menyerahkan minuman.
                </p>
            </div>
        </div>
    </div>

    <!-- Section Title -->
    <div class="flex items-center justify-between mb-3 px-1">
        <div class="flex items-center space-x-2">
            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
            <h2 class="font-extrabold text-sm text-slate-900">Menunggu Validasi Driver ({{ $pendingTransactions->count() }})</h2>
        </div>
        <a href="{{ route('driver.pos') }}" class="text-xs font-bold text-orange-600 hover:text-orange-700">
            <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke POS
        </a>
    </div>

    <!-- Pending Transactions List -->
    @if($pendingTransactions->isEmpty())
        <div class="bg-white border border-slate-200 rounded-2xl p-8 text-center mb-6">
            <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-2 text-xl">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <h4 class="font-bold text-sm text-slate-800">Semua QRIS Aman!</h4>
            <p class="text-xs text-slate-400 mt-1">Tidak ada transaksi QRIS pending saat ini.</p>
        </div>
    @else
        <div class="space-y-3 mb-6">
            @foreach($pendingTransactions as $trx)
                <div class="bg-white border-2 border-amber-300 rounded-2xl p-4 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 right-0 bg-amber-500 text-white font-extrabold text-[10px] uppercase px-3 py-0.5 rounded-bl-xl tracking-wider">
                        PENDING
                    </div>

                    <div class="mb-2">
                        <span class="text-[11px] font-mono text-slate-500">{{ $trx->invoice_number }}</span>
                        <div class="flex items-center justify-between mt-1">
                            <span class="text-lg font-black text-slate-900">Rp {{ number_format($trx->total_amount, 0, ',', '.') }}</span>
                            <span class="text-[11px] text-slate-500"><i class="fa-regular fa-clock mr-1"></i>{{ $trx->transacted_at->diffForHumans() }}</span>
                        </div>
                        @if($trx->location_name)
                            <p class="text-[11px] text-slate-600 mt-0.5"><i class="fa-solid fa-location-dot text-orange-600 mr-1"></i>{{ $trx->location_name }}</p>
                        @endif
                    </div>

                    <!-- Items Detail -->
                    <div class="bg-slate-50 rounded-xl p-2.5 my-3 text-xs space-y-1">
                        @foreach($trx->details as $d)
                            <div class="flex justify-between text-slate-700">
                                <span>{{ $d->qty }}x {{ $d->product->name ?? 'Minuman' }}</span>
                                <span class="font-medium">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>

                    <!-- 2 Touch Buttons: Validate vs Reject Fraud -->
                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <!-- Reject Fraud Button -->
                        <form action="{{ route('driver.qris.reject', $trx) }}" method="POST"
                            onsubmit="return confirm('YAKIN TOLAK TRANSAKSI INI?\n\nGunakan jika pembeli melakukan penipuan/transfer palsu. Stok akan otomatis dikembalikan ke gerobak!')">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                class="w-full py-2.5 px-3 bg-rose-50 hover:bg-rose-100 active:bg-rose-200 border border-rose-300 text-rose-700 font-extrabold text-xs rounded-xl flex items-center justify-center space-x-1 transition">
                                <i class="fa-solid fa-ban"></i>
                                <span>FRAUD / PALSU</span>
                            </button>
                        </form>

                        <!-- Validate Button -->
                        <form action="{{ route('driver.qris.validate', $trx) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                class="w-full py-2.5 px-3 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-extrabold text-xs rounded-xl shadow-md shadow-emerald-600/30 flex items-center justify-center space-x-1 transition">
                                <i class="fa-solid fa-check-double"></i>
                                <span>DANA MASUK</span>
                            </button>
                        </form>
                    </div>

                </div>
            @endforeach
        </div>
    @endif

    <!-- Recently Handled QRIS (History) -->
    @if($historyTransactions->isNotEmpty())
        <div class="mt-8">
            <h3 class="font-bold text-xs uppercase tracking-wider text-slate-400 mb-2 px-1">Riwayat Validasi Terakhir</h3>
            <div class="bg-white border border-slate-200 rounded-xl divide-y divide-slate-100 overflow-hidden">
                @foreach($historyTransactions as $h)
                    <div class="p-3 flex items-center justify-between text-xs">
                        <div>
                            <span class="font-mono text-[10px] text-slate-400">{{ $h->invoice_number }}</span>
                            <p class="font-bold text-slate-800">Rp {{ number_format($h->total_amount, 0, ',', '.') }}</p>
                            <p class="text-[10px] text-slate-500">{{ $h->details->pluck('product.name')->join(', ') }}</p>
                        </div>
                        <div>
                            @if($h->payment_status === 'validated')
                                <span class="bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded text-[10px] inline-flex items-center">
                                    <i class="fa-solid fa-check mr-1"></i> Tervalidasi
                                </span>
                            @else
                                <span class="bg-rose-100 text-rose-800 font-bold px-2 py-0.5 rounded text-[10px] inline-flex items-center">
                                    <i class="fa-solid fa-xmark mr-1"></i> Ditolak (Fraud)
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection

