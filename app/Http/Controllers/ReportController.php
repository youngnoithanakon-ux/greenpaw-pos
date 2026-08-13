<?php

namespace App\Http\Controllers;

use App\Exports\SalesReportExport;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $selectedMonth = $request->input('month', now()->format('m'));
        $selectedYear  = $request->input('year',  now()->format('Y'));

        // สรุปยอดรวมของเดือน
        $summary = Sale::where('status', 'normal')
            ->whereMonth('sale_date', $selectedMonth)
            ->whereYear('sale_date', $selectedYear)
            ->selectRaw('
                SUM(total_amount)                                       AS total_revenue,
                SUM(total_cost)                                         AS total_costs,
                SUM(total_consignment_fee)                              AS total_consignment,
                SUM(total_amount - total_cost - total_consignment_fee)  AS total_profit
            ')
            ->first();

        // ข้อมูลกราฟรายวัน
        $chartRows = Sale::where('status', 'normal')
            ->whereMonth('sale_date', $selectedMonth)
            ->whereYear('sale_date', $selectedYear)
            ->groupBy('s_date')
            ->orderBy('s_date')
            ->get([
                DB::raw('DATE(sale_date) AS s_date'),
                DB::raw('SUM(total_amount) AS daily_revenue'),
            ]);

        $chartLabels = $chartRows->map(fn ($r) => date('d/m', strtotime($r->s_date)))->toArray();
        $chartValues = $chartRows->map(fn ($r) => (float) $r->daily_revenue)->toArray();

        // ปีที่มีข้อมูล
        $availableYears = Sale::select('sale_date')
            ->get()
            ->map(fn($s) => (int) \Carbon\Carbon::parse($s->sale_date)->format('Y'))
            ->unique()
            ->sortDesc()
            ->values()
            ->toArray();

        if (empty($availableYears)) {
            $availableYears = [(int) now()->format('Y')];
        }

        $months = [
            '01' => 'มกราคม',   '02' => 'กุมภาพันธ์', '03' => 'มีนาคม',
            '04' => 'เมษายน',   '05' => 'พฤษภาคม',    '06' => 'มิถุนายน',
            '07' => 'กรกฎาคม',  '08' => 'สิงหาคม',     '09' => 'กันยายน',
            '10' => 'ตุลาคม',   '11' => 'พฤศจิกายน',   '12' => 'ธันวาคม',
        ];

        return view('admin.reports.index', compact(
            'summary',
            'chartLabels',
            'chartValues',
            'selectedMonth',
            'selectedYear',
            'availableYears',
            'months'
        ));
    }

    /**
     * Export monthly sales report as CSV (Excel-compatible).
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $month = $request->input('month', now()->format('m'));
        $year  = $request->input('year',  now()->format('Y'));

        return SalesReportExport::exportCsv($month, $year);
    }

    /**
     * Export monthly sales report as PDF.
     */
    public function exportPdf(Request $request)
    {
        $selectedMonth = $request->input('month', now()->format('m'));
        $selectedYear  = $request->input('year',  now()->format('Y'));

        $months = [
            '01' => 'มกราคม',   '02' => 'กุมภาพันธ์', '03' => 'มีนาคม',
            '04' => 'เมษายน',   '05' => 'พฤษภาคม',    '06' => 'มิถุนายน',
            '07' => 'กรกฎาคม',  '08' => 'สิงหาคม',     '09' => 'กันยายน',
            '10' => 'ตุลาคม',   '11' => 'พฤศจิกายน',   '12' => 'ธันวาคม',
        ];

        $monthName = $months[$selectedMonth] ?? $selectedMonth;

        $summary = Sale::where('status', 'normal')
            ->whereMonth('sale_date', $selectedMonth)
            ->whereYear('sale_date', $selectedYear)
            ->selectRaw('
                SUM(total_amount)                                       AS total_revenue,
                SUM(total_cost)                                         AS total_costs,
                SUM(total_consignment_fee)                              AS total_consignment,
                SUM(total_amount - total_cost - total_consignment_fee)  AS total_profit
            ')
            ->first();

        $sales = Sale::with('user')
            ->where('status', 'normal')
            ->whereMonth('sale_date', $selectedMonth)
            ->whereYear('sale_date', $selectedYear)
            ->orderBy('sale_date')
            ->get();

        $pdf = Pdf::loadView('exports.sales-report-pdf', compact(
            'summary', 'sales', 'monthName', 'selectedYear'
        ))->setPaper('a4', 'landscape');

        $filename = "รายงานขาย_{$monthName}_{$selectedYear}.pdf";

        return $pdf->download($filename);
    }
}
