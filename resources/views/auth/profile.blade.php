@extends('layouts.app')

@section('title', __('Edit Profil'))

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center">
            <i class="fas fa-user-cog text-[#8E6E4F] mr-2"></i>
            {{ __('Edit Profil') }}
        </h2>

        @if($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Personal Info -->
            <div class="space-y-4">
                <h3 class="text-md font-semibold text-gray-700 border-b pb-2">
                    {{ __('Informasi Profil') }}
                </h3>
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Nama Lengkap') }}</label>
                    <input type="text" name="name" id="name" required value="{{ old('name', $user->name) }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#8E6E4F] focus:ring-[#8E6E4F] p-2.5 border">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Alamat Email') }}</label>
                    <input type="email" name="email" id="email" required value="{{ old('email', $user->email) }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#8E6E4F] focus:ring-[#8E6E4F] p-2.5 border">
                </div>
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Nomor Ponsel') }}</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#8E6E4F] focus:ring-[#8E6E4F] p-2.5 border"
                           placeholder="0812XXXXXXXX">
                </div>
                <div>
                    <label for="company" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Profil Perusahaan') }}</label>
                    <input type="text" name="company" id="company" value="{{ old('company', $user->company) }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#8E6E4F] focus:ring-[#8E6E4F] p-2.5 border"
                           placeholder="PT United Tractors Tbk">
                </div>
            </div>

            <!-- Change Password -->
            <div class="space-y-4 pt-4">
                <h3 class="text-md font-semibold text-gray-700 border-b pb-2">
                    {{ __('Ubah Kata Sandi') }} <span class="text-xs font-normal text-gray-400">({{ __('Kosongkan jika tidak ingin mengubah') }})</span>
                </h3>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Kata Sandi Baru') }}</label>
                    <input type="password" name="password" id="password"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#8E6E4F] focus:ring-[#8E6E4F] p-2.5 border"
                           placeholder="••••••••">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Konfirmasi Kata Sandi Baru') }}</label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#8E6E4F] focus:ring-[#8E6E4F] p-2.5 border"
                           placeholder="••••••••">
                </div>
            </div>

            <div class="flex justify-end space-x-4 pt-4 border-t">
                <a href="{{ route('monitoring.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2 rounded-lg transition text-sm font-medium">
                    {{ __('Batal') }}
                </a>
                <button type="submit" class="bg-[#8E6E4F] hover:bg-[#7D5F43] text-white px-6 py-2 rounded-lg transition text-sm font-medium">
                    {{ __('Simpan Perubahan') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
