<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

/**
 * pstatus values in `order` table (from pstatus lookup):
 *  0  = NEW
 *  1  = Interested
 *  2  = FollowMore
 *  3  = AskingLow
 *  4  = NotInterested
 *  5  = NoResponse
 *  6  = TimeQuote
 *  7  = PaymentMissing
 *  8  = Booked
 *  9  = Listed
 *  10 = Schedule
 *  11 = Pickup
 *  12 = Delivered
 *  13 = Completed
 *  14 = Cancel
 *  15 = Deleted
 *  16 = OwesMoney
 *  17 = CarrierUpdate
 *  18 = OnApproval
 *  19 = On Approval Canceled
 *  99 = Approaching
 *
 * Actual column names (verified from DB schema):
 *  Customer name  → oname
 *  Customer email → oemail
 *  Customer phone → ophone
 *  Price          → payment
 *  Carrier price  → pay_carrier
 *  Deposit        → deposit_amount
 *  Paid amount    → paid_amount
 *  Payment method → payment_method
 *  Payment status → payment_status
 *  created_at     → created_at (varchar 255)
 *  Agent columns  → order_taker_id, dispatcher_id
 */
class EmployeeDashboardController extends Controller
{
    // pstatus → human readable label + badge colour
    private const PSTATUS = [
        0  => ['label' => 'NEW',                  'class' => 'bg-secondary'],
        1  => ['label' => 'Interested',            'class' => 'bg-info text-dark'],
        2  => ['label' => 'Follow More',           'class' => 'bg-info text-dark'],
        3  => ['label' => 'Asking Low',            'class' => 'bg-warning text-dark'],
        4  => ['label' => 'Not Interested',        'class' => 'bg-danger'],
        5  => ['label' => 'No Response',           'class' => 'bg-danger'],
        6  => ['label' => 'Time Quote',            'class' => 'bg-warning text-dark'],
        7  => ['label' => 'Payment Missing',       'class' => 'bg-warning text-dark'],
        8  => ['label' => 'Booked',                'class' => 'bg-success'],
        9  => ['label' => 'Listed',                'class' => 'bg-primary'],
        10 => ['label' => 'Schedule',              'class' => 'bg-primary'],
        11 => ['label' => 'Pickup',                'class' => 'bg-primary'],
        12 => ['label' => 'Delivered',             'class' => 'bg-success'],
        13 => ['label' => 'Completed',             'class' => 'bg-success'],
        14 => ['label' => 'Cancelled',             'class' => 'bg-danger'],
        15 => ['label' => 'Deleted',               'class' => 'bg-dark'],
        16 => ['label' => 'Owes Money',            'class' => 'bg-danger'],
        17 => ['label' => 'Carrier Update',        'class' => 'bg-info text-dark'],
        18 => ['label' => 'On Approval',           'class' => 'bg-warning text-dark'],
        19 => ['label' => 'On Approval Canceled',  'class' => 'bg-danger'],
        99 => ['label' => 'Approaching',           'class' => 'bg-secondary'],
    ];
    // ─────────────────────────────────────────────────────────────────────
    // Helper: apply role-based agent filter to a query builder
    // ─────────────────────────────────────────────────────────────────────
    private function applyAgentFilter($query, int $agentId, int $roleId)
    {
        if ($roleId === 2) {
            // Order Taker
            $query->where('order_taker_id', $agentId);
        } elseif ($roleId === 3) {
            // Dispatcher
            $query->where('dispatcher_id', $agentId);
        } else {
            // Manager or other — match either column
            $query->where(function ($q) use ($agentId) {
                $q->where('order_taker_id', $agentId)
                  ->orWhere('dispatcher_id', $agentId);
            });
        }
        return $query;
    }

