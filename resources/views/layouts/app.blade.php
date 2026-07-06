<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - Monitoring Alat Berat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Outfit', sans-serif; }
        @media print { .no-print { display: none !important; } }

        /* Smooth sidebar transition */
        #sidebarDrawer { transition: transform 0.28s cubic-bezier(.4,0,.2,1); }
        #sidebarBackdrop { transition: opacity 0.28s ease; }

        /* Scrollable table wrapper on mobile */
        .table-scroll { -webkit-overflow-scrolling: touch; }

        /* Desktop sidebar offset transition */
        #mainWrapper { transition: padding-left 0.28s cubic-bezier(.4,0,.2,1); }
    </style>
</head>
<body class="min-h-screen bg-[#FAF7F2] text-stone-800 flex flex-col">

    @auth
    <!-- Sidebar Backdrop (mobile only) -->
    <div id="sidebarBackdrop"
         class="fixed inset-0 bg-stone-900/50 backdrop-blur-sm z-20 hidden opacity-0 no-print"></div>

    <!-- Sidebar Drawer -->
    <aside id="sidebarDrawer"
           class="fixed top-16 left-0 bottom-0 w-64 bg-[#F9F9F9] border-r border-stone-200 z-30 -translate-x-full flex flex-col shadow-lg no-print">

        <!-- Mobile-only header inside drawer -->
        <div class="h-14 bg-[#111111] flex items-center justify-between px-4 text-white md:hidden flex-shrink-0">
            <span class="font-bold tracking-wide flex items-center text-sm">
                <i class="fas fa-desktop mr-2 text-[#FFC107]"></i>
                {{ __('Menu Navigasi') }}
            </span>
            <button id="sidebarClose" class="text-white hover:text-[#FFC107] transition focus:outline-none p-1">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- Nav links -->
        <nav class="flex-1 overflow-y-auto p-3 space-y-1">
            <a href="{{ route('monitoring.index') }}"
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition text-sm font-semibold
               {{ Route::currentRouteName() === 'monitoring.index' && !request()->route('idAset')
                   ? 'bg-stone-200/70 text-stone-900 border-l-4 border-[#FFC107] pl-3'
                   : 'text-stone-600 hover:bg-stone-100 hover:text-stone-900' }}">
                <i class="fas fa-chart-line w-5 text-center"></i>
                <span>{{ __('Dashboard') }}</span>
            </a>

            @if(Auth::user()->role === 'admin')
            <a href="{{ route('import.index') }}"
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition text-sm font-semibold
               {{ Route::currentRouteName() === 'import.index'
                   ? 'bg-stone-200/70 text-stone-900 border-l-4 border-[#FFC107] pl-3'
                   : 'text-stone-600 hover:bg-stone-100 hover:text-stone-900' }}">
                <i class="fas fa-upload w-5 text-center"></i>
                <span>{{ __('Import Data') }}</span>
            </a>
            <a href="{{ route('users.index') }}"
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition text-sm font-semibold
               {{ Route::currentRouteName() === 'users.index'
                   ? 'bg-stone-200/70 text-stone-900 border-l-4 border-[#FFC107] pl-3'
                   : 'text-stone-600 hover:bg-stone-100 hover:text-stone-900' }}">
                <i class="fas fa-users w-5 text-center"></i>
                <span>{{ __('Kelola Pengguna') }}</span>
            </a>
            @endif

            <a href="{{ route('bantuan.index') }}"
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition text-sm font-semibold
               {{ Route::currentRouteName() === 'bantuan.index'
                   ? 'bg-stone-200/70 text-stone-900 border-l-4 border-[#FFC107] pl-3'
                   : 'text-stone-600 hover:bg-stone-100 hover:text-stone-900' }}">
                <i class="fas fa-question-circle w-5 text-center"></i>
                <span>{{ __('Bantuan') }}</span>
            </a>
        </nav>

        <!-- Sidebar footer -->
        <div class="p-3 border-t border-stone-200 text-[10px] text-stone-400 text-center uppercase tracking-wider font-semibold flex-shrink-0">
            VISIONMONITOR &copy; {{ date('Y') }}
        </div>
    </aside>
    @endauth

    <!-- ======== TOP NAVBAR ======== -->
    <nav class="fixed top-0 left-0 right-0 h-16 bg-[#111111] text-white px-3 sm:px-4 shadow z-40 flex justify-between items-center no-print">
        <!-- Left: hamburger + brand -->
        <div class="flex items-center space-x-2 sm:space-x-3 min-w-0">
            @auth
            <button id="sidebarToggle"
                    class="text-white hover:text-[#FFC107] transition focus:outline-none p-1 flex-shrink-0"
                    title="{{ __('Toggle Menu') }}">
                <i class="fas fa-bars text-xl"></i>
            </button>
            @endauth
            <h1 class="text-base sm:text-lg font-bold tracking-wider flex items-center select-none whitespace-nowrap">
                <span class="text-[#FFC107] mr-0.5">VISION</span><span>MONITOR</span>
            </h1>
        </div>

        <!-- Right: user info -->
        <div class="flex items-center space-x-2 sm:space-x-4 flex-shrink-0">
            @auth
            <div class="relative inline-block text-left" id="profileDropdownContainer">
                <div class="flex items-center space-x-2 sm:space-x-3">
                    <!-- Company name — hidden on xs, visible sm+ -->
                    <span class="hidden sm:inline text-xs text-stone-400 font-semibold uppercase tracking-wide max-w-[140px] lg:max-w-[200px] truncate select-none">
                        {{ Auth::user()->company ?? __('Profil Perusahaan') }}
                    </span>
                    <!-- Avatar button -->
                    <button type="button" id="profileDropdownButton"
                            class="w-9 h-9 rounded-full bg-[#FFC107] hover:bg-[#e0a800] text-stone-900 font-bold
                                   flex items-center justify-center transition focus:outline-none select-none text-sm shadow flex-shrink-0">
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    </button>
                </div>

                <!-- Dropdown -->
                <div id="profileDropdownMenu"
                     class="hidden absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-stone-100 divide-y divide-stone-100 z-50 text-sm no-print">
                    <div class="p-3">
                        <p class="text-xs text-stone-400 font-semibold uppercase tracking-wider mb-1 px-2">{{ __('Profil Saya') }}</p>
                        <a href="{{ route('profile.edit') }}"
                           class="flex items-center space-x-2 px-3 py-2 text-stone-700 hover:bg-[#FAF7F2] hover:text-[#8E6E4F] rounded-lg transition">
                            <i class="fas fa-user-cog text-[#8E6E4F]"></i>
                            <span>{{ __('Edit Profil') }}</span>
                        </a>
                    </div>
                    <div class="p-3">
                        <p class="text-xs text-stone-400 font-semibold uppercase tracking-wider mb-1.5 px-2">{{ __('Bahasa') }}</p>
                        <div class="flex items-center justify-between bg-stone-50 rounded-lg p-1.5 border border-stone-200/50 mx-2">
                            <a href="{{ route('lang.switch', 'id') }}"
                               class="flex-1 text-center py-1 rounded text-xs font-semibold transition
                               {{ app()->getLocale() == 'id' ? 'bg-[#8E6E4F] text-white shadow-sm' : 'text-stone-500 hover:text-stone-700' }}">ID</a>
                            <a href="{{ route('lang.switch', 'en') }}"
                               class="flex-1 text-center py-1 rounded text-xs font-semibold transition
                               {{ app()->getLocale() == 'en' ? 'bg-[#8E6E4F] text-white shadow-sm' : 'text-stone-500 hover:text-stone-700' }}">EN</a>
                        </div>
                    </div>
                    <div class="p-3">
                        <form action="{{ route('logout') }}" method="POST" class="w-full">
                            @csrf
                            <button type="submit"
                                    class="w-full flex items-center space-x-2 px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg transition font-semibold text-left">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>{{ __('Keluar') }}</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @else
            <!-- Guest controls -->
            <div class="flex items-center space-x-1 text-xs bg-stone-800 rounded-lg p-0.5 border border-stone-700">
                <a href="{{ route('lang.switch', 'id') }}"
                   class="px-1.5 py-0.5 rounded transition
                   {{ app()->getLocale() == 'id' ? 'bg-[#FFC107] text-stone-900 font-bold' : 'text-stone-300 hover:text-white' }}">ID</a>
                <a href="{{ route('lang.switch', 'en') }}"
                   class="px-1.5 py-0.5 rounded transition
                   {{ app()->getLocale() == 'en' ? 'bg-[#FFC107] text-stone-900 font-bold' : 'text-stone-300 hover:text-white' }}">EN</a>
            </div>
            <a href="{{ route('login') }}" class="hover:text-stone-300 transition flex items-center text-sm font-semibold">
                <i class="fas fa-sign-in-alt mr-1"></i>
                <span class="hidden sm:inline">{{ __('Masuk') }}</span>
            </a>
            <a href="{{ route('register') }}"
               class="bg-[#FFC107] hover:bg-[#e0a800] text-stone-900 px-2.5 py-1.5 rounded-lg transition flex items-center text-sm font-semibold shadow-sm">
                <i class="fas fa-user-plus mr-1 sm:mr-1.5"></i>
                <span class="hidden sm:inline">{{ __('Daftar') }}</span>
            </a>
            @endauth
        </div>
    </nav>

    <!-- ======== BODY WRAPPER ======== -->
    <!-- On desktop: pad-left 256px for sidebar. On mobile: no left pad. -->
    <div id="mainWrapper" class="pt-16 flex-1 flex flex-col min-h-[calc(100vh-4rem)]">
        <main class="flex-1 p-3 sm:p-4 md:p-6 w-full max-w-screen-2xl mx-auto">

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

        <footer class="bg-stone-100 text-center p-3 text-stone-500 text-xs border-t border-stone-200 no-print">
            &copy; {{ date('Y') }} PT. Teladan Prima Agro, Tbk &mdash; {{ __('Sistem Monitoring') }}
        </footer>
    </div>

    <!-- ======== JS ======== -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- Profile dropdown ---
        const dropBtn  = document.getElementById('profileDropdownButton');
        const dropMenu = document.getElementById('profileDropdownMenu');
        if (dropBtn && dropMenu) {
            dropBtn.addEventListener('click', e => { e.stopPropagation(); dropMenu.classList.toggle('hidden'); });
            document.addEventListener('click', e => {
                if (!e.target.closest('#profileDropdownContainer')) dropMenu.classList.add('hidden');
            });
        }

        // --- Sidebar toggle (ALL screen sizes) ---
        const toggle   = document.getElementById('sidebarToggle');
        const closeBtn = document.getElementById('sidebarClose');
        const drawer   = document.getElementById('sidebarDrawer');
        const backdrop = document.getElementById('sidebarBackdrop');
        const wrapper  = document.getElementById('mainWrapper');

        const SIDEBAR_KEY = 'sidebarOpen';
        const isDesktop   = () => window.innerWidth >= 768;

        function openSidebar(save = true) {
            if (!drawer) return;
            drawer.classList.remove('-translate-x-full');
            if (!isDesktop()) {
                // Mobile: show backdrop overlay
                backdrop.classList.remove('hidden');
                requestAnimationFrame(() => backdrop.classList.replace('opacity-0','opacity-100'));
            } else {
                // Desktop: push main content to the right
                if (wrapper) wrapper.style.paddingLeft = '16rem';
                backdrop.classList.add('hidden');
            }
            if (save) localStorage.setItem(SIDEBAR_KEY, '1');
        }

        function closeSidebar(save = true) {
            if (!drawer) return;
            drawer.classList.add('-translate-x-full');
            if (!isDesktop()) {
                backdrop.classList.replace('opacity-100','opacity-0');
                setTimeout(() => backdrop.classList.add('hidden'), 280);
            } else {
                if (wrapper) wrapper.style.paddingLeft = '0';
            }
            if (save) localStorage.setItem(SIDEBAR_KEY, '0');
        }

        function toggleSidebar() {
            if (drawer.classList.contains('-translate-x-full')) openSidebar();
            else closeSidebar();
        }

        // Restore saved state (desktop only; mobile always starts closed)
        if (drawer) {
            if (isDesktop() && localStorage.getItem(SIDEBAR_KEY) !== '0') {
                openSidebar(false); // open by default on desktop
            }
        }

        if (toggle)   toggle.addEventListener('click', toggleSidebar);
        if (closeBtn) closeBtn.addEventListener('click', () => closeSidebar());
        if (backdrop) backdrop.addEventListener('click', () => closeSidebar());

        // On mobile: close after nav link click
        if (drawer) {
            drawer.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', () => {
                    if (!isDesktop()) closeSidebar();
                });
            });
        }

        // When resizing between mobile/desktop, fix state
        window.addEventListener('resize', () => {
            if (!drawer) return;
            if (isDesktop()) {
                backdrop.classList.add('hidden');
                backdrop.classList.replace('opacity-100','opacity-0');
                // Restore desktop offset if sidebar is open
                if (!drawer.classList.contains('-translate-x-full')) {
                    if (wrapper) wrapper.style.paddingLeft = '16rem';
                } else {
                    if (wrapper) wrapper.style.paddingLeft = '0';
                }
            } else {
                // Mobile: remove desktop push
                if (wrapper) wrapper.style.paddingLeft = '0';
            }
        });
    });
    </script>
</body>
</html>