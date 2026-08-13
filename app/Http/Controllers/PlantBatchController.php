<?php

namespace App\Http\Controllers;

use App\Models\PlantBatch;
use App\Models\Product;
use App\Models\StockLog;
use App\Models\SystemConfig;
use App\Services\LineMessagingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PlantBatchController extends Controller
{
    public function index(Request $request): View
    {
        $statusFilter  = $request->input('status', '');
        $productFilter = $request->input('product_id', '');

        $batches = PlantBatch::with(['product', 'creator'])
            ->when($statusFilter, fn ($q) => $q->where('status', $statusFilter))
            ->when($productFilter, fn ($q) => $q->where('product_id', $productFilter))
            ->orderByRaw("FIELD(status,'ready','growing','harvested','failed')")
            ->orderBy('expected_harvest_date')
            ->get();

        // อัพเดต status อัตโนมัติ: growing → ready เมื่อถึงหรือเลยวันเก็บเกี่ยว
        $batches->each(function ($b) {
            if ($b->status === 'growing' && now()->startOfDay()->gte($b->expected_harvest_date)) {
                $b->update(['status' => 'ready']);
                $b->status = 'ready';

                // LINE notify
                $storeName = SystemConfig::get('store_name', 'GreenPaw');
                LineMessagingService::send(
                    "🌿 [{$storeName}] หญ้าแมวพร้อมเก็บเกี่ยวแล้ว!\n" .
                    "สินค้า: {$b->product->product_name}\n" .
                    "รอบปลูก #{$b->id} — ปลูกวันที่ {$b->plant_date->format('d/m/Y')}\n" .
                    "จำนวน: {$b->qty_planted} กระถาง\n" .
                    "📅 ครบกำหนดวันที่: {$b->expected_harvest_date->format('d/m/Y')}"
                );
            }
        });

        $products     = Product::where('product_type', 'produced')->where('status', 1)->orderBy('product_name')->get();
        $statusCounts = PlantBatch::groupBy('status')
            ->selectRaw('status, count(*) as cnt')
            ->pluck('cnt', 'status');

        return view('admin.plant-batches.index', compact('batches', 'products', 'statusFilter', 'productFilter', 'statusCounts'));
    }

    public function create(): View
    {
        // เฉพาะสินค้าประเภทผลิตเองเท่านั้น
        $products = Product::where('product_type', 'produced')
            ->where('status', 1)
            ->orderBy('product_name')
            ->get();

        return view('admin.plant-batches.create', compact('products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'product_id'             => 'required|integer|exists:products,id',
            'plant_date'             => 'required|date',
            'expected_harvest_date'  => 'required|date|after_or_equal:plant_date',
            'qty_planted'            => 'required|numeric|min:0.1',
            'notes'                  => 'nullable|string|max:500',
        ]);

        // ตรวจว่าเป็น produced product
        $product = Product::findOrFail($request->product_id);
        if ($product->product_type !== 'produced') {
            return back()->withErrors(['product_id' => 'สามารถบันทึกการปลูกได้เฉพาะสินค้าที่ผลิตเองเท่านั้น']);
        }

        // ถ้าวันปลูกคือวันนี้หรือก่อนหน้า และวันเก็บก็ถึงแล้ว ให้ status = ready ทันที
        $status = now()->startOfDay()->gte(\Carbon\Carbon::parse($request->expected_harvest_date))
            ? 'ready'
            : 'growing';

        $batch = PlantBatch::create([
            'product_id'            => $request->product_id,
            'created_by'            => Auth::id(),
            'plant_date'            => $request->plant_date,
            'expected_harvest_date' => $request->expected_harvest_date,
            'qty_planted'           => $request->qty_planted,
            'status'                => $status,
            'notes'                 => $request->notes,
        ]);

        // LINE notify เมื่อ ready ทันที
        if ($status === 'ready') {
            $storeName = SystemConfig::get('store_name', 'GreenPaw');
            LineMessagingService::send(
                "🌿 [{$storeName}] หญ้าแมวพร้อมเก็บเกี่ยว!\n" .
                "สินค้า: {$product->product_name}\n" .
                "รอบปลูก #{$batch->id} — ปลูกวันที่ {$batch->plant_date->format('d/m/Y')}\n" .
                "จำนวน: {$batch->qty_planted} กระถาง"
            );
        }

        return redirect()->route('admin.plant-batches.index')
            ->with('success', "บันทึกการปลูก \"{$product->product_name}\" ({$request->qty_planted} กระถาง) สำเร็จ");
    }

    public function show(PlantBatch $plantBatch): View
    {
        $plantBatch->load(['product', 'creator', 'stockLog']);

        // อัพเดต status อัตโนมัติถ้าถึงวันแล้ว
        if ($plantBatch->status === 'growing' && now()->startOfDay()->gte($plantBatch->expected_harvest_date)) {
            $plantBatch->update(['status' => 'ready']);
            $plantBatch->status = 'ready';
        }

        return view('admin.plant-batches.show', compact('plantBatch'));
    }

    /**
     * เก็บเกี่ยว — เพิ่มสต็อกสินค้า + สร้าง StockLog
     */
    public function harvest(Request $request, PlantBatch $plantBatch): RedirectResponse
    {
        if (!in_array($plantBatch->status, ['growing', 'ready'])) {
            return back()->with('error', 'รอบปลูกนี้ถูกดำเนินการไปแล้ว');
        }

        $request->validate([
            'qty_harvested'       => 'required|numeric|min:0.1',
            'actual_harvest_date' => 'required|date',
            'harvest_notes'       => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($request, $plantBatch) {
            $product   = $plantBatch->product;
            $qtyBefore = $product->stock_qty;
            $qty       = (float) $request->qty_harvested;

            // เพิ่มสต็อก
            $product->increment('stock_qty', $qty);

            // บันทึก StockLog
            $log = StockLog::create([
                'product_id' => $product->id,
                'user_id'    => Auth::id(),
                'type'       => 'in',
                'qty_before' => $qtyBefore,
                'qty_change' => $qty,
                'qty_after'  => $qtyBefore + $qty,
                'note'       => "เก็บเกี่ยวจากรอบปลูก #{$plantBatch->id} ({$plantBatch->plant_date->format('d/m/Y')})" .
                                ($request->harvest_notes ? " — {$request->harvest_notes}" : ''),
            ]);

            // อัพเดตรอบปลูก
            $plantBatch->update([
                'status'               => 'harvested',
                'qty_harvested'        => $qty,
                'actual_harvest_date'  => $request->actual_harvest_date,
                'notes'                => $plantBatch->notes
                                         ? $plantBatch->notes . "\n[Harvest] " . ($request->harvest_notes ?? '')
                                         : $request->harvest_notes,
                'harvest_stock_log_id' => $log->id,
            ]);

            // LINE notify harvest สำเร็จ
            $storeName  = SystemConfig::get('store_name', 'GreenPaw');
            $threshold  = (int) SystemConfig::get('stock_alert_threshold', 5);
            $newStock   = $product->fresh()->stock_qty;

            LineMessagingService::send(
                "📦 [{$storeName}] เก็บเกี่ยวหญ้าแมวสำเร็จ!\n" .
                "สินค้า: {$product->product_name}\n" .
                "รอบปลูก #{$plantBatch->id} — เก็บได้ {$qty} กระถาง\n" .
                "📦 สต็อกปัจจุบัน: {$newStock} ชิ้น" .
                ($newStock <= $threshold ? "\n⚠️ สต็อกใกล้หมด! ควรปลูกเพิ่ม" : '')
            );
        });

        return redirect()->route('admin.plant-batches.index')
            ->with('success', "เก็บเกี่ยวสำเร็จ! สต็อก \"{$plantBatch->product->product_name}\" เพิ่มขึ้น {$request->qty_harvested}");
    }

    /**
     * ทำเครื่องหมายว่าล้มเหลว
     */
    public function fail(Request $request, PlantBatch $plantBatch): RedirectResponse
    {
        if (!in_array($plantBatch->status, ['growing', 'ready'])) {
            return back()->with('error', 'ไม่สามารถเปลี่ยนสถานะได้');
        }

        $request->validate(['fail_notes' => 'nullable|string|max:500']);

        $plantBatch->update([
            'status' => 'failed',
            'notes'  => $plantBatch->notes
                        ? $plantBatch->notes . "\n[Failed] " . ($request->fail_notes ?? '')
                        : $request->fail_notes,
        ]);

        return redirect()->route('admin.plant-batches.index')
            ->with('success', 'บันทึกรอบปลูกที่ล้มเหลวเรียบร้อย');
    }
}
