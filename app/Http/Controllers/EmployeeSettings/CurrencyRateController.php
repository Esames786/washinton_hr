<?php

namespace App\Http\Controllers\EmployeeSettings;

use App\Http\Controllers\Controller;
use App\Models\CurrencyRate;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class CurrencyRateController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = CurrencyRate::select(['id', 'from_currency', 'to_currency', 'rate', 'status']);

            return DataTables::of($data)
                ->addColumn('action', function ($row) {
                    $action = '<div class="d-flex justify-content-center gap-2">
                                 <button type="button" class="bg-success-focus text-success-600 bg-hover-success-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle edit_btn">
                                    <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
                                </button>
                                 ';
                     $action.= '</div>';
                    return $action;
                })
                ->editColumn('status', function ($row) {
                    return $row->status == 1
                        ? '<button class="bg-success-focus text-success-600 border border-success-main px-24 py-4 radius-4 fw-medium text-sm active_btn" data-id="'.$row->id.'">Active</button>'
                        : '<button class="bg-neutral-200 text-neutral-600 border border-neutral-400 px-24 py-4 radius-4 fw-medium text-sm inactive_btn" data-id="'.$row->id.'">Inactive</button>';

                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('admin.employee_settings.currency_rates');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'from_currency' => 'required|string|max:10',
                'to_currency'   => 'required|string|max:10',
                'rate' => 'required|numeric|min:0',
            ]);

            $currency_check = CurrencyRate::where('from_currency',$request->from_currency)->where('to_currency',$request->to_currency);
            if($currency_check->exists()){
                return redirect()->back()->with('error', 'Currency exchange rate already exists');
            }

            $currencyRate = new CurrencyRate();
            $currencyRate->from_currency = $request->from_currency;
            $currencyRate->to_currency = $request->to_currency;
            $currencyRate->rate = $request->rate;
            $currencyRate->created_by = auth('admin')->id();
            $currencyRate->save();

            return redirect()->back()->with('success', 'Currency rate added successfully!');
        } catch (\Throwable $th) {
            dd($th->getMessage());
        }
    }

    public function edit($id)
    {
        return response()->json(CurrencyRate::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'rate' => 'required|numeric|min:0',
        ]);

        $currency = CurrencyRate::findOrFail($id);
        $currency->rate = $request->rate;
        $currency->updated_by = auth('admin')->id();
        $currency->save();

//        $currency->update($request->only(['rate','status']));

        return redirect()->back()->with('success', 'Currency updated successfully!');
    }
}
