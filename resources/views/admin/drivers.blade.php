@extends('layouts.app')

@section('title', 'Kelola Driver')

@section('content')
<div class="space-y-4 sm:space-y-6" x-data="driverManager()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900">Kelola Driver</h1>
            <p class="text-xs text-slate-500 mt-0.5">Tambah, edit, dan atur akun driver gerobak</p>
        </div>
        <button type="button" @click="openCreateModal()"
            class="w-full sm:w-auto px-4 py-2.5 bg-orange-600 hover:bg-orange-700 text-white font-bold text-sm rounded-xl shadow-md shadow-orange-600/30 transition flex items-center justify-center space-x-2">
            <i class="fa-solid fa-plus"></i>
            <span>Tambah Driver</span>
        </button>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm font-medium flex items-center space-x-2" x-show="showSuccess" x-transition>
            <i class="fa-solid fa-check-circle"></i>
            <span>{{ session('success') }}</span>
            <button @click="showSuccess = false" class="ml-auto text-emerald-500 hover:text-emerald-700"><i class="fa-solid fa-xmark text-sm"></i></button>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl text-sm font-medium flex items-center space-x-2" x-show="showError" x-transition>
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span>{{ session('error') }}</span>
            <button @click="showError = false" class="ml-auto text-rose-500 hover:text-rose-700"><i class="fa-solid fa-xmark text-sm"></i></button>
        </div>
    @endif

    <!-- Drivers Table -->
    <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider text-[10px] sm:text-[11px] border-b border-slate-100">
                    <tr>
                        <th class="p-3.5 sm:p-4">Nama</th>
                        <th class="p-3.5 sm:p-4 hidden md:table-cell">Email</th>
                        <th class="p-3.5 sm:p-4 hidden lg:table-cell">Telepon</th>
                        <th class="p-3.5 sm:p-4">Status</th>
                        <th class="p-3.5 sm:p-4 hidden md:table-cell">Shift</th>
                        <th class="p-3.5 sm:p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($drivers as $driver)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-3.5 sm:p-4 font-semibold text-slate-900 whitespace-nowrap">{{ $driver->name }}</td>
                            <td class="p-3.5 sm:p-4 hidden md:table-cell text-slate-600 font-mono text-[10px] truncate max-w-[180px]">{{ $driver->email }}</td>
                            <td class="p-3.5 sm:p-4 hidden lg:table-cell text-slate-500">{{ $driver->phone ?? '-' }}</td>
                            <td class="p-3.5 sm:p-4">
                                <button type="button" @click="toggleStatus({{ $driver->id }}, {{ $driver->is_active ? 'true' : 'false' }})"
                                    class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold transition
                                        {{ $driver->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $driver->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                    <span>{{ $driver->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                </button>
                            </td>
                            <td class="p-3.5 sm:p-4 hidden md:table-cell text-slate-500 text-center">{{ $driver->shifts_count }}</td>
                            <td class="p-3.5 sm:p-4 text-center">
                                <div class="flex items-center justify-center space-x-1.5">
                                    <button type="button" @click="openEditModal({{ json_encode($driver) }})"
                                        class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-orange-600 hover:text-white text-slate-600 flex items-center justify-center transition"
                                        title="Edit Driver">
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </button>
                                    @if(!$driver->shifts_count)
                                        <button type="button" @click="confirmDelete({{ $driver->id }}, '{{ $driver->name }}')"
                                            class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-rose-600 hover:text-white text-slate-600 flex items-center justify-center transition"
                                            title="Hapus Driver">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    @else
                                        <span class="w-8 h-8 rounded-lg bg-slate-100 text-slate-300 flex items-center justify-center cursor-not-allowed"
                                            title="Tidak bisa dihapus (punya data shift)">
                                            <i class="fa-solid fa-ban text-xs"></i>
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400">
                                <i class="fa-solid fa-motorcycle text-3xl mb-2 opacity-50"></i>
                                <p class="font-medium">Belum ada driver terdaftar</p>
                                <p class="text-xs mt-1">Klik "Tambah Driver" untuk memulai</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($drivers->hasPages())
            <div class="p-4 border-t border-slate-100 flex items-center justify-between">
                <p class="text-xs text-slate-500">Menampilkan {{ $drivers->firstItem() }} - {{ $drivers->lastItem() }} dari {{ $drivers->total() }} driver</p>
                {{ $drivers->links('vendor.pagination.tailwind') }}
            </div>
        @endif
    </div>
</div>

<!-- Create Driver Modal -->
<div x-show="showCreateModal" x-transition class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" @click="closeCreateModal()"></div>
    <div class="min-h-full flex items-center justify-center p-3 sm:p-4">
        <div class="relative bg-white w-full max-w-md rounded-2xl sm:rounded-3xl p-5 sm:p-6 shadow-2xl max-h-[90vh] overflow-y-auto" @click.stop>
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                    <h3 class="font-extrabold text-sm text-slate-900">Tambah Driver Baru</h3>
                </div>
                <button type="button" @click="closeCreateModal()" class="w-7 h-7 rounded-full bg-slate-100 text-slate-400 hover:text-slate-700 flex items-center justify-center">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            </div>

            <form action="{{ route('admin.drivers.store') }}" method="POST" class="py-4 space-y-4" @submit.prevent="submitCreateForm">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Nama Lengkap</label>
                    <input type="text" name="name" required
                        class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-orange-500 focus:bg-white outline-none transition"
                        placeholder="Contoh: Budi Santoso">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Email</label>
                    <input type="email" name="email" required
                        class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-orange-500 focus:bg-white outline-none transition"
                        placeholder="budi@haus.com">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Password</label>
                    <input type="password" name="password" required minlength="8"
                        class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-orange-500 focus:bg-white outline-none transition"
                        placeholder="Minimal 8 karakter">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" required
                        class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-orange-500 focus:bg-white outline-none transition"
                        placeholder="Ulangi password">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Telepon (Opsional)</label>
                    <input type="tel" name="phone"
                        class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-orange-500 focus:bg-white outline-none transition"
                        placeholder="08xxxxxxxxxx">
                </div>
                <div class="flex items-center space-x-2">
                    <input type="checkbox" name="is_active" value="1" checked id="create_is_active"
                        class="rounded border-slate-300 text-orange-600 focus:ring-orange-500">
                    <label for="create_is_active" class="text-sm text-slate-700 cursor-pointer">Aktifkan akun ini</label>
                </div>

                <div class="flex space-x-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="closeCreateModal()"
                        class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 py-2.5 bg-orange-600 hover:bg-orange-700 text-white font-bold text-sm rounded-xl shadow-md shadow-orange-600/30 transition">
                        Simpan Driver
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Driver Modal -->
<div x-show="showEditModal" x-transition class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" @click="closeEditModal()"></div>
    <div class="min-h-full flex items-center justify-center p-3 sm:p-4">
        <div class="relative bg-white w-full max-w-md rounded-2xl sm:rounded-3xl p-5 sm:p-6 shadow-2xl max-h-[90vh] overflow-y-auto" @click.stop>
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-pen"></i>
                    </div>
                    <h3 class="font-extrabold text-sm text-slate-900">Edit Driver</h3>
                </div>
                <button type="button" @click="closeEditModal()" class="w-7 h-7 rounded-full bg-slate-100 text-slate-400 hover:text-slate-700 flex items-center justify-center">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            </div>

            <form action="" method="POST" class="py-4 space-y-4" @submit.prevent="submitEditForm">
                @method('PUT')
                @csrf
                <input type="hidden" name="driver_id" x-ref="editDriverId">

                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Nama Lengkap</label>
                    <input type="text" name="name" required x-ref="editName"
                        class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-orange-500 focus:bg-white outline-none transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Email</label>
                    <input type="email" name="email" required x-ref="editEmail"
                        class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-orange-500 focus:bg-white outline-none transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Password Baru (Kosongkan jika tidak diubah)</label>
                    <input type="password" name="password" minlength="8" x-ref="editPassword"
                        class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-orange-500 focus:bg-white outline-none transition"
                        placeholder="Minimal 8 karakter">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" x-ref="editPasswordConfirmation"
                        class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-orange-500 focus:bg-white outline-none transition"
                        placeholder="Ulangi password baru">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Telepon (Opsional)</label>
                    <input type="tel" name="phone" x-ref="editPhone"
                        class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-orange-500 focus:bg-white outline-none transition"
                        placeholder="08xxxxxxxxxx">
                </div>
                <div class="flex items-center space-x-2">
                    <input type="checkbox" name="is_active" value="1" id="edit_is_active" x-ref="editIsActive"
                        class="rounded border-slate-300 text-orange-600 focus:ring-orange-500">
                    <label for="edit_is_active" class="text-sm text-slate-700 cursor-pointer">Aktifkan akun ini</label>
                </div>

                <div class="flex space-x-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="closeEditModal()"
                        class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 py-2.5 bg-orange-600 hover:bg-orange-700 text-white font-bold text-sm rounded-xl shadow-md shadow-orange-600/30 transition">
                        Update Driver
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div x-show="showDeleteModal" x-transition class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" @click="closeDeleteModal()"></div>
    <div class="min-h-full flex items-center justify-center p-3 sm:p-4">
        <div class="relative bg-white w-full max-w-sm rounded-2xl sm:rounded-3xl p-5 sm:p-6 shadow-2xl" @click.stop>
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <h3 class="font-extrabold text-sm text-slate-900">Hapus Driver</h3>
                </div>
            </div>

            <div class="py-4 text-center">
                <p class="text-sm text-slate-600 mb-2">Yakin ingin menghapus driver <span class="font-bold text-slate-900" x-text="deleteDriverName"></span>?</p>
                <p class="text-xs text-slate-500">Tindakan ini tidak bisa dibatalkan.</p>
            </div>

            <div class="flex space-x-2 pt-2 border-t border-slate-100">
                <button type="button" @click="closeDeleteModal()"
                    class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm rounded-xl transition">
                    Batal
                </button>
                <form action="" method="POST" @submit.prevent="submitDeleteForm">
                    @method('DELETE')
                    @csrf
                    <input type="hidden" name="driver_id" x-ref="deleteDriverId">
                    <button type="submit"
                        class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-sm rounded-xl shadow-md shadow-rose-600/30 transition">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function driverManager() {
        return {
            showCreateModal: false,
            showEditModal: false,
            showDeleteModal: false,
            deleteDriverId: null,
            deleteDriverName: '',
            showSuccess: {{ session('success') ? 'true' : 'false' }},
            showError: {{ session('error') ? 'true' : 'false' }},

            openCreateModal() {
                this.showCreateModal = true;
                // Reset form
                this.$nextTick(() => {
                    const form = document.querySelector('form[action="{{ route('admin.drivers.store') }}"]');
                    if (form) form.reset();
                });
            },

            closeCreateModal() {
                this.showCreateModal = false;
            },

            openEditModal(driver) {
                this.$refs.editDriverId.value = driver.id;
                this.$refs.editName.value = driver.name;
                this.$refs.editEmail.value = driver.email;
                this.$refs.editPhone.value = driver.phone ?? '';
                this.$refs.editIsActive.checked = driver.is_active;
                this.$refs.editPassword.value = '';
                this.$refs.editPasswordConfirmation.value = '';
                this.showEditModal = true;
            },

            closeEditModal() {
                this.showEditModal = false;
            },

            confirmDelete(id, name) {
                this.deleteDriverId = id;
                this.deleteDriverName = name;
                this.$refs.deleteDriverId.value = id;
                this.showDeleteModal = true;
            },

            closeDeleteModal() {
                this.showDeleteModal = false;
                this.deleteDriverId = null;
                this.deleteDriverName = '';
            },

            submitCreateForm(event) {
                event.target.submit();
            },

            submitEditForm(event) {
                const form = event.target;
                form.action = "{{ route('admin.drivers.update', ':id') }}".replace(':id', this.$refs.editDriverId.value);
                form.submit();
            },

            submitDeleteForm(event) {
                const form = event.target;
                form.action = "{{ route('admin.drivers.destroy', ':id') }}".replace(':id', this.deleteDriverId);
                form.submit();
            },

            async toggleStatus(driverId, currentStatus) {
                try {
                    const response = await fetch("{{ route('admin.drivers.toggle-status', ':id') }}".replace(':id', driverId), {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                    });
                    const data = await response.json();
                    if (data.success) {
                        // Reload page to reflect changes
                        window.location.reload();
                    } else {
                        alert(data.message || 'Gagal mengubah status');
                    }
                } catch (error) {
                    alert('Terjadi kesalahan: ' + error.message);
                }
            },
        }
    }
</script>
@endpush
@endsection