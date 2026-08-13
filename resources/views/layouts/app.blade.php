<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'GreenPaw Systems' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; background: #f4f7f6; }

        /* ===== NAVBAR ===== */
        .navbar {
            background: #1a2e1a;
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 56px;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        .navbar-brand {
            color: #4ade80;
            font-size: 1.2rem;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .navbar-nav {
            display: flex;
            align-items: center;
            gap: 4px;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .navbar-nav a {
            color: #d1fae5;
            text-decoration: none;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: background 0.2s;
            white-space: nowrap;
        }
        .navbar-nav a:hover,
        .navbar-nav a.active { background: #2d4a2d; color: #4ade80; }

        /* Dropdown */
        .dropdown { position: relative; }
        .dropdown-toggle {
            color: #d1fae5;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 0.9rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            background: none;
            border: none;
            transition: background 0.2s;
        }
        .dropdown-toggle:hover { background: #2d4a2d; }
        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            top: calc(100% + 6px);
            background: #1a2e1a;
            border: 1px solid #2d4a2d;
            border-radius: 8px;
            min-width: 200px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.4);
            overflow: hidden;
        }
        .dropdown-menu a,
        .dropdown-menu button {
            display: block;
            width: 100%;
            text-align: left;
            padding: 11px 18px;
            color: #d1fae5;
            text-decoration: none;
            font-size: 0.9rem;
            background: none;
            border: none;
            cursor: pointer;
            transition: background 0.15s;
        }
        .dropdown-menu a:hover,
        .dropdown-menu button:hover { background: #2d4a2d; color: #4ade80; }
        .dropdown-menu .active-item { color: #4ade80 !important; background: #2d4a2d; }
        .dropdown.open .dropdown-menu { display: block; }


        .dropdown-user { font-size: 0.8rem; color: #86efac; padding: 10px 18px; }

        /* Hamburger */
        .hamburger {
            display: none;
            flex-direction: column;
            gap: 5px;
            cursor: pointer;
            padding: 6px;
            background: none;
            border: none;
        }
        .hamburger span {
            display: block;
            width: 22px;
            height: 2px;
            background: #d1fae5;
            border-radius: 2px;
            transition: 0.3s;
        }
        .mobile-menu {
            display: none;
            background: #1a2e1a;
            border-top: 1px solid #2d4a2d;
            padding: 10px 0;
        }
        .mobile-menu a {
            display: block;
            color: #d1fae5;
            text-decoration: none;
            padding: 12px 20px;
            font-size: 0.95rem;
            transition: background 0.15s;
        }
        .mobile-menu a:hover { background: #2d4a2d; }
        .mobile-menu .divider { border-top: 1px solid #2d4a2d; margin: 6px 0; }
        .mobile-menu.open { display: block; }

        @media (max-width: 768px) {
            .navbar-nav { display: none; }
            .dropdown    { display: none; }
            .hamburger   { display: flex; }
        }

        /* ===== FLASH MESSAGES ===== */
        .flash-success {
            background: #d4edda; color: #155724; border: 1px solid #c3e6cb;
            padding: 12px 20px; border-radius: 8px; margin: 16px 20px 0;
        }
        .flash-error {
            background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;
            padding: 12px 20px; border-radius: 8px; margin: 16px 20px 0;
        }
    </style>
</head>
<body>

{{-- ===== NAVBAR ===== --}}
<nav class="navbar">
    <a href="{{ route('home') }}" class="navbar-brand">
        <img src="{{ asset('images/logo.jpg') }}" alt="Logo" style="height: 28px; width: 28px; border-radius: 4px;">
        GreenPaw
    </a>

    {{-- Desktop nav --}}
    <ul class="navbar-nav">
        <li><a href="{{ route('pos.index') }}"   class="{{ request()->routeIs('pos.*') ? 'active' : '' }}">🛒 POS</a></li>
        <li><a href="{{ route('dashboard') }}"   class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">📊 Dashboard</a></li>

        @if(auth()->user()->role === 'admin')
        {{-- 🌿 การปลูก (เฉพาะ produced) --}}
        <li><a href="{{ route('admin.plant-batches.index') }}" class="{{ request()->routeIs('admin.plant-batches*') ? 'active' : '' }}">🌿 การปลูก</a></li>

        {{-- Backend Dropdown --}}
        <li class="dropdown" id="backendDropdown" style="list-style:none;">
            <button class="dropdown-toggle" onclick="toggleBackend(event)"
                style="{{ request()->routeIs('admin.*') && !request()->routeIs('admin.plant-batches*') ? 'color:#4ade80;background:#2d4a2d;' : '' }}">
                ⚙️ Backend
                <svg width="12" height="12" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
            </button>
            <div class="dropdown-menu" id="backendMenu">
                <a href="{{ route('admin.products.index') }}"   class="{{ request()->routeIs('admin.products*') ? 'active-item' : '' }}">🌱 จัดการสินค้า</a>
                <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories*') ? 'active-item' : '' }}">📁 หมวดหมู่</a>
                <a href="{{ route('admin.stock.index') }}"      class="{{ request()->routeIs('admin.stock*') ? 'active-item' : '' }}">📥 จัดการสต็อก</a>
                <div class="divider"></div>
                <a href="{{ route('admin.sales.index') }}"      class="{{ request()->routeIs('admin.sales*') ? 'active-item' : '' }}">📝 ประวัติขาย</a>
                <a href="{{ route('admin.reports.index') }}"    class="{{ request()->routeIs('admin.reports*') ? 'active-item' : '' }}">📈 รายงาน</a>
                <div class="divider"></div>
                <a href="{{ route('admin.users.index') }}"      class="{{ request()->routeIs('admin.users*') ? 'active-item' : '' }}">👥 พนักงาน</a>
                <a href="{{ route('admin.settings.index') }}"   class="{{ request()->routeIs('admin.settings*') ? 'active-item' : '' }}">🔧 ตั้งค่าระบบ</a>
            </div>
        </li>
        @endif
    </ul>

    {{-- User dropdown --}}
    <div class="dropdown" id="userDropdown">
        <button class="dropdown-toggle" onclick="toggleDropdown()">
            <span>{{ auth()->user()->fullname }}</span>
            <svg width="12" height="12" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
        </button>
        <div class="dropdown-menu">
            <div class="dropdown-user">{{ auth()->user()->username }} · {{ strtoupper(auth()->user()->role) }}</div>
            <div class="divider"></div>
            <a href="{{ route('profile.edit') }}">⚙️ ตั้งค่าโปรไฟล์</a>
            <div class="divider"></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">🚪 ออกจากระบบ</button>
            </form>
        </div>
    </div>

    {{-- Hamburger --}}
    <button class="hamburger" id="hamburger" onclick="toggleMobile()">
        <span></span><span></span><span></span>
    </button>
</nav>

{{-- Mobile menu --}}
<div class="mobile-menu" id="mobileMenu">
    <a href="{{ route('pos.index') }}">🛒 POS</a>
    <a href="{{ route('dashboard') }}">📊 Dashboard</a>
    @if(auth()->user()->role === 'admin')
    <div class="divider"></div>
    <a href="{{ route('admin.plant-batches.index') }}">🌿 การปลูกหญ้าแมว</a>
    <div class="divider"></div>
    <a href="{{ route('admin.products.index') }}">🌱 จัดการสินค้า</a>
    <a href="{{ route('admin.categories.index') }}">📁 หมวดหมู่</a>
    <a href="{{ route('admin.stock.index') }}">📥 จัดการสต็อก</a>
    <a href="{{ route('admin.sales.index') }}">📝 ประวัติขาย</a>
    <a href="{{ route('admin.reports.index') }}">📈 รายงาน</a>
    <a href="{{ route('admin.users.index') }}">👥 พนักงาน</a>
    <a href="{{ route('admin.settings.index') }}">🔧 ตั้งค่าระบบ</a>
    @endif
    <div class="divider"></div>
    <a href="{{ route('profile.edit') }}">⚙️ โปรไฟล์ ({{ auth()->user()->username }})</a>
    <form method="POST" action="{{ route('logout') }}" style="margin:0;">
        @csrf
        <button type="submit" style="width:100%;text-align:left;padding:12px 20px;background:none;border:none;color:#d1fae5;font-size:0.95rem;cursor:pointer;">🚪 ออกจากระบบ</button>
    </form>
</div>

{{-- ===== PAGE CONTENT ===== --}}
<div>
    @if(session('success'))
        <div class="flash-success">✅ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="flash-error">⛔ {{ session('error') }}</div>
    @endif

    {{ $slot }}
</div>

<script>
function toggleDropdown() {
    document.getElementById('userDropdown').classList.toggle('open');
    document.getElementById('backendDropdown')?.classList.remove('open');
}
function toggleBackend(e) {
    e.stopPropagation();
    document.getElementById('backendDropdown').classList.toggle('open');
    document.getElementById('userDropdown').classList.remove('open');
}
function toggleMobile() {
    document.getElementById('mobileMenu').classList.toggle('open');
}
document.addEventListener('click', function(e) {
    const ud = document.getElementById('userDropdown');
    const bd = document.getElementById('backendDropdown');
    if (ud && !ud.contains(e.target)) ud.classList.remove('open');
    if (bd && !bd.contains(e.target)) bd.classList.remove('open');
});
</script>

</body>
</html>
