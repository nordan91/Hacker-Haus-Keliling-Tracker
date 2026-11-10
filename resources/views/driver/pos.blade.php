@extends('layouts.app')

@section('title', 'Kasir Penjualan (POS)')

@section('content')
<div class="max-w-md mx-auto" x-data="posApp({{ json_encode($inventories) }}, {{ json_encode($locations) }}, {{ json_encode($gamification) }})">

    <!-- Top GPS Geotagging Banner -->
    <div class="mb-2.5 bg-white border border-slate-200 rounded-xl p-2.5 shadow-xs flex items-center justify-between">
        <div class="flex items-center space-x-2">
            <span class="relative flex h-3 w-3">
                <span :class="geoLocated ? 'bg-emerald-400' : 'bg-amber-400'" class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"></span>
                <span :class="geoLocated ? 'bg-emerald-500' : 'bg-amber-500'" class="relative inline-flex rounded-full h-3 w-3"></span>
            </span>
            <div>
                <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Geotag Lokasi Jualan</p>
                <p class="text-xs font-bold text-slate-800" x-text="matchedLocationName || 'Mencari sinyal GPS HP...'"></p>
            </div>
        </div>
        <button type="button" @click="detectLocation()" class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-600 px-2 py-1 rounded-lg font-medium flex items-center space-x-1">
            <i class="fa-solid fa-location-crosshairs text-orange-600"></i>
            <span class="text-[10px]">Refresh</span>
        </button>
    </div>

    <!-- Gamified Target & Live Incentive Tracker (Psychological Boost) -->
    <div class="mb-3 bg-gradient-to-r from-slate-900 to-slate-800 text-white rounded-2xl p-3.5 shadow-sm border border-slate-700/60">
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center space-x-2">
                <span class="px-2 py-0.5 rounded-lg text-[10px] font-black"
                    :class="gamification.badge?.color || 'bg-orange-500 text-white'"
                    x-text="gamification.badge?.title || 'Semangat Mulai 🚀'">
                </span>
                <span class="text-[10px] text-slate-400 font-medium">Rp 1.000/cup</span>
            </div>

            <!-- Total Incentive Collected -->
            <div class="text-right">
                <span class="text-[9px] uppercase font-bold text-slate-400 block tracking-wider">Insentifmu</span>
                <span class="text-base font-black text-amber-400" x-text="gamification.incentive_formatted || 'Rp 0'"></span>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="w-full bg-slate-700/80 h-2.5 rounded-full overflow-hidden p-0.5">
            <div class="bg-gradient-to-r from-orange-500 to-amber-400 h-1.5 rounded-full transition-all duration-500"
                :style="'width: ' + gamification.progress_percent + '%'"></div>
        </div>

        <!-- Motivational Guidance (Low Pressure, High Encouragement) -->
        <div class="flex items-center justify-between mt-2 text-[11px]">
            <span class="text-slate-300 font-medium">
                🎯 Terjual: <strong class="text-white" x-text="gamification.sold_qty"></strong> / <span x-text="gamification.target_qty"></span> Cup
                (<span x-text="gamification.progress_percent + '%'"></span>)
            </span>

            <template x-if="gamification.cups_to_target > 0">
                <span class="text-orange-300 font-bold">
                    Sisa <span x-text="gamification.cups_to_target"></span> cup lagi!
                </span>
            </template>
            <template x-if="gamification.cups_to_target === 0">
                <span class="text-emerald-400 font-black animate-pulse">
                    🎉 Target Tuntas! Bonus lanjut!
                </span>
            </template>
        </div>

        <!-- Live Status Nombok vs Bebas Nombok -->
        <div class="mt-2.5 pt-2 border-t border-slate-700/60 flex items-center justify-between text-[10px]">
            <template x-if="!gamification.is_nombok">
                <div class="flex items-center space-x-1.5 text-emerald-400 font-black">
                    <i class="fa-solid fa-shield-heart text-xs"></i>
                    <span>ZONA AMAN BEBAS NOMBOK!</span>
                </div>
            </template>
            <template x-if="gamification.is_nombok">
                <div class="flex items-center space-x-1.5 text-amber-300 font-bold">
                    <i class="fa-solid fa-shield-halved text-xs text-amber-400"></i>
                    <span>Posisi Tanggungan: <strong class="text-rose-300" x-text="'-Rp ' + (gamification.nombok_amount || 0).toLocaleString('id-ID')"></strong></span>
                </div>
            </template>

            <div>
                <template x-if="!gamification.is_nombok">
                    <span class="bg-emerald-500/20 text-emerald-300 px-2 py-0.5 rounded font-black text-[10px]"
                        x-text="'Kantong: +Rp ' + (gamification.net_take_home || 0).toLocaleString('id-ID')">
                    </span>
                </template>
                <template x-if="gamification.is_nombok">
                    <span class="text-slate-300 text-[10px] font-medium">
                        Sisa <strong class="text-white" x-text="gamification.unsold_qty || 0"></strong> botol lagi
                    </span>
                </template>
            </div>
        </div>
    </div>

    <!-- Category Filter Pills -->
    <div class="flex space-x-2 overflow-x-auto no-scrollbar pb-2 mb-3">
        <template x-for="cat in categories" :key="cat">
            <button type="button" @click="selectedCategory = cat"
                :class="selectedCategory === cat ? 'bg-orange-600 text-white font-bold shadow-sm' : 'bg-white text-slate-600 border border-slate-200'"
                class="px-3 py-1.5 rounded-full text-xs whitespace-nowrap transition active:scale-95"
                x-text="cat">
            </button>
        </template>
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-2 gap-2.5 mb-24">
        <template x-for="inv in filteredInventories" :key="inv.id">
            <div class="bg-white rounded-xl border border-slate-200 p-3 shadow-xs flex flex-col justify-between transition active:scale-[0.98] select-none"
                :class="inv.remaining_qty <= 0 ? 'opacity-50 grayscale' : 'cursor-pointer hover:border-orange-300'"
                @click="addToCart(inv)">
                
                <div>
                    <!-- Category Badge & Remaining Stock -->
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[9px] font-semibold uppercase px-1.5 py-0.5 rounded bg-slate-100 text-slate-600"
                            x-text="inv.product.category || 'Haus!'"></span>
                        
                        <span :class="inv.remaining_qty <= 3 ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700'"
                            class="text-[10px] font-black px-1.5 py-0.5 rounded">
                            Sisa <span x-text="inv.remaining_qty"></span>
                        </span>
                    </div>

                    <!-- Beverage Name -->
                    <h4 class="font-extrabold text-sm text-slate-900 leading-snug line-clamp-2" x-text="inv.product.name"></h4>
                </div>

                <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between">
                    <div>
                        <span class="font-black text-xs text-orange-600 block" x-text="formatRupiah(inv.product.price)"></span>
                        <span class="text-[9px] text-emerald-600 font-bold">+Rp 1.000</span>
                    </div>
                    
                    <button type="button"
                        :disabled="inv.remaining_qty <= 0"
                        class="w-7 h-7 rounded-lg bg-orange-600 text-white flex items-center justify-center font-bold text-xs shadow-xs active:bg-orange-700">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                </div>
            </div>
        </template>
    </div>

    <!-- Floating Bottom Cart Bar -->
    <div x-show="cartTotalItems > 0" x-transition
        class="fixed bottom-16 inset-x-3 max-w-md mx-auto z-30">
        <div @click="openCheckoutModal = true"
            class="bg-slate-900 text-white p-3 rounded-2xl shadow-xl flex items-center justify-between cursor-pointer active:scale-[0.99] transition">
            <div class="flex items-center space-x-3">
                <div class="relative bg-orange-600 text-white w-10 h-10 rounded-xl flex items-center justify-center font-black">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <span class="absolute -top-1 -right-1 bg-rose-500 text-white text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center"
                        x-text="cartTotalItems"></span>
                </div>
                <div>
                    <p class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold">Total Belanja</p>
                    <p class="text-sm font-black text-white" x-text="formatRupiah(cartTotalPrice)"></p>
                </div>
            </div>

            <div class="flex items-center space-x-1.5 bg-orange-600 text-white px-3 py-2 rounded-xl text-xs font-bold">
                <span>Bayar</span>
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
            </div>
        </div>
    </div>

    <!-- Checkout Modal / Bottom Sheet -->
    <div x-show="openCheckoutModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="openCheckoutModal = false"></div>

        <div class="min-h-full flex items-end sm:items-center justify-center p-0 sm:p-4">
            <div class="relative bg-white w-full max-w-md rounded-t-2xl sm:rounded-2xl p-5 shadow-2xl max-h-[90vh] flex flex-col"
                @click.stop>
                
                <!-- Drawer Header -->
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-receipt text-orange-600"></i>
                        <h3 class="font-extrabold text-base text-slate-800">Detail Pesanan</h3>
                    </div>
                    <button type="button" @click="openCheckoutModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <!-- Cart Items List -->
                <div class="flex-1 overflow-y-auto py-3 space-y-2.5 max-h-60 no-scrollbar">
                    <template x-for="(item, index) in cart" :key="item.product_id">
                        <div class="flex items-center justify-between p-2.5 bg-slate-50 rounded-xl">
                            <div class="flex-1 pr-2">
                                <h5 class="text-xs font-bold text-slate-800 leading-tight" x-text="item.name"></h5>
                                <p class="text-[11px] text-slate-500 mt-0.5" x-text="formatRupiah(item.price) + ' × ' + item.qty"></p>
                            </div>

                            <div class="flex items-center space-x-1.5">
                                <button type="button" @click="updateCartQty(item.product_id, -1)"
                                    class="w-7 h-7 bg-white border border-slate-200 rounded-lg text-slate-700 font-bold text-xs flex items-center justify-center active:bg-slate-100">
                                    -
                                </button>
                                <span class="w-6 text-center font-black text-xs" x-text="item.qty"></span>
                                <button type="button" @click="updateCartQty(item.product_id, 1)"
                                    class="w-7 h-7 bg-orange-100 border border-orange-200 rounded-lg text-orange-700 font-bold text-xs flex items-center justify-center active:bg-orange-200">
                                    +
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Incentive Preview for this transaction -->
                <div class="p-2.5 bg-emerald-50 rounded-xl border border-emerald-200 flex items-center justify-between text-xs text-emerald-800 mb-3">
                    <span class="flex items-center font-bold">
                        <i class="fa-solid fa-plus-circle text-emerald-600 mr-1.5"></i>
                        Insentif Transaksi Ini:
                    </span>
                    <span class="font-black text-emerald-700" x-text="'+Rp ' + (cartTotalItems * 1000).toLocaleString('id-ID')"></span>
                </div>

                <!-- Payment Method Toggle -->
                <div class="pt-2 border-t border-slate-100">
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Metode Pembayaran</label>
                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <button type="button" @click="paymentMethod = 'cash'"
                            :class="paymentMethod === 'cash' ? 'bg-emerald-500 text-white font-bold border-emerald-600 shadow-xs' : 'bg-slate-50 text-slate-700 border-slate-200'"
                            class="p-2.5 rounded-xl border flex items-center justify-center space-x-2 text-xs transition">
                            <i class="fa-solid fa-money-bill-wave"></i>
                            <span>Tunai (Cash)</span>
                        </button>

                        <button type="button" @click="paymentMethod = 'qris'"
                            :class="paymentMethod === 'qris' ? 'bg-orange-600 text-white font-bold border-orange-700 shadow-xs' : 'bg-slate-50 text-slate-700 border-slate-200'"
                            class="p-2.5 rounded-xl border flex items-center justify-center space-x-2 text-xs transition">
                            <i class="fa-solid fa-qrcode"></i>
                            <span>QRIS</span>
                        </button>
                    </div>

                    <!-- Anti-Fraud Warning for QRIS -->
                    <div x-show="paymentMethod === 'qris'" class="p-2.5 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-[11px] mb-3 flex items-start space-x-2">
                        <i class="fa-solid fa-shield-halved text-amber-600 mt-0.5"></i>
                        <span><strong>Anti-Fraud:</strong> Status akan <em>PENDING</em> sampai Anda validasi dana benar-benar masuk di rekening/notifikasi bank Anda!</span>
                    </div>

                    <!-- Geotag note -->
                    <div class="flex items-center justify-between text-[11px] text-slate-500 mb-4 px-1">
                        <span>Lokasi Terlampir:</span>
                        <span class="font-semibold text-slate-700" x-text="matchedLocationName || 'Koordinat GPS HP'"></span>
                    </div>

                    <!-- Total & Checkout Button -->
                    <div class="flex items-center justify-between mb-3 px-1">
                        <span class="text-xs text-slate-500">Total Tagihan:</span>
                        <span class="text-lg font-black text-slate-900" x-text="formatRupiah(cartTotalPrice)"></span>
                    </div>

                    <button type="button" @click="processCheckout()"
                        :disabled="isSubmitting || cart.length === 0"
                        class="w-full py-3.5 bg-orange-600 hover:bg-orange-700 active:bg-orange-800 text-white font-black text-sm rounded-xl shadow-lg shadow-orange-600/30 flex items-center justify-center space-x-2 transition disabled:opacity-50">
                        <span x-show="!isSubmitting">SELESAIKAN TRANSAKSI</span>
                        <span x-show="isSubmitting">Menyimpan Transaksi...</span>
                    </button>
                </div>

            </div>
        </div>
    </div>

