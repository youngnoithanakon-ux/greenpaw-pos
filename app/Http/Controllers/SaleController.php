<?php

namespace App\Http\Controllers;

use App\Exports\SalesHistoryExport;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SaleController extends Controller
{
    public function index(Request $request): View
    {
        $startDate = $request->input('start_date', now()->toDateString());
        $endDate   = $request->input('end_date',   now()->toDateString());

        $sales = Sale::with('user')
            ->whereBetween(DB::raw('DATE(sale_date)'), [$startDate, $endDate])
            ->orderByDesc('sale_date')
            ->get();

        // สรุปเฉพาะบิล normal
        $summary = [
            'sales'       => 0,
            'cost'        => 0,
            'consignment' => 0,
            'profit'      => 0,
        ];
        foreach ($sales as $s) {
            if ($s->status === 'normal') {
                $summary['sales']       += $s->total_amount;
                $summary['cost']        += $s->total_cost;
                $summary['consignment'] += $s->total_consignment_fee;
                $summary['profit']      += ($s->total_amount - $s->total_cost - $s->total_consignment_fee);
            }
        }

        return view('admin.sales.index', compact('sales', 'summary', 'startDate', 'endDate'));
    }

    public function items(Sale $sale): JsonResponse
    {
        $items = SaleItem::with('product')
            ->where('sale_id', $sale->id)
            ->get();

        return response()->json($items);
    }

    public function void(Request $request, Sale $sale): RedirectResponse
    {
        if ($sale->status === 'void') {
            return redirect()->route('admin.sales.index')
                ->with('error', 'บิลนี้ถูกยกเลิกไปแล้ว');
        }

        DB::transaction(function () use ($sale) {
            $userId = Auth::id();

            foreach ($sale->items as $item) {
                $product   = $item->product;
                $qtyBefore = $product->stock_qty;
                $product->increment('stock_qty', $item->qty);

                StockLog::create([
                    'product_id' => $product->id,
                    'user_id'    => $userId,
                    'type'       => 'in',
                    'qty_before' => $qtyBefore,
                    'qty_change' => $item->qty,
                    'qty_after'  => $qtyBefore + $item->qty,
                    'note'       => 'ยกเลิกบิลเลขที่ #' . str_pad($sale->id, 5, '0', STR_PAD_LEFT),
                ]);
            }

            $sale->update(['status' => 'void']);
        });

        return redirect()->route('admin.sales.index')
            ->with('success', 'ยกเลิกบิล #' . str_pad($sale->id, 5, '0', STR_PAD_LEFT) . ' สำเร็จ สต็อกถูกคืนแล้ว');
    }

    /**
     * Export sales history as CSV (Excel-compatible).
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $startDate = $request->input('start_date', now()->toDateString());
        $endDate   = $request->input('end_date',   now()->toDateString());

        return SalesHistoryExport::exportCsv($startDate, $endDate);
    }
}
