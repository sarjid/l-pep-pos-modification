<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class BarcodeController extends Controller
{
    public function barcode()
    {
        return view('product.barcode');
    }

    public function printBarcode(Request $request)
    {
        $data = [];
        $printData = [];
        foreach ($request->product_id as $key => $product_id) {
            $product = Product::query()
                ->with([
                    'category',
                ])
                ->findOrFail($product_id);

            $filter = [
                'product_name' => $product->product_name,
                'category_name' => $product->category->category_name,
                'price' => $product->discount_selling_price,
                'barcode' => $product->barcode,
                'qty' => $request->qty[$key],
            ];
            array_push($data, $filter);
        }

        if ($request->category_name) {
            array_push($printData, 'category');
        }

        if ($request->action) {
            array_push($printData, $request->action);
        }

        if ($request->product_price) {
            array_push($printData, 'price');
        }

        if ($request->product_name) {
            array_push($printData, 'product_name');
        }

        return view("product.partial.barcodePrint", [
            'data' => json_encode($data),
            'printData' => $request->action,
        ]);
    }

    public function printBarcodeBack()
    {
        return redirect()->route('product.barcode');
    }

    public function test()
    {
        return view('product.test');
    }

    public function autocompletePurchase(Request $request)
    {
        $request->validate(['q' => 'required']);

        return Product::query()
            ->where(function ($q) use ($request) {
                $q->where('barcode', "like", "%{$request->q}%")
                    ->orWhere('product_name', "like", "%{$request->q}%");
            })
            ->when(isRole(ROLE_AGENT), fn ($q) => $q->where('is_medicine', false))
            ->where('status', 1)
            ->get()
            ->map(function ($item) {
                $ob = new \stdClass;
                $ob->id = $item->id;
                $ob->is_medicine = $item->is_medicine;
                $ob->product_name = $item->product_name;
                $ob->barcode = $item->barcode;
                $ob->purchase_price = 0;
                return $ob;
            });
    }

    public function autocompleteSale(Request $request)
    {
        $request->validate(['q' => 'required']);

        return Product::query()
            ->where(function ($q) use ($request) {
                $q->where('barcode', "like", "%{$request->q}%")
                    ->orWhere('product_name', "like", "%{$request->q}%");
            })
            ->where('status', 1)
            ->select('id', 'product_name', 'barcode', 'is_medicine')
            ->withStockProperties()
            ->where(function ($q) use ($request) {
                $q->where('barcode', "like", "%{$request->q}%")
                    ->orWhere('product_name', "like", "%{$request->q}%");
            })
            ->get()
            ->map(function ($item) {
                $ob = new \stdClass;
                $ob->product_id = $item->id;
                $ob->is_medicine = $item->is_medicine;
                $ob->product_name = $item->product_name;
                $ob->barcode = $item->barcode;
                $ob->quantity = getCurrentStockQuantityFromProduct($item);
                return $ob;
            });
    }

    public function autocompleteBarcode(Request $request)
    {
        $request->validate(['q' => 'required']);

        return Product::query()
            ->where(function ($q) use ($request) {
                $q->where('barcode', "like", "%{$request->q}%")
                    ->orWhere('product_name', "like", "%{$request->q}%");
            })
            ->where('status', 1)
            ->select('id', 'product_name', 'barcode', 'is_medicine')
            ->get()
            ->map(function ($item) {
                $ob = new \stdClass;
                $ob->product_id = $item->id;
                $ob->is_medicine = $item->is_medicine;
                $ob->product_name = $item->product_name;
                $ob->barcode = $item->barcode;
                $ob->quantity = 1;
                return $ob;
            });
    }

    public function autocompleteTransfer(Request $request)
    {
        $request->validate(['q' => 'required']);

        return Product::query()
            ->where(function ($q) use ($request) {
                $q->where('barcode', "like", "%{$request->q}%")
                    ->orWhere('product_name', "like", "%{$request->q}%");
            })
            ->select('id', 'product_name', 'barcode')
            ->with([
                'purchaseProduct' => function ($q) {
                    $q->withStockProperties()
                        ->havingRaw('available_quantity > 0');
                }
            ])
            ->withStockProperties()
            ->get()
            ->filter(fn ($item) => $item->purchaseProduct->count())
            ->map(function ($item) {
                $ob = new \stdClass;
                $ob->id = $item->id;
                $ob->product_name = $item->product_name;
                $ob->barcode = $item->barcode;
                $ob->batches = collect($item->purchaseProduct)
                    ->sortByDesc('expiry_date')
                    ->reduce(function ($c, $it) {
                        $c[] = [
                            'id' => $it->id,
                            'batch_id' => $it->batch_id,
                            'available_quantity' => $it->available_quantity,
                        ];
                        return $c;
                    }, []);
                return $ob;
            });
    }

    public function autocompleteAgentTransfer(Request $request)
    {
        $request->validate(['q' => 'required']);

        return Product::query()
            ->where(function ($q) use ($request) {
                $q->where('barcode', "like", "%{$request->q}%")
                    ->orWhere('product_name', "like", "%{$request->q}%");
            })
            ->select('id', 'product_name', 'barcode')
            ->with([
                'agentPurchaseProducts' => function ($q) {
                    $q->withStockProperties()
                        ->havingRaw('available_quantity > 0');
                }
            ])
            ->whereHas('agentPurchaseProducts', function ($q) {
                $q->withStockProperties()
                    ->havingRaw('available_quantity > 0');
            })
            ->get()
            ->filter(fn ($item) => $item->agentPurchaseProducts->count())
            ->map(function ($item) {
                $ob = new \stdClass;
                $ob->id = $item->id;
                $ob->product_name = $item->product_name;
                $ob->barcode = $item->barcode;
                $ob->batches = collect($item->agentPurchaseProducts)
                    ->reduce(function ($c, $it) {
                        $c[] = [
                            'id' => $it->id,
                            'batch_id' => $it->batch_id,
                            'available_quantity' => $it->available_quantity,
                        ];
                        return $c;
                    }, []);
                return $ob;
            });
    }
}
