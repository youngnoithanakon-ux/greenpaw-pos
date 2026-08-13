<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PosController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::where('status', 1)
            ->orderByDesc('stock_qty')
            ->orderBy('product_name')
            ->get(['id', 'product_name', 'sale_price', 'stock_qty', 'image_path']);

        $cart       = session('cart', []);
        $grandTotal = collect($cart)->sum(fn ($item) => $item['price'] * $item['qty']);

        return view('pos.index', compact('products', 'cart', 'grandTotal'));
    }

    public function addToCart(Request $request): RedirectResponse
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'qty'        => 'required|numeric|min:0.1',
        ]);

        $product = Product::findOrFail($request->product_id);
        $pId     = $product->id;
        $qtyAdd  = (float) $request->qty;
        $cart    = session('cart', []);

        $currentInCart = isset($cart[$pId]) ? $cart[$pId]['qty'] : 0;
        $newTotal      = $currentInCart + $qtyAdd;

        if ($newTotal > $product->stock_qty) {
            $remaining = $product->stock_qty - $currentInCart;
            return redirect()->route('pos.index')
                ->with('error', "สต็อกไม่พอ! เพิ่มได้อีกแค่ {$remaining}");
        }

        if (isset($cart[$pId])) {
            $cart[$pId]['qty'] = $newTotal;
        } else {
            $cart[$pId] = [
                'name'        => $product->product_name,
                'price'       => (float) $product->sale_price,
                'cost'        => (float) $product->cost_price,
                'consignment' => (float) $product->consignment_fee,
                'qty'         => $qtyAdd,
            ];
        }

        session(['cart' => $cart]);

        return redirect()->route('pos.index');
    }

    public function removeFromCart(int $id): RedirectResponse
    {
        $cart = session('cart', []);
        unset($cart[$id]);
        session(['cart' => $cart]);

        return redirect()->route('pos.index');
    }

    public function receipt(Sale $sale): View
    {
        // ป้องกันดู receipt ของคนอื่น (staff ดูได้เฉพาะของตัวเอง)
        if (Auth::user()->role !== 'admin' && $sale->user_id !== Auth::id()) {
            abort(403);
        }

        $sale->load(['items.product', 'user']);
        $change = max(0, ($sale->received_amount ?? $sale->total_amount) - $sale->total_amount);

        return view('pos.receipt', compact('sale', 'change'));
    }

    public function checkout(Request $request): RedirectResponse
    {
        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()->route('pos.index')->with('error', 'ตะกร้าสินค้าว่างเปล่า');
        }

        $receivedAmount = (float) $request->input('received_amount', 0);

        $saleId = null;

        DB::transaction(function () use ($cart, $receivedAmount, &$saleId) {
            $userId           = Auth::id();
            $totalAmount      = 0;
            $totalCost        = 0;
            $totalConsignment = 0;

            foreach ($cart as $item) {
                $totalAmount      += $item['price']       * $item['qty'];
                $totalCost        += $item['cost']        * $item['qty'];
                $totalConsignment += $item['consignment'] * $item['qty'];
            }

            $sale = Sale::create([
                'user_id'               => $userId,
                'total_amount'          => $totalAmount,
                'total_cost'            => $totalCost,
                'total_consignment_fee' => $totalConsignment,
                'received_amount'       => $receivedAmount > 0 ? $receivedAmount : $totalAmount,
                'status'                => 'normal',
            ]);

            $saleId = $sale->id;

            foreach ($cart as $pId => $item) {
                $product = Product::findOrFail($pId);

                SaleItem::create([
                    'sale_id'         => $sale->id,
                    'product_id'      => $pId,
                    'qty'             => $item['qty'],
                    'unit_price'      => $item['price'],
                    'unit_cost'       => $item['cost'],
                    'consignment_fee' => $item['consignment'],
                ]);

                $qtyBefore = $product->stock_qty;
                $product->decrement('stock_qty', $item['qty']);

                StockLog::create([
                    'product_id' => $pId,
                    'user_id'    => $userId,
                    'type'       => 'out',
                    'qty_before' => $qtyBefore,
                    'qty_change' => $item['qty'],
                    'qty_after'  => $qtyBefore - $item['qty'],
                    'note'       => 'ขายสินค้า บิลเลขที่ #' . str_pad($sale->id, 5, '0', STR_PAD_LEFT),
                ]);
            }
        });

        session()->forget('cart');

        return redirect()->route('pos.receipt', $saleId);
    }
}