    // ─────────────────────────────────────────────────────────────────────
    // DASHBOARD
    // ─────────────────────────────────────────────────────────────────────
    public function index()
    {
        $today    = now()->toDateString();
        $employee = auth('employee')->user();
        $attendance  = EmployeeAttendanceController::getAttendanceForCard($employee);
        $breakStatus = EmployeeBreakController::getBreakStatus();

        $agentId = (int) $employee->agent_id;
        $roleId  = (int) $employee->role_id;

        $todayOrdersCount         = 0;
        $yesterdayOrdersCount     = 0;
        $todayCancelOrdersCount   = 0;
        $yesterdayCancelOrdersCount = 0;

        if ($agentId) {
            $yesterday = now()->subDay()->toDateString();

            // Booked today
            $todayOrdersCount = $this->applyAgentFilter(
                DB::table('order')->where('pstatus', 8)->whereDate('created_at', $today),
                $agentId, $roleId
            )->count();

            // Booked yesterday
            $yesterdayOrdersCount = $this->applyAgentFilter(
                DB::table('order')->where('pstatus', 8)->whereDate('created_at', $yesterday),
                $agentId, $roleId
            )->count();

            // Cancelled today
            $todayCancelOrdersCount = $this->applyAgentFilter(
                DB::table('order')->where('pstatus', 14)->whereDate('created_at', $today),
                $agentId, $roleId
            )->count();

            // Cancelled yesterday
            $yesterdayCancelOrdersCount = $this->applyAgentFilter(
                DB::table('order')->where('pstatus', 14)->whereDate('created_at', $yesterday),
                $agentId, $roleId
            )->count();
        }

        $difference       = $todayOrdersCount - $yesterdayOrdersCount;
        $cancelDifference = $todayCancelOrdersCount - $yesterdayCancelOrdersCount;

        return view('employee.dashboard', [
            'attendanceToday'  => $attendance['attendanceToday'],
            'checkInDisabled'  => $attendance['checkInDisabled'],
            'checkOutDisabled' => $attendance['checkOutDisabled'],
            'breakStatus'      => $breakStatus,
            'todayOrdersCount'      => $todayOrdersCount,
            'difference'            => $difference,
            'todayCancelOrdersCount' => $todayCancelOrdersCount,
            'cancelDifference'      => $cancelDifference,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // EMPLOYEE PROFILE
    // ─────────────────────────────────────────────────────────────────────
    public function employee_profile()
    {
        $employee_id = auth('employee')->id();
        $employee = Employee::with([
            'bankDetail', 'documents', 'working_days',
            'employment_type', 'employee_status', 'assignedLeaves',
            'gratuity', 'role.activityFields', 'shift', 'tax_slab',
        ])->where('id', $employee_id)->first();

        if (!$employee) {
            return redirect()->route('employee.dashboard');
        }

        return view('employee.profile', compact('employee'));
    }

    // ─────────────────────────────────────────────────────────────────────
    // TODAY'S ORDERS — DataTable (booked today)
    // ─────────────────────────────────────────────────────────────────────
    public function today_orders(Request $request)
    {
        if ($request->ajax()) {
            $today    = Carbon::today()->format('Y-m-d');
            $employee = auth('employee')->user();
            $agentId  = (int) $employee->agent_id;
            $roleId   = (int) $employee->role_id;

            $query = DB::table('order')->select(
                'id', 'pstatus', 'created_at',
                'oname', 'oemail', 'ophone',
                'payment', 'deposit_amount', 'paid_amount',
                'payment_method', 'payment_status',
                'origincity', 'originstate',
                'destinationcity', 'destinationstate',
                'ymk'
            )->whereDate('created_at', $today);

            if ($agentId) {
                $this->applyAgentFilter($query, $agentId, $roleId);
            } else {
                $query->whereRaw('1=0');
            }

            return DataTables::of($query)
                ->addColumn('action', function ($row) {
                    return '<div class="d-flex justify-content-center gap-2">
                        <button class="btn btn-outline-info btn-sm order-history-btn" data-id="' . $row->id . '">History</button>
                    </div>';
                })
                ->editColumn('created_at', fn($row) => $row->created_at ? Carbon::parse($row->created_at)->format('d-M-Y H:i') : '-')
                ->addColumn('order_status', function ($row) {
                    $ps    = self::PSTATUS[(int)$row->pstatus] ?? ['label' => 'Unknown', 'class' => 'bg-secondary'];
                    return '<span class="badge ' . $ps['class'] . ' px-2 py-1">' . $ps['label'] . '</span>';
                })
                ->editColumn('payment_status', function ($row) {
                    $status = $row->payment_status ?? 'Unpaid';
                    $class  = $status === 'Paid' ? 'bg-success' : 'bg-warning text-dark';
                    return '<span class="badge ' . $class . ' px-2 py-1">' . $status . '</span>';
                })
                ->addColumn('route', fn($row) => ($row->origincity ?? '') . ', ' . ($row->originstate ?? '') . ' → ' . ($row->destinationcity ?? '') . ', ' . ($row->destinationstate ?? ''))
                ->rawColumns(['order_status', 'payment_status', 'action'])
                ->make(true);
        }

        return view('employee.dashboard');
    }

    // ─────────────────────────────────────────────────────────────────────
    // ORDER LIST — DataTable with date filter
    // ─────────────────────────────────────────────────────────────────────
    public function order_list(Request $request)
    {
        if ($request->ajax()) {
            $employee = auth('employee')->user();
            $agentId  = (int) $employee->agent_id;
            $roleId   = (int) $employee->role_id;

            $query = DB::table('order')->select(
                'id', 'pstatus', 'created_at',
                'oname', 'oemail', 'ophone',
                'payment', 'deposit_amount', 'paid_amount',
                'payment_method', 'payment_status',
                'origincity', 'originstate',
                'destinationcity', 'destinationstate',
                'ymk'
            );

            if ($agentId) {
                $this->applyAgentFilter($query, $agentId, $roleId);
            } else {
                $query->whereRaw('1=0');
            }

            // Date filter — require at least from_date
            if ($request->filled('from_date') && $request->filled('to_date')) {
                $query->whereBetween('created_at', [
                    $request->from_date . ' 00:00:00',
                    $request->to_date . ' 23:59:59',
                ]);
            } elseif ($request->filled('from_date')) {
                $query->where('created_at', '>=', $request->from_date . ' 00:00:00');
            } else {
                return DataTables::of(collect([]))->make(true);
            }

            $helloTransportUrl = config('app.hellotransport_url', env('HELLOTRANSPORT_URL', '#'));

            return DataTables::of($query)
                ->editColumn('id', function ($row) use ($helloTransportUrl) {
                    return '<a href="' . $helloTransportUrl . '" target="_blank" class="text-primary fw-bold">' . $row->id . '</a>';
                })
                ->addColumn('action', function ($row) {
                    return '<div class="d-flex justify-content-center gap-2">
                        <button class="btn btn-outline-info btn-sm order-history-btn" data-id="' . $row->id . '" style="width:120px;">Order History</button>
                    </div>';
                })
                ->editColumn('created_at', fn($row) => $row->created_at ? Carbon::parse($row->created_at)->format('d-M-Y') : '-')
                ->addColumn('order_status', function ($row) {
                    $ps = self::PSTATUS[(int)$row->pstatus] ?? ['label' => 'Unknown', 'class' => 'bg-secondary'];
                    return '<span class="badge ' . $ps['class'] . ' px-2 py-1">' . $ps['label'] . '</span>';
                })
                ->editColumn('payment_status', function ($row) {
                    $status = $row->payment_status ?? 'Unpaid';
                    $class  = $status === 'Paid' ? 'bg-success' : 'bg-warning text-dark';
                    return '<span class="badge ' . $class . ' px-2 py-1">' . $status . '</span>';
                })
                ->addColumn('route', fn($row) => ($row->origincity ?? '') . ', ' . ($row->originstate ?? '') . ' → ' . ($row->destinationcity ?? '') . ', ' . ($row->destinationstate ?? ''))
                ->rawColumns(['order_status', 'payment_status', 'action', 'id'])
                ->make(true);
        }

        return view('employee.order_list');
    }

    // ─────────────────────────────────────────────────────────────────────
    // ORDER HISTORY — AJAX
    // ─────────────────────────────────────────────────────────────────────
    public function order_history($orderId)
    {
        $employee = auth('employee')->user();

        // order_quote_status table tracks status changes per order
        $history = DB::table('order_quote_status')
            ->where('order_id', $orderId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($history);
    }
}
