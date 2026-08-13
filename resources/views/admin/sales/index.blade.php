<x-app-layout>
    <x-slot name="title">ประวัติการขาย — GreenPaw</x-slot>
    <style>
        .container { padding: 24px; max-width: 1300px; margin: auto; }
        .filter-bar {
            background: white; padding: 16px 20px; border-radius: 10px;
            display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end;
            box-shadow: 0 2px 8px rgba(0,0,0,.05); margin-bottom: 20px;
        }
        .filter-group { display: flex; flex-direction: column; gap: 4px; }
        .filter-group label { font-size: .8rem; color: #666; }
        input[type=date] { padding: 9px 12px; border: 1px solid #ddd; border-radius: 7px; font-size: .9rem; outline: none; }
        .btn-search { background: #27ae60; color: white; border: none; padding: 10px 20px; border-radius: 7px; cursor: pointer; font-weight: bold; }
        .btn-reset  { color: #666; font-size: .85rem; text-decoration: none; padding: 10px 0; }

        .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 22px; }
        .sum-card { background: white; padding: 18px; border-radius: 10px; text-align: center; box-shadow: 0 2px 6px rgba(0,0,0,.05); border-top: 4px solid #eee; }
        .sum-card small { color: #666; font-size: .8rem; }
        .sum-card strong { display: block; font-size: 1.5rem; margin-top: 6px; }

        .table-wrap { background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,.06); overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 860px; }
        th { background: #f8f9fa; padding: 12px 14px; text-align: left; color: #718096; font-size: .8rem; text-transform: uppercase; font-weight: 500; border-bottom: 2px solid #edf2f7; }
        td { padding: 12px 14px; border-bottom: 1px solid #eee; vertical-align: middle; font-size: .9rem; }
        tr.void-row td { background: #fff5f5; opacity: .75; }

        .badge-void { background: #fee2e2; color: #b91c1c; padding: 3px 8px; border-radius: 4px; font-size: .72rem; font-weight: bold; }
        .btn-view {
            background: #3498db; color: white; border: none; padding: 6px 12px;
            border-radius: 5px; cursor: pointer; font-size: .82rem; transition: .15s;
        }
        .btn-view:hover { background: #2980b9; }
        .btn-void {
            color: #e74c3c; border: 1px solid #fecaca; background: #fff5f5;
            padding: 5px 10px; border-radius: 5px; font-size: .82rem;
            cursor: pointer; margin-left: 6px; transition: .15s;
        }
        .btn-void:hover { background: #e74c3c; color: white; }

        /* Modal */
        .modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 9999; overflow-y: auto; padding: 20px; }
        .modal-box { background: white; margin: 28px auto; padding: 28px; width: 100%; max-width: 860px; border-radius: 12px; box-sizing: border-box; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; }
        .modal-close { font-size: 1.8rem; cursor: pointer; color: #666; line-height: 1; background: none; border: none; }
        .modal-body-scroll { overflow-x: auto; }
        .modal-table { width: 100%; border-collapse: collapse; min-width: 500px; }
        .modal-table th { background: #f8f9fa; color: #333; padding: 10px 14px; text-align: left; border-bottom: 2px solid #eee; font-size: .85rem; }
        .modal-table td { padding: 10px 14px; border-bottom: 1px solid #eee; font-size: .9rem; }
        .modal-table tfoot td { padding: 12px 14px; font-weight: bold; background: #f0fdf4; }
        .loading { text-align: center; padding: 40px; color: #aaa; }
    </style>

    <div class="container">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px;">
            <h2 style="margin:0;">📝 ประวัติการขายโดยละเอียด</h2>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <a href="{{ route('admin.sales.export-excel', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                   style="padding:9px 16px;border:1px solid #27ae60;border-radius:7px;background:#f0fdf4;color:#27ae60;font-weight:bold;text-decoration:none;font-size:.88rem;">
                    📥 Export Excel
                </a>
                <button onclick="window.print()" style="padding:9px 16px;border:1px solid #ddd;border-radius:7px;background:white;cursor:pointer;font-size:.88rem;">🖨️ พิมพ์</button>
            </div>
        </div>

        {{-- Filter --}}
        <form method="GET" class="filter-bar">
            <div class="filter-group">
                <label>ตั้งแต่วันที่</label>
                <input type="date" name="start_date" value="{{ $startDate }}">
            </div>
            <div class="filter-group">
                <label>ถึงวันที่</label>
                <input type="date" name="end_date" value="{{ $endDate }}">
            </div>
            <button type="submit" class="btn-search">🔍 ค้นหา</button>
            <a href="{{ route('admin.sales.index') }}" class="btn-reset">ล้างค่า</a>
        </form>

        {{-- Summary --}}
        <div class="summary-grid">
            <div class="sum-card" style="border-color:#3498db;">
                <small>ยอดขายรวม (ปกติ)</small>
                <strong style="color:#2c3e50;">฿{{ number_format($summary['sales'], 2) }}</strong>
            </div>
            <div class="sum-card" style="border-color:#9b59b6;">
                <small>ค่าฝากขาย</small>
                <strong style="color:#9b59b6;">฿{{ number_format($summary['consignment'], 2) }}</strong>
            </div>
            <div class="sum-card" style="border-color:#1abc9c;">
                <small>รายรับสุทธิ</small>
                <strong style="color:#16a085;">฿{{ number_format($summary['sales'] - $summary['consignment'], 2) }}</strong>
            </div>
            <div class="sum-card" style="border-color:#e74c3c;">
                <small>ต้นทุนรวม</small>
                <strong style="color:#e74c3c;">฿{{ number_format($summary['cost'], 2) }}</strong>
            </div>
            <div class="sum-card" style="border-color:#27ae60;">
                <small>กำไรสุทธิ</small>
                <strong style="color:#27ae60;">฿{{ number_format($summary['profit'], 2) }}</strong>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>วันที่/เวลา</th>
                        <th>เลขที่บิล</th>
                        <th>ผู้ขาย</th>
                        <th>ยอดขาย</th>
                        <th>ค่าฝาก</th>
                        <th>ต้นทุน</th>
                        <th>กำไร</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $s)
                    @php
                        $isVoid  = $s->status === 'void';
                        $profit  = $s->total_amount - $s->total_cost - $s->total_consignment_fee;
                    @endphp
                    <tr class="{{ $isVoid ? 'void-row' : '' }}">
                        <td><small>{{ \Carbon\Carbon::parse($s->sale_date)->format('d/m/Y H:i') }}</small></td>
                        <td>
                            #{{ str_pad($s->id, 5, '0', STR_PAD_LEFT) }}
                            @if($isVoid) <span class="badge-void">ยกเลิกแล้ว</span> @endif
                        </td>
                        <td>{{ $s->user?->fullname ?? '-' }}</td>
                        <td style="{{ $isVoid ? 'text-decoration:line-through;color:#aaa;' : '' }}">
                            ฿{{ number_format($s->total_amount, 2) }}
                        </td>
                        <td style="color:#9b59b6;">฿{{ number_format($s->total_consignment_fee, 2) }}</td>
                        <td style="color:#e74c3c;">฿{{ number_format($s->total_cost, 2) }}</td>
                        <td style="color:#27ae60;font-weight:bold;">฿{{ number_format($profit, 2) }}</td>
                        <td>
                            <button class="btn-view" onclick="showItems({{ $s->id }})">👁️ รายการ</button>
                            @if(!$isVoid)
                            <form method="POST" action="{{ route('admin.sales.void', $s) }}"
                                  style="display:inline;"
                                  onsubmit="return confirm('⚠️ ยืนยันยกเลิกบิล #{{ str_pad($s->id,5,'0',STR_PAD_LEFT) }}?\nสต็อกจะถูกคืนเข้าคลัง')">
                                @csrf @method('POST')
                                <button type="submit" class="btn-void">🚫 Void</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" style="text-align:center;padding:50px;color:#aaa;">ไม่พบข้อมูลในช่วงเวลาที่เลือก</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal --}}
    <div id="itemModal" class="modal" onclick="if(event.target===this)closeModal()">
        <div class="modal-box">
            <div class="modal-header">
                <h3 id="modalTitle" style="margin:0;">รายละเอียดบิล</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div id="modalBody"><p class="loading">กำลังโหลด...</p></div>
        </div>
    </div>

    <script>
    async function showItems(saleId) {
        document.getElementById('modalTitle').textContent = 'รายละเอียดบิลเลขที่ #' + String(saleId).padStart(5, '0');
        document.getElementById('modalBody').innerHTML = '<p class="loading">กำลังโหลดข้อมูล...</p>';
        document.getElementById('itemModal').style.display = 'block';

        try {
            const res  = await fetch(`/admin/sales/${saleId}/items`);
            const data = await res.json();

            if (!data.length) {
                document.getElementById('modalBody').innerHTML = '<p style="color:#aaa;text-align:center;padding:30px;">ไม่มีรายการ</p>';
                return;
            }

            let html = `<div class="modal-body-scroll"><table class="modal-table">
                <thead><tr>
                    <th>สินค้า</th>
                    <th style="text-align:center;">จำนวน</th>
                    <th style="text-align:right;">ราคา/หน่วย</th>
                    <th style="text-align:right;">รวม</th>
                </tr></thead><tbody>`;

            let grandTotal = 0;
            data.forEach(item => {
                const subtotal = item.unit_price * item.qty;
                grandTotal += subtotal;
                html += `<tr>
                    <td>${item.product?.product_name ?? '-'}</td>
                    <td style="text-align:center;">${item.qty}</td>
                    <td style="text-align:right;">฿${parseFloat(item.unit_price).toLocaleString('th', {minimumFractionDigits:2})}</td>
                    <td style="text-align:right;font-weight:bold;">฿${subtotal.toLocaleString('th', {minimumFractionDigits:2})}</td>
                </tr>`;
            });

            html += `</tbody><tfoot><tr>
                <td colspan="3" style="text-align:right;">รวมทั้งสิ้น:</td>
                <td style="text-align:right;font-size:1.2rem;color:#27ae60;">฿${grandTotal.toLocaleString('th', {minimumFractionDigits:2})}</td>
            </tr></tfoot></table></div>`;

            document.getElementById('modalBody').innerHTML = html;
        } catch {
            document.getElementById('modalBody').innerHTML = '<p style="color:red;text-align:center;">เกิดข้อผิดพลาด</p>';
        }
    }

    function closeModal() {
        document.getElementById('itemModal').style.display = 'none';
    }
    </script>
</x-app-layout>
