<?php

namespace App\Http\Controllers\EmployeeSettings;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class AdminHolidayController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Holiday::select('id', 'name', 'holiday_date', 'is_recurring', 'status');

            return DataTables::of($data)
                ->addColumn('action', function ($row) {
                    if($row->is_recurring == 0){
                        return '<div class="d-flex justify-content-center gap-2">
                                <button type="button" class="bg-success-focus text-success-600 bg-hover-success-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle edit_btn" data-id="'.$row->id.'">
                                    <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
                                </button>
                            </div>';
                    }
                    return '-';
                })
                ->editColumn('holiday_date', fn($row) => $row->holiday_date ?? '-')
                ->editColumn('is_recurring', fn($row) => $row->is_recurring ? 'Yes' : 'No')
                ->editColumn('status', function ($row) {
                    return $row->status == 1
                        ? '<button class="bg-success-focus text-success-600 border border-success-main px-24 py-4 radius-4 fw-medium text-sm  active_btn" data-id="'.$row->id.'">Active</button>'
                        : '<button class="bg-neutral-200 text-neutral-600 border border-neutral-400 px-24 py-4 radius-4 fw-medium text-sm inactive_btn" data-id="'.$row->id.'">Inactive</button>';
                })
                ->rawColumns(['status','action'])
                ->make(true);
        }

        return view('admin.employee_settings.holidays');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'holiday_date' => 'required_if:is_recurring,0|nullable|date',
            'is_recurring' => 'required|boolean',
            'month' => 'required_if:is_recurring,1|nullable|integer|min:1|max:12',
            'day'   => 'required_if:is_recurring,1|nullable|integer|min:1|max:31',
            'status' => 'required|in:0,1',
        ]);

        $holiday = new Holiday();
        $holiday->name = $request->name;
        $holiday->is_recurring = $request->is_recurring;
        $holiday->status = $request->status;
        if ($request->is_recurring == 1) {
            // recurring → only month/day
            $holiday->holiday_date = null;
            $holiday->month = $request->month;
            $holiday->day = $request->day;
        } else {
            // one-time holiday
            $holiday->holiday_date = $request->holiday_date;
            $holiday->month = null;
            $holiday->day = null;
        }

        $holiday->save();

        session()->flash('success', 'Holiday added successfully.');
        return redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Holiday $holiday)
    {
        return response()->json($holiday);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function update(Request $request, Holiday $holiday)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'holiday_date' => 'required_if:is_recurring,0|nullable|date',
            'is_recurring' => 'required|boolean',
            'month' => 'required_if:is_recurring,1|nullable|integer|min:1|max:12',
            'day'   => 'required_if:is_recurring,1|nullable|integer|min:1|max:31',
            'status' => 'required|in:0,1',
        ]);

        $holiday->name = $request->name;
        $holiday->is_recurring = $request->is_recurring;
        $holiday->status = $request->status;

        if ($request->is_recurring == 1) {
            // recurring → only month/day
            $holiday->holiday_date = null;
            $holiday->month = $request->month;
            $holiday->day = $request->day;
        } else {
            // one-time holiday
            $holiday->holiday_date = $request->holiday_date;
            $holiday->month = null;
            $holiday->day = null;
        }

        $holiday->save();

        session()->flash('success', 'Holiday updated successfully.');
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Holiday $holiday)
    {
//        $holiday->delete();
//        return response()->json(['success' => 'Holiday deleted successfully.']);
    }
}
