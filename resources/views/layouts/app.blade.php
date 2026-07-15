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
                    },
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                        serif: ['Merriweather', 'serif'],
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
        @import url('https://fonts.googleapis.com/css2?family=Merriweather:wght@300;400;700;900&family=Outfit:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Outfit', sans-serif; font-size: 1rem; }
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
<body class="min-h-screen bg-slate-50 dark:bg-[#0B1120] text-slate-800 dark:text-slate-200 flex flex-col transition-colors duration-200">

    @auth
    <!-- Mobile Menu Dropdown -->
    <div id="mobileMenu"
         class="hidden fixed top-16 left-0 right-0 bg-[#0F172A] dark:bg-[#0B1120] border-b border-slate-700 dark:border-white/5 shadow-2xl z-30 lg:hidden no-print overflow-y-auto max-h-[calc(100vh-4rem)]">
        <div class="p-4 space-y-3">
            <!-- Home -->
            <a href="{{ route('home') }}"
               class="flex items-center space-x-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition
               {{ Route::currentRouteName() === 'home'
                   ? 'bg-white/10 text-white border-l-4 border-blue-500 pl-3'
                   : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                <i class="fas fa-home w-5 text-center"></i>
                <span>Home</span>
            </a>

            <!-- Pemantauan (Mobile Accordion) -->
            @php
                $monitoringRoutes = ['monitoring.working_hour', 'monitoring.fuel', 'monitoring.working_hour_detail', 'monitoring.fuel_detail', 'monitoring.flow'];
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
                        <span>Konsumsi BBM</span>
                    </a>
                    <a href="{{ route('monitoring.flow') }}"
                       class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition
                              {{ request()->routeIs('monitoring.flow*') ? 'bg-forest text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                        <i class="fas fa-project-diagram w-4 text-center"></i>
                        <span>Alur Sistem</span>
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
                        <span>Kelola Data</span>
                    </span>
                    <i id="mobileAdminChevron"
                       class="fas fa-chevron-down text-xs transition-transform duration-200 {{ $adminActive ? 'rotate-180' : '' }}"></i>
                </button>
                <div id="mobileAdminMenu" class="{{ $adminActive ? '' : 'hidden' }} mt-1.5 ml-4 pl-3 border-l border-slate-700 space-y-1">
                    <a href="{{ route('import.index') }}"
                       class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition
                              {{ Route::currentRouteName() === 'import.index' ? 'bg-forest text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                        <i class="fas fa-upload w-4 text-center"></i>
                        <span>Import Data</span>
                    </a>
                    <a href="{{ route('users.index') }}"
                       class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition
                              {{ request()->routeIs('users.index') ? 'bg-forest text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                        <i class="fas fa-users-cog w-4 text-center"></i>
                        <span>Kelola Pengguna</span>
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endauth

    <!-- ======== TOP NAVBAR ======== -->
    <nav class="fixed top-0 left-0 right-0 h-16 bg-[#0F172A] dark:bg-[#0B1120]/80 backdrop-blur-md border-b border-transparent dark:border-white/5 text-white px-3 sm:px-4 shadow-md z-40 flex justify-between items-center no-print transition-colors duration-200">
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
                    <img src="{{ asset('images/logo.png') }}" alt="TPA Logo" class="h-8 w-auto mr-2 flex-shrink-0">
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
                    <span>Home</span>
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
                         class="hidden absolute left-0 mt-2 w-52 bg-white dark:bg-slate-900 rounded-xl shadow-2xl border border-slate-200 dark:border-white/5 divide-y divide-slate-100 dark:divide-white/5 z-50 text-sm no-print">
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
                                <span>Konsumsi BBM</span>
                            </a>
                            <a href="{{ route('monitoring.flow') }}"
                               class="flex items-center space-x-2.5 px-3 py-2 rounded-lg transition-colors font-medium
                                      {{ request()->routeIs('monitoring.flow*')
                                          ? 'bg-forest text-white shadow-sm'
                                          : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/5 hover:text-forest dark:hover:text-forest' }}">
                                <i class="fas fa-project-diagram w-4 text-center"></i>
                                <span>Alur Sistem</span>
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
                        <span>Kelola Data</span>
                        <i class="fas fa-chevron-down text-[10px] transition-transform duration-150" id="adminChevron"></i>
                    </button>
                    <!-- Dropdown menu -->
                    <div id="adminDropdownMenu"
                         class="hidden absolute left-0 mt-2 w-52 bg-white dark:bg-slate-900 rounded-xl shadow-2xl border border-slate-200 dark:border-white/5 divide-y divide-slate-100 dark:divide-white/5 z-50 text-sm no-print">
                        <div class="p-1.5 space-y-1">
                            <a href="{{ route('import.index') }}"
                               class="flex items-center space-x-2.5 px-3 py-2 rounded-lg transition-colors font-medium
                                      {{ Route::currentRouteName() === 'import.index'
                                          ? 'bg-forest text-white shadow-sm'
                                          : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/5 hover:text-forest dark:hover:text-forest' }}">
                                <i class="fas fa-upload w-4 text-center"></i>
                                <span>Import Data</span>
                            </a>
                            <a href="{{ route('users.index') }}"
                               class="flex items-center space-x-2.5 px-3 py-2 rounded-lg transition-colors font-medium
                                      {{ request()->routeIs('users.index')
                                          ? 'bg-forest text-white shadow-sm'
                                          : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/5 hover:text-forest dark:hover:text-forest' }}">
                                <i class="fas fa-users-cog w-4 text-center"></i>
                                <span>Kelola Pengguna</span>
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
            <button id="themeToggleBtn" class="text-slate-300 hover:text-white transition focus:outline-none p-2 rounded-full hover:bg-slate-800">
                <i id="themeToggleIcon" class="fas fa-moon text-lg"></i>
            </button>
            
            @auth
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
                     class="hidden absolute right-0 mt-2 w-56 bg-white dark:bg-slate-900 rounded-xl shadow-2xl border border-slate-200 dark:border-white/5 divide-y divide-slate-100 dark:divide-white/5 z-50 text-sm no-print">
                    <div class="p-3">
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1.5 px-2">Profil Saya</p>
                        <a href="{{ route('profile.edit') }}"
                           class="flex items-center space-x-2 px-3 py-2 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-white/5 hover:text-forest dark:hover:text-forest rounded-lg transition font-medium">
                            <i class="fas fa-user-cog text-slate-400"></i>
                            <span>Edit Profil</span>
                        </a>
                    </div>
                    <div class="p-3">
                        <form action="{{ route('logout') }}" method="POST" class="w-full">
                            @csrf
                            <button type="submit"
                                    class="w-full flex items-center space-x-2 px-3 py-2 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg transition font-medium text-left">
                                <i class="fas fa-sign-out-alt opacity-70"></i>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @else
            <!-- Guest controls -->
            <a href="{{ route('login') }}" class="bg-forest hover:bg-blue-700 text-white px-2.5 py-1.5 rounded-lg transition flex items-center text-sm font-semibold shadow-sm">
                <i class="fas fa-sign-in-alt mr-1"></i>
                <span>Login</span>
            </a>
            @endauth
        </div>
    </nav>

    <!-- ======== BODY WRAPPER ======== -->
    <div id="mainWrapper" class="pt-16 flex-1 flex flex-col min-h-[calc(100vh-4rem)]">
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
            &copy; {{ date('Y') }} PT. Teladan Prima Agro &mdash; @testing
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
        });

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