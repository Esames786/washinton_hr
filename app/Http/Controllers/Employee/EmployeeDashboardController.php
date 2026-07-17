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

        // ── Shift + today's productive (active) time ──────────────────────────
        $shift = $employee->shift; // ShiftType relation
        $shiftData = null;
        $productiveSeconds = 0;
        $productivePercent = null;
        $productiveBand    = null;

        if ($shift && $shift->shift_start && $shift->shift_end) {
            $shiftData = [
                'name'  => $shift->name ?? 'Shift',
                'start' => substr($shift->shift_start, 0, 5), // HH:MM
                'end'   => substr($shift->shift_end, 0, 5),
            ];

            // Shift length (overnight-safe)
            $startTs = strtotime($today . ' ' . $shift->shift_start);
            $endTs   = strtotime($today . ' ' . $shift->shift_end);
            if ($endTs <= $startTs) $endTs += 86400;
            $shiftLen = max(1, $endTs - $startTs);

            if ($agentId && \Illuminate\Support\Facades\Schema::hasTable('agent_active_times')) {
                $productiveSeconds = (int) (DB::table('agent_active_times')
                    ->where('user_id', $agentId)
                    ->whereDate('work_date', $today)
                    ->value('active_seconds') ?? 0);
            }

            $cappedProductive  = min($productiveSeconds, $shiftLen);
            $productivePercent = round($cappedProductive / $shiftLen * 100, 1);

            if (\Illuminate\Support\Facades\Schema::hasTable('productivity_rules')) {
                $band = \App\Models\ProductivityRule::resolveFor($productivePercent);
                if ($band) $productiveBand = $band->label;
            }
        }

        return view('employee.dashboard', [
            'attendanceToday'  => $attendance['attendanceToday'],
            'checkInDisabled'  => $attendance['checkInDisabled'],
            'checkOutDisabled' => $attendance['checkOutDisabled'],
            'breakStatus'      => $breakStatus,
            'todayOrdersCount'      => $todayOrdersCount,
            'difference'            => $difference,
            'todayCancelOrdersCount' => $todayCancelOrdersCount,
            'cancelDifference'      => $cancelDifference,
            'shiftData'             => $shiftData,
            'productiveSeconds'     => $productiveSeconds,
            'productivePercent'     => $productivePercent,
            'productiveBand'        => $productiveBand,
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
            'gratuity', 'role.activityFields', 'shift', 'tax_slab', 'workEquipment',
        ])->where('id', $employee_id)->first();

        if (!$employee) {
            return redirect()->route('employee.dashboard');
        }

        // P3 (#3/#7): conditional documents only show for the matching house ownership.
        // Unconditional docs always show; own/rent docs only when the employee has chosen that.
        $ownership = $employee->house_ownership;
        $documentSettings = \App\Models\DocumentSetting::where('status', 1)
            ->where(function ($q) use ($ownership) {
                $q->whereNull('condition');
                if ($ownership) {
                    $q->orWhere('condition', $ownership);
                }
            })
            ->get();

        return view('employee.profile', compact('employee', 'documentSettings'));
    }

    // ─────────────────────────────────────────────────────────────────────
    // UPLOAD DOCUMENT
    // ─────────────────────────────────────────────────────────────────────
    public function uploadDocument(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'document_setting_id' => 'required|integer|exists:hr_document_settings,id',
        ]);

        $employee   = auth('employee')->user();
        $docSetting = \App\Models\DocumentSetting::findOrFail($request->document_setting_id);

        // P3: per-document limits (max_files) + allowed kind (image | video | any).
        $maxFiles = max(1, (int) ($docSetting->max_files ?? 1));
        $kind     = $docSetting->file_kind ?? 'any';
        $mimeRule = $kind === 'image' ? 'mimes:jpg,jpeg,png,webp'
                  : ($kind === 'video' ? 'mimes:mp4,mov,webm,avi,mkv,3gp' : '');
        $maxKb    = $kind === 'video' ? 61440 : 10240; // 60MB video, 10MB otherwise

        $request->validate([
            'file' => trim('required|file|max:' . $maxKb . ($mimeRule ? '|' . $mimeRule : '')),
        ]);

        // Enforce the per-document file cap (multi-file types like Selfie accumulate up to max_files).
        $existingCount = \App\Models\EmployeeDocument::where('employee_id', $employee->id)
            ->where('document_setting_id', $docSetting->id)->count();
        if ($maxFiles > 1 && $existingCount >= $maxFiles) {
            return back()->with('error', 'You can upload at most ' . $maxFiles . ' file(s) for "' . $docSetting->title . '". Remove one to add another.');
        }

        $path = 'Uploads/employees/' . $employee->id . '/';
        if (!file_exists(public_path($path))) {
            mkdir(public_path($path), 0777, true);
        }

        $file     = $request->file('file');
        $filename = 'doc_' . $employee->id . '_' . $docSetting->id . '_' . time() . '_' . mt_rand(100, 999) . '.' . $file->getClientOriginalExtension();
        $file->move(public_path($path), $filename);

        $payload = [
            'file_name' => $docSetting->title,
            'mime_type' => $file->getClientMimeType(),
            'file_path' => $path . $filename,
            'status'    => 0, // pending verification
        ];

        if ($maxFiles > 1) {
            // Multi-file: each upload is a new row (up to max_files).
            \App\Models\EmployeeDocument::create(array_merge($payload, [
                'employee_id'         => $employee->id,
                'document_setting_id' => $docSetting->id,
            ]));
        } else {
            // Single-file: replace the existing one (unchanged behaviour).
            \App\Models\EmployeeDocument::updateOrCreate(
                ['employee_id' => $employee->id, 'document_setting_id' => $docSetting->id],
                $payload
            );
        }

        return back()->with('success', 'Document "' . $docSetting->title . '" uploaded successfully. Awaiting verification.');
    }

    // ─────────────────────────────────────────────────────────────────────
    // DELETE DOCUMENT
    // ─────────────────────────────────────────────────────────────────────
    public function acceptContract(\Illuminate\Http\Request $request)
    {
        $employee = auth('employee')->user();
        $employee->contract_accepted_at = now();
        $employee->save();

        return response()->json(['success' => true, 'message' => 'Contract accepted']);
    }

    // ─────────────────────────────────────────────────────────────────────
    // P3 (#3/#7): house ownership — drives which conditional documents apply.
    // ─────────────────────────────────────────────────────────────────────
    public function setHouseOwnership(\Illuminate\Http\Request $request)
    {
        $request->validate(['house_ownership' => 'required|in:own,rent']);
        $employee = auth('employee')->user();
        $employee->house_ownership = $request->house_ownership;
        $employee->save();

        return back()->with('success', 'Saved. The required documents have been updated for your selection.');
    }

    // ─────────────────────────────────────────────────────────────────────
    // P3 (#9): self-reported working equipment.
    // ─────────────────────────────────────────────────────────────────────
    public function addEquipment(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:150',
            'details' => 'nullable|string|max:255',
        ]);
        \App\Models\EmployeeWorkEquipment::create([
            'employee_id' => auth('employee')->id(),
            'name'        => $request->name,
            'details'     => $request->details,
        ]);

        return back()->with('success', 'Equipment added.');
    }

    public function deleteEquipment($id)
    {
        \App\Models\EmployeeWorkEquipment::where('employee_id', auth('employee')->id())
            ->where('id', $id)->firstOrFail()->delete();

        return back()->with('success', 'Equipment removed.');
    }

    public function deleteDocument($id)
    {
        $employee = auth('employee')->user();

        $doc = \App\Models\EmployeeDocument::where('employee_id', $employee->id)
            ->where('id', $id)
            ->firstOrFail();

        // Delete file from disk
        if ($doc->file_path && file_exists(public_path($doc->file_path))) {
            unlink(public_path($doc->file_path));
        }

        $doc->delete();

        return back()->with('success', 'Document removed.');
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
                // Column names match the DataTable in employee/dashboard.blade.php
                ->addColumn('Listing_Status', function ($row) {
                    $ps    = self::PSTATUS[(int)$row->pstatus] ?? ['label' => 'Unknown', 'class' => 'bg-secondary'];
                    return '<span class="badge ' . $ps['class'] . ' px-2 py-1">' . $ps['label'] . '</span>';
                })
                ->editColumn('created_at', fn($row) => $row->created_at ? Carbon::parse($row->created_at)->format('d-M-Y H:i') : '-')
                ->addColumn('Customer_Name',  fn($row) => $row->oname  ?? '-')
                ->addColumn('Customer_Email', fn($row) => $row->oemail ?? '-')
                ->addColumn('Customer_Phone', fn($row) => $row->ophone ?? '-')
                ->addColumn('Address', fn($row) => ($row->origincity ?? '') . ', ' . ($row->originstate ?? '') . ' → ' . ($row->destinationcity ?? '') . ', ' . ($row->destinationstate ?? ''))
                ->addColumn('Book_Price',     fn($row) => $row->payment        ?? '-')
                ->addColumn('Deposit_Amount', fn($row) => $row->deposit_amount ?? '-')
                ->addColumn('Paid_Amount',    fn($row) => $row->paid_amount    ?? '-')
                ->addColumn('Paid_Method',    fn($row) => $row->payment_method ?? '-')
                ->addColumn('Received_Date',  fn($row) => '-')
                ->editColumn('payment_status', function ($row) {
                    $status = $row->payment_status ?? 'Unpaid';
                    $class  = $status === 'Paid' ? 'bg-success' : 'bg-warning text-dark';
                    return '<span class="badge ' . $class . ' px-2 py-1">' . $status . '</span>';
                })
                ->addColumn('action', function ($row) {
                    return '<div class="d-flex justify-content-center gap-2">
                        <button class="btn btn-outline-info btn-sm order-history-btn" data-id="' . $row->id . '">History</button>
                    </div>';
                })
                ->rawColumns(['Listing_Status', 'payment_status', 'action'])
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
                // Column names below match the DataTable definitions in
                // employee/order_list.blade.php and employee/dashboard.blade.php
                ->addColumn('Listing_Status', function ($row) {
                    $ps = self::PSTATUS[(int)$row->pstatus] ?? ['label' => 'Unknown', 'class' => 'bg-secondary'];
                    return '<span class="badge ' . $ps['class'] . ' px-2 py-1">' . $ps['label'] . '</span>';
                })
                ->editColumn('created_at', fn($row) => $row->created_at ? Carbon::parse($row->created_at)->format('d-M-Y') : '-')
                ->addColumn('Customer_Name',  fn($row) => $row->oname  ?? '-')
                ->addColumn('Customer_Email', fn($row) => $row->oemail ?? '-')
                ->addColumn('Customer_Phone', fn($row) => $row->ophone ?? '-')
                ->addColumn('Address', fn($row) => ($row->origincity ?? '') . ', ' . ($row->originstate ?? '') . ' → ' . ($row->destinationcity ?? '') . ', ' . ($row->destinationstate ?? ''))
                ->addColumn('Book_Price',     fn($row) => $row->payment        ?? '-')
                ->addColumn('Deposit_Amount', fn($row) => $row->deposit_amount ?? '-')
                ->addColumn('Paid_Amount',    fn($row) => $row->paid_amount    ?? '-')
                ->addColumn('Paid_Method',    fn($row) => $row->payment_method ?? '-')
                ->addColumn('Received_Date',  fn($row) => '-')
                ->editColumn('payment_status', function ($row) {
                    $status = $row->payment_status ?? 'Unpaid';
                    $class  = $status === 'Paid' ? 'bg-success' : 'bg-warning text-dark';
                    return '<span class="badge ' . $class . ' px-2 py-1">' . $status . '</span>';
                })
                ->addColumn('action', function ($row) {
                    return '<div class="d-flex justify-content-center gap-2">
                        <button class="btn btn-outline-info btn-sm order-history-btn" data-id="' . $row->id . '" style="width:120px;">Order History</button>
                    </div>';
                })
                ->rawColumns(['Listing_Status', 'payment_status', 'action', 'id'])
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

        $extraStatuses = [
            30 => 'Pickup Approval',
            31 => 'Delivered Approval',
            32 => 'Schedule For Delivery',
            33 => 'Cancel On Approval',
            34 => 'Dispatch Approval',
        ];

        $history = DB::table('call_histories')
            ->leftJoin('pstatus', 'pstatus.id', '=', 'call_histories.pstatus')
            ->where('call_histories.orderId', $orderId)
            ->orderBy('call_histories.created_at', 'desc')
            ->select('call_histories.pstatus', 'pstatus.name as pstatus_name', 'call_histories.history', 'call_histories.created_at')
            ->get()
            ->map(function ($row) use ($extraStatuses) {
                $label = $row->pstatus_name
                    ?? ($extraStatuses[$row->pstatus] ?? ('Status ' . $row->pstatus));
                return [
                    'history_status'      => $label,
                    'expected_date'       => $row->created_at ? \Carbon\Carbon::parse($row->created_at)->format('d-M-Y H:i') : '-',
                    'history_description' => $row->history ? strip_tags($row->history) : '-',
                ];
            });

        return response()->json($history);
    }
}
