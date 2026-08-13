<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $catFilter = $request->input('cat_id');

        $products = Product::with('category')
            ->when($catFilter, fn ($q) => $q->where('category_id', $catFilter))
            ->orderByDesc('updated_at')
            ->get();

        $categories = Category::orderBy('category_name')->get();

        return view('admin.products.index', compact('products', 'categories', 'catFilter'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('category_name')->get();
        return view('admin.products.form', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateProduct($request);

		// ส่ง null ไปเพราะเป็นการสร้างใหม่ (ไม่มีรูปเก่า)
		$data['image_path'] = $this->handleImageUpload($request, null);
		
		// ตรวจสอบว่าถ้าไม่มีการอัปโหลดไฟล์จริงๆ ไม่ควรให้ image_path เป็นค่าที่ผิดพลาด
		// หาก handleImageUpload คืนค่า null และคุณไม่ได้ต้องการให้เก็บ null ในฐานข้อมูล
		// อาจต้องทำเช็คอีกชั้น หรือตั้งค่า default ใน migration
		
		$data['stock_qty'] = 0;

		Product::create($data);

		return redirect()->route('admin.products.index')
			->with('success', 'เพิ่มสินค้าสำเร็จ');
    }

    public function edit(Product $product): View
    {
        $categories = Category::orderBy('category_name')->get();
        return view('admin.products.form', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validateProduct($request, $product->id);

        $newImage = $this->handleImageUpload($request, $product->image_path);
        if ($newImage !== null) {
            $data['image_path'] = $newImage;
        }

        $product->update($data);

        return redirect()->route('admin.products.index')
            ->with('success', 'แก้ไขสินค้าสำเร็จ');
    }

    public function destroy($id)
	{
    $product = Product::findOrFail($id);
    
    // เปลี่ยนสถานะแทนการลบ
    $product->update(['status' => 0]);
    
    return redirect()->route('admin.products.index')
                     ->with('success', 'ปิดการใช้งานสินค้าสำเร็จ');
	}
	public function toggleStatus($id)
	{
		$product = \App\Models\Product::findOrFail($id);
		$product->status = !$product->status; 
		$product->save();

		return redirect()->route('admin.products.index')
						 ->with('success', 'เปลี่ยนสถานะสินค้าสำเร็จ');
	}

    // ---------------------------------------------------------------

    private function validateProduct(Request $request, ?int $ignoreId = null): array
    {
        $request->validate([
            'product_name'    => 'required|string|max:255',
            'product_type'    => 'required|in:produced,bought',
            'sale_price'      => 'required|numeric|min:0',
            'consignment_fee' => 'nullable|numeric|min:0',
            'status'          => 'required|boolean',
            'category_id'     => 'nullable|exists:categories,id',
            'seed_cost'       => 'nullable|numeric|min:0',
            'soil_cost'       => 'nullable|numeric|min:0',
            'pot_cost'        => 'nullable|numeric|min:0',
            'labor_cost'      => 'nullable|numeric|min:0',
            'waste_percent'   => 'nullable|numeric|min:0|max:100',
            'fixed_cost'      => 'nullable|numeric|min:0',
            'product_image'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // คำนวณราคาทุน
        if ($request->product_type === 'produced') {
            $seed  = (float) ($request->seed_cost  ?? 0);
            $soil  = (float) ($request->soil_cost  ?? 0);
            $pot   = (float) ($request->pot_cost   ?? 0);
            $labor = (float) ($request->labor_cost ?? 0);
            $waste = (float) ($request->waste_percent ?? 0);
            $costPrice = ($seed + $soil + $pot + $labor) * (1 + ($waste / 100));
        } else {
            $costPrice = (float) ($request->fixed_cost ?? 0);
        }

        return [
            'product_name'    => $request->product_name,
            'category_id'     => $request->category_id ?: null,
            'product_type'    => $request->product_type,
            'cost_price'      => $costPrice,
            'sale_price'      => $request->sale_price,
            'consignment_fee' => $request->consignment_fee ?? 0,
            'seed_cost'       => $request->seed_cost    ?? 0,
            'soil_cost'       => $request->soil_cost    ?? 0,
            'pot_cost'        => $request->pot_cost     ?? 0,
            'labor_cost'      => $request->labor_cost   ?? 0,
            'waste_percent'   => $request->waste_percent ?? 0,
            'status'          => $request->status,
        ];
    }

	private function handleImageUpload(Request $request, ?string $existing = null): ?string
	{
		// 1. ตรวจสอบว่ามีไฟล์ส่งมาจริง
		if (!$request->hasFile('product_image')) {
			return $existing;
		}

		$file = $request->file('product_image');

		// 2. เช็คว่าไฟล์ถูกต้อง
		if (!$file->isValid()) {
			return $existing;
		}

		// 3. สร้างชื่อไฟล์และโฟลเดอร์ปลายทาง
		$filename = 'prod_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
		$directory = storage_path('app/public/products');

		// สร้างโฟลเดอร์ถ้ายังไม่มี
		if (!file_exists($directory)) {
			mkdir($directory, 0755, true);
		}

		// 4. ย้ายไฟล์โดยใช้ PHP native move (เลี่ยงบั๊กของ Laravel storeAs)
		if ($file->move($directory, $filename)) {
			// ลบไฟล์เก่าถ้ามี
			if ($existing && file_exists($directory . '/' . $existing)) {
				unlink($directory . '/' . $existing);
			}
			return $filename;
		}

		return $existing;
	}
}
