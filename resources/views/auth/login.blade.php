@extends('layouts.app')

@section('title', 'Masuk ke HACKER - Haus Keliling Tracker')

@section('content')
<div class="min-h-[80vh] flex flex-col justify-center items-center py-6 px-4" x-data="loginForm()">
    <div class="w-full max-w-sm">
        
        <!-- Logo & Header (HD, Clean & Non-Redundant) -->
        <div class="text-center mb-6">
            <img src="{{ asset('images/hacker-logo-hd.png') }}" alt="HACKER - Haus Keliling Tracker"
                class="w-44 sm:w-48 h-auto mx-auto object-contain drop-shadow-sm transition hover:scale-105">
            <p class="text-xs sm:text-sm font-extrabold text-orange-600 mt-2">
                "Retas Target, Bawa Pulang Cuan!"
            </p>
        </div>

        <!-- Login Card -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Email</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                            <i class="fa-solid fa-envelope text-sm"></i>
                        </span>
                        <input type="email" name="email" x-model="email" required autocomplete="email"
                            class="w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-orange-500 focus:bg-white outline-none transition"
                            placeholder="nama@haus.com">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                            <i class="fa-solid fa-lock text-sm"></i>
                        </span>
                        <input type="password" name="password" x-model="password" required
                            class="w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-orange-500 focus:bg-white outline-none transition"
                            placeholder="••••••••">
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs text-slate-600">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="remember" value="1" checked class="rounded border-slate-300 text-orange-600 focus:ring-orange-500">
                        <span>Ingat saya</span>
                    </label>
                </div>

                <button type="submit"
                    class="w-full py-3 px-4 bg-orange-600 hover:bg-orange-700 active:bg-orange-800 text-white font-bold text-sm rounded-xl shadow-md shadow-orange-600/30 transition flex items-center justify-center space-x-2">
                    <span>Masuk ke Sistem</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </button>
            </form>

            </div>

        <p class="text-center text-[11px] text-slate-400 mt-6">
            © 2026 Haus! Indonesia — Huling Mobile Tracking
        </p>
    </div>
</div>

<script>
    function loginForm() {
        return {
            email: '{{ old('email') }}',
            password: '',
        }
    }
</script>
@endsection

