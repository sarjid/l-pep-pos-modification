<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\IncomeType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IncomeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('income.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('income.create', [
            'income_types' => IncomeType::all(),
            'users' => User::active()->agent()->get(['id', 'name', 'employee_name'])
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreIncomeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        // dd($request->all());
        DB::beginTransaction();
        try {
            $income = Income::create(['income_date' => $request->income_date]);
            foreach ($request->income_types as $employeeId => $incomeData) {
                $total = $incomeData['total'] ?? 0;
                $note = $incomeData['note'];
                unset($incomeData['total'], $incomeData['note']);

                $income->details()->create([
                    'user_id' => $employeeId,
                    'income_types' => $incomeData,
                    'total' => $total,
                    'note' => $note,
                ]);
            }
            DB::commit();
            return redirect(route('income.index'))->with('message', "Income Added");
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Income  $income
     * @return \Illuminate\Http\Response
     */
    public function show(Income $income)
    {
        return view('income.view', [
            'data' => $income->load(['details', 'details.user:id,name,employee_name'])
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Income  $income
     * @return \Illuminate\Http\Response
     */
    public function edit(Income $income)
    {
        return view('income.edit', [
            'income' => $income->load(['details', 'details.user:id,name,employee_name'])
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateIncomeRequest  $request
     * @param  \App\Models\Income  $income
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Income $income)
    {
        DB::beginTransaction();
        try {
            $income->update(['income_date' => $request->income_date]);
            foreach ($request->income_types as $employeeId => $incomeData) {
                $total = $incomeData['total'] ?? 0;
                $note = $incomeData['note'];
                unset($incomeData['total'], $incomeData['note']);

                $incomeDetail = $income->details()->where('user_id', $employeeId)->first();
                if ($incomeDetail) {
                    $incomeDetail->update([
                        'income_types' => $incomeData,
                        'total' => $total,
                        'note' => $note,
                    ]);
                }
            }
            DB::commit();
            return redirect(route('income.index'))->with('message', "Income Updated");
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Income  $income
     * @return \Illuminate\Http\Response
     */
    public function destroy(Income $income)
    {
        try {
            $income->delete();
            return response()->json([
                'message' => "Income Deleted"
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }


    public function allIncomeJson(Request $request)
    {
        try {

            $year = $request->year;
            $month = $request->month;

            $totalIncome = Income::filterByYearAndMonth($year, $month)
                ->withSum('details', 'total')
                ->whereHas('details')
                ->get()
                ->sum('details_sum_total');

            // Fetch expense data with relationships
            $incomes = Income::with('details')
                ->filterByYearAndMonth($year, $month)
                ->orderBy('id', 'desc')
                ->withSum('details', 'total')
                ->get();


            $datatable = datatables()->of($incomes)

                ->addColumn('action', function ($data) {
                    $btn = "<div class='btn-group'>";
                    if (permission('uni3')) {
                        $btn .= '<a href="' . route("income.show", $data->id) . '" class="btn btn-secondary btn-sm">View</a>';
                    }

                    if (permission('uni3')) {
                        $btn .= '<a href="' . route("income.edit", $data->id) . '" class="btn btn-primary btn-sm">Edit</a>';
                    }
                    if (permission('uni4')) {
                        $btn .= ' <a href="javascript:void(0)" data-toggle="tooltip" data-id="' . $data->id . '" id="deleteData" class="btn btn-danger btn-sm">Delete</a>';
                    }
                    return $btn;
                })
                ->rawColumns(['action'])
                ->addIndexColumn()
                ->toArray();
            $datatable['total_income'] = $totalIncome;
            return response()->json($datatable);
        } catch (\Exception $e) {
            Log::error('Error in allExpenseJson method: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function history(Request $request)
{
    $year = $request->year;
    $month = $request->month;

    $incomes = Income::filterByYearAndMonth($year, $month)
        ->with(['details.user'])
        ->get();


    $incomeTypes = $incomes->flatMap->details->flatMap->income_types->keys()->unique();

    $employeeEarnings = [];

    foreach ($incomes as $income) {
        foreach ($income->details as $detail) {
            $employee = $detail->user->employee_name . ' - ' . $detail->user->name;

            if (!isset($employeeEarnings[$employee])) {
                $employeeEarnings[$employee] = array_fill_keys($incomeTypes->toArray(), 0);
            }

            foreach ($detail->income_types as $type => $amount) {
                $employeeEarnings[$employee][$type] += $amount;
            }
        }
    }

    return view('income.history', compact('year', 'month', 'incomeTypes', 'employeeEarnings'));
}





}
