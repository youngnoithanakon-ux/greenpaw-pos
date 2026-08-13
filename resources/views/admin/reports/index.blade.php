<x-app-layout>
    <x-slot name="title">รายงานการขาย — GreenPaw</x-slot>
    <style>
        .container { padding: 24px; max-width: 1200px; margin: auto; }
        .filter-bar {
            background: white; padding: 16px 20px; border-radius: 10px;
            display: flex; flex-wrap: wrap; gap: 12px; align-items: center;
            justify-content: space-between; box-shadow: 0 2px 8px rgba(0,0,0,.05);
            margin-bottom: 24px;
        }
        .filter-bar form { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .filter-bar select { padding: 9px 12px; border: 1px solid #ddd; border-radius: 7px; outline: none; min-width: 130px; }
        .btn-search { background: #2c3e50; color: white; border: none; padding: 10px 18px; border-radius: 7px; cursor: pointer; font-weight: bold; }
        .btn-reset  { font-size: .82rem; color: #666; text-decoration: none; }
        .note-void  { font-size: .8rem; color: #e74c3c; font-style: italic; }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px; margin-bottom: 28px; }
        .stat-card {
            padding: 22px; border-radius: 14px; color: white;
            box-shadow: 0 6px 16px rgba(0,0,0,.12); position: relative; overflow: hidden;
        }
        .stat-card h3 { margin: 0; font-size: .85rem; opacity: .9; font-weight: normal; }
        .stat-card .amount { font-size: 2rem; font-weight: bold; margin-top: 10px; display: block; }
        .stat-card small { opacity: .8; font-size: .78rem; }
        .bg-blue   { background: linear-gradient(135deg,#3498db,#2980b9); }
        .bg-purple { background: linear-gradient(135deg,#9b59b6,#8e44ad); }
        .bg-teal   { background: linear-gradient(135deg,#1abc9c,#16a085); }
        .bg-orange { background: linear-gradient(135deg,#f39c12,#d35400); }
        .bg-green  { background: linear-gradient(135deg,#27ae60,#2ecc71); }

        .chart-card { background: white; padding: 24px; border-radius: 14px; box-shadow: 0 4px 12px rgba(0,0,0,.06); }
        .chart-card h3 { margin: 0 0 4px; color: #2c3e50; }
        .chart-card p  { margin: 0 0 20px; color: #aaa; font-size: .85rem; }

        @media (max-width: 600px) { .stat-card .amount { font-size: 1.6rem; } }
        @media print {
            .filter-bar, button { display: none !important; }
            .stat-card { color: black !important; background: white !important; box-shadow: none !important; border: 1px solid #ddd; }
        }
    </style>

    <div class="container">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:10px;">
            <h2 style="margin:0;">📊 วิเคราะห์ยอดขายและกำไร</h2>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <a href="{{ route('admin.reports.export-excel', ['month' => $selectedMonth, 'year' => $selectedYear]) }}"
                   style="padding:9px 16px;border:1px solid #27ae60;border-radius:7px;background:#f0fdf4;color:#27ae60;cursor:pointer;text-decoration:none;font-size:.9rem;font-weight:bold;">
                    📥 Export Excel
                </a>
                <a href="{{ route('admin.reports.export-pdf', ['month' => $selectedMonth, 'year' => $selectedYear]) }}"
                   style="padding:9px 16px;border:1px solid #e74c3c;border-radius:7px;background:#fff5f5;color:#e74c3c;cursor:pointer;text-decoration:none;font-size:.9rem;font-weight:bold;">
                    📄 Export PDF
                </a>
                <button onclick="window.print()" style="padding:9px 16px;border:1px solid #ddd;border-radius:7px;background:white;cursor:pointer;">🖨️ พิมพ์รายงาน</button>
            </div>
        </div>

        <div class="filter-bar">
            <form method="GET">
                <span style="color:#555;font-size:.9rem;">แสดงรายงานประจำเดือน:</span>
                <select name="month">
                    @foreach($months as $num => $name)
                        <option value="{{ $num }}" {{ $selectedMonth == $num ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
                <select name="year">
                    @foreach($availableYears as $y)
                        <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-search">🔍 ดูข้อมูล</button>
                <a href="{{ route('admin.reports.index') }}" class="btn-reset">รีเซ็ต</a>
            </form>
            <span class="note-void">* ข้อมูลนี้หักรายการที่ยกเลิก (Void) ออกแล้ว</span>
        </div>

        <div class="stats-grid">
            <div class="stat-card bg-blue">
                <h3>รายรับรวม (Total Revenue)</h3>
                <span class="amount">฿{{ number_format($summary->total_revenue ?? 0, 2) }}</span>
            </div>
            <div class="stat-card bg-purple">
                <h3>ค่าฝากขาย (Consignment Fee)</h3>
                <span class="amount">฿{{ number_format($summary->total_consignment ?? 0, 2) }}</span>
                <small>ยอดที่ต้องหักให้ร้านที่รับฝาก</small>
            </div>
            <div class="stat-card bg-teal">
                <h3>รายรับสุทธิ (Net Revenue)</h3>
                <span class="amount">฿{{ number_format(($summary->total_revenue ?? 0) - ($summary->total_consignment ?? 0), 2) }}</span>
                <small>รายรับหลังหักค่าฝากขาย</small>
            </div>
            <div class="stat-card bg-orange">
                <h3>ต้นทุนสินค้า (Production Cost)</h3>
                <span class="amount">฿{{ number_format($summary->total_costs ?? 0, 2) }}</span>
            </div>
            <div class="stat-card bg-green">
                <h3>กำไรสุทธิ (Net Profit)</h3>
                <span class="amount">฿{{ number_format($summary->total_profit ?? 0, 2) }}</span>
            </div>
        </div>

        <div class="chart-card">
            <h3>📈 กราฟยอดขายรายวัน ประจำเดือน{{ $months[$selectedMonth] }} {{ $selectedYear }}</h3>
            <p>หักรายการ Void ออกแล้ว</p>
            @if(empty($chartValues))
                <p style="text-align:center;padding:60px;color:#aaa;">ไม่พบข้อมูลการขายในเดือนที่เลือก</p>
            @else
                <div style="position:relative;height:340px;">
                    <canvas id="salesChart"></canvas>
                </div>
            @endif
        </div>
    </div>

    @if(!empty($chartValues))
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    new Chart(document.getElementById('salesChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'ยอดขายรายวัน (บาท)',
                data: @json($chartValues),
                borderColor: '#3498db',
                backgroundColor: 'rgba(52,152,219,.15)',
                borderWidth: 3, fill: true, tension: 0.4,
                pointRadius: 4, pointBackgroundColor: '#fff', pointBorderColor: '#3498db'
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
            scales: {
                y: { beginAtZero: true,
                     ticks: { callback: v => '฿' + v.toLocaleString() } }
            }
        }
    });
    </script>
    @endif
</x-app-layout>
