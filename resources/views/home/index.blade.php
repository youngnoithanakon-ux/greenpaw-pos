<x-app-layout>
    <x-slot name="title">หน้าหลัก — GreenPaw</x-slot>
    <style>
        .container { padding: 2rem; max-width: 1200px; margin: auto; }
        .welcome-card { background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,.1); margin-bottom: 2rem; }
        .badge-role { background: #27ae60; color: white; padding: 4px 10px; border-radius: 5px; font-size: .85rem; }
        .badge-role.admin { background: #e74c3c; }
        .menu-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 18px; }
        .menu-item {
            background: white; padding: 2rem; border-radius: 10px; text-align: center;
            text-decoration: none; color: #333; box-shadow: 0 2px 4px rgba(0,0,0,.05);
            transition: .25s; border-top: 5px solid #27ae60;
        }
        .menu-item:hover { transform: translateY(-5px); box-shadow: 0 8px 16px rgba(0,0,0,.12); }
        .menu-item.admin-only { border-top-color: #e74c3c; }
        .menu-item h2 { margin: 0 0 .5rem; font-size: 1.4rem; }
        .menu-item p  { margin: 0; color: #666; font-size: .9rem; }
        .alert-card {
            background: #fff5f5; border-left: 5px solid #e74c3c; padding: 1.25rem 1.5rem;
            border-radius: 10px; margin-top: 2rem;
        }
        .alert-card h3 { margin: 0 0 .75rem; color: #c53030; }
        .low-stock-list { display: flex; flex-wrap: wrap; gap: 8px; }
        .low-stock-badge {
            background: white; border: 1px solid #feb2b2; padding: 5px 12px;
            border-radius: 20px; font-size: .85rem; color: #c53030; font-weight: bold;
        }
    </style>

    <div class="container">
        <div class="welcome-card">
            <h1 style="margin:0 0 .5rem;">ยินดีต้อนรับ, {{ auth()->user()->fullname }}! 👋</h1>
            <p style="margin:0;">สิทธิ์ของคุณ:
                <span class="badge-role {{ auth()->user()->role }}">{{ strtoupper(auth()->user()->role) }}</span>
            </p>
        </div>

        <div class="menu-grid">
            <a href="{{ route('pos.index') }}" class="menu-item">
                <h2>🛒 งานขาย (POS)</h2>
                <p>บันทึกการขายสินค้าหน้าร้าน</p>
            </a>
            <a href="{{ route('dashboard') }}" class="menu-item">
                <h2>📊 สรุปภาพรวม</h2>
                <p>สรุปภาพรวมยอดขายวันนี้</p>
            </a>

            @if($role === 'admin')
            <a href="{{ route('admin.products.index') }}" class="menu-item admin-only">
                <h2>🌱 จัดการสินค้า</h2>
                <p>เพิ่มสินค้าและแก้ไขข้อมูล</p>
            </a>
            <a href="{{ route('admin.stock.index') }}" class="menu-item admin-only">
                <h2>📥 เติมสต็อก</h2>
                <p>นำสินค้าเข้าคลัง / เพิ่มสต็อก</p>
            </a>
            <a href="{{ route('admin.sales.index') }}" class="menu-item admin-only">
                <h2>📝 ประวัติขาย</h2>
                <p>สรุปรายการขายแบบละเอียด</p>
            </a>
            <a href="{{ route('admin.reports.index') }}" class="menu-item admin-only">
                <h2>📈 รายงานกำไร</h2>
                <p>ดูสรุปยอดขายและกำไรสุทธิ</p>
            </a>
            <a href="{{ route('admin.users.index') }}" class="menu-item admin-only">
                <h2>👥 จัดการพนักงาน</h2>
                <p>เพิ่ม/แก้ไข สิทธิ์พนักงาน</p>
            </a>
            @endif
        </div>

        @if($lowStockItems->isNotEmpty())
        <div class="alert-card">
            <h3>⚠️ แจ้งเตือน: สินค้าใกล้หมดสต็อก</h3>
            <div class="low-stock-list">
                @foreach($lowStockItems as $item)
                    <div class="low-stock-badge">{{ $item->product_name }} [เหลือ: {{ $item->stock_qty }}]</div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</x-app-layout>
