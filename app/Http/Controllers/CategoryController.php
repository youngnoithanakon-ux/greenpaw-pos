<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::orderByDesc('id')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'category_name' => 'required|string|max:100|unique:categories,category_name',
        ]);

        Category::create(['category_name' => $request->category_name]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'เพิ่มหมวดหมู่สำเร็จ');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();
        return redirect()->route('admin.categories.index')
            ->with('success', 'ลบหมวดหมู่สำเร็จ');
    }
}
