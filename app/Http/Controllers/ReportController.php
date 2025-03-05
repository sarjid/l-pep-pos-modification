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

class ReportController extends Controller
{
    public function saleReport(SaleReportDataTable $dataTable)
    {

        // dd('hello')
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
}
