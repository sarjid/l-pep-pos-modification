<?php

namespace App\Service;

use App\Models\AgentCustomerTransaction;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Category;
use App\Models\VatGroup;
use App\Models\AgentSale;
use App\Models\AppCustomer;
use Illuminate\Http\Request;
use App\Models\AgentSaleCart;
use App\Models\AgentSaleProduct;
use App\Models\AgentSaleTransaction;
use Illuminate\Support\Facades\DB;
use App\Models\StockTransferDetail;

class AgentSaleService extends SaleService
{
    // Sale CRUD

    public function index(Request $request)
    {
        $sales = AgentSale::query()
            ->where('agent_id', auth()->id())
            ->with('customer:id,name')
            ->when($request->app_customer_id, function ($query) use ($request) {
                $query->where('app_customer_id', $request->app_customer_id);
            })
            ->when($request->sale_date, function ($query) use ($request) {
                $query->where('sale_date', $request->sale_date);
            })
            ->when($request->search, function ($query) use ($request) {
                $query->whereHas('customer', function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->search . '%');
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(25);

        return view('agent-sale.index', [
            'sales' => $sales,
            'customer' => $request->app_customer_id ? AppCustomer::query()->findOrFail($request->app_customer_id) : null
        ]);
    }

    public function create(Request $request)
    {
        $product = $this->getDefaultProducts();


        // return $product;

        return view('agent-sale.pos', [
            'products' => $product,
            'categories' => Category::query()->get(),
            'brands' => Brand::query()->get(),
        ]);
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $sale = AgentSale::query()->create([
                'invoice_no' => AgentSale::nextInvoiceNo(),
                'agent_id' => auth()->id(),
                'app_customer_id' => $request->customer_id,
                'sale_date' => date('Y-m-d'),
                'sub_total' => $request->sub_total,
                'discount_amount' => $request->discount_amount,
                'vat' => $request->vat,
                'total_amount' => $request->total_amount,
                'paying_amount' => $request->paying_amount,
                'deliverycharge' => $request->deliverycharge,
                'created_by' => auth()->id()
            ]);

            $cartItems = AgentSaleCart::query()
                ->where('agent_id', auth()->id())
                ->with('stockTransferDetail')
                ->get();

            $saleProducts = [];
            foreach ($cartItems as $cartItem) {
                $cartItem->stockTransferDetail->sold_quantity += $cartItem->qty;
                $cartItem->stockTransferDetail->save();

                $saleProducts[] = [
                    'agent_sale_id' => $sale->id,
                    'product_id' => $cartItem->product_id,
                    'stock_transfer_detail_id' => $cartItem->stock_transfer_detail_id,
                    'qty' => $cartItem->qty,
                    'price' => $cartItem->price,
                    'total_price' => $cartItem->total_price,
                    'created_at' => now()
                ];
            }
            AgentSaleProduct::query()->insert($saleProducts);

            $transactions = AgentCustomerTransaction::query()
                ->where('agent_id', auth()->id())
                ->where('app_customer_id', $request->customer_id)
                ->where('type', TXN_SEND)
                ->where('available_amount', '>', 0)
                ->get();

            $requiredAmount = $cartItem->total_price;
            foreach ($transactions as $transaction) {
                $amount = min($transaction->amount, $requiredAmount);

                $sale->saleTransactions()->create([
                    'agent_customer_transaction_id' => $transaction->id,
                    'amount' => $amount,
                ]);

                $transaction->used_amount += $amount;
                $transaction->save();
                $requiredAmount -= $amount;

                if ($requiredAmount == 0)
                    break;
            }

            AgentSaleCart::where('agent_id', auth()->id())->delete();
            DB::commit();
            return back()->with([
                'message' => "Product Sale Successful",
                'sale_id' => route('pos.invoice', $sale),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with([
                'type' => 'error',
                'message' => "Something Went Wrong",
            ]);
        }
    }

    public function view($id)
    {
        return view('agent-sale.partial.saleDetails', [
            'sale' => AgentSale::query()->with('customer:id,name,mobile,email')->with('saleProducts')->findOrFail($id)
        ]);
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $sale = AgentSale::query()->with('saleProducts.stockTransferDetail', 'saleTransactions.transaction')->findOrFail($id);
            foreach ($sale->saleProducts as $saleProduct) {
                $saleProduct->stockTransferDetail->sold_quantity -= $saleProduct->qty;
                $saleProduct->stockTransferDetail->save();
            }

            foreach ($sale->saleTransactions as $saleTransaction) {
                $saleTransaction->transaction->used_amount -= $saleTransaction->amount;
                $saleTransaction->transaction->save();
            }

            $sale->delete();

            DB::commit();
            return back()->with(['message' => "Success"]);
        } catch (\Exception $e) {
            DB::rollback();

            return back()->with([
                'type' => 'error',
                'message' => "Invalid Operation",
            ]);
        }
    }

    public function invoice($id)
    {
        $sale = AgentSale::query()->with('saleProducts.product.unit')->findOrFail($id);

        return view('agent-sale.invoice', [
            'sale' => $sale,
        ]);
    }

    public function posInvoice($id)
    {
        $sale = AgentSale::query()
            ->with("saleProducts.product.unit")
            ->findOrFail($id);

        return view('agent-sale.posInvoice', [
            'sale' => $sale,
        ]);
    }


    // Cart CRUD
    public function cartList()
    {
        $cartItems = AgentSaleCart::query()
            ->where('agent_id', auth()->id())
            ->with([
                'product.unit',
                'product.stockTransferDetails' => function ($q) {
                    $q->whereRelation('transfer', 'agent_id', auth()->id())
                        ->where('available_quantity', '>', 0)
                        ->with('purchaseProduct');
                }
            ])
            ->get();

        return response()->json([
            'cart' => view('agent-sale.partial.cartItems', ['cartItems' => $cartItems])->render(),
            'total' => $cartItems->sum('total_price'),
            'vat' => $cartItems->sum('vat'),
            'items' => $cartItems->count(),
        ]);
    }

    public function cartAdd(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
        ]);

        $product = Product::query()
            ->whereHas('stockTransferDetails', function ($q) {
                $q->whereRelation('transfer', 'agent_id', auth()->id())
                    ->where('available_quantity', '>', 0);
            })
            ->with([
                'vatGroup',
                'stockTransferDetails' => function ($q) {
                    $q->whereRelation('transfer', 'agent_id', auth()->id())
                        ->where('available_quantity', '>', 0);
                }
            ])
            ->findOrFail($request->product_id);

        $cartItem = AgentSaleCart::query()
            ->where('agent_id', auth()->id())
            ->where('product_id', $product->id)
            ->with('stockTransferDetail')
            ->first();

        if ($cartItem && $cartItem->stockTransferDetail->available_quantity < $cartItem->qty + 1) {
            return response()->json([
                'error' => "Out Of Stock 1"
            ]);
        } else if (!$cartItem && $this->isAvailableInStock($product, 1) === false) { // Stock Check
            return response()->json([
                'error' => "Out Of Stock 2"
            ]);
        }

        if ($cartItem) { // increment
            $cartItem->update([
                'qty' => $cartItem->qty + 1,
                'total_price' => $cartItem->total_price + $product->discount_selling_price,
                'vat' => $cartItem->vat + (($product->discount_selling_price * optional($product->vatGroup)->vat_percent) / 100)
            ]);
        } else { // add product
            $detail = StockTransferDetail::query()
                ->whereRelation('transfer', 'agent_id', auth()->id())
                ->join('purchase_products', 'purchase_products.id', 'stock_transfer_details.purchase_product_id')
                ->where('stock_transfer_details.product_id', $request->product_id)
                ->where('available_quantity', '>', 0)
                ->select('stock_transfer_details.*')
                ->orderBy('purchase_products.expiry_date')
                ->first();

            if (!$detail) {
                return response()->json([
                    'error' => "This Product is Out Of Stock"
                ]);
            }

            $cartItem = AgentSaleCart::query()
                ->create([
                    'agent_id' => auth()->id(),
                    'product_id' => $product->id,
                    'stock_transfer_detail_id' => $detail->id,
                    'qty' => 1,
                    'price' => $product->discount_selling_price,
                    'total_price' => $product->discount_selling_price,
                    'vat' => ($product->discount_selling_price * optional($product->vatGroup)->vat_percent) / 100,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
        }

        return $this->cartList();
    }

    public function cartIncrement(Request $request)
    {
        $cartItem = $this->getSingleCartItem($request->id);
        $product = Product::query()->findOrFail($cartItem->product_id);

        if (
            !$cartItem
            || ($cartItem->stockTransferDetail->available_quantity < $cartItem->qty + 1)
        ) {
            return response()->json([
                'error' => "Stock Out"
            ]);
        }

        $vat = null;
        if ($product->vat_group_id) {
            $vat = VatGroup::findOrFail($product->vat_group_id)->vat_percent;
        }

        $cartItem->update([
            'qty' => $cartItem->qty + 1,
            'total_price' => $cartItem->total_price + $cartItem->price,
            'vat' => $vat ?  $cartItem->vat + (($product->discount_selling_price * $vat) / 100) : $cartItem->vat
        ]);

        return $this->cartList();
    }

    public function cartQtyUpdate(Request $request)
    {
        $cartItem = $this->getSingleCartItem($request->id);
        $product = Product::query()->findOrFail($cartItem->product_id);

        if (
            !$cartItem
            || ($cartItem->stockTransferDetail->available_quantity < $request->value)
        ) {
            return response()->json([
                'error' => "Stock Out"
            ]);
        }

        if ($request->value <= 0) {
            $cartItem->delete();
            return $this->cartList();
        }

        $cartItem->qty = $request->value;
        $cartItem->total_price = $request->value * $cartItem->price;
        if ($product->vat_group_id) {
            $vat = VatGroup::findOrFail($product->vat_group_id)->vat_percent;
            $cartItem->vat = (($product->discount_selling_price * $vat) / 100) * $request->value;
        }

        if ($cartItem->save()) {
            return $this->cartList();
        }
    }

    public function cartDecrement(Request $request)
    {
        $cartItem = AgentSaleCart::query()->findOrFail($request->id);
        $product = Product::query()->findOrFail($cartItem->product_id);

        $vat = null;
        if ($product->vat_group_id) {
            $vat = VatGroup::findOrFail($product->vat_group_id)->vat_percent;
        }

        if ($cartItem->qty - 1 == 0) {
            $cartItem->delete();
        } else {
            $cartItem->update([
                'qty' => $cartItem->qty - 1,
                'total_price' => $cartItem->total_price - $cartItem->price,
                'vat' => $vat ? (($product->discount_selling_price * $vat) / 100) : $cartItem->vat
            ]);
        }

        return $this->cartList();
    }

    public function cartChangePrice(Request $request)
    {
        $cart = AgentSaleCart::query()->findOrFail($request->cart_id);
        $cart->price = $request->price;
        $cart->total_price = $request->price * $cart->qty;
        if ($cart->save()) {
            return true;
        }
    }

    public function cartChangeBatchId(Request $request)
    {
        $request->validate(['id' => 'required', 'stock_transfer_detail_id' => 'required|numeric']);

        $sellCart = $this->getSingleCartItem($request->id);
        $product = Product::query()
            ->with(['stockTransferDetails' => function ($q) {
                $q->whereRelation('transfer', 'agent_id', auth()->id())
                    ->where('available_quantity', '>', 0);
            }])
            ->findOrFail($sellCart->product_id);

        if (($detail = $product->stockTransferDetails->where('id', $request->stock_transfer_detail_id)->first())) {
            $sellCart->stock_transfer_detail_id = $request->stock_transfer_detail_id;
            $sellCart->qty = min($sellCart->qty, $detail->available_quantity);
        }

        if ($product->vat_group_id) {
            $vat = VatGroup::findOrFail($product->vat_group_id)->vat_percent;
            $sellCart->vat = (($product->discount_selling_price * $vat) / 100) * $sellCart->qty;
        }

        $sellCart->total_price = $sellCart->qty * $sellCart->price;
        $sellCart->save();

        return $this->cartList();
    }

    public function cartRemove(Request $request)
    {
        AgentSaleCart::query()->findOrFail($request->id)->delete();

        return $this->cartList();
    }

    public function cartClear()
    {
        AgentSaleCart::where('agent_id', auth()->id())->delete();

        return $this->cartList();
    }

    protected function getBatchIds($cartItem) {}


    // Misc
    protected function isAvailableInStock($product, $checkQty = 1)
    {
        return $product->stockTransferDetails->count() > 0   && $product->stockTransferDetails[0]->available_quantity >= $checkQty;
    }

    protected function getSingleCartItem($id)
    {
        return AgentSaleCart::query()
            ->with('stockTransferDetail')
            ->findOrFail($id);
    }
}
