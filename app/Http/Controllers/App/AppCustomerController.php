<?php

namespace App\Http\Controllers\App;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AppCustomer;
use App\Models\Expense;
use Illuminate\Support\Facades\Hash;

class AppCustomerController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            return datatables()->of(
                AppCustomer::query()
                    ->when($request->agent_id || isRole(ROLE_AGENT), fn($q) => $q->where('agent_id', $request->agent_id ?? auth()->id()))
                    ->with('farms:id,app_customer_id,name')
                    ->orderByDesc('id')
                    ->get()
            )
                ->addColumn('farms', function ($data) {
                    return $data->farms->reduce(function ($c, $i) {
                        return $c . $i->name;
                    }, '');
                })
                ->addColumn('action', function ($data) {
                    $btn = "<div class='btn btn-group'>" . $data->log();
                    $btn .= "<button type='button' class='btn btn-sm btn-primary' title='Edit' id='editAppCustomer' data-id='{$data->id}'><i class='fa fa-edit'></i></button>";
                    $btn .= "<button type='button' class='btn btn-sm btn-danger' title='Delete' id='deleteData' data-id='{$data->id}'><i class='fa fa-trash'></i></button>";
                    $btn .= "</div>";
                    return $btn;
                })
                ->addIndexColumn()
                ->make(true);
        }

        return view('app-customer.index');
    }

    public function create(Request $request)
    {
        return view('app-customer.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'agent_id' => 'required',
            'name' => 'required',
            'mobile' => 'required|unique:app_customers,mobile',
            // 'email' => 'nullable|unique:app_customers,email',
            // 'password' => 'required'
        ]);

        AppCustomer::query()->create([
            'agent_id' => $request->agent_id,
            'name' => $request->name,
            'mobile' => $request->mobile,
            'email' => $request->email,
            'password' => Hash::make('12345678'),
            'created_by' => auth()->id()
        ]);

        return response()->json('Customer Adeed Success');
    }

    public function edit($id)
    {
        return view('app-customer.edit', [
            'customer' => AppCustomer::query()->find($id)
        ]);
    }

    public function update(Request $request, $id)
    {
        $validation = $request->validate([
            'agent_id' => 'required',
            'name' => 'required',
            'mobile' => 'required|unique:app_customers,mobile,' . $id,
            // 'email' => 'nullable|unique:app_customers,email,' . $id,
        ]);

        if ($request->password)
            $validation['password'] = Hash::make($request->password);
        $validation['updated_by'] =  auth()->id();
        AppCustomer::query()
            ->find($id)
            ->update($validation);
        return response()->json('Success');
    }

    public function destroy($id)
    {
        try {
            AppCustomer::query()
                ->find($id)
                ->delete();
            return response()->json("Successfully deleted");
        } catch (Expense $e) {
            return response()->json("Something went wrong");
        }
    }

    // public function select2Ajax(Request $request)
    // {
    //     $customers = AppCustomer::query()
    //         ->select('id', 'name as text', 'mobile')
    //         ->where(function ($query) use ($request) {
    //             $query->where('name', 'like', '%' . $request->q . '%')
    //                 ->orWhere('mobile', 'like', '%' . $request->q . '%');
    //         })
    //         ->when(isRole(ROLE_AGENT), fn ($query) => $query->where('agent_id', auth()->id()))
    //         ->skip(($request->page - 1) * 5)
    //         ->orderByDesc("id")
    //         ->take(5)
    //         ->get()
    //         ->map(function ($item) {
    //             $item->text = $item->text . " ( " . $item->mobile . " )";
    //             return $item;
    //         });


    //     $customers_count = AppCustomer::query()
    //         ->select('id', 'name as text', 'mobile')
    //         ->where(function ($query) use ($request) {
    //             $query->where('name', 'like', '%' . $request->q . '%')
    //                 ->where('mobile', 'like', '%' . $request->q . '%');
    //         })
    //         ->skip(($request->page - 1) * 5)
    //         ->orderByDesc("id")
    //         ->take(5)
    //         ->get()
    //         ->count();

    //     return response()->json([
    //         'items' => $customers,
    //         'pagination' => ['more' => $customers_count > 0]
    //     ]);
    // }


    public function select2Ajax(Request $request)
    {

        $customers = AppCustomer::query()
        ->select('id', 'name', 'mobile')
        ->when(isRole(ROLE_AGENT), fn($query) => $query->where('agent_id', auth()->id()))
        ->orderByDesc("id")
        ->get();

        return view('contact.partial.jsonCustomer', [
            'customers' => $customers,
        ]);
    }
}
