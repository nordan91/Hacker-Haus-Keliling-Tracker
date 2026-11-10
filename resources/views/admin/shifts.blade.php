@extends('layouts.app')

@section('title', 'Riwayat Shift Driver')

@section('content')
<div class="space-y-6">

    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
        <div>
            <h1 class="text-xl font-black text-slate-900">Riwayat Shift Driver Keliling</h1>
            <p class="text-xs text-slate-500 mt-1">Audit per shift: status buka/tutup, jam kerja, penerimaan uang tunai & QRIS.</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider text-[10px] border-b border-slate-100">
                    <tr>
                        <th class="p-3.5">Tanggal</th>
                        <th class="p-3.5">Nama Driver</th>
                        <th class="p-3.5">Status Shift</th>
                        <th class="p-3.5">Jam Operasional</th>
                        <th class="p-3.5 text-right">Uang Tunai</th>
                        <th class="p-3.5 text-right">QRIS Sah</th>
                        <th class="p-3.5 text-right">Insentif Driver</th>
                        <th class="p-3.5 text-center">Status Nombok</th>
                        <th class="p-3.5 text-right">Total Omset</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($shifts as $shift)
                        <tr class="hover:bg-slate-50/50">
                            <td class="p-3.5 font-bold text-slate-800">{{ $shift->shift_date->format('d M Y') }}</td>
                            <td class="p-3.5 font-semibold text-slate-900">{{ $shift->user->name ?? '-' }}</td>
                            <td class="p-3.5">
                                <span class="font-bold text-[10px] px-2 py-0.5 rounded uppercase {{ $shift->isOpen() ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $shift->status }}
                                </span>
                            </td>
                            <td class="p-3.5 text-slate-500">
                                {{ $shift->started_at ? $shift->started_at->format('H:i') : '-' }} s/d {{ $shift->ended_at ? $shift->ended_at->format('H:i') : 'Aktif' }}
                            </td>
                            <td class="p-3.5 text-right font-semibold text-emerald-600">
                                Rp {{ number_format($shift->totalCash(), 0, ',', '.') }}
                            </td>
                            <td class="p-3.5 text-right font-semibold text-teal-600">
                                Rp {{ number_format($shift->totalQrisValidated(), 0, ',', '.') }}
                            </td>
                            <td class="p-3.5 text-right">
                                <span class="font-bold text-slate-800">Rp {{ number_format($shift->totalIncentive(), 0, ',', '.') }}</span>
                                <span class="block text-[9px] text-slate-400">({{ $shift->totalSoldQty() }} cup)</span>
                            </td>
                            <td class="p-3.5 text-center">
                                @if($shift->isNombok())
                                    <span class="inline-block bg-rose-50 text-rose-700 font-black text-[10px] px-2 py-0.5 rounded border border-rose-200">
                                        Nombok Rp {{ number_format($shift->nombokAmount(), 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="inline-block bg-emerald-50 text-emerald-700 font-black text-[10px] px-2 py-0.5 rounded border border-emerald-200">
                                        Bebas (Surplus +{{ number_format($shift->netTakeHomeIncentive(), 0, ',', '.') }})
                                    </span>
                                @endif
                            </td>
                            <td class="p-3.5 text-right font-black text-slate-900">
                                Rp {{ number_format($shift->totalCash() + $shift->totalQrisValidated(), 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-8 text-center text-slate-400">Belum ada riwayat shift tersimpan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($shifts->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $shifts->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

