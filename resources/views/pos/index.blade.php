<x-app-layout>
    <x-slot name="title">POS — GreenPaw</x-slot>
    <style>
        * { box-sizing: border-box; }
        .pos-container {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 18px;
            padding: 18px;
            min-height: calc(100vh - 56px);
        }
        /* ---- Product side ---- */
        .search-box {
            width: 100%; padding: 13px 18px; border: 2px solid #27ae60;
            border-radius: 10px; font-size: 1rem; outline: none; margin-bottom: 16px;
            transition: .25s;
        }
        .search-box:focus { box-shadow: 0 0 0 3px rgba(39,174,96,.2); }
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(155px, 1fr)); gap: 10px; }
        .product-card {
            background: white; border-radius: 12px; overflow: hidden;
            box-shadow: 0 3px 8px rgba(0,0,0,.07); display: flex; flex-direction: column;
            transition: .2s;
        }
        .product-card:hover { transform: translateY(-3px); box-shadow: 0 8px 16px rgba(0,0,0,.12); }
        .product-img-wrap { width: 100%; height: 130px; background: #f0fdf4; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .product-img-wrap img { width: 100%; height: 100%; object-fit: cover; }
        .product-img-wrap .no-img { font-size: 3rem; color: #ccc; }
        .product-info { padding: 10px; flex-grow: 1; text-align: center; }
        .product-info strong { display: block; font-size: .9rem; color: #2c3e50; margin-bottom: 4px; min-height: 2.5em; }
        .price-tag { color: #27ae60; font-weight: bold; font-size: 1.05rem; margin-bottom: 6px; }
        .stock-label { font-size: .8rem; color: #666; margin-bottom: 8px; }
        .btn-add {
            background: #27ae60; color: white; border: none; padding: 9px;
            width: 100%; border-radius: 7px; cursor: pointer; font-weight: bold; font-size: .85rem;
            transition: background .2s;
        }
        .btn-add:hover { background: #219150; }
        .btn-add:disabled { background: #ccc; cursor: not-allowed; }
        .add-row { display: flex; gap: 5px; align-items: center; }
        .qty-input {
            width: 58px; padding: 8px 4px; text-align: center;
            border: 1px solid #ddd; border-radius: 6px; font-size: .9rem;
        }

        /* ---- Cart side ---- */
        .cart-panel {
            background: white; border-radius: 12px; padding: 18px;
            box-shadow: 0 3px 10px rgba(0,0,0,.1);
            height: calc(100vh - 90px); position: sticky; top: 74px;
            display: flex; flex-direction: column;
        }
        .seller-tag {
            background: #f0fdf4; color: #15803d; padding: 8px 12px;
            border-radius: 7px; text-align: center; font-size: .85rem; margin-bottom: 12px;
        }
        .cart-items { flex-grow: 1; overflow-y: auto; margin: 10px 0; }
        .cart-item {
            display: flex; justify-content: space-between;
            padding: 9px 0; border-bottom: 1px solid #f1f1f1; font-size: .88rem;
        }
        .cart-empty { text-align: center; color: #999; padding: 40px 0; }
        .cart-total {
            border-top: 2px solid #f1f1f1; padding-top: 14px;
            display: flex; justify-content: space-between; align-items: center;
            font-size: 1.2rem; margin-bottom: 14px;
        }
        .btn-checkout {
            background: #27ae60; color: white; border: none; padding: 14px;
            width: 100%; border-radius: 9px; font-size: 1.1rem; font-weight: bold;
            cursor: pointer; transition: background .2s;
        }
        .btn-checkout:hover { background: #219150; }
        .btn-remove { color: #e74c3c; text-decoration: none; font-size: 1.3rem; line-height: 1; }

        @media (max-width: 900px) {
            .pos-container { grid-template-columns: 1fr; }
            .cart-panel { position: static; height: auto; order: -1; }
        }
        @media (max-width: 480px) {
            .product-grid { grid-template-columns: repeat(2, 1fr); }
            .pos-container { padding: 10px; gap: 12px; }
        }
    </style>

    <div class="pos-container">
        {{-- ===== ฝั่งสินค้า ===== --}}
        <div>
            <h2 style="margin:0 0 12px;color:#2c3e50;">🌱 หน้าจอขายสินค้า</h2>
            <input type="text" id="posSearch" class="search-box" placeholder="🔍 ค้นหาชื่อสินค้า..." oninput="filterProducts()">

            <div class="product-grid" id="productGrid">
                @foreach($products as $p)
                @php $outOfStock = $p->stock_qty <= 0; @endphp
                <div class="product-card" style="{{ $outOfStock ? 'opacity:.65;' : '' }}">
                    <div class="product-img-wrap">
                        @if($p->image_path && file_exists(storage_path('app/public/products/'.$p->image_path)))
                            <img src="{{ asset('storage/products/'.$p->image_path) }}" alt="{{ $p->product_name }}">
                        @else
                            <div class="no-img">🌱</div>
                        @endif
                    </div>
                    <div class="product-info">
                        <strong>{{ $p->product_name }}</strong>
                        <div class="price-tag">฿{{ number_format($p->sale_price, 2) }}</div>
                        <div class="stock-label">
                            @if($outOfStock)
                                <span style="color:#e74c3c;font-weight:bold;">🚫 สินค้าหมด</span>
                            @else
                                คงเหลือ: {{ $p->stock_qty }}
                            @endif
                        </div>
                        <form method="POST" action="{{ route('pos.add') }}">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $p->id }}">
                            <div class="add-row">
                                <input type="number" name="qty" value="{{ $outOfStock ? 0 : 1 }}"
                                    min="0.1" step="0.1" class="qty-input"
                                    {{ $outOfStock ? 'disabled' : '' }}>
                                @if($outOfStock)
                                    <button type="button" class="btn-add" disabled>หมด</button>
                                @else
                                    <button type="submit" class="btn-add">+ ใส่</button>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ===== ฝั่งตะกร้า ===== --}}
        <div class="cart-panel">
            <div class="seller-tag">ผู้ขาย: <strong>{{ auth()->user()->fullname }}</strong></div>
            <h3 style="margin:0;font-size:1rem;color:#2c3e50;">🛒 รายการในตะกร้า</h3>

            <div class="cart-items">
                @forelse($cart as $id => $item)
                @php $subtotal = $item['price'] * $item['qty']; @endphp
                <div class="cart-item">
                    <div style="max-width:68%;">
                        <div style="font-weight:bold;line-height:1.3;">{{ $item['name'] }}</div>
                        <small style="color:#666;">{{ $item['qty'] }} × ฿{{ number_format($item['price'], 2) }}</small>
                    </div>
                    <div style="text-align:right;">
                        <div>฿{{ number_format($subtotal, 2) }}</div>
                        <a href="{{ route('pos.remove', $id) }}" class="btn-remove" title="ลบ">&times;</a>
                    </div>
                </div>
                @empty
                <div class="cart-empty">
                    <div style="font-size:2.5rem;">🛒</div>
                    <p>ยังไม่มีสินค้าในตะกร้า</p>
                </div>
                @endforelse
            </div>

            @if(!empty($cart))
            <div class="cart-total">
                <span>รวมทั้งสิ้น:</span>
                <strong style="color:#27ae60;">฿{{ number_format($grandTotal, 2) }}</strong>
            </div>

            {{-- เงินสดที่รับ + เงินทอน --}}
            <div style="margin-bottom:12px;">
                <label style="font-size:.82rem;color:#555;font-weight:600;display:block;margin-bottom:5px;">💵 รับเงินมา (บาท)</label>
                <input type="number" id="cashInput" min="0" step="1"
                       placeholder="0.00"
                       style="width:100%;padding:10px 12px;border:2px solid #27ae60;border-radius:8px;font-size:1.1rem;font-weight:bold;outline:none;text-align:right;"
                       oninput="calcChange()">
                <div id="changeDisplay" style="display:none;margin-top:8px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:7px;padding:10px 14px;display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:.88rem;color:#374151;">เงินทอน:</span>
                    <strong id="changeAmount" style="font-size:1.3rem;color:#15803d;">฿0.00</strong>
                </div>
                <div id="shortDisplay" style="display:none;margin-top:8px;background:#fff5f5;border:1px solid #fecaca;border-radius:7px;padding:10px 14px;display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:.88rem;color:#b91c1c;">ยังขาดอีก:</span>
                    <strong id="shortAmount" style="font-size:1.3rem;color:#dc2626;">฿0.00</strong>
                </div>
            </div>

            <form method="POST" action="{{ route('pos.checkout') }}" id="checkoutForm">
                @csrf
                <input type="hidden" name="received_amount" id="receivedInput" value="0">
                <button type="button" class="btn-checkout" onclick="submitCheckout()">
                    💰 ชำระเงิน / ปิดบิล
                </button>
            </form>
            @endif
        </div>
    </div>

    <script>
    function filterProducts() {
        const q = document.getElementById('posSearch').value.toUpperCase();
        document.querySelectorAll('#productGrid .product-card').forEach(card => {
            const name = card.querySelector('strong').textContent.toUpperCase();
            card.style.display = name.includes(q) ? '' : 'none';
        });
    }

    const grandTotal = {{ $grandTotal }};

    function calcChange() {
        const cash     = parseFloat(document.getElementById('cashInput').value) || 0;
        const change   = cash - grandTotal;
        const fmtTotal = grandTotal.toLocaleString('th', {minimumFractionDigits:2});

        document.getElementById('receivedInput').value = cash;

        const changeDiv = document.getElementById('changeDisplay');
        const shortDiv  = document.getElementById('shortDisplay');

        if (cash <= 0) {
            changeDiv.style.display = 'none';
            shortDiv.style.display  = 'none';
        } else if (change >= 0) {
            changeDiv.style.display = 'flex';
            shortDiv.style.display  = 'none';
            document.getElementById('changeAmount').textContent =
                '฿' + change.toLocaleString('th', {minimumFractionDigits:2});
        } else {
            changeDiv.style.display = 'none';
            shortDiv.style.display  = 'flex';
            document.getElementById('shortAmount').textContent =
                '฿' + Math.abs(change).toLocaleString('th', {minimumFractionDigits:2});
        }
    }

    function submitCheckout() {
        const cash = parseFloat(document.getElementById('cashInput').value) || 0;
        if (cash > 0 && cash < grandTotal) {
            const short = (grandTotal - cash).toLocaleString('th', {minimumFractionDigits:2});
            if (!confirm(`เงินยังไม่พอ ขาดอีก ฿${short}\nยืนยันปิดบิลเลยไหม?`)) return;
        } else if (cash === 0) {
            if (!confirm('ยืนยันการชำระเงินและปิดบิล?')) return;
        }
        document.getElementById('checkoutForm').submit();
    }
    </script>
</x-app-layout>
