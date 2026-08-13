<x-app-layout>
<x-slot name="title">จัดการการปลูกหญ้าแมว — GreenPaw</x-slot>
<style>
.container { padding:24px; max-width:1300px; margin:auto; }
.header-bar { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px; }
.btn-new { background:#27ae60; color:white; text-decoration:none; font-weight:bold; padding:10px 18px; border-radius:8px; white-space:nowrap; }
.btn-new:hover { background:#219150; }

/* stat cards */
.stat-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:14px; margin-bottom:22px; }
.stat-card { background:white; padding:16px; border-radius:10px; text-align:center; box-shadow:0 2px 6px rgba(0,0,0,.06); border-top:4px solid #eee; }
.stat-card small { color:#666; font-size:.78rem; display:block; margin-bottom:6px; }
.stat-card strong { font-size:1.8rem; font-weight:bold; }

/* filter */
.filter-bar { background:white; padding:14px 18px; border-radius:10px; display:flex; gap:10px; flex-wrap:wrap; margin-bottom:18px; box-shadow:0 2px 6px rgba(0,0,0,.04); }
.filter-bar select { padding:9px 12px; border:1px solid #ddd; border-radius:7px; outline:none; }
.btn-filter { background:#2c3e50; color:white; border:none; padding:9px 18px; border-radius:7px; cursor:pointer; font-weight:bold; }

/* table */
.table-wrap { background:white; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,.06); overflow-x:auto; }
table { width:100%; border-collapse:collapse; min-width:860px; }
th { background:#2c3e50; color:white; padding:12px 14px; text-align:left; font-weight:500; font-size:.85rem; }
td { padding:12px 14px; border-bottom:1px solid #eee; vertical-align:middle; font-size:.88rem; }
tr:hover td { background:#f9fafb; }

.status-badge { display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:20px; font-size:.75rem; font-weight:bold; white-space:nowrap; }
.s-growing   { background:#dbeafe; color:#1d4ed8; }
.s-ready     { background:#dcfce7; color:#15803d; }
.s-harvested { background:#f3f4f6; color:#4b5563; }
.s-failed    { background:#fee2e2; color:#b91c1c; }

.days-badge { font-weight:bold; font-size:.82rem; }
.days-late { color:#ef4444; }
.days-soon { color:#f59e0b; }
.days-ok   { color:#6b7280; }

.btn-sm { padding:5px 10px; border-radius:5px; font-size:.8rem; font-weight:bold; text-decoration:none; cursor:pointer; border:none; display:inline-block; transition:.15s; }
.btn-detail  { background:#eff6ff; color:#2563eb; border:1px solid #bfdbfe; }
.btn-detail:hover { background:#2563eb; color:white; }
</style>

<div class="container">
    <div class="header-bar">
        <div>
            <h2 style="margin:0;">🌿 จัดการการปลูกหญ้าแมว</h2>
            <p style="margin:4px 0 0;color:#888;font-size:.88rem;">ติดตามรอบการเพาะปลูกตั้งแต่เริ่มจนเก็บเกี่ยว</p>
        </div>
        <a href="{{ route('admin.plant-batches.create') }}" class="btn-new">🌱 บันทึกรอบปลูกใหม่</a>
    </div>

    {{-- Stat Cards --}}
    <div class="stat-row">
        <div class="stat-card" style="border-color:#3b82f6;">
            <small>กำลังปลูก</small>
            <strong style="color:#2563eb;">{{ $statusCounts['growing'] ?? 0 }}</strong>
        </div>
        <div class="stat-card" style="border-color:#22c55e;">
            <small>พร้อมเก็บเกี่ยว</small>
            <strong style="color:#15803d;">{{ $statusCounts['ready'] ?? 0 }}</strong>
        </div>
        <div class="stat-card" style="border-color:#6b7280;">
            <small>เก็บเกี่ยวแล้ว</small>
            <strong style="color:#4b5563;">{{ $statusCounts['harvested'] ?? 0 }}</strong>
        </div>
        <div class="stat-card" style="border-color:#ef4444;">
            <small>ล้มเหลว</small>
            <strong style="color:#b91c1c;">{{ $statusCounts['failed'] ?? 0 }}</strong>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" class="filter-bar">
        <select name="status">
            <option value="">ทุกสถานะ</option>
            <option value="growing"   {{ $statusFilter === 'growing'   ? 'selected':'' }}>🌱 กำลังปลูก</option>
            <option value="ready"     {{ $statusFilter === 'ready'     ? 'selected':'' }}>✅ พร้อมเก็บเกี่ยว</option>
            <option value="harvested" {{ $statusFilter === 'harvested' ? 'selected':'' }}>📦 เก็บเกี่ยวแล้ว</option>
            <option value="failed"    {{ $statusFilter === 'failed'    ? 'selected':'' }}>❌ ล้มเหลว</option>
        </select>
        <select name="product_id">
            <option value="">ทุกสินค้า</option>
            @foreach($products as $p)
                <option value="{{ $p->id }}" {{ $productFilter == $p->id ? 'selected':'' }}>{{ $p->product_name }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-filter">🔍 กรอง</button>
        <a href="{{ route('admin.plant-batches.index') }}" style="padding:9px 0;color:#888;font-size:.85rem;text-decoration:none;">ล้าง</a>
    </form>

    {{-- Table --}}
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>สินค้า</th>
                    <th style="text-align:center;">จำนวน (กระถาง)</th>
                    <th>วันที่เพาะ</th>
                    <th>กำหนดเก็บ</th>
                    <th style="text-align:center;">เหลือ/เลย</th>
                    <th>สถานะ</th>
                    <th>บันทึกโดย</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($batches as $b)
                @php
                    $days   = $b->days_until_harvest;
                    $dClass = $b->status === 'growing' ? ($days < 0 ? 'days-late' : ($days <= 3 ? 'days-soon' : 'days-ok')) : '';
                    $dText  = match($b->status) {
                        'growing'   => ($days < 0 ? "เลย {$days} วัน" : ($days === 0 ? 'วันนี้!' : "อีก {$days} วัน")),
                        'ready'     => '✅ พร้อมแล้ว',
                        'harvested' => $b->actual_harvest_date?->format('d/m/Y') ?? '-',
                        'failed'    => '—',
                        default     => '—',
                    };
                @endphp
                <tr>
                    <td style="color:#aaa;">#{{ $b->id }}</td>
                    <td><strong>{{ $b->product?->product_name ?? '-' }}</strong></td>
                    <td style="text-align:center;font-weight:bold;">{{ number_format($b->qty_planted, 1) }}</td>
                    <td>{{ $b->plant_date->format('d/m/Y') }}</td>
                    <td>{{ $b->expected_harvest_date->format('d/m/Y') }}</td>
                    <td style="text-align:center;">
                        <span class="days-badge {{ $dClass }}">{{ $dText }}</span>
                    </td>
                    <td>
                        <span class="status-badge s-{{ $b->status }}">{{ $b->status_label }}</span>
                    </td>
                    <td style="color:#888;font-size:.82rem;">{{ $b->creator?->fullname ?? '-' }}</td>
                    <td>
                        <a href="{{ route('admin.plant-batches.show', $b) }}" class="btn-sm btn-detail">
                            {{ in_array($b->status, ['growing','ready']) ? '📋 จัดการ' : '👁️ ดูรายละเอียด' }}
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" style="text-align:center;padding:50px;color:#aaa;">ไม่พบรอบปลูก</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</x-app-layout>
