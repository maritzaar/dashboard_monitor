<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Beranda') - Teladan Prima Agro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        forest: '#218838',
                        chalice: '#AAAAAA',
                        tpaGreen: '#218838',
                        tpaOrange: '#D37A3C',
                    },
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                        serif: ['Poppins', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="{{ asset('js/chart.js') }}"></script>
    <script>
        // Check local storage for theme
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap');
        body { font-family: 'Poppins', sans-serif; font-size: 1rem; }
        @media print { .no-print { display: none !important; } }

        /* Scale up Tailwind text utility classes globally for readability & comfort */
        .text-\[9px\] { font-size: 0.75rem !important; }   /* ~12px */
        .text-\[10px\] { font-size: 0.8rem !important; }   /* ~12.8px */
        .text-xs { font-size: 0.875rem !important; }        /* ~14px */
        .text-sm { font-size: 0.975rem !important; }        /* ~15.6px */
        .text-base { font-size: 1.1rem !important; }        /* ~17.6px */
        .text-lg { font-size: 1.25rem !important; }        /* ~20px */
        .text-xl { font-size: 1.45rem !important; }        /* ~23px */
        .text-2xl { font-size: 1.75rem !important; }        /* ~28px */
        .text-3xl { font-size: 2.15rem !important; }        /* ~34px */

        /* Scrollable table wrapper on mobile */
        .table-scroll { -webkit-overflow-scrolling: touch; }

        /* Page load animation */
        @keyframes pageFadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .page-transition {
            animation: pageFadeIn 0.35s ease-out forwards;
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-white via-slate-100 to-slate-200 dark:from-[#0B1120] dark:via-[#0F172A] dark:to-slate-900/80 text-slate-800 dark:text-slate-200 flex flex-col transition-colors duration-200">

    @auth
    <!-- Mobile Menu Dropdown -->
    <div id="mobileMenu"
         class="hidden fixed top-20 sm:top-24 left-2 right-2 rounded-2xl bg-[#0F172A]/90 dark:bg-[#0B1120]/90 backdrop-blur-2xl backdrop-saturate-150 border border-slate-700 dark:border-white/10 shadow-2xl z-30 lg:hidden no-print overflow-y-auto max-h-[calc(100vh-6rem)]">
        <div class="p-4 space-y-3">
            <!-- Home -->
            <a href="{{ route('home') }}"
               class="flex items-center space-x-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition
               {{ Route::currentRouteName() === 'home'
                   ? 'bg-white/10 text-white border-l-4 border-blue-500 pl-3'
                   : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                <i class="fas fa-home w-5 text-center"></i>
                <span>Beranda</span>
            </a>

            <!-- Pemantauan (Mobile Accordion) -->
            @php
                $monitoringRoutes = ['monitoring.working_hour', 'monitoring.fuel', 'monitoring.working_hour_detail', 'monitoring.fuel_detail', 'monitoring.flow', 'monitoring.efficiency'];
                $monitoringActive = in_array(Route::currentRouteName(), $monitoringRoutes);
            @endphp
            <div>
                <button type="button" id="mobileMonitoringToggle"
                        class="w-full flex items-center justify-between px-4 py-2.5 rounded-lg text-sm font-bold transition focus:outline-none
                        {{ $monitoringActive ? 'text-white bg-white/10' : 'text-slate-300 hover:bg-white/5' }}">
                    <span class="flex items-center space-x-3">
                        <i class="fas fa-chart-line w-5 text-center text-slate-400"></i>
                        <span>Pemantauan</span>
                    </span>
                    <i id="mobileMonitoringChevron"
                       class="fas fa-chevron-down text-xs transition-transform duration-200 {{ $monitoringActive ? 'rotate-180' : '' }}"></i>
                </button>
                <div id="mobileMonitoringMenu" class="{{ $monitoringActive ? '' : 'hidden' }} mt-1.5 ml-4 pl-3 border-l border-slate-700 space-y-1">
                    <a href="{{ route('monitoring.working_hour') }}"
                       class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition
                              {{ in_array(Route::currentRouteName(), ['monitoring.working_hour', 'monitoring.working_hour_detail'])
                                  ? 'bg-forest text-white shadow-sm'
                                  : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                        <i class="fas fa-clock w-4 text-center"></i>
                        <span>Jam Kerja</span>
                    </a>
                    <a href="{{ route('monitoring.fuel') }}"
                       class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition
                              {{ request()->routeIs('monitoring.fuel*') ? 'bg-forest text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                        <i class="fas fa-gas-pump w-4 text-center"></i>
                        <span>Konsumsi Solar</span>
                    </a>
                    <a href="{{ route('monitoring.efficiency') }}"
                       class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition
                              {{ request()->routeIs('monitoring.efficiency*') ? 'bg-forest text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                        <i class="fas fa-tachometer-alt w-4 text-center"></i>
                        <span>Efisiensi BBM</span>
                    </a>
                    <a href="{{ route('monitoring.flow') }}"
                       class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition
                              {{ request()->routeIs('monitoring.flow*') ? 'bg-forest text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                        <i class="fas fa-project-diagram w-4 text-center"></i>
                        <span>Alur Integrasi</span>
                    </a>
                </div>
            </div>

            <!-- Kelola Data (Mobile Accordion) (Admin Only) -->
            @if(Auth::user()->role === 'admin')
            @php
                $adminRoutes = ['import.index','import.upload','import.clear','users.index'];
                $adminActive = in_array(Route::currentRouteName(), $adminRoutes);
            @endphp
            <div>
                <button type="button" id="mobileAdminToggle"
                        class="w-full flex items-center justify-between px-4 py-2.5 rounded-lg text-sm font-bold transition focus:outline-none
                        {{ $adminActive ? 'text-white bg-white/10' : 'text-slate-300 hover:bg-white/5' }}">
                    <span class="flex items-center space-x-3">
                        <i class="fas fa-shield-alt w-5 text-center text-slate-400"></i>
                        <span>Manajemen Data</span>
                    </span>
                    <i id="mobileAdminChevron"
                       class="fas fa-chevron-down text-xs transition-transform duration-200 {{ $adminActive ? 'rotate-180' : '' }}"></i>
                </button>
                <div id="mobileAdminMenu" class="{{ $adminActive ? '' : 'hidden' }} mt-1.5 ml-4 pl-3 border-l border-slate-700 space-y-1">
                    <a href="{{ route('import.index') }}"
                       class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition
                              {{ Route::currentRouteName() === 'import.index' ? 'bg-forest text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                        <i class="fas fa-upload w-4 text-center"></i>
                        <span>Impor Telemetri</span>
                    </a>
                    <a href="{{ route('users.index') }}"
                       class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition
                              {{ request()->routeIs('users.index') ? 'bg-forest text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                        <i class="fas fa-users-cog w-4 text-center"></i>
                        <span>Manajemen Pengguna</span>
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endauth

    <!-- ======== TOP NAVBAR ======== -->
    <nav class="fixed top-2 sm:top-4 left-2 sm:left-4 right-2 sm:right-4 max-w-screen-2xl mx-auto h-16 bg-[#0F172A]/70 dark:bg-[#0B1120]/70 backdrop-blur-xl backdrop-saturate-150 border border-white/20 border-b-white/10 dark:border-white/10 rounded-2xl sm:rounded-full text-white px-4 sm:px-6 shadow-lg shadow-slate-900/20 dark:shadow-none z-40 flex justify-between items-center no-print transition-all duration-300">
        <!-- Left: hamburger + brand + tabs -->
        <div class="flex items-center space-x-2 sm:space-x-3 min-w-0">
            @auth
            <button id="mobileMenuToggle"
                    class="lg:hidden text-white hover:text-slate-300 transition focus:outline-none p-1 flex-shrink-0"
                    title="Toggle Menu">
                <i class="fas fa-bars text-xl" id="mobileMenuIcon"></i>
            </button>
            @endauth
            <a href="{{ route('home') }}" class="flex items-center hover:opacity-80 transition-opacity">
                <h1 class="text-xs sm:text-sm md:text-base font-bold tracking-wider flex items-center select-none whitespace-nowrap text-white">
                    <img src="{{ asset('images/logo.png') }}" alt="TPA Logo" class="h-11 w-auto mr-2 flex-shrink-0">
                    <span>TELADAN PRIMA AGRO</span>
                </h1>
            </a>

            @auth
            <!-- Navigation Tabs (Desktop) -->
            <div class="hidden lg:flex items-center space-x-1 ml-4 md:ml-6">
                <!-- Home -->
                <a href="{{ route('home') }}"
                   class="flex items-center space-x-1.5 px-3 py-2 rounded-lg text-sm font-semibold transition-colors duration-150
                   {{ Route::currentRouteName() === 'home'
                       ? 'bg-white/15 text-white shadow-sm'
                       : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <i class="fas fa-home text-sm"></i>
                    <span>Beranda</span>
                </a>

                <!-- Pemantauan (Dropdown) -->
                <div class="relative inline-block text-left" id="monitoringDropdownContainer">
                    <button type="button" id="monitoringDropdownButton"
                            class="flex items-center space-x-1.5 px-3 py-2 rounded-lg text-sm font-semibold transition-colors duration-150 focus:outline-none
                            {{ $monitoringActive
                                ? 'bg-white/15 text-white'
                                : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <i class="fas fa-chart-line text-sm"></i>
                        <span>Pemantauan</span>
                        <i class="fas fa-chevron-down text-[10px] transition-transform duration-150" id="monitoringChevron"></i>
                    </button>
                    <!-- Dropdown menu -->
                    <div id="monitoringDropdownMenu"
                         class="hidden absolute left-0 mt-2 w-52 bg-white/70 dark:bg-[#0B1120]/60 backdrop-blur-2xl rounded-xl shadow-2xl border border-slate-200/50 dark:border-white/10 divide-y divide-slate-100/50 dark:divide-white/5 z-50 text-sm no-print">
                        <div class="p-1.5 space-y-1">
                            <a href="{{ route('monitoring.working_hour') }}"
                               class="flex items-center space-x-2.5 px-3 py-2 rounded-lg transition-colors font-medium
                                      {{ in_array(Route::currentRouteName(), ['monitoring.working_hour', 'monitoring.working_hour_detail'])
                                          ? 'bg-forest text-white shadow-sm'
                                          : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/5 hover:text-forest dark:hover:text-forest' }}">
                                <i class="fas fa-clock w-4 text-center"></i>
                                <span>Jam Kerja</span>
                            </a>
                            <a href="{{ route('monitoring.fuel') }}"
                               class="flex items-center space-x-2.5 px-3 py-2 rounded-lg transition-colors font-medium
                                      {{ request()->routeIs('monitoring.fuel*')
                                          ? 'bg-forest text-white shadow-sm'
                                          : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/5 hover:text-forest dark:hover:text-forest' }}">
                                <i class="fas fa-gas-pump w-4 text-center"></i>
                                <span>Konsumsi Solar</span>
                            </a>
                            <a href="{{ route('monitoring.efficiency') }}"
                               class="flex items-center space-x-2.5 px-3 py-2 rounded-lg transition-colors font-medium
                                      {{ request()->routeIs('monitoring.efficiency*')
                                          ? 'bg-forest text-white shadow-sm'
                                          : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/5 hover:text-forest dark:hover:text-forest' }}">
                                <i class="fas fa-tachometer-alt w-4 text-center"></i>
                                <span>Efisiensi BBM</span>
                            </a>
                            <a href="{{ route('monitoring.flow') }}"
                               class="flex items-center space-x-2.5 px-3 py-2 rounded-lg transition-colors font-medium
                                      {{ request()->routeIs('monitoring.flow*')
                                          ? 'bg-forest text-white shadow-sm'
                                          : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/5 hover:text-forest dark:hover:text-forest' }}">
                                <i class="fas fa-project-diagram w-4 text-center"></i>
                                <span>Alur Sistem Data</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Kelola Data (Dropdown, Admin Only) -->
                @if(Auth::user()->role === 'admin')
                <div class="relative inline-block text-left" id="adminDropdownContainer">
                    <button type="button" id="adminDropdownButton"
                            class="flex items-center space-x-1.5 px-3 py-2 rounded-lg text-sm font-semibold transition-colors duration-150 focus:outline-none
                            {{ $adminActive
                                ? 'bg-white/15 text-white'
                                : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <i class="fas fa-shield-alt text-sm"></i>
                        <span>Manajemen Data</span>
                        <i class="fas fa-chevron-down text-[10px] transition-transform duration-150" id="adminChevron"></i>
                    </button>
                    <!-- Dropdown menu -->
                    <div id="adminDropdownMenu"
                         class="hidden absolute left-0 mt-2 w-52 bg-white/70 dark:bg-[#0B1120]/60 backdrop-blur-2xl rounded-xl shadow-2xl border border-slate-200/50 dark:border-white/10 divide-y divide-slate-100/50 dark:divide-white/5 z-50 text-sm no-print">
                        <div class="p-1.5 space-y-1">
                            <a href="{{ route('import.index') }}"
                               class="flex items-center space-x-2.5 px-3 py-2 rounded-lg transition-colors font-medium
                                      {{ Route::currentRouteName() === 'import.index'
                                          ? 'bg-forest text-white shadow-sm'
                                          : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/5 hover:text-forest dark:hover:text-forest' }}">
                                <i class="fas fa-upload w-4 text-center"></i>
                                <span>Impor Telemetri</span>
                            </a>
                            <a href="{{ route('users.index') }}"
                               class="flex items-center space-x-2.5 px-3 py-2 rounded-lg transition-colors font-medium
                                      {{ request()->routeIs('users.index')
                                          ? 'bg-forest text-white shadow-sm'
                                          : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/5 hover:text-forest dark:hover:text-forest' }}">
                                <i class="fas fa-users-cog w-4 text-center"></i>
                                <span>Manajemen Pengguna</span>
                            </a>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @endauth
        </div>

        <!-- Right: theme toggle + user info -->
        <div class="flex items-center space-x-2 sm:space-x-4 flex-shrink-0">
            <!-- Theme Toggle -->
            <button id="themeToggleBtn" class="text-slate-300 hover:text-white transition focus:outline-none p-2 rounded-full hover:bg-slate-800 mr-1 sm:mr-2">
                <i id="themeToggleIcon" class="fas fa-moon text-lg"></i>
            </button>
            
            @auth
            <!-- Notification Bell -->
            <div class="relative inline-block text-left mr-1 sm:mr-3" id="notificationDropdownContainer">
                <button type="button" id="notificationDropdownButton" class="relative text-slate-300 hover:text-white transition focus:outline-none p-2 rounded-full hover:bg-slate-800 flex-shrink-0">
                    <i class="fas fa-bell text-lg"></i>
                    @if(auth()->user()->unreadNotifications->count() > 0)
                        <span class="absolute top-1 right-1 flex h-3.5 w-3.5 items-center justify-center rounded-full bg-rose-500 text-[9px] font-bold text-white shadow-sm ring-2 ring-[#0F172A]">
                            {{ auth()->user()->unreadNotifications->count() }}
                        </span>
                    @endif
                </button>
                
                <!-- Notification Dropdown -->
                <div id="notificationDropdownMenu" class="hidden absolute right-0 mt-2 w-72 sm:w-80 bg-white/70 dark:bg-[#0B1120]/60 backdrop-blur-2xl rounded-xl shadow-2xl border border-slate-200/50 dark:border-white/10 divide-y divide-slate-100/50 dark:divide-white/5 z-50 text-sm no-print">
                    <div class="p-3 flex justify-between items-center border-b border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-slate-800/50 rounded-t-xl">
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider px-1"><i class="fas fa-bell mr-1"></i> Notifikasi</p>
                        @if(auth()->user()->unreadNotifications->count() > 0)
                        <form action="{{ route('notifications.markAllAsRead') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-[10px] text-blue-600 hover:text-blue-800 dark:text-blue-400 font-bold uppercase tracking-wider px-1 transition">Tandai Semua Dibaca</button>
                        </form>
                        @endif
                    </div>
                    <div class="max-h-[300px] overflow-y-auto">
                        @forelse(auth()->user()->unreadNotifications->take(5) as $notification)
                            <div class="p-3 border-b border-slate-50 dark:border-white/5 hover:bg-slate-50 dark:hover:bg-white/5 transition flex flex-col gap-1.5 group">
                                <p class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed">{{ $notification->data['message'] ?? 'Notifikasi baru' }}</p>
                                <div class="flex justify-between items-center mt-1">
                                    <span class="text-[10px] text-slate-400"><i class="far fa-clock mr-1"></i>{{ $notification->created_at->diffForHumans() }}</span>
                                    <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-[10px] text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 font-bold opacity-0 group-hover:opacity-100 transition-opacity"><i class="fas fa-check mr-1"></i>Tandai Dibaca</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-center text-slate-400 dark:text-slate-500 flex flex-col items-center">
                                <i class="fas fa-bell-slash text-2xl mb-2 opacity-50"></i>
                                <p class="text-xs">Belum ada notifikasi baru.</p>
                            </div>
                        @endforelse
                    </div>
                    <div class="p-2 text-center bg-slate-50 dark:bg-slate-800/50 rounded-b-xl border-t border-slate-100 dark:border-white/5">
                        <a href="{{ route('notifications.index') }}" class="text-xs text-forest hover:text-blue-700 dark:text-emerald-500 dark:hover:text-emerald-400 font-bold w-full block py-1.5 transition">Lihat Semua Notifikasi <i class="fas fa-arrow-right ml-1"></i></a>
                    </div>
                </div>
            </div>
            <div class="relative inline-block text-left" id="profileDropdownContainer">
                <div class="flex items-center space-x-2 sm:space-x-3">
                    <!-- User name -->
                    <span class="hidden sm:inline text-xs text-slate-300 font-bold uppercase tracking-wide max-w-[140px] lg:max-w-[200px] truncate select-none">
                        {{ Auth::user()->name }}
                    </span>
                    <!-- Avatar button -->
                    <button type="button" id="profileDropdownButton"
                            class="w-9 h-9 rounded-full bg-forest hover:bg-blue-700 text-white font-bold
                                   flex items-center justify-center transition focus:outline-none select-none text-sm shadow flex-shrink-0">
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    </button>
                </div>

                <!-- Dropdown -->
                <div id="profileDropdownMenu"
                     class="hidden absolute right-0 mt-2 w-56 bg-white/70 dark:bg-[#0B1120]/60 backdrop-blur-2xl rounded-xl shadow-2xl border border-slate-200/50 dark:border-white/10 divide-y divide-slate-100/50 dark:divide-white/5 z-50 text-sm no-print">
                    <div class="p-3">
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1.5 px-2">Profil Saya</p>
                        <a href="{{ route('profile.edit') }}"
                           class="flex items-center space-x-2 px-3 py-2 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-white/5 hover:text-forest dark:hover:text-forest rounded-lg transition font-medium">
                            <i class="fas fa-user-cog text-slate-400"></i>
                            <span>Ubah Profil</span>
                        </a>
                    </div>
                    <div class="p-3">
                        <form action="{{ route('logout') }}" method="POST" class="w-full">
                            @csrf
                            <button type="submit"
                                    class="w-full flex items-center space-x-2 px-3 py-2 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg transition font-medium text-left">
                                <i class="fas fa-sign-out-alt opacity-70"></i>
                                <span>Keluar</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @else
            <!-- Guest controls -->
            <a href="{{ route('login') }}" class="bg-forest hover:bg-blue-700 text-white px-2.5 py-1.5 rounded-lg transition flex items-center text-sm font-semibold shadow-sm">
                <i class="fas fa-sign-in-alt mr-1"></i>
                <span>Masuk</span>
            </a>
            @endauth
        </div>
    </nav>

    <!-- ======== BODY WRAPPER ======== -->
    <div id="mainWrapper" class="pt-24 flex-1 flex flex-col min-h-[calc(100vh-6rem)]">
        <main class="flex-1 p-3 sm:p-4 md:p-6 w-full max-w-screen-2xl mx-auto page-transition">

            @if(session('success'))
            <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-3 sm:p-4 mb-4 rounded shadow-sm no-print flex items-start space-x-2">
                <i class="fas fa-check-circle text-emerald-600 mt-0.5 flex-shrink-0"></i>
                <span class="text-sm">{{ session('success') }}</span>
            </div>
            @endif

            @if(session('error'))
            <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-800 p-3 sm:p-4 mb-4 rounded shadow-sm no-print flex items-start space-x-2">
                <i class="fas fa-exclamation-circle text-rose-600 mt-0.5 flex-shrink-0"></i>
                <span class="text-sm">{{ session('error') }}</span>
            </div>
            @endif

            @yield('content')
        </main>

        <footer class="bg-slate-100 dark:bg-[#0B1120] text-center p-3 text-slate-500 dark:text-slate-400 text-xs border-t border-slate-200 dark:border-white/5 no-print transition-colors duration-200">
            &copy; {{ date('Y') }} PT. Teladan Prima Agro.
        </footer>
    </div>

    <!-- ======== JS ======== -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- Utility to toggle clean rotations ---
        function toggleRotation(chevronEl, shouldRotate) {
            if (!chevronEl) return;
            if (shouldRotate) {
                chevronEl.classList.add('rotate-180');
            } else {
                chevronEl.classList.remove('rotate-180');
            }
        }

        // --- Profile dropdown ---
        const dropBtn  = document.getElementById('profileDropdownButton');
        const dropMenu = document.getElementById('profileDropdownMenu');
        if (dropBtn && dropMenu) {
            dropBtn.addEventListener('click', e => { e.stopPropagation(); dropMenu.classList.toggle('hidden'); });
            document.addEventListener('click', e => {
                if (!e.target.closest('#profileDropdownContainer')) dropMenu.classList.add('hidden');
            });
        }

        // --- Desktop Dropdowns ---
        const monitoringBtn = document.getElementById('monitoringDropdownButton');
        const monitoringMenu = document.getElementById('monitoringDropdownMenu');
        const monitoringChevron = document.getElementById('monitoringChevron');

        const adminBtn = document.getElementById('adminDropdownButton');
        const adminMenu = document.getElementById('adminDropdownMenu');
        const adminChevron = document.getElementById('adminChevron');

        if (monitoringBtn && monitoringMenu) {
            monitoringBtn.addEventListener('click', e => {
                e.stopPropagation();
                monitoringMenu.classList.toggle('hidden');
                const isClosed = monitoringMenu.classList.contains('hidden');
                toggleRotation(monitoringChevron, !isClosed);
                if (adminMenu) {
                    adminMenu.classList.add('hidden');
                    toggleRotation(adminChevron, false);
                }
            });
        }

        if (adminBtn && adminMenu) {
            adminBtn.addEventListener('click', e => {
                e.stopPropagation();
                adminMenu.classList.toggle('hidden');
                const isClosed = adminMenu.classList.contains('hidden');
                toggleRotation(adminChevron, !isClosed);
                if (monitoringMenu) {
                    monitoringMenu.classList.add('hidden');
                    toggleRotation(monitoringChevron, false);
                }
            });
        }

        // Notification Dropdown Toggle
        const notifBtn = document.getElementById('notificationDropdownButton');
        const notifMenu = document.getElementById('notificationDropdownMenu');
        
        // Close desktop dropdowns on click outside
        document.addEventListener('click', e => {
            if (monitoringMenu && !e.target.closest('#monitoringDropdownContainer')) {
                monitoringMenu.classList.add('hidden');
                toggleRotation(monitoringChevron, false);
            }
            if (adminMenu && !e.target.closest('#adminDropdownContainer')) {
                adminMenu.classList.add('hidden');
                toggleRotation(adminChevron, false);
            }
            if (dropMenu && !e.target.closest('#profileDropdownContainer')) {
                dropMenu.classList.add('hidden');
            }
            if (notifMenu && !e.target.closest('#notificationDropdownContainer')) {
                notifMenu.classList.add('hidden');
            }
        });

        if (notifBtn && notifMenu) {
            notifBtn.addEventListener('click', e => {
                e.stopPropagation();
                notifMenu.classList.toggle('hidden');
            });
        }

        // --- Mobile Navigation ---
        const mobileToggleBtn = document.getElementById('mobileMenuToggle');
        const mobileMenu = document.getElementById('mobileMenu');
        const mobileMenuIcon = document.getElementById('mobileMenuIcon');

        if (mobileToggleBtn && mobileMenu) {
            mobileToggleBtn.addEventListener('click', e => {
                e.stopPropagation();
                mobileMenu.classList.toggle('hidden');
                const isOpen = !mobileMenu.classList.contains('hidden');
                if (mobileMenuIcon) {
                    if (isOpen) {
                        mobileMenuIcon.classList.remove('fa-bars');
                        mobileMenuIcon.classList.add('fa-times');
                    } else {
                        mobileMenuIcon.classList.remove('fa-times');
                        mobileMenuIcon.classList.add('fa-bars');
                    }
                }
            });

            // Close mobile menu when clicking outside
            document.addEventListener('click', e => {
                if (mobileMenu && !mobileMenu.classList.contains('hidden') && !e.target.closest('#mobileMenu') && !e.target.closest('#mobileMenuToggle')) {
                    mobileMenu.classList.add('hidden');
                    if (mobileMenuIcon) {
                        mobileMenuIcon.classList.remove('fa-times');
                        mobileMenuIcon.classList.add('fa-bars');
                    }
                }
            });
        }

        // --- Mobile Accordions ---
        const mobileMonitoringToggle = document.getElementById('mobileMonitoringToggle');
        const mobileMonitoringMenu = document.getElementById('mobileMonitoringMenu');
        const mobileMonitoringChevron = document.getElementById('mobileMonitoringChevron');

        if (mobileMonitoringToggle && mobileMonitoringMenu) {
            mobileMonitoringToggle.addEventListener('click', () => {
                mobileMonitoringMenu.classList.toggle('hidden');
                const isClosed = mobileMonitoringMenu.classList.contains('hidden');
                toggleRotation(mobileMonitoringChevron, !isClosed);
            });
        }

        const mobileAdminToggle = document.getElementById('mobileAdminToggle');
        const mobileAdminMenu = document.getElementById('mobileAdminMenu');
        const mobileAdminChevron = document.getElementById('mobileAdminChevron');

        if (mobileAdminToggle && mobileAdminMenu) {
            mobileAdminToggle.addEventListener('click', () => {
                mobileAdminMenu.classList.toggle('hidden');
                const isClosed = mobileAdminMenu.classList.contains('hidden');
                toggleRotation(mobileAdminChevron, !isClosed);
            });
        }
        
        // --- Theme Toggle Logic ---
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        const themeToggleIcon = document.getElementById('themeToggleIcon');
        
        function updateThemeIcon() {
            if (document.documentElement.classList.contains('dark')) {
                themeToggleIcon.classList.remove('fa-moon');
                themeToggleIcon.classList.add('fa-sun');
            } else {
                themeToggleIcon.classList.remove('fa-sun');
                themeToggleIcon.classList.add('fa-moon');
            }
        }
        
        if (themeToggleIcon) updateThemeIcon();
        
        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', () => {
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.theme = 'light';
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.theme = 'dark';
                }
                updateThemeIcon();
            });
        }
    });
    </script>
</body>
</html>