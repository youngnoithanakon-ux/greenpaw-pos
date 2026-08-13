<?php

namespace App\Exports;

use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesReportExport
{
    /**
     * Export รายงานสรุปรายเดือนเป็น CSV (Excel-compatible)
     */
    public static function exportCsv(string $month, string $year): StreamedResponse
    {
        $months = [
            '01' => 'มกราคม', '02' => 'กุมภาพันธ์', '03' => 'มีนาคม',
            '04' => 'เมษายน', '05' => 'พฤษภาคม',    '06' => 'มิถุนายน',
            '07' => 'กรกฎาคม','08' => 'สิงหาคม',     '09' => 'กันยายน',
            '10' => 'ตุลาคม', '11' => 'พฤศจิกายน',   '12' => 'ธันวาคม',
        ];
        $monthName = $months[$month] ?? $month;
        $filename  = "รายงานขาย_{$monthName}_{$year}.csv";

        // ดึงข้อมูลรายวัน
        $dailyRows = Sale::where('status', 'normal')
            ->whereMonth('sale_date', $month)
            ->whereYear('sale_date', $year)
            ->groupBy('s_date')
            ->orderBy('s_date')
            ->get([
                DB::raw('DATE(sale_date) AS s_date'),
                DB::raw('COUNT(id)                                               AS total_bills'),
                DB::raw('SUM(total_amount)                                       AS revenue'),
                DB::raw('SUM(total_consignment_fee)                              AS consignment'),
                DB::raw('SUM(total_cost)                                         AS cost'),
                DB::raw('SUM(total_amount - total_cost - total_consignment_fee)  AS profit'),
            ]);

        // สรุปรวม
        $summary = Sale::where('status', 'normal')
            ->whereMonth('sale_date', $month)
            ->whereYear('sale_date', $year)
            ->selectRaw('
                SUM(total_amount)                                       AS total_revenue,
                SUM(total_consignment_fee)                              AS total_consignment,
                SUM(total_cost)                                         AS total_costs,
                SUM(total_amount - total_cost - total_consignment_fee)  AS total_profit,
                COUNT(id)                                               AS total_bills
            ')
            ->first();

        return response()->stream(function () use ($dailyRows, $summary, $monthName, $year) {
            $out = fopen('php://output', 'w');

            // UTF-8 BOM
            fputs($out, "\xEF\xBB\xBF");

            // Title
            fputcsv($out, ["รายงานยอดขายประจำเดือน{$monthName} {$year}"]);
            fputcsv($out, ['หักบิลที่ยกเลิก (Void) ออกแล้ว']);
            fputcsv($out, []);

            // Summary section
            fputcsv($out, ['=== สรุปภาพรวม ===']);
            fputcsv($out, ['รายรับรวม',      number_format($summary->total_revenue   ?? 0, 2)]);
            fputcsv($out, ['ค่าฝากขาย',      number_format($summary->total_consignment ?? 0, 2)]);
            fputcsv($out, ['รายรับสุทธิ',    number_format(($summary->total_revenue ?? 0) - ($summary->total_consignment ?? 0), 2)]);
            fputcsv($out, ['ต้นทุนรวม',      number_format($summary->total_costs    ?? 0, 2)]);
            fputcsv($out, ['กำไรสุทธิ',      number_format($summary->total_profit   ?? 0, 2)]);
            fputcsv($out, ['จำนวนบิล',       $summary->total_bills ?? 0]);
            fputcsv($out, []);

            // Daily breakdown
            fputcsv($out, ['=== รายละเอียดรายวัน ===']);
            fputcsv($out, ['วันที่', 'จำนวนบิล', 'รายรับ', 'ค่าฝาก', 'ต้นทุน', 'กำไร']);

            foreach ($dailyRows as $row) {
                fputcsv($out, [
                    \Carbon\Carbon::parse($row->s_date)->format('d/m/Y'),
                    $row->total_bills,
                    number_format($row->revenue,     2),
                    number_format($row->consignment, 2),
                    number_format($row->cost,        2),
                    number_format($row->profit,      2),
                ]);
            }

            fclose($out);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
        ]);
    }
}
