<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ใบเสร็จ #{{ str_pad($sale->id, 5, '0', STR_PAD_LEFT) }} — GreenPaw</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f4f7f6; display: flex; justify-content: center; padding: 30px 16px; }

/* Receipt paper */
.receipt {
    background: white; width: 100%; max-width: 400px;
    border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,.12);
    overflow: hidden;
}
.receipt-header {
    background: #1a2e1a; color: white; text-align: center; padding: 22px 20px;
}
.receipt-header .logo { font-size: 2rem; margin-bottom: 6px; }
.receipt-header h1 { font-size: 1.2rem; color: #4ade80; margin-bottom: 4px; }
.receipt-header p { font-size: .82rem; color: #a7f3d0; }

.receipt-body { padding: 20px; }

.receipt-meta {
    display: flex; justify-content: space-between;
    font-size: .8rem; color: #6b7280; margin-bottom: 16px;
    padding-bottom: 12px; border-bottom: 1px dashed #e5e7eb;
}

/* Items */
.item-row {
    display: flex; justify-content: space-between; align-items: flex-start;
    padding: 8px 0; border-bottom: 1px solid #f9fafb; font-size: .9rem;
}
.item-row:last-child { border-bottom: none; }
.item-name { flex: 1; color: #1f2937; }
.item-qty  { color: #6b7280; font-size: .82rem; margin-top: 2px; }
.item-price { font-weight: bold; color: #1f2937; white-space: nowrap; margin-left: 12px; }

/* Totals */
.totals { margin-top: 14px; padding-top: 14px; border-top: 2px dashed #e5e7eb; }
.total-row {
    display: flex; justify-content: space-between;
    font-size: .9rem; padding: 4px 0; color: #374151;
}
.total-row.grand {
    font-size: 1.15rem; font-weight: bold; color: #1f2937;
    padding: 10px 0 6px; border-top: 1px solid #e5e7eb; margin-top: 6px;
}
.total-row.received { color: #2563eb; }
.total-row.change {
    font-size: 1.1rem; font-weight: bold;
    color: #15803d; background: #f0fdf4;
    padding: 8px 12px; border-radius: 8px; margin-top: 4px;
}

/* QR Code Section */
.qr-section {
    text-align: center; padding: 16px 20px 8px;
    border-top: 1px dashed #e5e7eb;
}
.qr-section .qr-label {
    font-size: .72rem; color: #9ca3af; margin-top: 8px;
}
#qrcode {
    display: inline-block;
}
#qrcode img, #qrcode canvas {
    margin: 0 auto;
}

/* Footer */
.receipt-footer {
    text-align: center; padding: 12px 20px 20px;
    font-size: .8rem; color: #9ca3af; line-height: 1.8;
}
.bill-no { font-size: 1rem; font-weight: bold; color: #374151; }

/* Buttons (no-print) */
.btn-bar {
    display: flex; gap: 10px; padding: 16px 20px;
    border-top: 1px solid #e5e7eb; background: #fafafa;
}
.btn { flex: 1; padding: 12px; border: none; border-radius: 8px; font-weight: bold; font-size: .9rem; cursor: pointer; text-align: center; text-decoration: none; display: block; transition: filter .2s; }
.btn:hover { filter: brightness(1.07); }
.btn-print { background: #1a2e1a; color: #4ade80; }
.btn-new   { background: #27ae60; color: white; }

@media print {
    body { background: white; padding: 0; }
    .receipt { box-shadow: none; border-radius: 0; max-width: 100%; }
    .btn-bar { display: none; }
    .receipt-header { background: #000 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    #qrcode canvas { display: none; }
    #qrcode img { display: block !important; }
}
</style>
</head>
<body>

<div class="receipt">
    <div class="receipt-header">
        <div class="logo">🌿</div>
        <h1>GreenPaw Systems</h1>
        <p>ใบเสร็จรับเงิน</p>
    </div>

    <div class="receipt-body">
        {{-- Meta --}}
        <div class="receipt-meta">
            <div>
                <div class="bill-no">บิล #{{ str_pad($sale->id, 5, '0', STR_PAD_LEFT) }}</div>
                <div>ผู้ขาย: {{ $sale->user?->fullname ?? '-' }}</div>
            </div>
            <div style="text-align:right;">
                <div>{{ $sale->sale_date ? \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') : now()->format('d/m/Y') }}</div>
                <div>{{ $sale->sale_date ? \Carbon\Carbon::parse($sale->sale_date)->format('H:i') : now()->format('H:i') }}</div>
            </div>
        </div>

        {{-- Items --}}
        <div style="margin-bottom:4px;">
            @foreach($sale->items as $item)
            <div class="item-row">
                <div class="item-name">
                    {{ $item->product?->product_name ?? '-' }}
                    <div class="item-qty">{{ $item->qty }} × ฿{{ number_format($item->unit_price, 2) }}</div>
                </div>
                <div class="item-price">฿{{ number_format($item->unit_price * $item->qty, 2) }}</div>
            </div>
            @endforeach
        </div>

        {{-- Totals --}}
        <div class="totals">
            <div class="total-row grand">
                <span>รวมทั้งสิ้น</span>
                <span>฿{{ number_format($sale->total_amount, 2) }}</span>
            </div>
            @if($sale->received_amount && $sale->received_amount > 0)
            <div class="total-row received">
                <span>รับเงินมา</span>
                <span>฿{{ number_format($sale->received_amount, 2) }}</span>
            </div>
            <div class="total-row change">
                <span>เงินทอน</span>
                <span>฿{{ number_format($change, 2) }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- QR Code --}}
    <div class="qr-section">
        <div id="qrcode"></div>
        <div class="qr-label">สแกน QR เพื่อตรวจสอบบิล #{{ str_pad($sale->id, 5, '0', STR_PAD_LEFT) }}</div>
    </div>

    <div class="receipt-footer">
        <div>ขอบคุณที่ใช้บริการ 🌿</div>
        <div>GreenPaw Systems</div>
    </div>

    <div class="btn-bar no-print">
        <button class="btn btn-print" onclick="window.print()">🖨️ พิมพ์ใบเสร็จ</button>
        <a href="{{ route('pos.index') }}" class="btn btn-new">🛒 ขายต่อ</a>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
// สร้าง QR Code แสดงเลขบิล
new QRCode(document.getElementById('qrcode'), {
    text: 'GREENPAW-BILL-{{ str_pad($sale->id, 5, "0", STR_PAD_LEFT) }}',
    width: 120,
    height: 120,
    colorDark: '#1a2e1a',
    colorLight: '#ffffff',
    correctLevel: QRCode.CorrectLevel.M
});
</script>
</body>
</html>
