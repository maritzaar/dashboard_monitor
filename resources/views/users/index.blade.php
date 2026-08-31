@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')
<div class="w-full space-y-6">
    
    <!-- Header Block -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-4 sm:p-6 shadow-sm transition-colors duration-200">
        <h2 class="text-xl sm:text-2xl font-bold text-slate-800 dark:text-slate-200 flex items-center">
            <i class="fas fa-users text-forest mr-2"></i>
            {{ __('Manajemen Pengguna') }}
        </h2>
        <button onclick="toggleUserModal(true)" class="bg-forest hover:bg-green-700 text-white px-4 py-2.5 rounded-lg transition text-sm font-semibold shadow-sm inline-flex items-center justify-center">
            <i class="fas fa-user-plus mr-2"></i> {{ __('Tambah Pengguna Baru') }}
        </button>
    </div>

    <!-- User Table -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-4 sm:p-6 shadow-sm transition-colors duration-200">
        <div class="overflow-x-auto -mx-4 sm:mx-0 table-scroll">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-white/10 border border-slate-100 dark:border-white/5 rounded-lg overflow-hidden">
                <thead class="bg-slate-50 dark:bg-white/5">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">{{ __('Nama Lengkap') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">{{ __('Nama Pengguna (Username)') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">{{ __('Tanggal Terdaftar') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">{{ __('Peran') }}</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-slate-900 divide-y divide-slate-100 dark:divide-white/5 text-sm transition-colors duration-200">
                    @forelse($users as $user)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-white/5 transition">
                        <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-200 whitespace-nowrap">{{ $user->name }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400 whitespace-nowrap">{{ $user->email }}</td>
                        <td class="px-4 py-3 text-slate-550 dark:text-slate-400 whitespace-nowrap">{{ $user->created_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="px-2.5 py-1 text-xs rounded-full font-semibold border
                                @if($user->role === 'admin') bg-green-50 text-green-700 border-green-200 dark:bg-green-900/20 dark:text-green-400 dark:border-green-800/30
                                @else bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700 @endif">
                                {{ $user->role === 'admin' ? __('Admin') : __('Viewer') }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center whitespace-nowrap space-x-1.5">
                            @if(Auth::id() !== $user->id)
                            <form action="{{ route('users.toggle', $user->id) }}" method="POST" class="inline"
                                  onsubmit="return confirm('{{ __('Yakin ingin mengubah peran pengguna ini?') }}')">
                                @csrf
                                <button type="submit" class="text-xs font-bold px-3 py-2 rounded-lg border transition inline-flex items-center
                                    @if($user->role === 'admin') bg-slate-100 text-slate-700 border-slate-300 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700 dark:hover:bg-slate-700
                                    @else bg-forest text-white border-transparent hover:bg-green-700 shadow-sm @endif">
                                    @if($user->role === 'admin')
                                        <i class="fas fa-user-minus mr-1.5 text-slate-400"></i> {{ __('Ubah Peran') }}
                                    @else
                                        <i class="fas fa-user-shield mr-1.5 text-blue-200"></i> {{ __('Ubah Peran') }}
                                    @endif
                                </button>
                            </form>

                            <button type="button" onclick="openEditUserModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}')"
                                    class="text-xs font-bold px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition inline-flex items-center shadow-sm">
                                <i class="fas fa-edit mr-1.5 text-blue-500"></i> {{ __('Ubah') }}
                            </button>

                            <button type="button" onclick="openResetPasswordModal({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                    class="text-xs font-bold px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition inline-flex items-center shadow-sm">
                                <i class="fas fa-key mr-1.5 text-slate-500 dark:text-slate-400"></i> {{ __('Reset Kata Sandi') }}
                            </button>

                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline"
                                  onsubmit="return confirm('{{ __('Apakah Anda yakin ingin menghapus akun pengguna ini secara permanen?') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-bold px-3 py-2 rounded-lg border border-transparent bg-rose-600 hover:bg-rose-700 text-white transition inline-flex items-center shadow-sm">
                                    <i class="fas fa-trash-can mr-1.5 text-rose-200"></i> {{ __('Hapus') }}
                                </button>
                            </form>
                            @else
                            <span class="text-xs text-slate-400 font-medium italic"><i class="fas fa-user-lock mr-1"></i>{{ __('Anda') }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-slate-450">
                            <i class="fas fa-inbox text-3xl block mb-2 text-slate-350"></i>
                            {{ __('Belum ada pengguna lain terdaftar.') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ================= CREATE USER MODAL ================= -->
<div id="createUserModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl border border-slate-200 w-full max-w-md shadow-2xl overflow-hidden transform transition-all duration-300">
        <!-- Modal Header -->
        <div class="h-14 bg-slate-900 text-white px-5 flex items-center justify-between">
            <span class="font-bold text-sm tracking-wider flex items-center">
                <i class="fas fa-user-plus mr-2 text-blue-400"></i>
                {{ __('Tambah Pengguna Baru') }}
            </span>
            <button onclick="toggleUserModal(false)" class="text-slate-400 hover:text-white transition focus:outline-none p-1">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        
        <!-- Modal Body -->
        <form action="{{ route('users.store') }}" method="POST" class="p-5 space-y-4">
            @csrf
            
            <!-- Full Name -->
            <div>
                <label for="modal_name" class="block text-xs font-semibold text-slate-600 mb-1.5">{{ __('Nama Lengkap') }} <span class="text-rose-500">*</span></label>
                <input type="text" name="name" id="modal_name" required
                       class="w-full rounded-lg border border-slate-300 bg-slate-50 text-slate-800 py-2.5 px-3 focus:border-blue-600 focus:ring-blue-600 focus:outline-none text-sm"
                       placeholder="Contoh: John Doe">
            </div>

            <!-- Username -->
            <div>
                <label for="modal_email" class="block text-xs font-semibold text-slate-600 mb-1.5">{{ __('Nama Pengguna (Username)') }} <span class="text-rose-500">*</span></label>
                <input type="text" name="email" id="modal_email" required
                       class="w-full rounded-lg border border-slate-300 bg-slate-50 text-slate-800 py-2.5 px-3 focus:border-blue-600 focus:ring-blue-600 focus:outline-none text-sm"
                       placeholder="Contoh: john_tpa">
            </div>

            <!-- Password -->
            <div>
                <label for="modal_password" class="block text-xs font-semibold text-slate-600 mb-1.5">{{ __('Kata Sandi') }} <span class="text-rose-500">*</span></label>
                <input type="password" name="password" id="modal_password" required minlength="6"
                       class="w-full rounded-lg border border-slate-300 bg-slate-50 text-slate-800 py-2.5 px-3 focus:border-blue-600 focus:ring-blue-600 focus:outline-none text-sm"
                       placeholder="••••••••">
                <p class="text-[10px] text-slate-400 mt-1"><i class="fas fa-info-circle mr-1"></i>{{ __('Minimal 6 karakter.') }}</p>
            </div>

            <!-- Role Selector -->
            <div>
                <label for="modal_role" class="block text-xs font-semibold text-slate-600 mb-1.5">{{ __('Peran') }} <span class="text-rose-500">*</span></label>
                <select name="role" id="modal_role" required
                        class="w-full rounded-lg border border-slate-300 bg-slate-50 text-slate-800 py-2.5 px-3 focus:border-blue-600 focus:ring-blue-600 focus:outline-none text-sm">
                    <option value="viewer" selected>{{ __('Viewer (Hanya Baca)') }}</option>
                    <option value="admin">{{ __('Admin/Operator (Akses Penuh)') }}</option>
                </select>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end space-x-2.5 pt-4 border-t border-slate-100">
                <button type="button" onclick="toggleUserModal(false)" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-lg transition text-xs font-semibold">
                    {{ __('Batal') }}
                </button>
                <button type="submit" class="bg-forest hover:bg-green-700 text-white px-4 py-2 rounded-lg transition text-xs font-semibold shadow-sm inline-flex items-center">
                    <i class="fas fa-save mr-1.5"></i> {{ __('Simpan Pengguna') }}
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= RESET PASSWORD MODAL ================= -->
<div id="resetPasswordModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl border border-slate-200 w-full max-w-md shadow-2xl overflow-hidden transform transition-all duration-300">
        <!-- Modal Header -->
        <div class="h-14 bg-slate-900 text-white px-5 flex items-center justify-between">
            <span class="font-bold text-sm tracking-wider flex items-center">
                <i class="fas fa-key mr-2 text-blue-400"></i>
                {{ __('Reset Kata Sandi') }}
            </span>
            <button type="button" onclick="toggleResetPasswordModal(false)" class="text-slate-400 hover:text-white transition focus:outline-none p-1">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        
        <!-- Modal Body -->
        <form id="resetPasswordForm" action="" method="POST" class="p-5 space-y-4 text-left">
            @csrf
            
            <p class="text-sm text-slate-650">
                {{ __('Reset kata sandi untuk pengguna:') }} <strong id="reset_user_name" class="text-slate-800"></strong>
            </p>

            <!-- Password -->
            <div>
                <label for="reset_password" class="block text-xs font-semibold text-slate-600 mb-1.5">{{ __('Kata Sandi Baru') }} <span class="text-rose-500">*</span></label>
                <input type="password" name="password" id="reset_password" required minlength="6"
                       class="w-full rounded-lg border border-slate-300 bg-slate-50 text-slate-800 py-2.5 px-3 focus:border-blue-600 focus:ring-blue-600 focus:outline-none text-sm"
                       placeholder="••••••••">
                <p class="text-[10px] text-slate-400 mt-1"><i class="fas fa-info-circle mr-1"></i>{{ __('Minimal 6 karakter.') }}</p>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end space-x-2.5 pt-4 border-t border-slate-100">
                <button type="button" onclick="toggleResetPasswordModal(false)" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-lg transition text-xs font-semibold">
                    {{ __('Batal') }}
                </button>
                <button type="submit" class="bg-forest hover:bg-green-700 text-white px-4 py-2 rounded-lg transition text-xs font-semibold shadow-sm inline-flex items-center">
                    <i class="fas fa-save mr-1.5"></i> {{ __('Simpan Kata Sandi') }}
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= EDIT USER MODAL ================= -->
<div id="editUserModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl border border-slate-200 w-full max-w-md shadow-2xl overflow-hidden transform transition-all duration-300">
        <!-- Modal Header -->
        <div class="h-14 bg-slate-900 text-white px-5 flex items-center justify-between">
            <span class="font-bold text-sm tracking-wider flex items-center">
                <i class="fas fa-user-edit mr-2 text-blue-400"></i>
                {{ __('Ubah Data Pengguna') }}
            </span>
            <button type="button" onclick="toggleEditUserModal(false)" class="text-slate-400 hover:text-white transition focus:outline-none p-1">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        
        <!-- Modal Body -->
        <form id="editUserForm" action="" method="POST" class="p-5 space-y-4 text-left">
            @csrf
            
            <!-- Full Name -->
            <div>
                <label for="edit_name" class="block text-xs font-semibold text-slate-600 mb-1.5">{{ __('Nama Lengkap') }} <span class="text-rose-500">*</span></label>
                <input type="text" name="name" id="edit_name" required
                       class="w-full rounded-lg border border-slate-300 bg-slate-50 text-slate-800 py-2.5 px-3 focus:border-blue-600 focus:ring-blue-600 focus:outline-none text-sm">
            </div>

            <!-- Username -->
            <div>
                <label for="edit_email" class="block text-xs font-semibold text-slate-600 mb-1.5">{{ __('Nama Pengguna (Username)') }} <span class="text-rose-500">*</span></label>
                <input type="text" name="email" id="edit_email" required
                       class="w-full rounded-lg border border-slate-300 bg-slate-50 text-slate-800 py-2.5 px-3 focus:border-blue-600 focus:ring-blue-600 focus:outline-none text-sm">
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end space-x-2.5 pt-4 border-t border-slate-100">
                <button type="button" onclick="toggleEditUserModal(false)" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-lg transition text-xs font-semibold">
                    {{ __('Batal') }}
                </button>
                <button type="submit" class="bg-forest hover:bg-green-700 text-white px-4 py-2 rounded-lg transition text-xs font-semibold shadow-sm inline-flex items-center">
                    <i class="fas fa-save mr-1.5"></i> {{ __('Simpan Perubahan') }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleUserModal(show) {
    const modal = document.getElementById('createUserModal');
    if (show) {
        modal.classList.remove('hidden');
    } else {
        modal.classList.add('hidden');
    }
}

function toggleResetPasswordModal(show) {
    const modal = document.getElementById('resetPasswordModal');
    if (show) {
        modal.classList.remove('hidden');
        document.getElementById('reset_password').value = '';
    } else {
        modal.classList.add('hidden');
    }
}

function openResetPasswordModal(userId, userName) {
    document.getElementById('reset_user_name').innerText = userName;
    
    // Set dynamic route action
    const form = document.getElementById('resetPasswordForm');
    form.action = `/pengguna/reset-password/${userId}`;
    
    toggleResetPasswordModal(true);
}

function toggleEditUserModal(show) {
    const modal = document.getElementById('editUserModal');
    if (show) {
        modal.classList.remove('hidden');
    } else {
        modal.classList.add('hidden');
    }
}

function openEditUserModal(userId, userName, userEmail) {
    document.getElementById('edit_name').value = userName;
    document.getElementById('edit_email').value = userEmail;
    
    // Set dynamic route action
    const form = document.getElementById('editUserForm');
    form.action = `/pengguna/edit/${userId}`;
    
    toggleEditUserModal(true);
}
</script>
@endsection
