<x-app-layout>
    @php $editing = isset($product); @endphp
    <x-slot name="title">{{ $editing ? 'แก้ไขสินค้า' : 'เพิ่มสินค้าใหม่' }} — GreenPaw</x-slot>
    <style>
        .container { padding: 30px; max-width: 820px; margin: auto; }
        .card {
            background: white; padding: 32px; border-radius: 14px;
            box-shadow: 0 4px 16px rgba(0,0,0,.08);
            border-top: 5px solid {{ $editing ? '#f39c12' : '#27ae60' }};
        }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 7px; font-weight: 600; color: #2c3e50; font-size: .9rem; }
        input[type=text], input[type=number], input[type=file], select {
            width: 100%; padding: 11px 13px; border: 1px solid #ddd; border-radius: 8px;
            font-size: .95rem; outline: none; transition: .2s; box-sizing: border-box;
        }
        input:focus, select:focus { border-color: #27ae60; box-shadow: 0 0 0 3px rgba(39,174,96,.15); }
        .img-preview {
            width: 140px; height: 140px; border: 2px dashed #ddd; border-radius: 10px;
            margin-top: 10px; display: flex; align-items: center; justify-content: center;
            overflow: hidden; background: #fafafa;
        }
        .img-preview img { width: 100%; height: 100%; object-fit: cover; }
        .cost-box { background: #f0fdf4; border: 1px solid #bbf7d0; padding: 18px; border-radius: 10px; margin-top: 8px; }
        .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
        .bought-box { background: #f0f7ff; border: 1px solid #bae6fd; padding: 18px; border-radius: 10px; display: none; }
        .consign-box { background: #fdf2f8; border: 1px solid #fbcfe8; padding: 18px; border-radius: 10px; margin-top: 18px; }
        .cost-total { text-align: right; margin-top: 12px; font-size: .9rem; color: #555; }
        .cost-total strong { font-size: 1.3rem; color: #166534; margin-left: 8px; }
        .btn-submit {
            width: 100%; padding: 14px; border: none; border-radius: 9px; font-size: 1rem;
            font-weight: bold; cursor: pointer; margin-top: 20px; color: white;
            background: {{ $editing ? '#f39c12' : '#27ae60' }};
            transition: filter .2s;
        }
        .btn-submit:hover { filter: brightness(1.08); }
        .btn-cancel { display: block; text-align: center; margin-top: 12px; color: #666; text-decoration: none; font-size: .9rem; }
        .error-msg { color: #e74c3c; font-size: .82rem; margin-top: 4px; }
        hr { border: none; border-top: 1px solid #eee; margin: 22px 0; }
    </style>

    <div class="container">
        <div class="card">
            <h2 style="margin:0 0 6px;">{{ $editing ? '📝 แก้ไขข้อมูลสินค้า' : '➕ เพิ่มสินค้าใหม่' }}</h2>
            <p style="margin:0 0 20px;color:#888;font-size:.9rem;">
                {{ $editing ? 'แก้ไขรายละเอียดสินค้า' : 'กรอกข้อมูลสินค้าใหม่' }}
            </p>
            <hr>

            <form method="POST"
                  action="{{ $editing ? route('admin.products.update', $product) : route('admin.products.store') }}"
                  enctype="multipart/form-data"
                  id="productForm">
                @csrf
                @if($editing) @method('PUT') @endif

                {{-- รูปภาพ --}}
                <div class="form-group">
                    <label>รูปภาพสินค้า</label>
                    <input type="file" name="product_image" accept="image/*" onchange="previewImg(this)">
                    <div class="img-preview" id="imgPreview">
                        @if($editing && $product->image_path && file_exists(storage_path('app/public/products/'.$product->image_path)))
                            <img src="{{ asset('storage/products/'.$product->image_path) }}" id="previewImg">
                        @else
                            <small style="color:#aaa;">ไม่มีรูปภาพ</small>
                        @endif
                    </div>
                </div>

                {{-- ชื่อสินค้า --}}
                <div class="form-group">
                    <label>ชื่อสินค้า <span style="color:#e74c3c">*</span></label>
                    <input type="text" name="product_name" maxlength="255" required
                        value="{{ old('product_name', $product->product_name ?? '') }}" placeholder="ระบุชื่อสินค้า">
                    @error('product_name')<p class="error-msg">{{ $message }}</p>@enderror
                </div>

                {{-- หมวดหมู่ --}}
                <div class="form-group">
                    <label>
                        หมวดหมู่สินค้า
                        <a href="{{ route('admin.categories.index') }}" style="float:right;font-weight:normal;font-size:.8rem;color:#27ae60;">+ จัดการหมวดหมู่</a>
                    </label>
                    <select name="category_id">
                        <option value="">-- ไม่ระบุหมวดหมู่ --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->category_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- ประเภทสินค้า --}}
                <div class="form-group">
                    <label>ประเภทสินค้า / วิธีคำนวณต้นทุน</label>
                    <select name="product_type" id="pType" onchange="toggleType()">
                        <option value="produced" {{ old('product_type', $product->product_type ?? 'produced') === 'produced' ? 'selected' : '' }}>🌱 สินค้าผลิตเอง</option>
                        <option value="bought"   {{ old('product_type', $product->product_type ?? '') === 'bought' ? 'selected' : '' }}>📦 สินค้าซื้อมาขายไป</option>
                    </select>
                </div>

                {{-- ต้นทุน: ผลิตเอง --}}
                <div id="producedSection" class="cost-box">
                    <label style="color:#166534;font-size:.9rem;">📊 รายละเอียดต้นทุนย่อย</label>
                    <div class="grid-4">
                        <div><small>เมล็ด (บาท)</small>   <input type="number" name="seed_cost"  step="0.01" min="0" value="{{ old('seed_cost',  $product->seed_cost  ?? 0) }}" oninput="calcCost()"></div>
                        <div><small>ดิน (บาท)</small>     <input type="number" name="soil_cost"  step="0.01" min="0" value="{{ old('soil_cost',  $product->soil_cost  ?? 0) }}" oninput="calcCost()"></div>
                        <div><small>กระถาง (บาท)</small>  <input type="number" name="pot_cost"   step="0.01" min="0" value="{{ old('pot_cost',   $product->pot_cost   ?? 0) }}" oninput="calcCost()"></div>
                        <div><small>ค่าแรง (บาท)</small>  <input type="number" name="labor_cost" step="0.01" min="0" value="{{ old('labor_cost', $product->labor_cost ?? 0) }}" oninput="calcCost()"></div>
                    </div>
                    <div class="cost-total">
                        <label style="font-weight:normal;display:inline;">อัตราสูญเสีย (Waste %):</label>
                        <input type="number" name="waste_percent" step="0.1" min="0" max="100"
                               value="{{ old('waste_percent', $product->waste_percent ?? 0) }}"
                               style="width:80px;display:inline;padding:6px;margin-left:8px;"
                               oninput="calcCost()">
                        <span style="display:block;margin-top:8px;">
                            ต้นทุนรวมสุทธิ: <strong>฿ <span id="displayCost">0.00</span></strong>
                        </span>
                    </div>
                </div>

                {{-- ต้นทุน: ซื้อมา --}}
                <div id="boughtSection" class="bought-box">
                    <label style="color:#0369a1;">💰 ราคาทุนที่ซื้อมา</label>
                    <input type="number" name="fixed_cost" step="0.01" min="0"
                           value="{{ old('fixed_cost', $product->cost_price ?? 0) }}" placeholder="ระบุต้นทุนต่อชิ้น">
                </div>

                {{-- ค่าฝากขาย --}}
                <div class="consign-box">
                    <label style="color:#9d174d;">🤝 ค่าฝากขาย (ต่อหน่วย)</label>
                    <input type="number" name="consignment_fee" step="0.01" min="0"
                           value="{{ old('consignment_fee', $product->consignment_fee ?? 0) }}" placeholder="0.00">
                    <small style="color:#888;display:block;margin-top:6px;">จำนวนเงินที่ร้านรับฝากหักไป (หักออกจากกำไรของเรา)</small>
                </div>

                {{-- ราคาขาย --}}
                <div class="form-group" style="margin-top:20px;">
                    <label>ราคาขายหน้าร้าน (ต่อหน่วย) <span style="color:#e74c3c">*</span></label>
                    <input type="number" name="sale_price" step="0.01" min="0" required
                           style="border:2px solid #27ae60;color:#27ae60;font-weight:bold;"
                           value="{{ old('sale_price', $product->sale_price ?? '') }}" placeholder="0.00">
                    @error('sale_price')<p class="error-msg">{{ $message }}</p>@enderror
                </div>

                {{-- สถานะ --}}
                <div class="form-group">
                    <label>สถานะการจำหน่าย</label>
                    <select name="status">
                        <option value="1" {{ old('status', $product->status ?? 1) == 1 ? 'selected' : '' }}>✅ เปิดการใช้งาน (Active)</option>
                        <option value="0" {{ old('status', $product->status ?? 1) == 0 ? 'selected' : '' }}>❌ ปิดใช้งาน (Inactive)</option>
                    </select>
                </div>

                <button type="submit" class="btn-submit">
                    {{ $editing ? '💾 บันทึกการแก้ไข' : '✅ สร้างรายการสินค้า' }}
                </button>
                <a href="{{ route('admin.products.index') }}" class="btn-cancel">🔙 กลับไปหน้ารายการสินค้า</a>
            </form>
        </div>
    </div>

    <script>
    function previewImg(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                document.getElementById('imgPreview').innerHTML = `<img src="${e.target.result}">`;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function toggleType() {
        const type = document.getElementById('pType').value;
        document.getElementById('producedSection').style.display = type === 'produced' ? 'block' : 'none';
        document.getElementById('boughtSection').style.display   = type === 'bought'   ? 'block' : 'none';
        calcCost();
    }

    function calcCost() {
        const get = name => parseFloat(document.querySelector(`[name="${name}"]`)?.value) || 0;
        const total = (get('seed_cost') + get('soil_cost') + get('pot_cost') + get('labor_cost'))
                      * (1 + get('waste_percent') / 100);
        document.getElementById('displayCost').textContent = total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // init on load
    toggleType();
    calcCost();
    </script>
</x-app-layout>
