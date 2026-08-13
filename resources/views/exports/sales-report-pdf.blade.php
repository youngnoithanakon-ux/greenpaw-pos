<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<style>
  @page { margin: 18mm 16mm; }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'DejaVu Sans', 'Sarabun', sans-serif; font-size: 11px; color: #1a1a1a; }

  .header { text-align: center; padding-bottom: 12px; border-bottom: 2px solid #27ae60; margin-bottom: 16px; }
  .header h1 { font-size: 18px; color: #1a2e1a; margin-bottom: 4px; }
  .header p  { font-size: 11px; color: #555; }

  .summary-grid { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
  .stat-box {
    flex: 1; min-width: 120px; padding: 10px 14px;
    border-radius: 8px; text-align: center;
  }
  .stat-box small { display: block; font-size: 9px; margin-bottom: 4px; }
  .stat-box strong { font-size: 14px; font-weight: bold; display: block; }
  .s-blue   { background: #dbeafe; color: #1e40af; }
  .s-purple { background: #ede9fe; color: #6d28d9; }
  .s-teal   { background: #ccfbf1; color: #0f766e; }
  .s-orange { background: #ffedd5; color: #c2410c; }
  .s-green  { background: #dcfce7; color: #15803d; }

  h2 { font-size: 13px; color: #374151; margin: 16px 0 8px; padding-bottom: 4px; border-bottom: 1px solid #e5e7eb; }

  table { width: 100%; border-collapse: collapse; font-size: 10.5px; }
  th { background: #1a2e1a; color: white; padding: 7px 10px; text-align: left; font-weight: 500; }
  th.r, td.r { text-align: right; }
  td { padding: 6px 10px; border-bottom: 1px solid #f1f1f1; }
  tr:nth-child(even) td { background: #f9fafb; }
  tfoot td { background: #f0fdf4; font-weight: bold; border-top: 2px solid #27ae60; }

  .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #9ca3af; padding-top: 10px; border-top: 1px solid #e5e7eb; }
</style>
</head>
<body>

<div class="header">
  <h1>🌿 GreenPaw Systems</h1>
  <p>รายงานยอดขายและกำไร ประจำเดือน{{ $monthName }} {{ $selectedYear }}</p>
  <p style="font-size:10px;color:#888;">สร้างเมื่อ {{ now()->format('d/m/Y H:i') }} · หักบิลที่ยกเลิก (Void) ออกแล้ว</p>
</div>

{{-- Summary --}}
<div class="summary-grid">
  <div class="stat-box s-blue">
    <small>รายรับรวม</small>
    <strong>฿{{ number_format($summary->total_revenue ?? 0, 2) }}</strong>
  </div>
  <div class="stat-box s-purple">
    <small>ค่าฝากขาย</small>
    <strong>฿{{ number_format($summary->total_consignment ?? 0, 2) }}</strong>
  </div>
  <div class="stat-box s-teal">
    <small>รายรับสุทธิ</small>
    <strong>฿{{ number_format(($summary->total_revenue ?? 0) - ($summary->total_consignment ?? 0), 2) }}</strong>
  </div>
  <div class="stat-box s-orange">
    <small>ต้นทุนรวม</small>
    <strong>฿{{ number_format($summary->total_costs ?? 0, 2) }}</strong>
  </div>
  <div class="stat-box s-green">
    <small>กำไรสุทธิ</small>
    <strong>฿{{ number_format($summary->total_profit ?? 0, 2) }}</strong>
  </div>
</div>

{{-- Sales Table --}}
<h2>รายการขายทั้งหมด ({{ $sales->count() }} บิล)</h2>
<table>
  <thead>
    <tr>
      <th>เลขที่บิล</th>
      <th>วันที่</th>
      <th>ผู้ขาย</th>
      <th class="r">ยอดขาย</th>
      <th class="r">ค่าฝาก</th>
      <th class="r">ต้นทุน</th>
      <th class="r">กำไร</th>
    </tr>
  </thead>
  <tbody>
    @php $totRev=0; $totCon=0; $totCost=0; $totProfit=0; @endphp
    @foreach($sales as $s)
    @php
      $profit = $s->total_amount - $s->total_cost - $s->total_consignment_fee;
      $totRev    += $s->total_amount;
      $totCon    += $s->total_consignment_fee;
      $totCost   += $s->total_cost;
      $totProfit += $profit;
    @endphp
    <tr>
      <td>#{{ str_pad($s->id,5,'0',STR_PAD_LEFT) }}</td>
      <td>{{ \Carbon\Carbon::parse($s->sale_date)->format('d/m/Y H:i') }}</td>
      <td>{{ $s->user?->fullname ?? '-' }}</td>
      <td class="r">฿{{ number_format($s->total_amount,2) }}</td>
      <td class="r" style="color:#7c3aed;">฿{{ number_format($s->total_consignment_fee,2) }}</td>
      <td class="r" style="color:#dc2626;">฿{{ number_format($s->total_cost,2) }}</td>
      <td class="r" style="color:#15803d;font-weight:bold;">฿{{ number_format($profit,2) }}</td>
    </tr>
    @endforeach
  </tbody>
  <tfoot>
    <tr>
      <td colspan="3">รวมทั้งหมด</td>
      <td class="r">฿{{ number_format($totRev,2) }}</td>
      <td class="r">฿{{ number_format($totCon,2) }}</td>
      <td class="r">฿{{ number_format($totCost,2) }}</td>
      <td class="r">฿{{ number_format($totProfit,2) }}</td>
    </tr>
  </tfoot>
</table>

<div class="footer">
  GreenPaw Systems · รายงานสร้างโดยระบบอัตโนมัติ · ข้อมูลอาจมีการเปลี่ยนแปลง
</div>

</body>
</html>
