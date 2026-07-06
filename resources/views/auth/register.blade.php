<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Monitoring Alat Berat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Outfit', sans-serif;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-[#FAF7F2] via-[#E6DCCF] to-[#A7825E] min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
    <!-- Floating Language Switcher -->
    <div class="absolute top-4 right-4 z-20 flex items-center space-x-1 text-xs bg-white/80 backdrop-blur-md rounded-xl p-1 border border-[#E6DCCF] shadow-md">
        <a href="{{ route('lang.switch', 'id') }}" class="px-3 py-1.5 rounded-lg font-semibold transition {{ app()->getLocale() == 'id' ? 'bg-[#8E6E4F] text-white shadow-sm' : 'text-stone-500 hover:text-stone-850' }}">ID</a>
        <a href="{{ route('lang.switch', 'en') }}" class="px-3 py-1.5 rounded-lg font-semibold transition {{ app()->getLocale() == 'en' ? 'bg-[#8E6E4F] text-white shadow-sm' : 'text-stone-500 hover:text-stone-850' }}">EN</a>
    </div>

    <!-- Decorative background elements -->
    <div class="absolute w-96 h-96 rounded-full bg-[#A7825E]/10 -top-12 -left-12 blur-3xl"></div>
    <div class="absolute w-96 h-96 rounded-full bg-[#FAF7F2]/40 -bottom-12 -right-12 blur-3xl"></div>
 
    <div class="w-full max-w-md bg-white/90 backdrop-blur-md border border-[#E6DCCF] rounded-2xl shadow-xl p-8 relative z-10">
        <!-- Logo/Header -->
        <div class="text-center mb-6">
            <div class="inline-flex p-3 bg-[#F5EBE0] rounded-2xl border border-[#E6DCCF] text-[#704F37] mb-3 shadow-md shadow-[#8E6E4F]/5">
                <i class="fas fa-user-plus text-3xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-stone-850 tracking-wide">{{ __('Buat Akun Baru') }}</h2>
            <p class="text-stone-500 text-sm mt-1">{{ __('Daftarkan diri Anda untuk masuk ke sistem') }}</p>
        </div>
 
        <!-- Validation Error Banner -->
        @if($errors->any())
            <div class="bg-rose-50 border border-rose-100 text-rose-800 p-3.5 mb-6 rounded-xl text-sm shadow-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
 
        <form action="{{ route('register') }}" method="POST" class="space-y-4">
            @csrf
            
            <!-- Name Input -->
            <div>
                <label for="name" class="block text-xs font-semibold text-stone-600 uppercase tracking-wider mb-1.5">{{ __('Nama Lengkap') }}</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-stone-400">
                        <i class="far fa-user"></i>
                    </span>
                    <input type="text" name="name" id="name" required value="{{ old('name') }}"
                           class="block w-full pl-10 pr-4 py-2.5 bg-stone-50 border border-[#E6DCCF] rounded-xl text-stone-800 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-[#8E6E4F]/40 focus:border-[#8E6E4F] transition text-sm"
                           placeholder="John Doe">
                </div>
            </div>
 
            <!-- Email Input -->
            <div>
                <label for="email" class="block text-xs font-semibold text-stone-600 uppercase tracking-wider mb-1.5">{{ __('Alamat Email') }}</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-stone-400">
                        <i class="far fa-envelope"></i>
                    </span>
                    <input type="email" name="email" id="email" required value="{{ old('email') }}"
                           class="block w-full pl-10 pr-4 py-2.5 bg-stone-50 border border-[#E6DCCF] rounded-xl text-stone-800 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-[#8E6E4F]/40 focus:border-[#8E6E4F] transition text-sm"
                           placeholder="nama@email.com">
                </div>
            </div>
 
            <!-- Password Input -->
            <div>
                <label for="password" class="block text-xs font-semibold text-stone-600 uppercase tracking-wider mb-1.5">{{ __('Kata Sandi') }}</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-stone-400">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" name="password" id="password" required
                           class="block w-full pl-10 pr-4 py-2.5 bg-stone-50 border border-[#E6DCCF] rounded-xl text-stone-800 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-[#8E6E4F]/40 focus:border-[#8E6E4F] transition text-sm"
                           placeholder="••••••••">
                </div>
            </div>
 
            <!-- Password Confirmation Input -->
            <div>
                <label for="password_confirmation" class="block text-xs font-semibold text-stone-600 uppercase tracking-wider mb-1.5">{{ __('Konfirmasi Kata Sandi') }}</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-stone-400">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                           class="block w-full pl-10 pr-4 py-2.5 bg-stone-50 border border-[#E6DCCF] rounded-xl text-stone-800 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-[#8E6E4F]/40 focus:border-[#8E6E4F] transition text-sm"
                           placeholder="••••••••">
                </div>
            </div>
 
            <!-- Submit Button -->
            <button type="submit" 
                    class="w-full py-3 bg-gradient-to-r from-[#8E6E4F] to-[#7D5F43] hover:from-[#7D5F43] hover:to-[#8E6E4F] text-white font-semibold rounded-xl transition duration-300 transform active:scale-98 shadow-lg shadow-[#8E6E4F]/20 flex items-center justify-center mt-6">
                {{ __('Daftar Akun') }} <i class="fas fa-arrow-right ml-2 text-xs"></i>
            </button>
        </form>
 
        <!-- Redirect Link -->
        <div class="mt-6 text-center text-sm">
            <span class="text-stone-500">{{ __('Sudah punya akun?') }}</span>
            <a href="{{ route('login') }}" class="text-[#8E6E4F] hover:text-[#7D5F43] font-semibold ml-1 transition hover:underline">{{ __('Masuk di sini') }}</a>
        </div>
    </div>
</body>
</html>
