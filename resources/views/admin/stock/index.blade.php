<x-app-layout>
    <x-slot name="title">เติมสต็อกสินค้า — GreenPaw</x-slot>
    <style>
        .container { padding: 24px; max-width: 1100px; margin: auto; }
        .header-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 12px; }
        .search-wrap { background: white; padding: 14px 16px; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,.05); margin-bottom: 18px; }
        .search-wrap input {
            width: 100%; padding: 11px 14px; border: 2px solid #eee; border-radius: 8px;
            font-size: .95rem; outline: none; transition: .2s;
        }
        .search-wrap input:focus { border-color: #27ae60; }
        .btn-log {
            color: #3498db; border: 1px solid #bfdbfe; background: #eff6ff;
            padding: 9px 16px; border-radius: 8px; text-decoration: none;
            font-size: .88rem; font-weight: bold; transition: .15s;
        }
        .btn-log:hover { background: #3498db; color: white; }

        .table-wrap { background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,.06); overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 600px; }
        th { background: #f8f9fa; padding: 13px 14px; text-align: left; color: #718096; font-size: .8rem; text-transform: uppercase; font-weight: 500; border-bottom: 2px solid #edf2f7; }
        td { padding: 12px 14px; border-bottom: 1px solid #edf2f7; vertical-align: middle; }
        tr:hover td { background: #f9fafb; }
        tr.inactive td { opacity: .7; }

        .prod-cell { display: flex; align-items: center; gap: 14px; }
        .prod-img { width: 46px; height: 46px; border-radius: 7px; object-fit: cover; border: 1px solid #eee; }
        .no-img { width: 46px; height: 46px; border-radius: 7px; background: #f0fdf4; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        .prod-name { font-weight: 600; color: #2d3748; }
        .prod-sub  { font-size: .78rem; color: #a0aec0; margin-top: 2px; }

        .qty-badge { background: #edf2f7; padding: 4px 12px; border-radius: 20px; font-weight: bold; font-size: .9rem; }
        .qty-badge.low { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
        .badge-inactive { background: #fee2e2; color: #b91c1c; font-size: .72rem; padding: 2px 7px; border-radius: 4px; margin-left: 6px; }

        .add-form { display: flex; align-items: center; gap: 8px; justify-content: flex-end; }
        .qty-input { width: 78px; padding: 8px; border: 1px solid #cbd5e0; border-radius: 6px; text-align: center; font-weight: bold; }
        .btn-add {
            background: #27ae60; color: white; border: none; padding: 8px 16px;
            border-radius: 6px; cursor: pointer; font-weight: bold; font-size: .85rem; transition: .2s;
        }
        .btn-add:hover { background: #219150; }
        .btn-add:disabled { background: #e2e8f0; color: #a0aec0; cursor: not-allowed; }

        #noResults td { text-align: center; padding: 50px; color: #a0aec0; }
    </style>

    <div class="container">
        <div class="header-bar">
            <h2 style="margin:0;">📥 นำสินค้าเข้าสต็อก</h2>
            <a href="{{ route('admin.stock.log') }}" class="btn-log">📜 ดูประวัติสต็อก</a>
        </div>

        <div class="search-wrap">
            <input type="text" id="searchInput" placeholder="🔍 ค้นหาชื่อสินค้า หรือ หมวดหมู่..." oninput="filterRows()">
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>รายการสินค้า</th>
                        <th style="text-align:center;">คงเหลือ</th>
                        <th style="text-align:right;">เพิ่มจำนวน</th>
                    </tr>
                </thead>
                <tbody id="stockTable">
                    @forelse($products as $p)
                    <tr class="stock-row {{ !$p->status ? 'inactive' : '' }}">
                        <td>
                            <div class="prod-cell">
                                @if($p->image_path && file_exists(storage_path('app/public/products/'.$p->image_path)))
                                    <img src="{{ asset('storage/products/'.$p->image_path) }}" class="prod-img" alt="">
                                @else
                                    <div class="no-img">🌱</div>
                                @endif
                                <div>
                                    <div class="prod-name">
                                        {{ $p->product_name }}
                                        @if(!$p->status)<span class="badge-inactive">เลิกจำหน่าย</span>@endif
                                    </div>
                                    <div class="prod-sub">
                                        {{ $p->product_type === 'produced' ? '🌱 ผลิตเอง' : '📦 ซื้อมา' }}
                                        @if($p->category) · <strong style="color:#27ae60;" class="cat-label">{{ $p->category->category_name }}</strong>@endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td style="text-align:center;">
                            <span class="qty-badge {{ $p->stock_qty <= 5 ? 'low' : '' }}">
                                {{ number_format($p->stock_qty, 1) }}
                            </span>
                        </td>
                        <td style="text-align:right;">
                            @if($p->status)
                            <form method="POST" action="{{ route('admin.stock.store') }}"
                                  class="add-form"
                                  onsubmit="return confirm('ยืนยันการเพิ่มสต็อก?')">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $p->id }}">
                                <input type="number" name="add_qty" value="1" min="0.1" step="0.1" class="qty-input" required>
                                <button type="submit" name="update_stock" class="btn-add">+ เพิ่มเข้าคลัง</button>
                            </form>
                            @else
                            <div class="add-form">
                                <input type="number" disabled value="-" class="qty-input" style="background:#edf2f7;">
                                <button disabled class="btn-add">เลิกจำหน่าย</button>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="3" style="text-align:center;padding:50px;color:#a0aec0;">ไม่พบสินค้า</td></tr>
                    @endforelse
                    <tr id="noResults" style="display:none;"><td colspan="3">ไม่พบรายการที่ค้นหา</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    function filterRows() {
        const q = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('.stock-row');
        let found = false;
        rows.forEach(row => {
            const name = row.querySelector('.prod-name').textContent.toLowerCase();
            const cat  = row.querySelector('.cat-label')?.textContent.toLowerCase() ?? '';
            const show = name.includes(q) || cat.includes(q);
            row.style.display = show ? '' : 'none';
            if (show) found = true;
        });
        document.getElementById('noResults').style.display = found ? 'none' : '';
    }
    </script>
</x-app-layout>
