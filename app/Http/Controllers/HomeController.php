<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $lowStockItems = Product::where('status', 1)
            ->where('stock_qty', '<=', 5)
            ->orderBy('stock_qty', 'asc')
            ->get();

        $role = Auth::user()->role;

        return view('home.index', compact('lowStockItems', 'role'));
    }
}
