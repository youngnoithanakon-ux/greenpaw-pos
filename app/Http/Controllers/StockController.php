<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(): View
    {
        $products = Product::with('category')
            ->orderByDesc('status')
            ->orderBy('stock_qty')
            ->get();

        return view('admin.stock.index', compact('products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'add_qty'    => 'required|numeric|min:0.1',
        ]);

        $product = Product::findOrFail($request->product_id);

        if (!$product->status) {
            return redirect()->route('admin.stock.index')
                ->with('error', 'ไม่สามารถเติมสต็อกสินค้าที่เลิกจำหน่ายแล้วได้');
        }

        $addQty    = (float) $request->add_qty;
        $qtyBefore = $product->stock_qty;
        $product->increment('stock_qty', $addQty);

        StockLog::create([
            'product_id' => $product->id,
            'user_id'    => Auth::id(),
            'type'       => 'in',
            'qty_before' => $qtyBefore,
            'qty_change' => $addQty,
            'qty_after'  => $qtyBefore + $addQty,
            'note'       => 'เติมสต็อกสินค้า (หน้าจัดการสต็อก)',
        ]);

        return redirect()->route('admin.stock.index')
            ->with('success', "เติมสต็อก \"{$product->product_name}\" สำเร็จ (+{$addQty})");
    }

    public function log(): View
    {
        $logs = StockLog::with(['product', 'user'])
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();

        return view('admin.stock.log', compact('logs'));
    }
}
