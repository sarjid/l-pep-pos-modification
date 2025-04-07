<?php

namespace App\Http\Controllers;

use App\Models\AgentSale;
use App\Models\Sale;
use App\Models\Contact;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\SaleReturn;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\ContactPayment;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {



        $month_sales = Sale::select(DB::raw("SUM(total_amount) as amount"))
            ->whereYear('sale_date', date("Y"))
            ->groupBy(DB::raw('MONTH(sale_date)'))
            ->pluck('amount');

        $months = Sale::select(DB::raw("MONTH(sale_date) as month"))
            ->whereYear('sale_date', date("Y"))
            ->groupBy(DB::raw('MONTH(sale_date)'))
            ->pluck('month');

        $datas = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
        foreach ($months as $index => $month) {
            $datas[$month - 1] = round($month_sales[$index]);
        }

        $best_sale_products = DB::table('sale_products')
            ->join('products', 'products.id', 'sale_products.product_id')
            ->select('sale_products.product_id', 'products.product_name', DB::raw('COUNT(product_id) as total_sale'))
            ->groupBy('product_id')
            ->whereMonth('sale_products.created_at', date('m'))
            ->orderBy('total_sale', 'desc')
            ->take(10)
            ->get();

        $popular_product = [];
        foreach ($best_sale_products as $product) {
            array_push($popular_product, Str::limit($product->product_name, 20));
        }

        $total_products = Product::query()->count();

        $customer_receiveable = Contact::with('sale', 'contactPayment')->where('type', 'customer')->get();
        $supplier_payable = Contact::with('sale', 'contactPayment')->where('type', 'supplier')->get();

        $top_ten_customer = DB::table('sales')
            ->join('contacts', 'contacts.id', 'sales.contact_id')
            ->select('contact_id', 'contacts.name', DB::raw('SUM(total_amount) as total_amount'))
            ->groupBy('contact_id')
            ->whereMonth('sales.created_at', date('m'))
            ->orderBy('total_amount', 'desc')
            ->take(10)
            ->get();

        $popular_customer = [];
        foreach ($top_ten_customer as $customer) {
            $popular_customer[] = [
                'name' => $customer->name,
                'amount' => $customer->total_amount
            ];
        }

        $products = Product::query()
            ->select('*')
            ->withSum('purchaseProduct as purchase_qty', 'quantity')
            ->withSum('purchaseReturnProduct as purchase_return_qty', 'quantity')
            ->withSum('saleProduct as sale_qty', 'qty')
            ->withSum('saleReturnProduct as sale_return_qty', 'qty')
            ->get();

        $todaySale = isRole(ROLE_AGENT) ? AgentSale::where('agent_id', auth()->user()->id)->where('sale_date', date("Y-m-d"))->sum('total_amount') : Sale::where('sale_date', date("Y-m-d"))->sum('total_amount');

        $purchase = Purchase::query()
        ->when($request->start && $request->end, function ($query) use ($request) {
            $query->whereBetween("purchase_date", [$request->start, $request->end]);
        })
        ->sum('total');

    $sales = Sale::query()
        ->when($request->start && $request->end, function ($query) use ($request) {
            $query->whereBetween("sale_date", [$request->start, $request->end]);
        })
        ->sum('total_amount');

    $return = SaleReturn::query()
        ->when($request->start && $request->end, function ($query) use ($request) {
            $query->whereBetween("return_date", [$request->start, $request->end]);
        })
        ->sum('total_amount');


    $receive_payment = Contact::join('contact_payments', "contact_payments.contact_id", "=", "contacts.id")
        ->when($request->start && $request->end, function ($query) use ($request) {
            $query->whereBetween("contact_payments.paying_date", [$request->start, $request->end]);
        })
        ->where('contacts.type', "=", 'customer')
        ->sum('paying_amount');

    $send_payment = Contact::join('contact_payments', "contact_payments.contact_id", "=", "contacts.id")
        ->when($request->start && $request->end, function ($query) use ($request) {
            $query->whereBetween("contact_payments.paying_date", [$request->start, $request->end]);
        })
        ->where('contacts.type', "=", 'supplier')
        ->sum('paying_amount');

    $expense = Expense::query()
        ->when($request->start && $request->end, function ($query) use ($request) {
            $query->whereBetween("expanse_date", [$request->start, $request->end]);
        })
        ->sum('amount');

    $salary =  Employee::join('salaries', "salaries.employee_id", "=", "employees.id")
        ->when($request->start && $request->end, function ($query) use ($request) {
            $query->whereBetween("salaries.salary_date", [$request->start, $request->end]);
        })
        ->sum('amount');



        return view('home', [
            'datas' => $datas,
            'best_sale_product' => $best_sale_products->pluck('total_sale'),
            'best_sale_product_name' => $popular_product,
            'total_customer' => Contact::where('type', 'customer')->count(),
            'total_supplier' => Contact::where('type', 'supplier')->count(),
            'total_product' => $total_products,
            'today_sale' => $todaySale,
            'customer_receiveable' => $customer_receiveable,
            'supplier_payable' => $supplier_payable,
            'popular_customers' => $popular_customer,
            'products' => $products,
            'purchase' => $purchase,
            'sale' => $sales,
            'return' => $return,
            'receive_payment' => $receive_payment,
            'send_payment' => $send_payment,
            'expense' => $expense,
            'salary' => $salary,
        ]);
    }


    public function todaySummary()
    {
        $total_purchase = Purchase::where('purchase_date', date('Y-m-d'))->sum('total_pay');
        $total_sale = Sale::where('sale_date', date('Y-m-d'))->sum('paying_amount');
        $total_expense = Expense::where('expanse_date', date('Y-m-d'))->sum('amount');

        $supplier_payment = ContactPayment::join('contacts', 'contacts.id', 'contact_payments.contact_id')
            ->where('contact_payments.paying_date', date('Y-m-d'))
            ->where('contacts.type', "supplier")
            ->whereNull('contact_payments.purchase_id')
            ->sum('contact_payments.paying_amount');

        $customer_receive = ContactPayment::join('contacts', 'contacts.id', 'contact_payments.contact_id')
            ->where('contact_payments.paying_date', date('Y-m-d'))
            ->where('contacts.type', "customer")
            ->whereNull('contact_payments.sale_id')
            ->whereNull('contact_payments.sale_return_id')
            ->sum('contact_payments.paying_amount');
        $return_sale_total = SaleReturn::where('return_date', date('Y-m-d'))->sum('paying_amount');

        return view('todaySummery', [
            'total_purchase' => $total_purchase,
            'total_sale' => $total_sale,
            'total_expense' => $total_expense,
            'supplier_payment' => $supplier_payment,
            'customer_receive' => $customer_receive,
            'return_sale_total' => $return_sale_total,
        ]);
    }
}
