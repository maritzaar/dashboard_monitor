<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Teladan Prima Agro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Outfit', sans-serif;
        }
    </style>
</head>
<body class="bg-white min-h-screen flex flex-col md:flex-row overflow-x-hidden">
    
    <!-- LEFT SIDE: Login Form (Dark Blue Background - 55% Width) -->
     <!-- command -->
    <div class="w-full md:w-[55%] bg-[#0F172A] text-white flex flex-col justify-between p-6 sm:p-10 md:p-16 relative">
        
        <!-- Header Brand Info -->
        <div class="flex items-center space-x-2 z-10">
            <img src="{{ asset('images/logo.png') }}" alt="TPA Logo" class="h-8 w-auto mr-2 flex-shrink-0">
            <span class="font-bold text-xs tracking-widest text-slate-350">TELADAN PRIMA AGRO</span>
        </div>

        <!-- Form Container -->
        <div class="my-auto py-10 max-w-md w-full mx-auto z-10 space-y-8">
            <div>
                <p class="text-blue-400 text-sm font-semibold uppercase tracking-wider mb-2">Welcome back to</p>
                <h2 class="text-3xl font-extrabold text-white tracking-wide leading-tight">
                    Teladan Prima Agro
                </h2>
                <div class="h-1.5 w-16 bg-blue-600 rounded-full mt-4"></div>
            </div>

            <!-- Success/Error Banner -->
            @if(session('success'))
                <div class="bg-emerald-950/40 border border-emerald-800 text-emerald-300 p-4 rounded-xl text-sm flex items-center shadow-sm">
                    <i class="fas fa-check-circle mr-2.5 text-emerald-400"></i> {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-rose-950/40 border border-rose-800 text-rose-300 p-4 rounded-xl text-sm shadow-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-6">
                @csrf
                
                <!-- Username Input -->
                <div class="space-y-2">
                    <label for="email" class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Username <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500">
                            <i class="far fa-user text-sm"></i>
                        </span>
                        <input type="text" name="email" id="email" required value="{{ old('email') }}"
                               class="block w-full pl-10 pr-4 py-3 bg-slate-900 border border-slate-750 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-650 focus:border-transparent transition text-sm"
                               placeholder="Username">
                    </div>
                </div>

                <!-- Password Input -->
                <div class="space-y-2">
                    <label for="password" class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Password <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500">
                            <i class="fas fa-lock text-sm"></i>
                        </span>
                        <input type="password" name="password" id="password" required
                               class="block w-full pl-10 pr-4 py-3 bg-slate-900 border border-slate-750 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-650 focus:border-transparent transition text-sm"
                               placeholder="Password">
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition duration-200 transform active:scale-98 shadow-md hover:shadow-blue-500/10 flex items-center justify-center text-sm">
                    Login <i class="fas fa-arrow-right ml-2 text-xs"></i>
                </button>
            </form>
        </div>

        <!-- Footer Copyright -->
        <div class="text-xs text-slate-500 z-10 flex justify-between mt-auto pt-6 border-t border-slate-800/60 font-medium">
            <span>&copy; {{ date('Y') }} PT. Teladan Prima Agro, Tbk.</span>
        </div>
    </div>

    <!-- RIGHT SIDE: Large Corporate Logo (White Background - 45% Width) -->
    <div class="hidden md:flex md:w-[45%] bg-white items-center justify-center p-12 relative overflow-hidden select-none border-l border-slate-100">
        
        <!-- Large centered logo and info -->
        <div class="text-center space-y-8 max-w-sm z-10">
            <div class="inline-block transition hover:scale-105 duration-300">
                <img src="{{ asset('images/logo.png') }}" alt="Teladan Prima Agro Logo" class="w-48 h-auto object-contain mx-auto">
            </div>
            <div class="space-y-3">
                <h3 class="text-xl font-extrabold text-slate-800 tracking-wide uppercase">PT. Teladan Prima Agro</h3>
            </div>
        </div>
        
        <!-- Subtle background decorative blobs -->
        <div class="absolute w-80 h-80 rounded-full bg-blue-50/30 -bottom-20 -right-20 blur-3xl"></div>
        <div class="absolute w-60 h-60 rounded-full bg-slate-50 -top-10 -left-10 blur-2xl"></div>
    </div>
</body>
</html>