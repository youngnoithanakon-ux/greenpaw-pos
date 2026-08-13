<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'GreenPaw Systems' }}</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo_v2.jpg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Flash messages animations */
        @keyframes fadeOut {
            0% { opacity: 1; transform: translateY(0); }
            80% { opacity: 1; transform: translateY(0); }
            100% { opacity: 0; transform: translateY(-10px); }
        }
        .flash-alert { animation: fadeOut 5s forwards; }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-800">

{{-- ===== NAVBAR ===== --}}
<nav x-data="{ mobileMenuOpen: false }" class="bg-green-900 shadow-md sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-14">
            
            {{-- Left side (Brand & Desktop Nav) --}}
            <div class="flex items-center">
                {{-- Logo --}}
                <a href="{{ route('home') }}" class="flex items-center gap-2 text-green-400 font-bold text-xl hover:text-green-300 transition shrink-0">
                    <img src="{{ asset('images/logo_v2.jpg') }}" alt="Logo" class="h-8 w-8 rounded-md object-cover bg-white">
                    <span class="hidden sm:block">GreenPaw</span>
                </a>

                {{-- Desktop Nav Links --}}
                <div class="hidden md:flex md:ml-8 space-x-2">
                    <a href="{{ route('pos.index') }}" class="px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('pos.*') ? 'bg-green-800 text-green-300' : 'text-green-100 hover:bg-green-800 hover:text-green-300' }}">
                        🛒 POS
                    </a>
                    <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('dashboard') ? 'bg-green-800 text-green-300' : 'text-green-100 hover:bg-green-800 hover:text-green-300' }}">
                        📊 Dashboard
                    </a>
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.plant-batches.index') }}" class="px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('admin.plant-batches*') ? 'bg-green-800 text-green-300' : 'text-green-100 hover:bg-green-800 hover:text-green-300' }}">
                            🌿 การปลูก
                        </a>
                    @endif
                </div>
            </div>

            {{-- Right side (Desktop Dropdowns) --}}
            <div class="hidden md:flex md:items-center space-x-4">
                @if(auth()->user()->role === 'admin')
                    {{-- Backend Dropdown --}}
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" @click.away="open = false" class="flex items-center gap-1 px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('admin.*') && !request()->routeIs('admin.plant-batches*') ? 'bg-green-800 text-green-300' : 'text-green-100 hover:bg-green-800 hover:text-green-300' }}">
                            ⚙️ Backend
                            <svg class="h-4 w-4" :class="{'rotate-180': open}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="transition: transform 0.2s;">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        
                        <div x-show="open" x-transition.opacity style="display: none;" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-xl py-1 z-50 border border-gray-100 ring-1 ring-black ring-opacity-5">
                            <a href="{{ route('admin.products.index') }}" class="block px-4 py-2 text-sm {{ request()->routeIs('admin.products*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-700 hover:bg-gray-100 hover:text-green-600' }}">🌱 จัดการสินค้า</a>
                            <a href="{{ route('admin.categories.index') }}" class="block px-4 py-2 text-sm {{ request()->routeIs('admin.categories*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-700 hover:bg-gray-100 hover:text-green-600' }}">📁 หมวดหมู่</a>
                            <a href="{{ route('admin.stock.index') }}" class="block px-4 py-2 text-sm {{ request()->routeIs('admin.stock*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-700 hover:bg-gray-100 hover:text-green-600' }}">📥 จัดการสต็อก</a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <a href="{{ route('admin.sales.index') }}" class="block px-4 py-2 text-sm {{ request()->routeIs('admin.sales*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-700 hover:bg-gray-100 hover:text-green-600' }}">📝 ประวัติขาย</a>
                            <a href="{{ route('admin.reports.index') }}" class="block px-4 py-2 text-sm {{ request()->routeIs('admin.reports*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-700 hover:bg-gray-100 hover:text-green-600' }}">📈 รายงาน</a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <a href="{{ route('admin.users.index') }}" class="block px-4 py-2 text-sm {{ request()->routeIs('admin.users*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-700 hover:bg-gray-100 hover:text-green-600' }}">👥 พนักงาน</a>
                            <a href="{{ route('admin.settings.index') }}" class="block px-4 py-2 text-sm {{ request()->routeIs('admin.settings*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-700 hover:bg-gray-100 hover:text-green-600' }}">🔧 ตั้งค่าระบบ</a>
                        </div>
                    </div>
                @endif

                {{-- User Dropdown --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @click.away="open = false" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium text-green-100 hover:bg-green-800 hover:text-green-300 transition">
                        <span>{{ auth()->user()->fullname }}</span>
                        <svg class="h-4 w-4" :class="{'rotate-180': open}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="transition: transform 0.2s;">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    
                    <div x-show="open" x-transition.opacity style="display: none;" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-xl py-1 z-50 border border-gray-100 ring-1 ring-black ring-opacity-5">
                        <div class="px-4 py-2 text-xs text-gray-500 bg-gray-50 border-b border-gray-100">
                            <div>{{ auth()->user()->username }}</div>
                            <div class="uppercase tracking-wider font-bold text-green-600">{{ auth()->user()->role }}</div>
                        </div>
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-green-600">⚙️ ตั้งค่าโปรไฟล์</a>
                        <div class="border-t border-gray-100 my-1"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                🚪 ออกจากระบบ
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Hamburger Mobile Button --}}
            <div class="-mr-2 flex items-center md:hidden">
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="inline-flex items-center justify-center p-2 rounded-md text-green-200 hover:text-white hover:bg-green-800 focus:outline-none focus:bg-green-800 focus:text-white transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': mobileMenuOpen, 'inline-flex': !mobileMenuOpen }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': !mobileMenuOpen, 'inline-flex': mobileMenuOpen }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile Menu --}}
    <div x-show="mobileMenuOpen" style="display: none;" class="md:hidden bg-green-900 border-t border-green-800">
        <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
            <a href="{{ route('pos.index') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('pos.*') ? 'bg-green-800 text-green-300' : 'text-green-100 hover:bg-green-800 hover:text-green-300' }}">🛒 POS</a>
            <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('dashboard') ? 'bg-green-800 text-green-300' : 'text-green-100 hover:bg-green-800 hover:text-green-300' }}">📊 Dashboard</a>
            
            @if(auth()->user()->role === 'admin')
                <div class="border-t border-green-800 my-2"></div>
                <div class="px-3 py-1 text-xs font-semibold text-green-400 uppercase tracking-wider">แอดมิน</div>
                <a href="{{ route('admin.plant-batches.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-green-100 hover:bg-green-800 hover:text-green-300">🌿 การปลูกหญ้าแมว</a>
                <a href="{{ route('admin.products.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-green-100 hover:bg-green-800 hover:text-green-300">🌱 จัดการสินค้า</a>
                <a href="{{ route('admin.categories.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-green-100 hover:bg-green-800 hover:text-green-300">📁 หมวดหมู่</a>
                <a href="{{ route('admin.stock.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-green-100 hover:bg-green-800 hover:text-green-300">📥 จัดการสต็อก</a>
                <a href="{{ route('admin.sales.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-green-100 hover:bg-green-800 hover:text-green-300">📝 ประวัติขาย</a>
                <a href="{{ route('admin.reports.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-green-100 hover:bg-green-800 hover:text-green-300">📈 รายงาน</a>
                <a href="{{ route('admin.users.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-green-100 hover:bg-green-800 hover:text-green-300">👥 พนักงาน</a>
                <a href="{{ route('admin.settings.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-green-100 hover:bg-green-800 hover:text-green-300">🔧 ตั้งค่าระบบ</a>
            @endif
            
            <div class="border-t border-green-800 my-2"></div>
            <div class="px-3 py-1 text-xs font-semibold text-green-400 uppercase tracking-wider">โปรไฟล์ ({{ auth()->user()->username }})</div>
            <a href="{{ route('profile.edit') }}" class="block px-3 py-2 rounded-md text-base font-medium text-green-100 hover:bg-green-800 hover:text-green-300">⚙️ ตั้งค่าโปรไฟล์</a>
            <form method="POST" action="{{ route('logout') }}" class="mt-1">
                @csrf
                <button type="submit" class="block w-full text-left px-3 py-2 rounded-md text-base font-medium text-red-400 hover:bg-red-900 hover:text-red-300">
                    🚪 ออกจากระบบ
                </button>
            </form>
        </div>
    </div>
</nav>

{{-- ===== PAGE CONTENT ===== --}}
<main class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-8 py-6">
    
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="flash-alert mb-4 px-4 py-3 rounded-lg bg-green-100 border border-green-300 text-green-800 flex items-center shadow-sm">
            <svg class="w-5 h-5 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flash-alert mb-4 px-4 py-3 rounded-lg bg-red-100 border border-red-300 text-red-800 flex items-center shadow-sm">
            <svg class="w-5 h-5 mr-2 text-red-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
            {{ session('error') }}
        </div>
    @endif

    {{ $slot }}
</main>

</body>
</html>
