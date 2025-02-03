<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseProduct;
use App\Models\StockTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockTransferController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'type' => 'required|in:transferred,received',
            'agent_id' => 'nullable',
            'date' => 'nullable',
            'search' => 'nullable',
        ]);

        $transfers = StockTransfer::query()
            ->when($request->type == 'received', fn ($q) => $q->where('agent_id', auth()->id()))
            ->when($request->agent_id, fn ($q) => $q->where('agent_id', $request->agent_id))
            ->when($request->date, fn ($q) => $q->where('date', $request->date))
            ->when($request->search, fn ($q) => $q->where('invoice_no', $request->search)->orWhere('total_quantity', $request->search))
            ->with('agent:id,name')
            ->orderByDesc('id')
            ->paginate(20);

        $totalQuantity = StockTransfer::query()
            ->when($request->type == 'received', fn ($q) => $q->where('agent_id', auth()->id()))
            ->when($request->agent_id, fn ($q) => $q->where('agent_id', $request->agent_id))
            ->when($request->date, fn ($q) => $q->where('date', $request->date))
            ->when($request->search, fn ($q) => $q->where('invoice_no', $request->search)->orWhere('total_quantity', $request->search))
            ->sum('total_quantity');

        return view('stock-transfer.index', compact('transfers', 'totalQuantity'));
    }

    public function create(Request $request)
    {
        return view('stock-transfer.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'agent_id' => 'required',
            'date' => 'required',
            'product_id' => 'required',
            'product_id.*' => 'required',
            'quantity' => 'required',
            'quantity.*' => 'required|numeric|min:1',
            'purchase_product_id' => 'required',
            'purchase_product_id.*' => 'required',
        ], [
            'product_id' => 'Product is required.',
            'product_id.*' => 'Product is required.',
            'quantity' => 'Quantity is required.',
            'quantity.*' => 'Transfer Quantity is required.',
            'purchase_product_id' => 'Batch is required.',
            'purchase_product_id.*' => 'Batch is required.',
        ]);

        try {
            DB::beginTransaction();

            $transfer = StockTransfer::query()
                ->create([
                    'invoice_no' => StockTransfer::nextInvoiceNo(),
                    'date' => $request->date,
                    'agent_id' => $request->agent_id,
                    'total_quantity' => array_sum($request->quantity),
                    'created_by' => auth()->user()->id,
                ]);

            foreach ($request->product_id as $key => $product_id) {
                $purchaseProduct = PurchaseProduct::query()
                    ->where('id', $request->purchase_product_id[$key])
                    ->withSum('stockTransferDetails as transfer_qty', 'quantity')
                    ->addSelect([
                        'final_quantity' => function ($q) {
                            $q->selectRaw('COALESCE(quantity) - COALESCE(transfer_qty, 0)');
                        }
                    ])
                    ->havingRaw('final_quantity > ' . $request->quantity[$key])
                    ->firstOrFail();

                $transfer->details()->create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $product_id,
                    'purchase_product_id' => $purchaseProduct->id,
                    'quantity' => $request->quantity[$key]
                ]);
            }

            DB::commit();
            return redirect()->route('stock-transfer.index', ['type' => 'transferred'])->with('message', 'Success');
        } catch (\Exception $e) {
            DB::rollback();
        }

        return back()->with('type', 'error')->with('message', 'Couldn\'t Transfer');
    }

    public function show(Request $request, $id)
    {
        $transfer = StockTransfer::query()
            ->with('details.product', 'details.purchaseProduct')
            ->findOrFail($id);

        return view('stock-transfer.show', compact('transfer'));
    }

    public function destroy(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $transfer = StockTransfer::query()->with('details')->find($id);

            foreach ($transfer->details as $detail) {
                $product = Product::query()
                    ->select('id')
                    ->withStockProperties($transfer->agent_id)
                    ->find($detail->product_id);

                $stockQuantity = getCurrentStockQuantityFromProduct($product, true);

                if ($stockQuantity < $detail->quantity) {
                    DB::rollBack();
                    return back()->with('type', 'error')->with('message', 'Receiver Product Stock Quantity is Insufficient');
                } else {
                    $detail->delete();
                }
            }

            $transfer->delete();
            DB::commit();
            return back()->with('message', 'Success');
        } catch (\Exception $e) {
            DB::rollback();
        }

        return back()->with('type', 'error')->with('message', 'An unknown error occurred');
    }
}
