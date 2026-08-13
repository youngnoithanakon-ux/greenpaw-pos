<x-app-layout>
    <x-slot name="title">จัดการหมวดหมู่ — GreenPaw</x-slot>
    <style>
        .container { padding: 30px; max-width: 800px; margin: auto; }
        .card { background: white; padding: 24px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,.07); }
        .add-row { display: flex; gap: 10px; margin-bottom: 24px; }
        .add-row input {
            flex: 1; padding: 11px 14px; border: 1px solid #ddd; border-radius: 8px;
            font-size: .95rem; outline: none; transition: .2s;
        }
        .add-row input:focus { border-color: #27ae60; box-shadow: 0 0 0 3px rgba(39,174,96,.15); }
        .btn-add {
            background: #27ae60; color: white; border: none; padding: 11px 20px;
            border-radius: 8px; cursor: pointer; font-weight: bold; white-space: nowrap;
            transition: background .2s;
        }
        .btn-add:hover { background: #219150; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 13px 12px; border-bottom: 1px solid #eee; text-align: left; }
        th { color: #888; font-weight: 500; font-size: .85rem; text-transform: uppercase; }
        .btn-del {
            color: #e74c3c; text-decoration: none; font-size: .85rem; font-weight: bold;
            padding: 5px 10px; border-radius: 5px; border: 1px solid #fecaca;
            background: #fff5f5; transition: .15s;
        }
        .btn-del:hover { background: #e74c3c; color: white; }
    </style>

    <div class="container">
        <h2 style="margin-bottom:20px;">📁 จัดการหมวดหมู่สินค้า</h2>

        <div class="card">
            <form method="POST" action="{{ route('admin.categories.store') }}" class="add-row">
                @csrf
                <input type="text" name="category_name" placeholder="ชื่อหมวดหมู่ใหม่..."
                    value="{{ old('category_name') }}" required maxlength="100">
                <button type="submit" class="btn-add">➕ เพิ่มหมวดหมู่</button>
            </form>

            @error('category_name')
                <p style="color:#e74c3c;margin:-16px 0 16px;font-size:.85rem;">{{ $message }}</p>
            @enderror

            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ชื่อหมวดหมู่</th>
                        <th style="text-align:right;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $c)
                    <tr>
                        <td style="color:#aaa;">{{ $c->id }}</td>
                        <td><strong>{{ $c->category_name }}</strong></td>
                        <td style="text-align:right;">
                            <form method="POST"
                                  action="{{ route('admin.categories.destroy', $c) }}"
                                  onsubmit="return confirm('ยืนยันการลบหมวดหมู่ \"{{ $c->category_name }}\"?')"
                                  style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-del">ลบ</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="3" style="text-align:center;color:#aaa;padding:30px;">ยังไม่มีหมวดหมู่</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
