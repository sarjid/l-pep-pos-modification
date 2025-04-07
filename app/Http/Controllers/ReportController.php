<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Contact;
use App\Models\Expense;
use App\Models\Employee;
use App\Models\Purchase;
use App\Models\SaleReturn;
use Illuminate\Http\Request;
use App\DataTables\SaleReportDataTable;
use App\DataTables\SoldReportDataTable;
use App\DataTables\ProductReportDataTable;
use App\DataTables\CategoryReportDataTable;
use App\DataTables\CustomerReportDataTable;
use App\DataTables\PurchaseReportDataTable;
use App\DataTables\SupplierReportDataTable;
use App\DataTables\DailySaleReportDataTable;
use App\DataTables\MonthlySaleReportDataTable;
use App\Models\AgentSale;
use App\Models\User;

class ReportController extends Controller
{
    public function saleReport(SaleReportDataTable $dataTable)
    {
        return $dataTable->render('report.saleReport');
    }

    public function dailySaleReport(DailySaleReportDataTable $dataTable)
    {
        return $dataTable->render('report.dailySaleReport');
    }

    public function monthlySaleReport(MonthlySaleReportDataTable $dataTable)
    {
        return $dataTable->render('report.monthlySaleReport');
    }

    public function stockReport(Request $request)
    {
        return view('report.stockReport');
    }

    public function productReport(ProductReportDataTable $dataTable)
    {
        return $dataTable->render('report.productReport');
    }

    public function categoryReport(CategoryReportDataTable $dataTable)
    {
        return $dataTable->render('report.categoryReport');
    }

    public function purchaseReport(PurchaseReportDataTable $dataTable)
    {
        return $dataTable->render('report.purchaseReport');
    }

    public function customerReport(CustomerReportDataTable $dataTable)
    {
        return $dataTable->render('report.customerReport');
    }

    public function supplierReport(SupplierReportDataTable $dataTable)
    {
        return $dataTable->render('report.supplierReport');
    }

    public function profitLoss(Request $request)
    {
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

        return view('report.profitLossReport', [
            'purchase' => $purchase,
            'sale' => $sales,
            'return' => $return,
            'receive_payment' => $receive_payment,
            'send_payment' => $send_payment,
            'expense' => $expense,
            'salary' => $salary,
        ]);
    }

    public function soldProduct(SoldReportDataTable $dataTable)
    {
        return $dataTable->render('report.soldProductReport');
    }


    public function agentSaleReport()
    {

        $startDate = request('start_date');
        $endDate = request('end_date');
        $user = request('user');
        $query = AgentSale::query()
                ->when($user, function ($query) use ($user) {
                    return $query->where('agent_id', $user);
                })
                ->filterByDate($startDate, $endDate)
                ->with([
                    'agent:id,name,employee_name',
                    'customer:id,agent_id,name,mobile',
                    'saleProducts:id,product_id,agent_sale_id,stock_transfer_detail_id,qty,price,total_price',
                    'saleProducts.product:id,product_name',
                    'saleProducts.stockTransferDetail:id,purchase_product_id',
                    'saleProducts.stockTransferDetail.purchaseProduct:id,purchase_price'
                ]);

        $results = $query->orderBy('id','DESC')->paginate(20);





        $results->map(function ($sale) {
            $purchaseAmount = 0;
            $sale->saleProducts->map(function ($saleProduct) use ( &$purchaseAmount) {
                if ($saleProduct->stockTransferDetail && $saleProduct->stockTransferDetail->purchaseProduct) {
                    $purchasePrice = $saleProduct->stockTransferDetail->purchaseProduct->purchase_price;
                    $purchaseAmount += $purchasePrice * $saleProduct->qty;
                }
            });
            // unset($sale->saleProducts);
            $sale->purchase_amount = $purchaseAmount;
            $sale->profit_amount = $sale->total_amount - $purchaseAmount;
            return $sale;
        });



        return view('report.agentSales',[
            'agents' => User::active()->agent()->get(['id','name','employee_name']),
            'sales' => $results,
            'total_profit' =>  $results->sum('profit_amount')
        ]);
    }
}
