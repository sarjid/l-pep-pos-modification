<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StockReportExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Product::all()->map(function ($product) {
            $total_stock_in = round($product->purchase_qty + $product->sale_return_qty + $product->transferred_in_qty, 2);
            $total_stock_out = round($product->sale_qty + $product->purchase_return_qty + $product->transferred_out_qty, 2);
            $stock_qty = $total_stock_in - $total_stock_out;
            $sale_price = $stock_qty * $product->selling_price;
            $purchase_price = $stock_qty * $product->purchase_price;

            return [
                'Product Name' => $product->product_name,
                'Purchase Price' => $product->purchase_price,
                'Selling Price' => $product->selling_price,
                'Total In' => $total_stock_in,
                'Total Out' => $total_stock_out,
                'Stock Qty' => $stock_qty,
                'Sale Value' => $sale_price,
                'Purchase Value' => $purchase_price,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Product Name',
            'Purchase Price',
            'Selling Price',
            'Total In',
            'Total Out',
            'Stock Qty',
            'Sale Value',
            'Purchase Value',
        ];
    }
}
