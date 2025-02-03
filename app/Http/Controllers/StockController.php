<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::query()
            ->when($request->product_name, function ($q) use ($request) {
                $q->where('products.product_name', 'like', '%' . $request->product_name . '%');
            })
            ->addSelect(['purchase_price' => function ($q) {
                $q->select('purchase_price')
                    ->from('purchase_products')
                    ->whereColumn('purchase_products.product_id', 'products.id')
                    ->orderByDesc('purchase_products.id')
                    ->limit(1);
            }])
            ->withStockProperties()
            ->paginate(25);

        return view('stock.index', [
            'products' => $products,
            'search' => $request->product_name ?? null,
        ]);
    }
}
