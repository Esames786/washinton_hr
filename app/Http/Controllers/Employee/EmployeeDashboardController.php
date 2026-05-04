<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class EmployeeDashboardController extends Controller
{
   public function index()
   {
       $today = now()->toDateString();
       $employee = auth('employee')->user();
       $attendance = EmployeeAttendanceController::getAttendanceForCard($employee);
       $breakStatus = EmployeeBreakController::getBreakStatus();

       // Orders table se aaj ke orders ka count
       $todayOrdersCount = DB::table('orders')
           ->where('user_id',$employee->agent_id)
           ->where('Listing_Status','Book Order')
           ->whereDate('created_at', $today)
           ->count();

       $yesterdayOrdersCount = DB::table('orders')
           ->where('user_id',$employee->agent_id)
           ->where('Listing_Status','Book Order')
           ->whereDate('created_at', now()->subDay()->toDateString())
           ->count();
       $difference = $todayOrdersCount - $yesterdayOrdersCount;

       $todayCancelOrdersCount = DB::table('orders')
           ->where('user_id', $employee->agent_id)
           ->where('Listing_Status', 'Cancelled')
           ->whereDate('created_at', $today)
           ->count();


       $yesterdayCancelOrdersCount = DB::table('orders')
           ->where('user_id', $employee->agent_id)
           ->where('Listing_Status', 'Cancelled')
           ->whereDate('created_at', now()->subDay()->toDateString())
           ->count();

       $cancelDifference = $todayCancelOrdersCount - $yesterdayCancelOrdersCount;


       return view('employee.dashboard')->with(
           [
               'attendanceToday' => $attendance['attendanceToday'],
               'checkInDisabled' => $attendance['checkInDisabled'],
               'checkOutDisabled' => $attendance['checkOutDisabled'],
               'breakStatus'=> $breakStatus,
               'todayOrdersCount' => $todayOrdersCount,
               'difference' => $difference,
               'todayCancelOrdersCount' => $todayCancelOrdersCount,
               'cancelDifference' => $cancelDifference,
           ]
       );
   }

   public function employee_profile()
   {
       $employee_id = auth('employee')->id();
       $employee = Employee::with([
           'bankDetail',
           'documents',
           'working_days',
           'employment_type',
           'employee_status',
           'assignedLeaves',
           'gratuity',
           'role.activityFields',
           'shift',
           'tax_slab'

       ])->where('id',$employee_id)->first();
       if(!$employee){
            return redirect()->route('employee.dashboard');
       }
       return view('employee.profile',compact('employee'));
   }

    public function today_orders(Request $request)
    {
        if ($request->ajax()) {
            $today = Carbon::today()->format('Y-m-d');
            $employee= auth('employee')->user();
            // Orders ko DB query se fetch karte hain
            $orders = DB::table('orders')
                ->select(
                    'id',
                    'Listing_Status',
                    'created_at',
                    'Customer_Name',
                    'Customer_Email',
                    'Customer_Phone',
                    'Address',
                    'Book_Price',
                    'Deposit_Amount',
                    'Paid_Amount',
                    'Paid_Method',
                    'Received_Date',
                    'payment_status'
                )
                ->where('user_id',$employee->agent_id)
                ->whereDate('created_at', $today);
            return DataTables::of($orders)
                ->addColumn('action', function ($row) {
                    return  '
                                 <div class="d-flex justify-content-center gap-2">
                                     <button class="btn btn-outline-info ms-2 order-history-btn" data-id="' . $row->id . '">Order History</button>
                                 </div>
                            ';
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at ? Carbon::parse($row->created_at)->format('d-M-Y H:i') : '-';
                })
                ->editColumn('Received_Date', function ($row) {
                    return $row->Received_Date ? Carbon::parse($row->Received_Date)->format('d-M-Y') : '-';
                })
                ->editColumn('payment_status', function ($row) {
                    $status = $row->payment_status ?? 'Unknown';
                    $class = 'badge rounded-pill bg-info text-white px-3 py-3';
                    return '<span class="'.$class.'">'.$status.'</span>';
                })
                ->rawColumns(['payment_status','action'])
                ->make(true);
        }

        return view('employee.dashboard');
    }

    public function order_list(Request $request)
    {
        if ($request->ajax()) {
            $today = Carbon::today()->format('Y-m-d');
            $employee= auth('employee')->user();
            // Orders ko DB query se fetch karte hain
            $orders = DB::table('orders')
                ->select(
                    'id',
                    'Listing_Status',
                    'created_at',
                    'Customer_Name',
                    'Customer_Email',
                    'Customer_Phone',
                    'Address',
                    'Book_Price',
                    'Deposit_Amount',
                    'Paid_Amount',
                    'Paid_Method',
                    'Received_Date',
                    'payment_status'
                )
                ->where('user_id',$employee->agent_id);

                if ($request->from_date && $request->to_date) {
                    $orders->whereBetween('created_at', [
                        $request->from_date . ' 00:00:00',
                        $request->to_date . ' 23:59:59'
                    ]);
                } elseif ($request->from_date) {
                    $orders->whereBetween('created_at', [
                        $request->from_date . ' 00:00:00',
                        now()->format('Y-m-d') . ' 23:59:59'
                    ]);
                } else {
                    // Agar filter nahi diya gaya → empty result bhejna
                    return DataTables::of(collect([]))->make(true);
                }

            return DataTables::of($orders)
                ->editColumn('id', function ($row) {
                    $helloTransportUrl = env('HELLOTRANSPORT_URL');
//                        return '<a href="' . $helloTransportUrl . '/orders/' . $row->id . '"
                    return '<a href="'.$helloTransportUrl.'"
                           target="_blank"
                           class="text-primary fw-bold">
                           ' . $row->id . '
                        </a>';
                })
                ->addColumn('action', function ($row) {
                    return  '
                                 <div class="d-flex justify-content-center gap-2">
                                     <button class="btn btn-outline-info ms-2 order-history-btn" data-id="' . $row->id . '" style="width: 130px;">Order History</button>
                                 </div>
                            ';
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at ? Carbon::parse($row->created_at)->format('d-M-Y') : '-';
                })
                ->editColumn('Received_Date', function ($row) {
                    return $row->Received_Date ? Carbon::parse($row->Received_Date)->format('d-M-Y') : '-';
                })
                ->editColumn('payment_status', function ($row) {
                    $status = $row->payment_status ?? 'Unknown';
                    $class = 'badge rounded-pill bg-info text-white px-3 py-3';
                    return '<span class="'.$class.'">'.$status.'</span>';
                })
                ->rawColumns(['payment_status','action','id'])
                ->make(true);
        }

        return view('employee.order_list');
    }

    public function order_history($orderId)
    {
        $employee = auth('employee')->user();

        $history = DB::table('order_quote_status')
            ->where('user_id', $employee->agent_id)
            ->where('order_id', $orderId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($history);
    }

}
