<x-app-layout>
    <x-slot name="title">ประวัติสต็อก — GreenPaw</x-slot>
    <style>
        .container { padding: 24px; max-width: 1100px; margin: auto; }
        .header-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn-back {
            color: #27ae60; border: 1px solid #bbf7d0; background: #f0fdf4;
            padding: 9px 16px; border-radius: 8px; text-decoration: none;
            font-size: .88rem; font-weight: bold; transition: .15s;
        }
        .btn-back:hover { background: #27ae60; color: white; }

        .table-wrap { background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,.06); overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 700px; }
        th { background: #34495e; color: white; padding: 13px 14px; text-align: left; font-weight: 500; font-size: .85rem; }
        td { padding: 12px 14px; border-bottom: 1px solid #eee; vertical-align: middle; font-size: .9rem; }
        tr:hover td { background: #f9fafb; }

        .type-in  { color: #16a34a; font-weight: bold; }
        .type-out { color: #dc2626; font-weight: bold; }
        .qty-after { background: #f0fdf4; font-weight: bold; color: #15803d; }
        .note-text { color: #a0aec0; font-size: .8rem; display: block; margin-top: 2px; }
    </style>

    <div class="container">
        <div class="header-bar">
            <h2 style="margin:0;">📜 ประวัติความเคลื่อนไหวสต็อก</h2>
            <a href="{{ route('admin.stock.index') }}" class="btn-back">← กลับหน้าเติมสต็อก</a>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>วัน-เวลา</th>
                        <th>สินค้า</th>
                        <th>ประเภท</th>
                        <th style="text-align:center;">ก่อน</th>
                        <th style="text-align:center;">เปลี่ยนแปลง</th>
                        <th style="text-align:center;">คงเหลือ</th>
                        <th>ผู้ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td><small>{{ $log->created_at ? $log->created_at->format('d/m/Y H:i') : '-' }}</small></td>
                        <td>
                            <strong>{{ $log->product?->product_name ?? '-' }}</strong>
                            @if($log->note)<span class="note-text">{{ $log->note }}</span>@endif
                        </td>
                        <td>
                            @if($log->type === 'in')
                                <span class="type-in">➕ รับเข้า</span>
                            @else
                                <span class="type-out">➖ จ่ายออก</span>
                            @endif
                        </td>
                        <td style="text-align:center;">{{ $log->qty_before }}</td>
                        <td style="text-align:center;font-weight:bold;">
                            {{ $log->type === 'in' ? '+' : '-' }}{{ $log->qty_change }}
                        </td>
                        <td style="text-align:center;" class="qty-after">{{ $log->qty_after }}</td>
                        <td>{{ $log->user?->fullname ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="text-align:center;padding:50px;color:#a0aec0;">ยังไม่มีประวัติสต็อก</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
