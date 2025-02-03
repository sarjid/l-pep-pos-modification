<?php

namespace App\Http\Controllers;

use App\Models\IncomeType;
use App\Http\Requests\StoreIncomeTypeRequest;
use App\Http\Requests\UpdateIncomeTypeRequest;
use App\Models\Income;
use App\Models\IncomeDetails;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class IncomeTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $rquest)
    {
        if ($rquest->ajax()) {
            $data = IncomeType::query()->orderBy('id', 'desc')->get();
            return DataTables::of($data)
                ->addColumn('action', function ($row) {
                    $viewLink = route('incomeType.show', $row->id);
                    $editLink = route('incomeType.edit', $row->id);
                    $deleteLink = route('incomeType.destroy', $row->id);
                    return yajraViewEditDeleteButtons($viewLink, $editLink, $deleteLink);
                })
                ->rawColumns(['action'])
                ->addIndexColumn()
                ->make(true);
        }

        return view('income.type.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('income.type.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreIncomeTypeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreIncomeTypeRequest $request)
    {
        try {
            IncomeType::create($request->validated());
            return redirect()->back()->with('message', "New Income Type Added");
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\IncomeType  $incomeType
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, IncomeType $incomeType)
    {
        $filterYear = $request->year ?? Carbon::now()->year;
        $filterMonth = $request->month ?? Carbon::now()->month;

        $incomes = Income::filterByYearAndMonth($filterYear, $filterMonth)
            ->with(['details' => function ($query) {
                $query->select('income_id', 'income_types');
            }])
            ->orderBy('income_date', 'desc')
            ->get();

        return view('income.type.show', [
            'type' => $incomeType,
            'reqYear' => $filterYear,
            'reqMonth' => $filterMonth,
            'incomes' => $incomes
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\IncomeType  $incomeType
     * @return \Illuminate\Http\Response
     */
    public function edit(IncomeType $incomeType)
    {
        return view('income.type.create', [
            'type' => $incomeType
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateIncomeTypeRequest  $request
     * @param  \App\Models\IncomeType  $incomeType
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateIncomeTypeRequest $request, IncomeType $incomeType)
    {
        try {
            $incomeType->update($request->validated());
            return redirect(route('incomeType.index'))->with('message', "New Income Type Added");
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\IncomeType  $incomeType
     * @return \Illuminate\Http\Response
     */
    public function destroy(IncomeType $incomeType)
    {
        try {
            $incomeType->delete();
            return redirect()->back()->with('message', "Income Type Deleted");
        } catch (\Exception $e) {
            throw $e;
        }
    }
    public function print(Request $request, IncomeType $incomeType)
    {
        $filterYear = $request->year ?? Carbon::now()->year;
        $filterMonth = $request->month ?? Carbon::now()->month;

        $incomes = Income::filterByYearAndMonth($filterYear, $filterMonth)
            ->with(['details' => function ($query) {
                $query->select('income_id', 'income_types');
            }])
            ->orderBy('income_date', 'asc')
            ->get();

        return view('income.type.print', [
            'type' => $incomeType,
            'incomes' => $incomes
        ]);
    }
}
