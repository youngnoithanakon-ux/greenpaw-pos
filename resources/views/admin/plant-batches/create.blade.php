<x-app-layout>
<x-slot name="title">บันทึกรอบปลูกหญ้าแมว — GreenPaw</x-slot>
<style>
.container { padding:30px; max-width:700px; margin:auto; }
.card { background:white; padding:32px; border-radius:14px; box-shadow:0 4px 16px rgba(0,0,0,.08); border-top:5px solid #27ae60; }
.form-group { margin-bottom:20px; }
label { display:block; margin-bottom:7px; font-weight:600; color:#2c3e50; font-size:.9rem; }
label span { color:#e74c3c; }
input[type=text], input[type=number], input[type=date], select, textarea {
    width:100%; padding:11px 13px; border:1px solid #ddd; border-radius:8px;
    font-size:.95rem; outline:none; transition:.2s; box-sizing:border-box;
    font-family:inherit;
}
input:focus, select:focus, textarea:focus { border-color:#27ae60; box-shadow:0 0 0 3px rgba(39,174,96,.15); }
textarea { resize:vertical; min-height:90px; }
.grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.btn-submit { width:100%; padding:14px; border:none; border-radius:9px; font-size:1rem; font-weight:bold; cursor:pointer; color:white; background:#27ae60; margin-top:8px; transition:filter .2s; }
.btn-submit:hover { filter:brightness(1.08); }
.btn-back { display:block; text-align:center; margin-top:12px; color:#666; text-decoration:none; font-size:.9rem; }
.error-msg { color:#e74c3c; font-size:.82rem; margin-top:4px; }
.info-box { background:#f0fdf4; border:1px solid #bbf7d0; padding:12px 16px; border-radius:8px; font-size:.85rem; color:#166534; margin-bottom:20px; }
.tip { font-size:.8rem; color:#888; margin-top:4px; }
hr { border:none; border-top:1px solid #eee; margin:20px 0; }
</style>

<div class="container">
    <div class="card">
        <h2 style="margin:0 0 6px;">🌱 บันทึกรอบปลูกหญ้าแมวใหม่</h2>
        <p style="margin:0 0 18px;color:#888;font-size:.9rem;">กรอกข้อมูลการเพาะปลูกเพื่อติดตามและแจ้งเตือน</p>

        <div class="info-box">
            💡 ระบบจะแจ้งเตือนผ่าน LINE โดยอัตโนมัติเมื่อถึงกำหนดวันเก็บเกี่ยว (ถ้าตั้งค่า LINE Notify ไว้แล้ว)
        </div>

        <form method="POST" action="{{ route('admin.plant-batches.store') }}">
            @csrf

            <div class="form-group">
                <label>สินค้า (หญ้าแมว) <span>*</span></label>
                <select name="product_id" required>
                    <option value="">-- เลือกสินค้า --</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ old('product_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->product_name }}
                            (สต็อกปัจจุบัน: {{ $p->stock_qty }})
                        </option>
                    @endforeach
                </select>
                @error('product_id')<p class="error-msg">{{ $message }}</p>@enderror
                @if($products->isEmpty())
                    <p class="tip" style="color:#e74c3c;">⚠️ ยังไม่มีสินค้าประเภท "ผลิตเอง" ในระบบ — <a href="{{ route('admin.products.create') }}">เพิ่มสินค้าก่อน</a></p>
                @endif
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label>วันที่เริ่มเพาะปลูก <span>*</span></label>
                    <input type="date" name="plant_date" value="{{ old('plant_date', today()->format('Y-m-d')) }}" required>
                    @error('plant_date')<p class="error-msg">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label>วันที่คาดว่าจะเก็บเกี่ยวได้ <span>*</span></label>
                    <input type="date" name="expected_harvest_date"
                           value="{{ old('expected_harvest_date', today()->addDays(14)->format('Y-m-d')) }}"
                           required id="harvestDate">
                    <p class="tip">หญ้าแมวโดยทั่วไปพร้อมขายใน 10–21 วัน</p>
                    @error('expected_harvest_date')<p class="error-msg">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="form-group">
                <label>จำนวนกระถางที่ปลูก <span>*</span></label>
                <input type="number" name="qty_planted" min="0.1" step="0.1"
                       value="{{ old('qty_planted', 1) }}" required
                       oninput="updateDuration()">
                @error('qty_planted')<p class="error-msg">{{ $message }}</p>@enderror
            </div>

            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px;margin-bottom:20px;font-size:.88rem;color:#475569;">
                📅 ระยะเวลาการปลูก: <strong id="durationText">—</strong>
            </div>

            <div class="form-group">
                <label>หมายเหตุ / รายละเอียดเพิ่มเติม</label>
                <textarea name="notes" placeholder="เช่น ปลูกในโรงเรือน A, ใช้ดินผสม X...">{{ old('notes') }}</textarea>
            </div>

            <hr>
            <button type="submit" class="btn-submit">✅ บันทึกรอบปลูก</button>
            <a href="{{ route('admin.plant-batches.index') }}" class="btn-back">🔙 กลับหน้ารายการ</a>
        </form>
    </div>
</div>

<script>
const plantDate    = document.querySelector('[name=plant_date]');
const harvestDate  = document.getElementById('harvestDate');
const durationText = document.getElementById('durationText');

function updateDuration() {
    const p = new Date(plantDate.value);
    const h = new Date(harvestDate.value);
    if (!isNaN(p) && !isNaN(h) && h >= p) {
        const days = Math.round((h - p) / 86400000);
        durationText.textContent = days + ' วัน';
        durationText.style.color = days <= 21 ? '#15803d' : '#f59e0b';
    } else {
        durationText.textContent = '—';
    }
}

plantDate.addEventListener('change', updateDuration);
harvestDate.addEventListener('change', updateDuration);
updateDuration();
</script>
</x-app-layout>