</div>

<script>
    function posApp(initialInventories, locations, initialGamification) {
        return {
            inventories: initialInventories,
            locations: locations,
            gamification: initialGamification || {
                sold_qty: 0,
                target_qty: 40,
                progress_percent: 0,
                incentive: 0,
                incentive_formatted: 'Rp 0',
                cups_to_target: 40,
                badge: { title: 'Semangat Mulai 🚀', color: 'bg-slate-700 text-white' }
            },
            selectedCategory: 'Semua',
            cart: [],
            paymentMethod: 'cash',
            openCheckoutModal: false,
            isSubmitting: false,
            
            // Geolocation
            latitude: null,
            longitude: null,
            geoLocated: false,
            matchedLocationName: null,

            init() {
                this.detectLocation();
            },

            get categories() {
                let cats = ['Semua'];
                this.inventories.forEach(inv => {
                    if (inv.product.category && !cats.includes(inv.product.category)) {
                        cats.push(inv.product.category);
                    }
                });
                return cats;
            },

            get filteredInventories() {
                if (this.selectedCategory === 'Semua') {
                    return this.inventories;
                }
                return this.inventories.filter(inv => inv.product.category === this.selectedCategory);
            },

            get cartTotalItems() {
                return this.cart.reduce((acc, item) => acc + item.qty, 0);
            },

            get cartTotalPrice() {
                return this.cart.reduce((acc, item) => acc + (item.price * item.qty), 0);
            },

            addToCart(inv) {
                if (inv.remaining_qty <= 0) return;

                let existing = this.cart.find(i => i.product_id === inv.product_id);
                if (existing) {
                    if (existing.qty < inv.remaining_qty) {
                        existing.qty++;
                    } else {
                        alert('Stok tidak mencukupi untuk menambah lebih banyak!');
                    }
                } else {
                    this.cart.push({
                        product_id: inv.product_id,
                        name: inv.product.name,
                        price: inv.product.price,
                        qty: 1,
                        max_qty: inv.remaining_qty
                    });
                }
            },

            updateCartQty(productId, delta) {
                let item = this.cart.find(i => i.product_id === productId);
                if (!item) return;

                let inv = this.inventories.find(i => i.product_id === productId);
                let newQty = item.qty + delta;

                if (newQty <= 0) {
                    this.cart = this.cart.filter(i => i.product_id !== productId);
                } else if (inv && newQty <= inv.remaining_qty) {
                    item.qty = newQty;
                } else {
                    alert('Melebihi sisa stok yang ada!');
                }
            },

            detectLocation() {
                if ('geolocation' in navigator) {
                    navigator.geolocation.getCurrentPosition(
                        (pos) => {
                            this.latitude = pos.coords.latitude;
                            this.longitude = pos.coords.longitude;
                            this.geoLocated = true;
                            this.findNearestLocation(this.latitude, this.longitude);
                        },
                        (err) => {
                            console.warn('GPS Error / Denied:', err.message);
                            this.matchedLocationName = 'Area Penjualan Keliling (GPS Off)';
                        },
                        { enableHighAccuracy: true, timeout: 10000 }
                    );
                } else {
                    this.matchedLocationName = 'Area Penjualan Keliling';
                }
            },

            findNearestLocation(lat, lng) {
                let nearest = null;
                let minDistance = Infinity;

                this.locations.forEach(loc => {
                    let d = this.calculateDistance(lat, lng, loc.latitude, loc.longitude);
                    if (d <= loc.radius_meters && d < minDistance) {
                        nearest = loc;
                        minDistance = d;
                    }
                });

                if (nearest) {
                    this.matchedLocationName = nearest.name;
                } else {
                    this.matchedLocationName = 'Keliling (Lat: ' + lat.toFixed(4) + ')';
                }
            },

            calculateDistance(lat1, lon1, lat2, lon2) {
                let R = 6371000; // meters
                let dLat = (lat2 - lat1) * Math.PI / 180;
                let dLon = (lon2 - lon1) * Math.PI / 180;
                let a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                        Math.sin(dLon/2) * Math.sin(dLon/2);
                let c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                return R * c;
            },

            async processCheckout() {
                if (this.cart.length === 0 || this.isSubmitting) return;

                this.isSubmitting = true;

                let payload = {
                    payment_method: this.paymentMethod,
                    latitude: this.latitude,
                    longitude: this.longitude,
                    items: this.cart.map(i => ({ product_id: i.product_id, qty: i.qty }))
                };

                try {
                    let token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    let res = await fetch('{{ route('driver.pos.checkout') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });

                    let data = await res.json();

                    if (data.success) {
                        // Update inventory local cache
                        this.inventories = data.inventories;
                        this.cart = [];
                        this.openCheckoutModal = false;

                        // Update gamification & incentive
                        if (data.gamification) {
                            this.gamification = data.gamification;
                        }

                        if (this.paymentMethod === 'qris') {
                            alert('⚠️ TRANSAKSI QRIS TERCATAT!\n\nStatus: PENDING.\nJangan serahkan minuman sebelum dana divalidasi!');
                            window.location.href = '{{ route('driver.qris.pending') }}';
                        } else {
                            alert(data.message || '✅ Transaksi Berhasil!');
                        }
                    } else {
                        alert('Gagal: ' + (data.message || 'Terjadi kesalahan sistem.'));
                    }
                } catch (e) {
                    alert('Gagal mengirim transaksi: ' + e.message);
                } finally {
                    this.isSubmitting = false;
                }
            },

            formatRupiah(num) {
                return 'Rp ' + (num || 0).toLocaleString('id-ID');
            }
        }
    }
</script>
@endsection
