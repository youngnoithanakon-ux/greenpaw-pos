<x-app-layout>
    <x-slot name="title">รายการสินค้า — GreenPaw</x-slot>
    <style>
        .container { padding: 24px; max-width: 1300px; margin: auto; }
        .header-bar { display: flex; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 20px; flex-wrap: wrap; }
        .filter-row { display: flex; gap: 10px; flex-wrap: wrap; }
        .filter-row select, .filter-row input {
            padding: 9px 13px; border: 1px solid #ddd; border-radius: 8px;
            font-size: .9rem; outline: none; transition: .2s;
        }
        .filter-row input:focus { border-color: #27ae60; }
        .btn-new {
            background: #27ae60; color: white; text-decoration: none; font-weight: bold;
            padding: 10px 18px; border-radius: 8px; white-space: nowrap; transition: background .2s;
        }
        .btn-new:hover { background: #219150; }

        .table-wrap { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,.06); overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 800px; }
        th { background: #34495e; color: white; padding: 13px 14px; text-align: left; font-weight: 500; font-size: .85rem; }
        td { padding: 12px 14px; border-bottom: 1px solid #eee; vertical-align: middle; }
        tr:hover td { background: #f9fafb; }
        tr.inactive td { background: #fffafa; opacity: .8; }

        .prod-img { width: 50px; height: 50px; border-radius: 8px; object-fit: cover; border: 1px solid #eee; }
        .no-img {
            width: 50px; height: 50px; border-radius: 8px; background: #f1f1f1;
            display: flex; align-items: center; justify-content: center; color: #ccc; font-size: 1.3rem;
        }
        .prod-cell { display: flex; align-items: center; gap: 12px; }
        .prod-meta { font-size: .75rem; color: #aaa; margin-top: 3px; }

        .badge { padding: 3px 9px; border-radius: 4px; font-size: .75rem; font-weight: bold; }
        .badge-active   { background: #dcfce7; color: #15803d; }
        .badge-inactive { background: #fee2e2; color: #b91c1c; }
        .badge-produced { background: #dcfce7; color: #166534; }
        .badge-bought   { background: #dbeafe; color: #1d4ed8; }
        .badge-lowstock { background: #fee2e2; color: #b91c1c; font-weight: bold; }

        .btn-edit {
            color: #f39c12; font-weight: bold; text-decoration: none; font-size: .85rem;
            padding: 5px 10px; border-radius: 5px; border: 1px solid #fde68a;
            background: #fffbeb; transition: .15s;
        }
        .btn-edit:hover { background: #f39c12; color: white; }
        .btn-del {
            color: #e74c3c; font-weight: bold; text-decoration: none; font-size: .85rem;
            padding: 5px 10px; border-radius: 5px; border: 1px solid #fecaca;
            background: #fff5f5; margin-left: 6px; transition: .15s;
        }
        .btn-del:hover { background: #e74c3c; color: white; }
		.btn-active {
            color: #27AE60; font-weight: bold; text-decoration: none; font-size: .85rem;
            padding: 5px 10px; border-radius: 5px; border: 1px solid #27AE60;
            background: #fff5f5; margin-left: 6px; transition: .15s;
        }
        .btn-active:hover { background: #27AE60; color: white; }
    </style>

    <div class="container">
        <div class="header-bar">
            <h2 style="margin:0;">📦 รายการสินค้า</h2>
            <div class="filter-row">
                <select onchange="location.href='?cat_id='+this.value">
                    <option value="">ทุกหมวดหมู่</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $catFilter == $cat->id ? 'selected' : '' }}>
                            {{ $cat->category_name }}
                        </option>
                    @endforeach
                </select>
                <input type="text" id="searchInput" placeholder="🔍 ค้นหาชื่อสินค้า..." oninput="filterTable()">
            </div>
            <a href="{{ route('admin.products.create') }}" class="btn-new">➕ เพิ่มสินค้าใหม่</a>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width:38%;">ชื่อสินค้า</th>
                        <th>สถานะ</th>
                        <th>ประเภท</th>
                        <th>ราคาทุน</th>
                        <th>ราคาขาย</th>
                        <th style="text-align:center;">สต็อก</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody id="productTable">
                    @forelse($products as $p)
                    <tr class="{{ !$p->status ? 'inactive' : '' }} product-row">
                        <td>
                            <div class="prod-cell">
                                @if($p->image_path && file_exists(storage_path('app/public/products/'.$p->image_path)))
                                    <img src="{{ asset('storage/products/'.$p->image_path) }}" class="prod-img" alt="">
                                @else
                                    <div class="no-img">📦</div>
                                @endif
                                <div>
                                    <strong class="prod-name">{{ $p->product_name }}</strong>
                                    <div class="prod-meta">
                                        {{ $p->created_at ? $p->created_at->format('d/m/y') : '' }}
                                        @if($p->category) · <span style="color:#27ae60;">{{ $p->category->category_name }}</span>@endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge {{ $p->status ? 'badge-active' : 'badge-inactive' }}">
                                {{ $p->status ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $p->product_type === 'produced' ? 'badge-produced' : 'badge-bought' }}">
                                {{ $p->product_type === 'produced' ? '🌱 ผลิตเอง' : '📦 ซื้อมา' }}
                            </span>
                        </td>
                        <td style="color:#e74c3c;">฿{{ number_format($p->cost_price, 2) }}</td>
                        <td style="color:#27ae60;font-weight:bold;">฿{{ number_format($p->sale_price, 2) }}</td>
                        <td style="text-align:center;">
                            <span class="{{ $p->stock_qty <= 5 ? 'badge badge-lowstock' : '' }}">
                                {{ $p->stock_qty }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.products.edit', $p) }}" class="btn-edit">แก้ไข</a>
                            <form method="POST" action="{{ route('admin.products.toggle', $p->id) }}" style="display:inline;">
								@csrf 
								@method('PATCH')
								
								@if($p->status)
									<button type="submit" class="btn-del" onclick="return confirm('ต้องการปิดการใช้งานสินค้าใช่หรือไม่?')">
										ปิดการใช้งาน
									</button>
								@else
									<button type="submit" class="btn-active">
										เปิดใช้งาน
									</button>
								@endif
							</form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="text-align:center;padding:40px;color:#aaa;">ไม่พบสินค้า</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
    function filterTable() {
        const q = document.getElementById('searchInput').value.toUpperCase();
        document.querySelectorAll('.product-row').forEach(row => {
            const name = row.querySelector('.prod-name').textContent.toUpperCase();
            row.style.display = name.includes(q) ? '' : 'none';
        });
    }
    </script>
</x-app-layout>
