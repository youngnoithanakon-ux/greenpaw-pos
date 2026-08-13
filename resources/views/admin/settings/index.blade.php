<x-app-layout>
<x-slot name="title">ตั้งค่าระบบ — GreenPaw</x-slot>
<style>
.container { padding:28px; max-width:760px; margin:auto; }
.card { background:white; padding:28px; border-radius:14px; box-shadow:0 4px 12px rgba(0,0,0,.07); margin-bottom:22px; }
.card-title { font-size:1rem; font-weight:bold; color:#1f2937; margin:0 0 18px; padding-bottom:10px; border-bottom:2px solid #f1f1f1; display:flex; justify-content:space-between; align-items:center; }
.form-group { margin-bottom:18px; }
.form-group label { display:block; font-size:.85rem; font-weight:600; color:#374151; margin-bottom:6px; }
.form-group small { display:block; color:#9ca3af; font-size:.78rem; margin-top:5px; line-height:1.5; }
input[type=text], input[type=number] {
    width:100%; padding:11px 13px; border:1px solid #ddd; border-radius:8px;
    font-size:.9rem; outline:none; transition:.2s; box-sizing:border-box;
}
input:focus { border-color:#27ae60; box-shadow:0 0 0 3px rgba(39,174,96,.15); }
.token-row { display:flex; gap:8px; }
.token-row input { flex:1; }

/* Toggle */
.toggle-row { display:flex; align-items:center; gap:14px; margin-bottom:6px; }
.toggle { position:relative; width:52px; height:28px; flex-shrink:0; }
.toggle input { display:none; }
.toggle-slider { position:absolute; inset:0; background:#ccc; border-radius:28px; cursor:pointer; transition:.3s; }
.toggle-slider:before { content:''; position:absolute; height:20px; width:20px; left:4px; bottom:4px; background:white; border-radius:50%; transition:.3s; }
.toggle input:checked + .toggle-slider { background:#27ae60; }
.toggle input:checked + .toggle-slider:before { transform:translateX(24px); }

/* Buttons */
.btn { padding:10px 20px; border:none; border-radius:8px; font-weight:bold; font-size:.88rem; cursor:pointer; color:white; transition:filter .2s; white-space:nowrap; }
.btn:hover { filter:brightness(1.08); }
.btn-green { background:#27ae60; }
.btn-blue  { background:#3b82f6; }
.btn-gray  { background:#6b7280; }
.btn-save  { width:100%; padding:13px; border:none; border-radius:9px; font-size:1rem; font-weight:bold; cursor:pointer; color:white; background:#27ae60; }

.status-ok  { color:#15803d; background:#dcfce7; padding:3px 10px; border-radius:4px; font-size:.78rem; font-weight:bold; }
.status-off { color:#b91c1c; background:#fee2e2; padding:3px 10px; border-radius:4px; font-size:.78rem; font-weight:bold; }

.info-box { background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:14px 16px; font-size:.82rem; color:#1e40af; line-height:1.7; }
.warn-box { background:#fffbeb; border:1px solid #fde68a; border-radius:8px; padding:14px 16px; font-size:.82rem; color:#92400e; line-height:1.7; }
.step-box { background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:16px; font-size:.82rem; color:#166534; }
.step-box ol { padding-left:18px; margin:8px 0 0; line-height:2; }
.step-box a { color:#15803d; font-weight:bold; }

.test-form { background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:16px; margin-top:16px; }
.test-form p { font-size:.85rem; font-weight:600; color:#374151; margin:0 0 10px; }
.test-grid { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
.test-grid input { padding:9px 12px; border:1px solid #ddd; border-radius:7px; font-size:.85rem; outline:none; }
hr { border:none; border-top:1px solid #eee; margin:18px 0; }
</style>

<div class="container">
    <h2 style="margin-bottom:22px;">🔧 ตั้งค่าระบบ</h2>

    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf

        {{-- ข้อมูลร้าน --}}
        <div class="card">
            <div class="card-title">🏪 ข้อมูลร้าน</div>
            <div class="form-group">
                <label>ชื่อร้าน</label>
                <input type="text" name="store_name" value="{{ $configs['store_name'] }}" maxlength="100" required>
                <small>ใช้แสดงในข้อความแจ้งเตือน LINE</small>
            </div>
        </div>

        {{-- LINE Messaging API --}}
        <div class="card">
            <div class="card-title">
                <span>📲 LINE Messaging API</span>
                @if($configs['line_notify_enabled'] === '1')
                    <span class="status-ok">✅ เปิดใช้งาน</span>
                @else
                    <span class="status-off">❌ ปิดอยู่</span>
                @endif
            </div>

            {{-- วิธีตั้งค่า --}}
            <div class="step-box">
                <strong>📋 วิธีตั้งค่า LINE Messaging API</strong>
                <ol>
                    <li>สร้าง LINE Official Account ที่ <a href="https://manager.line.biz/" target="_blank">manager.line.biz</a></li>
                    <li>ไปที่ <a href="https://developers.line.biz/" target="_blank">developers.line.biz</a> → เปิด Messaging API</li>
                    <li>Copy <strong>Channel access token (long-lived)</strong> มาใส่ช่องด้านล่าง</li>
                    <li>หา <strong>Target ID</strong>: เพิ่มบอทเป็นเพื่อน หรือเพิ่มเข้ากลุ่ม<br>
                        จากนั้นส่งข้อความมาที่บอท แล้วดู <code>source.userId</code> หรือ <code>source.groupId</code> ใน Webhook log</li>
                    <li>กดทดสอบเพื่อยืนยันว่าส่งได้จริง</li>
                </ol>
            </div>

            <hr>

            <div class="form-group" style="margin-top:4px;">
                <div class="toggle-row">
                    <label class="toggle">
                        <input type="checkbox" name="line_notify_enabled" value="1"
                               {{ $configs['line_notify_enabled'] === '1' ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                    <span style="font-size:.9rem;color:#374151;font-weight:600;">เปิดใช้งานการแจ้งเตือน LINE</span>
                </div>
                <small style="padding-left:66px;">เมื่อปิด ระบบจะไม่ส่ง LINE แม้จะมี token อยู่</small>
            </div>

            <div class="form-group">
                <label>Channel Access Token (Long-lived)</label>
                <div class="token-row">
                    <input type="text" name="line_channel_token" id="tokenInput"
                           value="{{ $configs['line_channel_token'] }}"
                           placeholder="วาง Channel access token ที่ได้จาก LINE Developers Console">
                    <button type="button" class="btn btn-gray" onclick="toggleToken('tokenInput', this)">👁️</button>
                </div>
                <small>ได้จาก LINE Developers Console → เลือก Channel → Messaging API → Channel access token</small>
            </div>

            <div class="form-group">
                <label>Target ID (User ID หรือ Group ID)</label>
                <input type="text" name="line_target_id" id="targetInput"
                       value="{{ $configs['line_target_id'] }}"
                       placeholder="U... (User ID) หรือ C... (Group ID)">
                <small>
                    <strong>User ID</strong> — ขึ้นต้นด้วย <code>U</code> (ส่งถึงคนเดียว)<br>
                    <strong>Group ID</strong> — ขึ้นต้นด้วย <code>C</code> (ส่งเข้ากลุ่ม LINE)<br>
                    วิธีหา: เปิด Webhook → ส่งข้อความหาบอท → ดูใน <code>source.userId</code> หรือ <code>source.groupId</code>
                </small>
            </div>

            {{-- Test --}}
            <div class="test-form">
                <p>🔔 ทดสอบการส่งข้อความ (ใส่ค่าใหม่เพื่อทดสอบก่อนบันทึก หรือเว้นว่างเพื่อใช้ค่าที่บันทึกไว้)</p>
                <form method="POST" action="{{ route('admin.settings.test-line') }}">
                    @csrf
                    <div class="test-grid" style="margin-bottom:10px;">
                        <input type="text" name="test_token"
                               placeholder="Channel Access Token (ทดสอบ)">
                        <input type="text" name="test_target_id"
                               placeholder="Target ID (ทดสอบ)">
                    </div>
                    <button type="submit" class="btn btn-blue">📤 ส่งข้อความทดสอบ</button>
                </form>
            </div>

            <hr>

            <div class="warn-box">
                <strong>📌 ระบบจะแจ้งเตือน LINE อัตโนมัติเมื่อ:</strong>
                <ul style="margin:8px 0 0;padding-left:18px;">
                    <li>รอบปลูกหญ้าแมวถึงกำหนดเก็บเกี่ยว (status → พร้อมเก็บ)</li>
                    <li>เก็บเกี่ยวสำเร็จ พร้อมสรุปจำนวนสต็อกที่เพิ่มเข้า</li>
                    <li>สต็อกต่ำกว่า threshold ที่กำหนด (แนบท้ายข้อความ)</li>
                </ul>
            </div>
        </div>

        {{-- Stock Alert --}}
        <div class="card">
            <div class="card-title">📦 แจ้งเตือนสต็อก</div>
            <div class="form-group">
                <label>แจ้งเตือนเมื่อสต็อกต่ำกว่า (จำนวนชิ้น)</label>
                <input type="number" name="stock_alert_threshold"
                       value="{{ $configs['stock_alert_threshold'] }}"
                       min="0" max="999" required style="max-width:160px;">
                <small>ระบบจะแนบข้อความเตือนใน LINE เมื่อสต็อกหลังเก็บเกี่ยวต่ำกว่าค่านี้</small>
            </div>
        </div>

        <button type="submit" class="btn-save">💾 บันทึกการตั้งค่าทั้งหมด</button>
    </form>
</div>

<script>
function toggleToken(inputId, btn) {
    const input = document.getElementById(inputId);
    input.type  = input.type === 'password' ? 'text' : 'password';
    btn.textContent = input.type === 'password' ? '👁️' : '🔒';
}
// ซ่อน token เริ่มต้น
document.getElementById('tokenInput').type = 'password';
</script>
</x-app-layout>
