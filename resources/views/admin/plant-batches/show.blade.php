<x-app-layout>
<x-slot name="title">รายละเอียดรอบปลูก #{{ $plantBatch->id }} — GreenPaw</x-slot>
<style>
.container { padding:28px; max-width:860px; margin:auto; }
.card { background:white; padding:28px; border-radius:14px; box-shadow:0 4px 16px rgba(0,0,0,.08); margin-bottom:22px; }
.info-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.info-item label { font-size:.78rem; color:#888; display:block; margin-bottom:3px; }
.info-item strong { font-size:1rem; color:#1f2937; }
.status-badge { display:inline-block; padding:6px 16px; border-radius:20px; font-weight:bold; font-size:.88rem; }
.s-growing   { background:#dbeafe; color:#1d4ed8; }
.s-ready     { background:#dcfce7; color:#15803d; }
.s-harvested { background:#f3f4f6; color:#4b5563; }
.s-failed    { background:#fee2e2; color:#b91c1c; }
hr { border:none; border-top:1px solid #eee; margin:20px 0; }

/* Forms */
.action-card { border-top:4px solid #27ae60; }
.action-card.danger { border-top-color:#ef4444; }
.action-card h3 { margin:0 0 16px; font-size:1rem; color:#1f2937; }
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.form-group label { display:block; font-size:.82rem; font-weight:600; color:#374151; margin-bottom:5px; }
input[type=date], input[type=number], textarea {
    width:100%; padding:10px 12px; border:1px solid #ddd; border-radius:7px;
    font-size:.9rem; outline:none; box-sizing:border-box; font-family:inherit; transition:.2s;
}
input:focus, textarea:focus { border-color:#27ae60; box-shadow:0 0 0 3px rgba(39,174,96,.15); }
textarea { resize:vertical; min-height:70px; }
.btn { padding:11px 22px; border:none; border-radius:8px; font-weight:bold; font-size:.9rem; cursor:pointer; color:white; transition:filter .2s; }
.btn:hover { filter:brightness(1.08); }
.btn-green { background:#27ae60; }
.btn-red   { background:#ef4444; }
.btn-back  { color:#27ae60; border:1px solid #bbf7d0; background:#f0fdf4; padding:9px 16px; border-radius:8px; text-decoration:none; font-size:.88rem; font-weight:bold; }
.error-msg { color:#e74c3c; font-size:.8rem; margin-top:3px; }

.timeline-item { display:flex; gap:14px; padding:10px 0; border-bottom:1px solid #f1f1f1; font-size:.88rem; }
.tl-icon { width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:1rem; }
</style>

<div class="container">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
        <h2 style="margin:0;">🌿 รายละเอียดรอบปลูก <span style="color:#888;">#{{ str_pad($plantBatch->id,4,'0',STR_PAD_LEFT) }}</span></h2>
        <a href="{{ route('admin.plant-batches.index') }}" class="btn-back">← กลับรายการ</a>
    </div>

    {{-- Info Card --}}
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
            <div>
                <h3 style="margin:0 0 4px;font-size:1.3rem;">{{ $plantBatch->product?->product_name ?? '-' }}</h3>
                <p style="margin:0;color:#888;font-size:.88rem;">บันทึกโดย {{ $plantBatch->creator?->fullname }} · {{ $plantBatch->created_at?->format('d/m/Y H:i') }}</p>
            </div>
            <span class="status-badge s-{{ $plantBatch->status }}">{{ $plantBatch->status_label }}</span>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <label>📅 วันที่เริ่มเพาะ</label>
                <strong>{{ $plantBatch->plant_date->format('d/m/Y') }}</strong>
            </div>
            <div class="info-item">
                <label>🎯 กำหนดเก็บเกี่ยว</label>
                <strong>{{ $plantBatch->expected_harvest_date->format('d/m/Y') }}</strong>
            </div>
            <div class="info-item">
                <label>🌱 จำนวนที่ปลูก</label>
                <strong>{{ number_format($plantBatch->qty_planted, 1) }} กระถาง</strong>
            </div>
            <div class="info-item">
                <label>📦 จำนวนที่เก็บได้</label>
                <strong>{{ $plantBatch->qty_harvested ? number_format($plantBatch->qty_harvested,1).' กระถาง' : '—' }}</strong>
            </div>
            @if($plantBatch->actual_harvest_date)
            <div class="info-item">
                <label>✅ วันที่เก็บจริง</label>
                <strong>{{ $plantBatch->actual_harvest_date->format('d/m/Y') }}</strong>
            </div>
            @endif
            @if($plantBatch->status === 'growing')
            <div class="info-item">
                <label>⏳ เหลือ</label>
                @php $d = $plantBatch->days_until_harvest; @endphp
                <strong style="color:{{ $d < 0 ? '#ef4444' : ($d <= 3 ? '#f59e0b' : '#374151') }}">
                    {{ $d < 0 ? "เลย {abs($d)} วัน" : ($d === 0 ? 'วันนี้!' : "อีก {$d} วัน") }}
                </strong>
            </div>
            @endif
        </div>

        @if($plantBatch->notes)
        <hr>
        <div class="info-item">
            <label>📝 หมายเหตุ</label>
            <p style="margin:4px 0 0;color:#374151;white-space:pre-wrap;font-size:.9rem;">{{ $plantBatch->notes }}</p>
        </div>
        @endif

        @if($plantBatch->stockLog)
        <hr>
        <div class="info-item">
            <label>📊 StockLog ที่สร้างตอนเก็บเกี่ยว</label>
            <strong>+{{ number_format($plantBatch->stockLog->qty_change,1) }} ชิ้น → คงเหลือ {{ number_format($plantBatch->stockLog->qty_after,1) }}</strong>
        </div>
        @endif
    </div>

    {{-- Harvest Form --}}
    @if(in_array($plantBatch->status, ['growing','ready']))
    <div class="card action-card">
        <h3>📦 เก็บเกี่ยวและเพิ่มสต็อก</h3>
        <p style="margin:-8px 0 16px;color:#888;font-size:.85rem;">ระบบจะเพิ่มจำนวนเข้าสต็อกสินค้าและบันทึก StockLog อัตโนมัติ</p>
        <form method="POST" action="{{ route('admin.plant-batches.harvest', $plantBatch) }}"
              onsubmit="return confirm('ยืนยันการเก็บเกี่ยว? สต็อกจะเพิ่มทันที')">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label>จำนวนที่เก็บได้จริง (กระถาง) *</label>
                    <input type="number" name="qty_harvested" step="0.1" min="0.1"
                           value="{{ old('qty_harvested', $plantBatch->qty_planted) }}" required>
                    @error('qty_harvested')<p class="error-msg">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label>วันที่เก็บเกี่ยวจริง *</label>
                    <input type="date" name="actual_harvest_date"
                           value="{{ old('actual_harvest_date', today()->format('Y-m-d')) }}" required>
                    @error('actual_harvest_date')<p class="error-msg">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="form-group" style="margin-top:8px;">
                <label>หมายเหตุการเก็บเกี่ยว</label>
                <textarea name="harvest_notes" placeholder="เช่น คุณภาพดี, มีบางกระถางที่ยังเล็กอยู่...">{{ old('harvest_notes') }}</textarea>
            </div>
            <button type="submit" class="btn btn-green" style="margin-top:4px;">
                🌾 ยืนยันเก็บเกี่ยว — เพิ่มเข้าสต็อก
            </button>
        </form>
    </div>

    {{-- Fail Form --}}
    <div class="card action-card danger">
        <h3>❌ รายงานรอบปลูกล้มเหลว</h3>
        <form method="POST" action="{{ route('admin.plant-batches.fail', $plantBatch) }}"
              onsubmit="return confirm('ยืนยันว่ารอบปลูกนี้ล้มเหลว?')">
            @csrf
            <div class="form-group">
                <label>สาเหตุที่ล้มเหลว</label>
                <textarea name="fail_notes" placeholder="เช่น เชื้อราระบาด, น้ำท่วม...">{{ old('fail_notes') }}</textarea>
            </div>
            <button type="submit" class="btn btn-red">❌ บันทึกว่าล้มเหลว</button>
        </form>
    </div>
    @endif
</div>
</x-app-layout>
