<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();
        $role  = Auth::user()->role;

        // ยอดวันนี้ (หักบิล void)
        $summaryToday = Sale::where('status', 'normal')
            ->whereDate('sale_date', $today)
            ->selectRaw('
                COUNT(id)                                               AS total_bills,
                SUM(total_amount)                                       AS total_sales,
                SUM(total_consignment_fee)                              AS total_consignment,
                SUM(total_amount - total_cost - total_consignment_fee)  AS total_profit
            ')
            ->first();

        // สต็อกใกล้หมด 5 รายการ
        $lowStock = Product::where('stock_qty', '<=', 5)
            ->orderBy('stock_qty')
            ->limit(5)
            ->get(['product_name', 'stock_qty']);

        // 5 อันดับขายดี (ตลอดกาล เฉพาะบิลปกติ)
        $topSellers = SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.status', 'normal')
            ->groupBy('sale_items.product_id', 'products.product_name')
            ->orderByRaw('SUM(sale_items.qty) DESC')
            ->limit(5)
            ->get([
                'products.product_name',
                DB::raw('SUM(sale_items.qty) AS total_qty'),
            ]);

        // กราฟ 7 วันย้อนหลัง
        $chartLabels = [];
        $chartData   = [];
        for ($i = 6; $i >= 0; $i--) {
            $date          = now()->subDays($i)->toDateString();
            $chartLabels[] = now()->subDays($i)->locale('th')->isoFormat('ddd');
            $chartData[]   = Sale::where('status', 'normal')
                ->whereDate('sale_date', $date)
                ->sum('total_amount') ?? 0;
        }

        return view('dashboard.index', compact(
            'summaryToday',
            'lowStock',
            'topSellers',
            'chartLabels',
            'chartData',
            'role'
        ));
    }
}
