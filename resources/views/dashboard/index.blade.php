<x-app-layout>
    <x-slot name="title">Dashboard — GreenPaw</x-slot>
    <style>
        .container { padding: 25px; max-width: 1200px; margin: auto; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px; margin-bottom: 28px; }
        .stat-card {
            background: white; padding: 20px; border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,.06); border-bottom: 5px solid #eee;
        }
        .stat-card .label { font-size: .85rem; color: #666; margin-bottom: 8px; }
        .stat-card .value { font-size: 1.8rem; font-weight: bold; color: #2c3e50; }
        .c-green  { border-color: #27ae60; } .c-green  .value { color: #27ae60; }
        .c-blue   { border-color: #3498db; } .c-blue   .value { color: #3498db; }
        .c-purple { border-color: #9b59b6; } .c-purple .value { color: #9b59b6; }
        .c-teal   { border-color: #1abc9c; } .c-teal   .value { color: #16a085; }
        .c-yellow { border-color: #f1c40f; }

        .dashboard-row { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        .panel { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,.06); }
        .panel h3 { margin: 0 0 14px; font-size: 1rem; color: #2c3e50; }
        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 9px 10px; text-align: left; border-bottom: 1px solid #eee; font-size: .9rem; }
        th { color: #888; font-weight: 500; }
        .stock-alert { color: #e74c3c; font-weight: bold; }

        @media (max-width: 768px) { .dashboard-row { grid-template-columns: 1fr; } }
    </style>

    <div class="container">
        <h2 style="margin-bottom:22px;">📊 ภาพรวมวันนี้</h2>

        <div class="stats-grid">
            <div class="stat-card c-yellow">
                <div class="label">จำนวนบิลวันนี้</div>
                <div class="value">{{ number_format($summaryToday->total_bills ?? 0) }}</div>
            </div>
            <div class="stat-card c-blue">
                <div class="label">ยอดขายรวมวันนี้</div>
                <div class="value">฿{{ number_format($summaryToday->total_sales ?? 0, 2) }}</div>
            </div>
            @if($role === 'admin')
            <div class="stat-card c-purple">
                <div class="label">ค่าฝากขายวันนี้</div>
                <div class="value">฿{{ number_format($summaryToday->total_consignment ?? 0, 2) }}</div>
            </div>
            <div class="stat-card c-teal">
                <div class="label">รายรับสุทธิวันนี้</div>
                <div class="value">฿{{ number_format(($summaryToday->total_sales ?? 0) - ($summaryToday->total_consignment ?? 0), 2) }}</div>
            </div>
            <div class="stat-card c-green">
                <div class="label">กำไรสุทธิวันนี้</div>
                <div class="value">฿{{ number_format($summaryToday->total_profit ?? 0, 2) }}</div>
            </div>
            @endif
        </div>

        <div class="dashboard-row">
            <div class="panel">
                <h3>📈 ยอดขาย 7 วันย้อนหลัง</h3>
                <div style="position:relative;height:280px;">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <div>
                <div class="panel" style="margin-bottom:18px;">
                    <h3>⚠️ สต็อกใกล้หมด</h3>
                    <table>
                        @forelse($lowStock as $item)
                        <tr>
                            <td>{{ $item->product_name }}</td>
                            <td class="stock-alert" style="text-align:right;">{{ $item->stock_qty }} ชิ้น</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" style="color:#888;">✅ สต็อกปกติทุกรายการ</td></tr>
                        @endforelse
                    </table>
                </div>

                <div class="panel">
                    <h3>🏆 5 อันดับขายดี</h3>
                    <table>
                        @forelse($topSellers as $i => $item)
                        <tr>
                            <td>{{ $i+1 }}. {{ $item->product_name }}</td>
                            <td style="text-align:right;font-weight:bold;">{{ number_format($item->total_qty) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" style="color:#888;">ยังไม่มีข้อมูล</td></tr>
                        @endforelse
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'ยอดขาย (บาท)',
                data: @json($chartData),
                borderColor: '#3498db',
                backgroundColor: 'rgba(52,152,219,.12)',
                fill: true, tension: 0.35, borderWidth: 3,
                pointRadius: 4, pointBackgroundColor: '#fff', pointBorderColor: '#3498db'
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            scales: { y: { beginAtZero: true,
                ticks: { callback: v => '฿' + v.toLocaleString() }
            }},
            plugins: { legend: { display: false } }
        }
    });
    </script>
</x-app-layout>
