<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'HACKER') — Haus Keliling Tracker</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('images/hacker-logo.jpg') }}">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        haus: {
                            50: '#fff7ed',
                            100: '#ffedd5',
                            500: '#f97316',
                            600: '#ea580c',
                            700: '#c2410c',
                            800: '#9a3412',
                            teal: '#0d9488',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        /* Touch friendly tap target */
        button, a, input, select {
            touch-action: manipulation;
        }
        /* Hide scrollbars for category pills */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
    @stack('styles')
</head>
<body class="h-full flex flex-col text-slate-800 antialiased font-sans">

    @auth
        @if(auth()->user()->isAdmin())
            <!-- Admin Top Navigation with Mobile Drawer -->
            <header class="bg-slate-900 text-white shadow-md sticky top-0 z-40" x-data="{ mobileMenu: false }">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between h-16">
                        <!-- Brand -->
                        <div class="flex items-center space-x-2.5">
                            <div class="bg-white p-1 rounded-xl flex items-center shadow-xs">
                                <img src="{{ asset('images/hacker-icon.png') }}" alt="HACKER" class="h-8 w-auto object-contain">
                            </div>
                            <div>
                                <span class="font-black text-sm text-white block leading-tight tracking-tight">HACKER PORTAL</span>
                                <span class="text-[10px] text-orange-400 font-bold block">Retas Target, Bawa Pulang Cuan!</span>
                            </div>
                        </div>

                        <!-- Desktop Navigation Links -->
                        <nav class="hidden md:flex items-center space-x-1 lg:space-x-2 text-xs lg:text-sm">
                            <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 rounded-xl font-bold {{ request()->routeIs('admin.dashboard') ? 'bg-orange-600 text-white shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
                                <i class="fa-solid fa-chart-pie mr-1"></i> Dashboard
                            </a>
                            <a href="{{ route('admin.dead-stock') }}" class="px-3 py-2 rounded-xl font-bold {{ request()->routeIs('admin.dead-stock') ? 'bg-orange-600 text-white shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
                                <i class="fa-solid fa-triangle-exclamation mr-1"></i> Dead Stock
                            </a>
                            <a href="{{ route('admin.transactions') }}" class="px-3 py-2 rounded-xl font-bold {{ request()->routeIs('admin.transactions') ? 'bg-orange-600 text-white shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
                                <i class="fa-solid fa-receipt mr-1"></i> Transaksi
                            </a>
                            <a href="{{ route('admin.shifts') }}" class="px-3 py-2 rounded-xl font-bold {{ request()->routeIs('admin.shifts') ? 'bg-orange-600 text-white shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
                                <i class="fa-solid fa-route mr-1"></i> Shift
                            </a>
                            <a href="{{ route('admin.drivers') }}" class="px-3 py-2 rounded-xl font-bold {{ request()->routeIs('admin.drivers') ? 'bg-orange-600 text-white shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
                                <i class="fa-solid fa-users mr-1"></i> Driver
                            </a>
                            <a href="{{ route('admin.products') }}" class="px-3 py-2 rounded-xl font-bold {{ request()->routeIs('admin.products') ? 'bg-orange-600 text-white shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
                                <i class="fa-solid fa-wine-bottle mr-1"></i> Produk
                            </a>
                            <a href="{{ route('admin.locations') }}" class="px-3 py-2 rounded-xl font-bold {{ request()->routeIs('admin.locations') ? 'bg-orange-600 text-white shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
                                <i class="fa-solid fa-location-dot mr-1"></i> Lokasi
                            </a>
                        </nav>

                        <!-- Right Section (User & Mobile Menu Toggle) -->
                        <div class="flex items-center space-x-2">
                            <span class="text-xs text-slate-400 hidden xl:inline">{{ auth()->user()->name }}</span>
                            
                            <!-- Mobile Menu Hamburger Button -->
                            <button type="button" @click="mobileMenu = !mobileMenu"
                                class="md:hidden p-2 text-slate-300 hover:text-white bg-slate-800 rounded-xl focus:outline-none">
                                <i class="fa-solid" :class="mobileMenu ? 'fa-xmark text-lg' : 'fa-bars text-lg'"></i>
                            </button>

                            <form action="{{ route('logout') }}" method="POST" class="hidden sm:inline">
                                @csrf
                                <button type="submit" class="text-slate-400 hover:text-rose-400 p-2 rounded-xl hover:bg-slate-800" title="Logout">
                                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Mobile Dropdown Navigation Menu -->
                <div x-show="mobileMenu" x-transition
                    class="md:hidden bg-slate-800 border-t border-slate-700/80 px-4 py-3 space-y-1 shadow-2xl"
                    style="display: none;">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-xs font-bold {{ request()->routeIs('admin.dashboard') ? 'bg-orange-600 text-white' : 'text-slate-200 hover:bg-slate-700' }}">
                        <i class="fa-solid fa-chart-pie w-5 text-center"></i>
                        <span>Dashboard Utama</span>
                    </a>
                    <a href="{{ route('admin.dead-stock') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-xs font-bold {{ request()->routeIs('admin.dead-stock') ? 'bg-orange-600 text-white' : 'text-slate-200 hover:bg-slate-700' }}">
                        <i class="fa-solid fa-triangle-exclamation w-5 text-center text-amber-400"></i>
                        <span>Mitigasi Dead Stock</span>
                    </a>
                    <a href="{{ route('admin.transactions') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-xs font-bold {{ request()->routeIs('admin.transactions') ? 'bg-orange-600 text-white' : 'text-slate-200 hover:bg-slate-700' }}">
                        <i class="fa-solid fa-receipt w-5 text-center text-teal-400"></i>
                        <span>Riwayat Semua Transaksi</span>
                    </a>
                    <a href="{{ route('admin.shifts') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-xs font-bold {{ request()->routeIs('admin.shifts') ? 'bg-orange-600 text-white' : 'text-slate-200 hover:bg-slate-700' }}">
                        <i class="fa-solid fa-route w-5 text-center text-blue-400"></i>
                        <span>Shift Driver Keliling</span>
                    </a>
                    <a href="{{ route('admin.drivers') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-xs font-bold {{ request()->routeIs('admin.drivers') ? 'bg-orange-600 text-white' : 'text-slate-200 hover:bg-slate-700' }}">
                        <i class="fa-solid fa-users w-5 text-center text-purple-400"></i>
                        <span>Kelola Driver</span>
                    </a>
                    <a href="{{ route('admin.products') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-xs font-bold {{ request()->routeIs('admin.products') ? 'bg-orange-600 text-white' : 'text-slate-200 hover:bg-slate-700' }}">
                        <i class="fa-solid fa-wine-bottle w-5 text-center text-orange-400"></i>
                        <span>Katalog Produk</span>
                    </a>
                    <a href="{{ route('admin.locations') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-xs font-bold {{ request()->routeIs('admin.locations') ? 'bg-orange-600 text-white' : 'text-slate-200 hover:bg-slate-700' }}">
                        <i class="fa-solid fa-location-dot w-5 text-center text-emerald-400"></i>
                        <span>Titik Lokasi GPS</span>
                    </a>

                    <div class="pt-2 mt-2 border-t border-slate-700 flex items-center justify-between text-xs text-slate-400">
                        <span>{{ auth()->user()->name }}</span>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-rose-400 font-bold flex items-center space-x-1 py-1">
                                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </header>
        @else
            <!-- Driver Top Status Bar -->
            <header class="bg-white border-b border-slate-200 sticky top-0 z-40 px-4 py-3 shadow-sm">
                <div class="max-w-md mx-auto flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <img src="{{ asset('images/hacker-icon.png') }}" alt="HACKER" class="h-9 w-auto object-contain">
                        <div class="border-l border-slate-200 pl-2">
                            <div class="flex items-center space-x-1.5">
                                <span class="font-black text-sm text-slate-900 tracking-tight leading-none">HACKER</span>
                                <span class="text-[9px] bg-orange-100 text-orange-700 font-extrabold px-1.5 py-0.2 rounded">DRIVER</span>
                            </div>
                            <p class="text-[10px] text-slate-500 font-medium leading-none mt-1">{{ auth()->user()->name }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('driver.qris.pending') }}" class="relative inline-flex items-center p-2 text-slate-600 hover:text-orange-600">
                            <i class="fa-solid fa-qrcode text-lg"></i>
                            @php
                                $currentShiftId = \App\Models\Shift::where('user_id', auth()->id())->where('status', 'open')->latest('shift_date')->value('id');
                                $pendingQrisCount = $currentShiftId ? \App\Models\Transaction::where('shift_id', $currentShiftId)->where('payment_method', 'qris')->where('payment_status', 'pending')->count() : 0;
                            @endphp
                            @if($pendingQrisCount > 0)
                                <span class="absolute top-1 right-1 flex h-4 w-4">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-4 w-4 bg-rose-600 text-white text-[10px] font-bold items-center justify-center">{{ $pendingQrisCount }}</span>
                                </span>
                            @endif
                        </a>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-slate-400 hover:text-rose-600 p-2" title="Keluar">
                                <i class="fa-solid fa-power-off text-base"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </header>
        @endif
    @endauth

    <!-- Flash Messages -->
    <div class="max-w-4xl mx-auto px-4 mt-3 w-full">
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" class="mb-3 p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm rounded-xl flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fa-solid fa-circle-check text-emerald-600 mr-2 text-base"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button @click="show = false" class="text-emerald-500 hover:text-emerald-700"><i class="fa-solid fa-xmark"></i></button>
            </div>
        @endif

        @if(session('warning'))
            <div x-data="{ show: true }" x-show="show" class="mb-3 p-3 bg-amber-50 border border-amber-200 text-amber-800 text-sm rounded-xl flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fa-solid fa-triangle-exclamation text-amber-600 mr-2 text-base"></i>
                    <span>{{ session('warning') }}</span>
                </div>
                <button @click="show = false" class="text-amber-500 hover:text-amber-700"><i class="fa-solid fa-xmark"></i></button>
            </div>
        @endif

        @if(session('error') || $errors->any())
            <div x-data="{ show: true }" x-show="show" class="mb-3 p-3 bg-rose-50 border border-rose-200 text-rose-800 text-sm rounded-xl flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fa-solid fa-circle-exclamation text-rose-600 mr-2 text-base"></i>
                    <div>
                        @if(session('error')) <span>{{ session('error') }}</span> @endif
                        @foreach($errors->all() as $err) <div>{{ $err }}</div> @endforeach
                    </div>
                </div>
                <button @click="show = false" class="text-rose-500 hover:text-rose-700"><i class="fa-solid fa-xmark"></i></button>
            </div>
        @endif
    </div>

    <!-- Main Content Area -->
    <main class="flex-1 w-full max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-3 pb-24 sm:pb-8">
        @yield('content')
    </main>

    @auth
        @if(auth()->user()->isDriver())
            <!-- Driver Bottom Tab Navigation (Mobile App Feel) -->
            <nav class="fixed bottom-0 inset-x-0 bg-white border-t border-slate-200 py-1.5 px-2 z-40 shadow-lg">
                <div class="max-w-md mx-auto grid grid-cols-4 gap-1">
                    <a href="{{ route('driver.pos') }}" class="flex flex-col items-center py-1 {{ request()->routeIs('driver.pos') ? 'text-orange-600 font-bold' : 'text-slate-500 hover:text-orange-500' }}">
                        <i class="fa-solid fa-cash-register text-lg"></i>
                        <span class="text-[10px] mt-0.5">Kasir POS</span>
                    </a>
                    <a href="{{ route('driver.qris.pending') }}" class="relative flex flex-col items-center py-1 {{ request()->routeIs('driver.qris.pending') ? 'text-orange-600 font-bold' : 'text-slate-500 hover:text-orange-500' }}">
                        <i class="fa-solid fa-shield-halved text-lg"></i>
                        <span class="text-[10px] mt-0.5">Validasi QRIS</span>
                        @if($pendingQrisCount > 0)
                            <span class="absolute top-0 right-4 bg-rose-600 text-white text-[9px] font-bold px-1.5 rounded-full">
                                {{ $pendingQrisCount }}
                            </span>
                        @endif
                    </a>
                    <a href="{{ route('driver.transactions') }}" class="flex flex-col items-center py-1 {{ request()->routeIs('driver.transactions') ? 'text-orange-600 font-bold' : 'text-slate-500 hover:text-orange-500' }}">
                        <i class="fa-solid fa-clock-rotate-left text-lg"></i>
                        <span class="text-[10px] mt-0.5">Riwayat Trx</span>
                    </a>
                    <a href="{{ route('driver.recap') }}" class="flex flex-col items-center py-1 {{ request()->routeIs('driver.recap') || request()->routeIs('driver.shift') ? 'text-orange-600 font-bold' : 'text-slate-500 hover:text-orange-500' }}">
                        <i class="fa-solid fa-receipt text-lg"></i>
                        <span class="text-[10px] mt-0.5">Rekap Stok</span>
                    </a>
                </div>
            </nav>
        @endif
    @endauth

    @stack('scripts')
</body>
</html>

