<?php

namespace App\Exports;

use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesHistoryExport
{
    /**
     * Export ประวัติการขายช่วงวันที่เป็น CSV (Excel-compatible UTF-8 BOM)
     */
    public static function exportCsv(string $startDate, string $endDate): StreamedResponse
    {
        $sales = Sale::with(['user', 'items.product'])
            ->whereBetween(DB::raw('DATE(sale_date)'), [$startDate, $endDate])
            ->orderByDesc('sale_date')
            ->get();

        $filename = "ประวัติการขาย_{$startDate}_ถึง_{$endDate}.csv";

        return response()->stream(function () use ($sales) {
            $out = fopen('php://output', 'w');

            // UTF-8 BOM เพื่อให้ Excel อ่านภาษาไทยได้
            fputs($out, "\xEF\xBB\xBF");

            // Header
            fputcsv($out, [
                'เลขที่บิล', 'วันที่', 'เวลา', 'ผู้ขาย',
                'สินค้า', 'จำนวน', 'ราคา/หน่วย', 'ยอดรวมรายการ',
                'ยอดบิล', 'ค่าฝาก', 'ต้นทุน', 'กำไร',
                'รับเงิน', 'เงินทอน', 'สถานะ',
            ]);

            foreach ($sales as $sale) {
                $billNo  = str_pad($sale->id, 5, '0', STR_PAD_LEFT);
                $date    = \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y');
                $time    = \Carbon\Carbon::parse($sale->sale_date)->format('H:i');
                $seller  = $sale->user?->fullname ?? '-';
                $status  = $sale->status === 'void' ? 'ยกเลิก' : 'ปกติ';
                $profit  = $sale->total_amount - $sale->total_cost - $sale->total_consignment_fee;
                $received = $sale->received_amount ?? $sale->total_amount;
                $change   = max(0, $received - $sale->total_amount);

                if ($sale->items->isEmpty()) {
                    fputcsv($out, [
                        $billNo, $date, $time, $seller,
                        '-', 0, 0, 0,
                        $sale->total_amount, $sale->total_consignment_fee,
                        $sale->total_cost, $profit,
                        $received, $change, $status,
                    ]);
                } else {
                    $first = true;
                    foreach ($sale->items as $item) {
                        $lineTotal = $item->unit_price * $item->qty;
                        fputcsv($out, [
                            $first ? $billNo   : '',
                            $first ? $date     : '',
                            $first ? $time     : '',
                            $first ? $seller   : '',
                            $item->product?->product_name ?? '-',
                            $item->qty,
                            $item->unit_price,
                            $lineTotal,
                            $first ? $sale->total_amount          : '',
                            $first ? $sale->total_consignment_fee : '',
                            $first ? $sale->total_cost            : '',
                            $first ? $profit                      : '',
                            $first ? $received                    : '',
                            $first ? $change                      : '',
                            $first ? $status                      : '',
                        ]);
                        $first = false;
                    }
                }
            }

            fclose($out);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
        ]);
    }
}
