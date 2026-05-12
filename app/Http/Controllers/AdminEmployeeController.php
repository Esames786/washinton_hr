<?php

namespace App\Http\Controllers;

use App\Models\CommissionSetting;
use App\Models\Department;
use App\Models\Designation;
use App\Models\DocumentSetting;
use App\Models\Employee;
use App\Models\EmployeeAccountType;
use App\Models\EmployeeAssignLeave;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeBankDetail;
use App\Models\EmployeeBreak;
use App\Models\EmployeeDailyActivity;
use App\Models\EmployeeDocument;
use App\Models\EmployeeHolidayException;
use App\Models\EmployeeLeave;
use App\Models\EmployeeStatus;
use App\Models\EmployeeStatusHistory;
use App\Models\EmployeeWorkingDay;
use App\Models\EmploymentType;
use App\Models\GratuityBalance;
use App\Models\GratuityPayout;
use App\Models\GratuitySetting;
use App\Models\Holiday;
use App\Models\LeaveType;
use App\Models\PayrollDetail;
use App\Models\Role;
use App\Models\ShiftType;
use App\Models\TaxSlabSetting;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use function Ramsey\Uuid\v1;

class AdminEmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    protected $statusColors = [
        'Present'    => 'bg-success-focus text-success-main',
        'Late'       => 'bg-warning-focus text-warning-main',
        'Half Day'   => 'bg-info-focus text-info-main',
        'Early Exit' => 'bg-warning-focus text-warning-main',
        'Absent'     => 'bg-danger-focus text-danger-main',

        'Holiday'    => 'bg-info-focus text-info-main',         // Light Blue (neutral)
        'Weekend'    => 'bg-primary-focus text-primary-main',   // Blue
        'Leave'      => 'bg-warning-focus text-warning-main',   // Yellow (special case)
    ];

    public function index(Request $request)
    {
        if ($request->ajax()) {

            // Eager load relations and select necessary columns
            $employees = Employee::select(
                'hr_employees.id',
                'hr_employees.full_name',
                'hr_employees.email',
                'hr_employees.employee_code',
                'hr_employees.cnic',
                'hr_employees.department_id',
                'hr_employees.designation_id',
                'hr_employees.joining_date',
                'hr_employees.shift_id',
                'hr_employees.role_id',
                'hr_employees.country',
                'hr_employees.city',
                'hr_employees.state',
                'hr_employees.employee_status_id',
                'hr_employees.agent_id',
                'user.name as agent_name',
                'hr_employees.account_type_id',
                'hr_employees.employment_type_id',
            )
                ->with([
                    'shift:id,name',
                    'role:id,name',
                    'department:id,name',
                    'designation:id,name',
                    'account_type:id,name',
                    'employment_type:id,name'
                ])
                ->leftJoin('user', 'hr_employees.agent_id', '=', 'user.id');

            // Fetch all statuses once
            $allStatuses = EmployeeStatus::all()->keyBy('id');

            return DataTables::of($employees)
                ->addColumn('action', function ($row) {
                    $dropdownItems = '
                        <div class="d-flex justify-content-center gap-2">
                            <a href="' . route('admin.employees.edit', $row->id) . '"
                               class="bg-success-focus text-success-600 bg-hover-success-200 fw-medium w-40-px h-40-px
                                      d-flex justify-content-center align-items-center rounded-circle"   data-bs-toggle="tooltip"  title="Edit Employee">
                                <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
                            </a>
                            <a href="' . route('admin.employees.show', $row->id) . '"
                               class="bg-info-focus bg-hover-info-200 text-info-600 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle"  data-bs-toggle="tooltip"  title="View Employee">
                               <iconify-icon icon="majesticons:eye-line" class="icon text-xl"></iconify-icon>
                            </a>
                           <button class="btn btn-outline-info ms-2 document-verification-btn"
                                data-id="' . $row->id . '"
                                title="Document Verification">
                                <iconify-icon icon="bi:file-earmark-text" class="icon"></iconify-icon>
                            </button>
                             <button class="btn btn-outline-info ms-2 attach-agent-btn"
                                data-id="' . $row->id . '"
                                title="Attach With Agent">
                                <iconify-icon icon="lucide:link" class="icon text-xl"></iconify-icon>
                            </button>
                        </div>


                    ';

                    return '
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary px-18 py-11 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Actions
                            </button>
                            <ul class="dropdown-menu">
                                ' . $dropdownItems . '
                            </ul>
                        </div>';
                })
//                ->addColumn('action', function ($row) use ($allStatuses) {
//                    // Existing buttons
//                    $buttons = '
//                        <div class="d-flex justify-content-center gap-2">
//                            <a href="' . route('admin.employees.edit', $row->id) . '"
//                               class="bg-success-focus text-success-600 bg-hover-success-200 fw-medium w-40-px h-40-px
//                                      d-flex justify-content-center align-items-center rounded-circle">
//                                <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
//                            </a>
//                            <a href="' . route('admin.employees.show', $row->id) . '"
//                               class="bg-info-focus bg-hover-info-200 text-info-600 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle">
//                               <iconify-icon icon="majesticons:eye-line" class="icon text-xl"></iconify-icon>
//                            </a>
//                        </div>
//            ';
//
//                    // Status dropdown
////                    $dropdownItems = '';
////                    foreach ($allStatuses as $status) {
////                        $dropdownItems .= '<li>
////                            <a class="dropdown-item status-change px-16 py-8 rounded"
////                               href="javascript:void(0)"
////                               data-id="' . $row->id . '"
////                               data-status="' . $status->id . '">' . ucfirst($status->name) . '</a>
////                        </li>';
////                    }
////
////                    $dropdown = '
////                        <div class="dropdown ms-2">
////                            <button class="btn btn-outline-primary-600 px-18 py-11 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
////                                Change Status
////                            </button>
////                            <ul class="dropdown-menu">
////                                ' . $dropdownItems . '
////                            </ul>
////                        </div>
////                    ';
//                    $buttons .= '
//                        <button class="btn btn-outline-info ms-2 document-verification-btn"
//                            data-id="' . $row->id . '"
//                            title="Document Verification">
//                            <iconify-icon icon="bi:file-earmark-text" class="icon"></iconify-icon>
//                        </button>
//                    ';
//
//                    return '<div class="d-flex justify-content-center align-items-center gap-2">' . $buttons  . '</div>';
//                })
                ->editColumn('agent_id', fn($row) => $row->agent_id ?? '-')
                ->editColumn('agent_name', fn($row) => $row->agent_name ?? '-')
                ->addColumn('shift_name', fn($row) => $row->shift->name ?? '-')
                ->addColumn('role_name', fn($row) => $row->role->name ?? '-')
                ->addColumn('department_name', fn($row) => $row->department->name ?? '-')
                ->addColumn('designation_name', fn($row) => $row->designation->name ?? '-')
                ->addColumn('account_type_name', fn($row) => $row->account_type->name ?? '-')
                ->addColumn('employment_type_name', fn($row) => $row->employment_type->name ?? '-')
//                ->editColumn('employee_status_id', function ($row) use ($allStatuses) {
//                    $status = $allStatuses->get($row->employee_status_id);
//                    if (!$status) return '-';
//
//                    $name = strtolower($status->name);
//
//                    if ($name === 'active') {
//                        $class = 'bg-success-focus text-success-main';
//                    } elseif ($name === 'terminated') {
//                        $class = 'bg-danger-focus text-danger-main';
//                    } elseif (str_contains($name, 'pending')) {
//                        $class = 'bg-warning-focus text-warning-main';
//                    } else {
//                        $class = 'bg-info-focus text-info-main';
//                    }
//
//                    return '<span class="' . $class . ' px-24 py-4 rounded-pill fw-medium text-sm">'
//                        . ucfirst($status->name) . '</span>';
//                })
                ->editColumn('employee_status_id', function ($row) use ($allStatuses) {
                    $status = $allStatuses->get($row->employee_status_id);
                    if (!$status) return '-';

                    // Status dropdown banate hain
                    $dropdownItems = '';
                    foreach ($allStatuses as $st) {
                        $dropdownItems .= '<li>
                            <a class="dropdown-item status-change px-16 py-8 rounded"
                               href="javascript:void(0)"
                               data-id="' . $row->id . '"
                               data-status="' . $st->id . '">' . ucfirst($st->name) . '</a>
                        </li>';
                                    }

                                    return '
                        <div class="dropdown">
                            <button class="btn btn-outline-primary-600 px-18 py-11 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                ' . ucfirst($status->name) . '
                            </button>
                            <ul class="dropdown-menu">
                                ' . $dropdownItems . '
                            </ul>
                        </div>';
                })


                ->filter(function ($query) use ($request) {
                    if ($search = request('search')['value'] ?? false) {
                        $query->where(function ($q) use ($search) {
                            $q->where('hr_employees.full_name', 'like', "%{$search}%")
                                ->orWhere('hr_employees.email', 'like', "%{$search}%")
                                ->orWhere('hr_employees.employee_code', 'like', "%{$search}%");
                        });
                    }

                    // Employee IDs filter
                    if ($request->filled('employee_ids')) {
                        $query->whereIn('hr_employees.id', $request->employee_ids);
                    }

                    // Account type filter
                    if ($request->filled('account_type_id')) {
                        $query->where('hr_employees.account_type_id', $request->account_type_id);
                    }

                    // Employment type filter
                    if ($request->filled('employment_type_id')) {
                        $query->where('hr_employees.employment_type_id', $request->employment_type_id);
                    }
                })

                ->rawColumns(['action','employee_status_id'])
                ->make(true);
        }

        $authorized_users = DB::table('user')->select('id','name','email')
            ->where('status', 1)
            ->where('deleted', 0)
            ->orderBy('name')
            ->get();
        $employees = Employee::all();
        $account_types = EmployeeAccountType::where('id','!=',2)->get();
        $employee_types = EmploymentType::all();
        return view('admin.user_management.employees.index', compact('authorized_users', 'employees', 'account_types', 'employee_types'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $employment_types = EmploymentType::whereIn('id',[1,3])->get();
        $roles = Role::where('guard_name', 'employee')->where('status',1)->get();
        $shift_types = ShiftType::where('status',1)->get();
        $document_types = DocumentSetting::where('status',1)->get();
        $employee_statuses = EmployeeStatus::all();
        $holidays = Holiday::where('status',1)->get();
        $departments = Department::where('status',1)->get();
        $designations = Designation::where('status',1)->get();
        $gratuties = GratuitySetting::where('status',1)->get();
        $commissions = CommissionSetting::where('status',1)->get();
        $leave_types = LeaveType::where('status',1)->get();
        $tax_slabs = TaxSlabSetting::where('status',1)->get();
        $account_types = EmployeeAccountType::all();
        return view('admin.user_management.employees.add_employee',compact('tax_slabs','gratuties','commissions','departments','designations','employment_types', 'roles', 'shift_types', 'document_types','employee_statuses','holidays','leave_types','account_types'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
//    public function store_old(Request $request)
//    {
//        // Validate employee and bank fields
//        $request->validate([
//            'full_name' => 'required|string|max:255',
//            'email' => 'required|email|unique:employees,email',
//            'password' => 'required|string|min:6|confirmed',
//            'employee_code' => 'required|string|unique:employees,employee_code',
//            'employment_type_id' => 'required|integer',
//            'employee_status_id' => 'required|integer',
//            // optional employee fields
//            'department' => 'nullable|string|max:255',
//            'designation' => 'nullable|string|max:255',
//            'father_name' => 'nullable|string|max:255',
//            'mother_name' => 'nullable|string|max:255',
//            'cnic' => 'nullable|string|max:255',
//            'dob' => 'nullable|date',
//            'gender' => 'nullable|in:male,female,other',
//            'marital_status' => 'nullable|in:single,married,divorced,widowed',
//            'kids_count' => 'nullable|integer',
//            'skills' => 'nullable|string',
//            'phone' => 'nullable|string|max:255',
//            'phone2' => 'nullable|string|max:255',
//            'contact_person' => 'nullable|string|max:255',
//            'emergency_contact' => 'nullable|string|max:255',
//            'address' => 'nullable|string|max:255',
//            'city' => 'nullable|string|max:255',
//            'state' => 'nullable|string|max:255',
//            'country' => 'nullable|string|max:255',
//            // bank fields
//            'bank_name' => 'nullable|string|max:255',
//            'account_title' => 'nullable|string|max:255',
//            'account_number' => 'nullable|string|max:255',
//            'iban' => 'nullable|string|max:255',
//        ]);
//
//        // Create Employee
//        $employee = new \App\Models\Employee();
//        $employee->first_name = $request->first_name;
//        $employee->last_name = $request->last_name;
//        $employee->email = $request->email;
//        $employee->password = \Illuminate\Support\Facades\Hash::make($request->password);
//        $employee->employee_code = $request->employee_code;
//        $employee->department = $request->department;
//        $employee->designation = $request->designation;
//        $employee->employment_type_id = $request->employment_type_id;
//        $employee->employee_status_id = $request->employee_status_id;
//        $employee->father_name = $request->father_name;
//        $employee->mother_name = $request->mother_name;
//        $employee->cnic = $request->cnic;
//        $employee->dob = $request->dob;
//        $employee->gender = $request->gender;
//        $employee->marital_status = $request->marital_status;
//        $employee->kids_count = $request->kids_count;
//        $employee->skills = $request->skills;
//        $employee->phone = $request->phone;
//        $employee->phone2 = $request->phone2;
//        $employee->contact_person = $request->contact_person;
//        $employee->emergency_contact = $request->emergency_contact;
//        $employee->address = $request->address;
//        $employee->city = $request->city;
//        $employee->state = $request->state;
//        $employee->country = $request->country;
//        $employee->created_by = auth()->id();
//        $employee->updated_by = auth()->id();
//        $employee->save();
//
//        // Save Bank Details using save()
//        if ($request->bank_name || $request->account_title || $request->account_number || $request->iban) {
//            $bankDetail = new EmployeeBankDetail();
//            $bankDetail->bank_name = $request->bank_name;
//            $bankDetail->account_title = $request->account_title;
//            $bankDetail->account_number = $request->account_number;
//            $bankDetail->iban = $request->iban;
//            $bankDetail->status = 1;
//
//            // Save through relationship
//            $employee->bankDetail()->save($bankDetail);
//        }
//
//        return redirect()->route('admin.employees.index')->with('success', 'Employee created successfully.');
//    }

    public function store(Request $request)
    {


        // Validation
        $request->validate([
            'first_name'            => 'required|string|max:255',
            'last_name'             => 'required|string|max:255',
            'profile_path'          => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'basic_salary'          => 'nullable|required_if:account_type_id,1,3|numeric',
            'email'                 => 'required|email|unique:hr_employees,email',
            'password'              => 'required|string|min:8|confirmed',
            'employee_code'         => 'required|string',
//            'employee_code'         => 'required|string|unique:hr_employees,employee_code',
            'employment_type'       => 'required|integer',
            'employee_status_id'    => 'required|integer',
            'role_id'               => 'required|integer',
            'shift_id'              => 'required|integer',
            'gratuity_id'           => 'nullable|required_if:account_type_id,1,3|integer',
            'valid_gratuity_date'   => 'nullable|required_if:account_type_id,1,3|date',
            'account_type_id'       => 'required|integer',
            'commission_id'         => 'required_if:account_type_id,2,3|integer',
            'joining_date'          => 'required|date',

            // optional employee fields
            'department_id'         => 'nullable|integer|max:255',
            'designation_id'        => 'nullable|integer|max:255',
            'father_name'           => 'nullable|string|max:255',
            'mother_name'           => 'nullable|string|max:255',
            'cnic'                  => 'required|string|size:15|unique:hr_employees,cnic',
            'dob'                   => 'nullable|date|before_or_equal:today',
            'gender'                => 'nullable|in:male,female,other',
            'marital_status'        => 'nullable|in:single,married,divorced,widowed',
            'kids_count'            => 'nullable|integer|min:0',
            'skills'                => 'nullable|string',
            'phone'                 => 'nullable|regex:/^[0-9]{11,}$/',
            'phone2'                => 'nullable|regex:/^[0-9]{11,}$/',
            'contact_person'        => 'nullable|string|max:255',
            'emergency_contact'     => 'nullable|regex:/^[0-9]{11,}$/',
            'address'               => 'nullable|string|max:255',
            'city'                  => 'nullable|string|max:255',
            'state'                 => 'nullable|string|max:255',
            'country'               => 'nullable|string|max:255',

            // bank fields
            'bank_name'             => 'nullable|string|max:255',
            'account_title'         => 'nullable|string|max:255',
            'account_number'        => 'nullable|string|max:255',
            'iban'                  => 'nullable|string|max:255',
            'tax_slab_setting_id'   => 'nullable|integer|max:255',


            'working_days'          => 'required|array|min:1',
            'working_days.*'        => 'in:0,1',

            'leaves' => 'required|array',
            'leaves.*.leave_type_id' => 'required|exists:hr_leave_types,id',
            'leaves.*.assigned_quota' => 'required|integer|min:0',
            'leaves.*.valid_from' => 'required|date',
            'leaves.*.valid_to' => 'required|date'

        ]);

        DB::beginTransaction();
        try {

            if ($request->account_type_id == 2) {
                $request->merge([
                    'basic_salary' => 1,
                    'tax_slab_setting_id' => null,
                    'gratuity_id' => null,
                    'valid_gratuity_date' => null,
                ]);
            }
            $time_stamp = now();

            // Create Employee (no mass assignment)
            $employee = new Employee();
            $employee->full_name              = $request->first_name . ' ' . $request->last_name;
            $employee->email                  = $request->email;
            $employee->basic_salary           = $request->basic_salary;
            $employee->joining_date           = $request->joining_date;
            $employee->password               = Hash::make($request->password);
            $employee->employee_code          = $request->employee_code;
            $employee->department_id          = $request->department_id;
            $employee->designation_id         = $request->designation_id;
            $employee->employment_type_id     = $request->employment_type;
            $employee->employee_status_id     = $request->employee_status_id;
            $employee->role_id                = $request->role_id;
            $employee->shift_id               = $request->shift_id;
            $employee->gratuity_id            = $request->gratuity_id;
            $employee->valid_gratuity_date    = $request->valid_gratuity_date;
            $employee->commission_id          = $request->commission_id;
            $employee->account_type_id        = $request->account_type_id;
            $employee->father_name            = $request->father_name;
            $employee->mother_name            = $request->mother_name;
            $employee->cnic                   = $request->cnic;
            $employee->dob                    = $request->dob;
            $employee->gender                 = $request->gender;
            $employee->marital_status         = $request->marital_status;
            $employee->kids_count             = $request->kids_count;
            $employee->skills                 = $request->skills;
            $employee->phone                  = $request->phone;
            $employee->phone2                 = $request->phone2;
            $employee->contact_person         = $request->contact_person;
            $employee->emergency_contact      = $request->emergency_contact;
            $employee->address                = $request->address;
            $employee->city                   = $request->city;
            $employee->state                  = $request->state;
            $employee->country                = $request->country;
            $employee->employee_status_id     = 7;
            $employee->created_by             = auth('admin')->id();
            $employee->updated_by             = auth('admin')->id();

            if ($request->filled('tax_slab_setting_id') && $request->tax_slab_setting_id > 0) {
                $employee->tax_slab_setting_id = $request->tax_slab_setting_id;
                $employee->is_taxable = 1;
            }
            $employee->save();

            if ($request->hasFile('profile_path')) {
                $path = 'Uploads/employees/' . $employee->id . '/';

                // Make directory if not exists
                if (!file_exists(public_path($path))) {
                    mkdir(public_path($path), 0777, true);
                }

                $file = $request->file('profile_path');
                $filename = 'profile_' . $employee->id . '.' . $file->extension();
                $file->move(public_path($path), $filename);

                $employee->profile_path = $path . $filename;
                $employee->save();

            }




            // Save Bank Details
            if ($request->bank_name || $request->account_title || $request->account_number || $request->iban) {
                $bankDetail = new EmployeeBankDetail();
                $bankDetail->bank_name     = $request->bank_name;
                $bankDetail->account_title = $request->account_title;
                $bankDetail->account_number= $request->account_number;
                $bankDetail->iban          = $request->iban;
                $bankDetail->status        = 1;
                $employee->bankDetail()->save($bankDetail);
            }

            // Save Documents
            if ($request->has('documents')) {

                $documentSettings = DocumentSetting::whereIn('id', array_keys($request->file('documents', [])))
                    ->get()
                    ->keyBy('id');

                foreach ($request->file('documents', []) as $docId => $file) {

                    $documentSetting = $documentSettings->get($docId);

                    $path = 'Uploads/employees/' . $employee->id . '/';

                    // âœ… Make directory if not exists
                    if (!file_exists(public_path($path))) {
                        mkdir(public_path($path), 0777, true);
                    }

                    // New file save
                    $filename = 'doc_' . $employee->id . '_' . $docId . '.' . $file->extension();
                    $file->move(public_path($path), $filename);

                    // Save / Update record
                    EmployeeDocument::updateOrCreate(
                        [
                            'employee_id' => $employee->id,
                            'document_setting_id' => $docId,
                        ],
                        [
                            'file_name' => $documentSetting?->title ?? $filename,
                            'mime_type' => $file->getClientMimeType(),
                            'file_path' => $path . $filename,
                            'status' => 0
                        ]
                    );
                }
            }

            if ($request->has('working_days')) {
                foreach ($request->working_days as $day => $isWorking) {
                    $workingDay = new EmployeeWorkingDay();
                    $workingDay->employee_id = $employee->id;
                    $workingDay->day_of_week = $day;
                    $workingDay->is_working = $isWorking ? 1 : 0;
                    $workingDay->created_by = auth('admin')->id();
                    $workingDay->save();
                }
            }

            if ($request->has('leaves')) {
                foreach ($request->leaves as $leave) {
                    $assignLeave = new EmployeeAssignLeave();
                    $assignLeave->employee_id    = $employee->id;
                    $assignLeave->leave_type_id  = $leave['leave_type_id'];
                    $assignLeave->assigned_quota = $leave['assigned_quota'];
                    $assignLeave->valid_from     = $leave['valid_from'];
                    $assignLeave->valid_to       = $leave['valid_to'];
                    $assignLeave->status         = 1;
                    $assignLeave->created_by     = auth('admin')->id();
                    $assignLeave->save();
                }
            }
            if($request->filled('excluded_holiday_ids') && is_array($request->excluded_holiday_ids)) {
                    $employee_days =[];
                    foreach ($request->excluded_holiday_ids as $holidayId) {
                        $employee_days[]=[
                            'employee_id' => $employee->id,
                            'holiday_id' => $holidayId,
                            'status' => 1,
                            'created_by' => auth('admin')->id(),
                            'created_at' =>$time_stamp,
                            'updated_at' => $time_stamp,
                        ];
                    }

                    if(!empty($employee_days)){
                        EmployeeHolidayException::insert($employee_days);
                    }
            }


            DB::commit();
            return redirect()->route('admin.employees.index')
                ->with('success', 'Employee created successfully.');

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::channel('admin_log')->error([
                'message' => $th->getMessage(),
                'file'    => $th->getFile(),
                'line'    => $th->getLine(),
                'trace'   => $th->getTraceAsString(),
            ]);
            session()->flash('error', 'Something went wrong!');
            return redirect()->back();
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
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

        ])->findOrFail($id);

        return view('admin.user_management.employees.profile', compact('employee'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $employee = Employee::with([
            'bankDetail',
            'documents',
            'working_days',
            'assignedLeaves',
            'holiday_exceptions',
            'shift',
            'tax_slab'
        ])->findOrFail($id);

        $employee->excluded_holiday_ids = $employee->holiday_exceptions()->pluck('holiday_id')->toArray();

        // Normalize working_days keys: bridge stores integers (0=Sun…6=Sat),
        // the edit view expects string day names ('Monday', 'Tuesday', …).
        $intDayNames = [0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'];
        $rawDays = $employee->working_days()->pluck('is_working', 'day_of_week')->toArray();
        $normalisedDays = [];
        foreach ($rawDays as $key => $isWorking) {
            $normalisedDays[is_numeric($key) ? ($intDayNames[(int)$key] ?? $key) : $key] = (int) $isWorking;
        }
        $employee->working_days = $normalisedDays;

        $employment_types = EmploymentType::whereIn('id',[1,3])->get();
        $roles = Role::where('guard_name', 'employee')->where('status',1)->get();
        $shift_types = ShiftType::where('status',1)->get();
        $document_types = DocumentSetting::where('status',1)->get();
        $employee_statuses = EmployeeStatus::all();
        $holidays = Holiday::where('status',1)->get();
        $departments = Department::where('status',1)->get();
        $designations = Designation::where('status',1)->get();
        $gratuties = GratuitySetting::where('status',1)->get();
        $commissions = CommissionSetting::where('status',1)->get();
        $leave_types = LeaveType::where('status',1)->get();
        $assignedLeaves = $employee->assignedLeaves->keyBy('leave_type_id');
        $tax_slabs = TaxSlabSetting::where('status',1)->get();
        $account_types = EmployeeAccountType::all();

        return view('admin.user_management.employees.edit_employee',
            compact(
                'employee','employment_types','roles','shift_types',
                'document_types','employee_statuses','holidays','departments',
                'designations','gratuties','commissions','leave_types','assignedLeaves','tax_slabs','account_types'
            )
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Validation
        $request->validate([
            'full_name'            => 'required|string|max:255',
            'email' => 'required|email|unique:hr_employees,email,' . $id,
            'basic_salary'          => 'nullable|required_if:account_type_id,1,3|numeric',
            'employee_code' => 'required|string',
//            'employee_code' => 'required|string|unique:hr_employees,employee_code,' . $id,
            'employment_type_id'       => 'required|integer',
            'employee_status_id'    => 'required|integer',
            'role_id'               => 'required|integer',
            'shift_id'              => 'required|integer',
            'gratuity_id'           => 'nullable|required_if:account_type_id,1,3|integer',
            'valid_gratuity_date'   => 'nullable|required_if:account_type_id,1,3|date',
            'account_type_id'       => 'required|integer',
            'commission_id'         => 'required_if:account_type_id,2,3|integer',
            'joining_date'          => 'required|date',

            // optional employee fields
            'department_id'         => 'nullable|integer|max:255',
            'designation_id'        => 'nullable|integer|max:255',
            'father_name'           => 'nullable|string|max:255',
            'mother_name'           => 'nullable|string|max:255',
            'cnic' => 'required|string|size:15|unique:hr_employees,cnic,' . $id,
            'dob'                   => 'nullable|date|before_or_equal:today',
            'gender'                => 'nullable|in:male,female,other',
            'marital_status'        => 'nullable|in:single,married,divorced,widowed',
            'kids_count'            => 'nullable|integer|min:0',
            'skills'                => 'nullable|string',
            'phone'                 => 'nullable|regex:/^[0-9]{11,}$/',
            'phone2'                => 'nullable|regex:/^[0-9]{11,}$/',
            'contact_person'        => 'nullable|string|max:255',
            'emergency_contact'     => 'nullable|regex:/^[0-9]{11,}$/',
            'address'               => 'nullable|string|max:255',
            'city'                  => 'nullable|string|max:255',
            'state'                 => 'nullable|string|max:255',
            'country'               => 'nullable|string|max:255',

            // bank fields
            'bank_name'             => 'nullable|string|max:255',
            'account_title'         => 'nullable|string|max:255',
            'account_number'        => 'nullable|string|max:255',
            'iban'                  => 'nullable|string|max:255',
            'tax_slab_setting_id'   => 'nullable|integer|max:255',

            'working_days'          => 'required|array|min:1',
            'working_days.*'        => 'in:0,1',

            'leaves' => 'required|array',
            'leaves.*.leave_type_id' => 'required|exists:hr_leave_types,id',
            'leaves.*.assigned_quota' => 'required|integer|min:0',
            'leaves.*.valid_from' => 'required|date',
            'leaves.*.valid_to' => 'required|date',

        ]);

        DB::beginTransaction();
        try {

            if ($request->account_type_id == 2) {
                $request->merge([
                    'basic_salary' => 1,
                    'gratuity_id' => null,
                    'valid_gratuity_date' => null,
                    'tax_slab_setting_id' => null
                ]);
            }

            $employee = Employee::findOrFail($id);
            $employee->full_name        = $request->full_name ;
            $employee->email            = $request->email;
            $employee->basic_salary     = $request->basic_salary;
            $employee->joining_date     = $request->joining_date;
//            if($request->filled('password')){
//                $employee->password     = Hash::make($request->password);
//            }
            $employee->employee_code    = $request->employee_code;
            $employee->department_id    = $request->department_id;
            $employee->designation_id   = $request->designation_id;
            $employee->employment_type_id = $request->employment_type_id;
            $employee->employee_status_id = $request->employee_status_id;
            $employee->role_id          = $request->role_id;
            $employee->shift_id         = $request->shift_id;
            $employee->gratuity_id      = $request->gratuity_id;
            $employee->valid_gratuity_date    = $request->valid_gratuity_date;
            $employee->commission_id    = $request->commission_id;
            $employee->account_type_id    = $request->account_type_id;
            $employee->father_name      = $request->father_name;
            $employee->mother_name      = $request->mother_name;
            $employee->cnic             = $request->cnic;
            $employee->dob              = $request->dob;
            $employee->gender           = $request->gender;
            $employee->marital_status   = $request->marital_status;
            $employee->kids_count       = $request->kids_count;
            $employee->skills           = $request->skills;
            $employee->phone            = $request->phone;
            $employee->phone2           = $request->phone2;
            $employee->contact_person   = $request->contact_person;
            $employee->emergency_contact= $request->emergency_contact;
            $employee->address          = $request->address;
            $employee->city             = $request->city;
            $employee->state            = $request->state;
            $employee->country          = $request->country;
            $employee->updated_by       = auth('admin')->id();
            if ($request->filled('tax_slab_setting_id') && $request->tax_slab_setting_id > 0) {
                $employee->tax_slab_setting_id = $request->tax_slab_setting_id;
                $employee->is_taxable = 1;
            } elseif ($request->tax_slab_setting_id == null && $request->account_type_id == 2) {
                $employee->tax_slab_setting_id = null;
                $employee->is_taxable = 0;
            }
            $employee->save();

            // ðŸ”¹ Profile Image
            if ($request->hasFile('profile_path')) {
                $file = $request->file('profile_path');
                $filename = 'profile_' . $employee->id . '.' . $file->extension();
                $path = public_path('Uploads/employees/' . $employee->id . '/');
                if (!file_exists($path)) {
                    mkdir($path, 0755, true);
                }

                // Delete old profile if exists
                if ($employee->profile_path && file_exists(public_path($employee->profile_path))) {
                    unlink(public_path($employee->profile_path));
                }

                $file->move($path, $filename);
                $employee->profile_path = 'Uploads/employees/' . $employee->id . '/' . $filename;
                $employee->save();
            }



            // ðŸ”¹ Bank Detail update
            if ($request->bank_name || $request->account_title || $request->account_number || $request->iban) {
                $bankDetail = $employee->bankDetail ?? new EmployeeBankDetail();
                $bankDetail->bank_name     = $request->bank_name;
                $bankDetail->account_title = $request->account_title;
                $bankDetail->account_number= $request->account_number;
                $bankDetail->iban          = $request->iban;
                $bankDetail->status        = 1;
                $employee->bankDetail()->save($bankDetail);
            }

//            // ðŸ”¹ Working Days (purane delete karke new insert)
//            EmployeeWorkingDay::where('employee_id', $employee->id)->delete();
//            foreach ($request->working_days as $day => $isWorking) {
//                $workingDay = new EmployeeWorkingDay();
//                $workingDay->employee_id = $employee->id;
//                $workingDay->day_of_week = $day;
//                $workingDay->is_working  = $isWorking ? 1 : 0;
//                $workingDay->created_by  = auth('admin')->id();
//                $workingDay->save();
//            }

            if ($request->filled('working_days')) {
                foreach ($request->working_days as $day => $isWorking) {
                    $record = EmployeeWorkingDay::updateOrCreate(
                        [
                            'employee_id' => $employee->id,
                            'day_of_week' => $day,
                        ],
                        [
                            'is_working' => $isWorking ? 1 : 0,
                            'updated_by' => auth('admin')->id(),
                        ]
                    );

                    // Sirf nayi row ke liye created_by set karen
                    if (!$record->wasRecentlyCreated) {
                        continue;
                    }
                    $record->created_by = auth('admin')->id();
                    $record->save();
                }
            }

            // ðŸ”¹ Leaves assignment (update/insert)
            if ($request->has('leaves')) {
                foreach ($request->leaves as $leave) {
                    $assignLeave = EmployeeAssignLeave::updateOrCreate(
                        [
                            'employee_id'   => $employee->id,
                            'leave_type_id' => $leave['leave_type_id'],
                        ],
                        [
                            'assigned_quota' => $leave['assigned_quota'],
                            'valid_from'     => $leave['valid_from'],
                            'valid_to'       => $leave['valid_to'],
                            'status'         => 1,
//                            'created_by' => auth('admin')->id(),
                            'updated_by'     => auth('admin')->id(),
                        ]
                    );

                    // Sirf nayi row ke liye created_by set karen
                    if ($assignLeave->wasRecentlyCreated) {
                        $assignLeave->created_by = auth('admin')->id();
                        $assignLeave->save();
                    }
                }
            }

            // ðŸ”¹ Holidays exception (safe update/insert)
            if ($request->filled('excluded_holiday_ids')) {
                foreach ($request->excluded_holiday_ids as $holidayId) {
                    $record = EmployeeHolidayException::updateOrCreate(
                        [
                            'employee_id' => $employee->id,
                            'holiday_id'  => $holidayId,
                        ],
                        [
                            'status'     => 1,
//                            'created_by' => auth('admin')->id(),
                            'updated_by' => auth('admin')->id(),
                        ]
                    );

                    if ($record->wasRecentlyCreated) {
                        $record->created_by = auth('admin')->id();
                        $record->save();
                    }
                }
            }

//            // ðŸ”¹ Leaves (purane delete karke new insert)
//            EmployeeAssignLeave::where('employee_id', $employee->id)->delete();
//            if ($request->has('leaves')) {
//                foreach ($request->leaves as $leave) {
//                    $assignLeave = new EmployeeAssignLeave();
//                    $assignLeave->employee_id    = $employee->id;
//                    $assignLeave->leave_type_id  = $leave['leave_type_id'];
//                    $assignLeave->assigned_quota = $leave['assigned_quota'];
//                    $assignLeave->valid_from     = $leave['valid_from'];
//                    $assignLeave->valid_to       = $leave['valid_to'];
//                    $assignLeave->status         = 1;
//                    $assignLeave->created_by     = auth('admin')->id();
//                    $assignLeave->save();
//                }
//            }

//            // ðŸ”¹ Holidays exception
//            EmployeeHolidayException::where('employee_id', $employee->id)->delete();
//            if ($request->filled('excluded_holiday_ids')) {
//                foreach ($request->excluded_holiday_ids as $holidayId) {
//                    $employeeholiday = new EmployeeHolidayException();
//                    $employeeholiday->employee_id = $employee->id;
//                    $employeeholiday->holiday_id = $holidayId;
//                    $employeeholiday->status = 1;
//                    $employeeholiday->created_by = auth('admin')->id();
//                    $employeeholiday->save();
//
//                }
//            }

                 // ðŸ”¹ Documents
                if ($request->hasFile('documents')) {

                    $documentSettings = DocumentSetting::whereIn('id', array_keys($request->file('documents', [])))
                        ->get()
                        ->keyBy('id');

                    foreach ($request->file('documents') as $docId => $file) {

                        $documentSetting = $documentSettings->get($docId);

                        $oldDoc = EmployeeDocument::where('employee_id', $employee->id)
                            ->where('document_setting_id', $docId)
                            ->first();

                        if ($oldDoc && file_exists(public_path($oldDoc->file_path))) {
                            unlink(public_path($oldDoc->file_path));
                        }

                        $filename = 'doc_' . $employee->id . '_' . $docId . '.' . $file->extension();
                        $path = 'Uploads/employees/' . $employee->id . '/';
                        if (!file_exists(public_path($path))) {
                            mkdir(public_path($path), 0755, true);
                        }

                        $file->move(public_path($path), $filename);

                        EmployeeDocument::updateOrCreate(
                            [
                                'employee_id' => $employee->id,
                                'document_setting_id' => $docId,
                            ],
                            [
                                'file_name' => $documentSetting?->title ?? $filename,
                                'mime_type' => $file->getClientMimeType(),
                                'file_path' => $path . $filename,
                                'status'    => 0
                            ]
                        );
                    }
                }


            DB::commit();
            return redirect()->route('admin.employees.index')
                ->with('success', 'Employee updated successfully.');

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::channel('admin_log')->error([
                'message' => $th->getMessage(),
                'file'    => $th->getFile(),
                'line'    => $th->getLine(),
                'trace'   => $th->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Something went wrong!');
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function changeStatus(Request $request)
    {
        $request->validate([
            'employee_id'   => 'required|integer|exists:hr_employees,id',
            'status'        => 'required|integer|exists:hr_employee_statuses,id', // update as per your statuses
            'disable_email' => 'sometimes|boolean',
            'disable_phone' => 'sometimes|boolean',
        ]);

        $employee = Employee::where('id', $request->employee_id)
            ->where('employee_status_id', '!=', $request->status)
            ->first();

        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Employee not found or status unchanged!'], 404);
        }

        try {
            DB::transaction(function() use ($employee, $request) {
                // Update employee status
                $employee->employee_status_id = $request->status;
                $employee->save();

                // Log status history
                $history = new EmployeeStatusHistory();
                $history->employee_id = $employee->id;
                $history->employee_status_id = $request->status;
                $history->is_email_disabled = $request->status == 3 ? $request->disable_email : 0;
                $history->is_phone_disabled = $request->status == 3 ? $request->disable_phone : 0;
                $history->save();
            });

            return response()->json(['success' => true, 'message' => 'Status updated successfully']);

        } catch (\Throwable $th) {
            Log::channel('admin_log')->error([
                'message' => $th->getMessage(),
                'file'    => $th->getFile(),
                'line'    => $th->getLine(),
                'trace'   => $th->getTraceAsString(),
            ]);
            return response()->json(['success' => false, 'message' => 'Something went wrong!'], 500);
        }
    }

    public function getDocuments(Employee $employee)
    {
        return view('admin.user_management.employees.partials.documents_verify', compact('employee'));
    }

    public function verify(EmployeeDocument $document, Request $request)
    {
        $document->status = $request->status;
        $document->updated_by = auth('admin')->id();
        $document->save();

        return response()->json([
            'success' => true,
            'message' => 'Document status updated'
        ]);
    }


    public function attendance_list(Request $request)
    {
        if($request->ajax()){
            $data = EmployeeAttendance::with('attendance_status','employee')
                ->select('hr_employee_attendances.id','hr_employee_attendances.employee_id', 'hr_employee_attendances.attendance_date','hr_employee_attendances.check_in','hr_employee_attendances.check_out','hr_employee_attendances.working_hours','hr_employee_attendances.attendance_status_id');

            if($request->employee_ids) {
                $data->whereIn('employee_id',$request->employee_ids);
            }
            if ($request->from_date && $request->to_date) {
                $data->whereBetween('attendance_date', [$request->from_date, $request->to_date]);
            } elseif ($request->from_date) {
                $data->whereDate('attendance_date', '>=', $request->from_date);
            } elseif ($request->to_date) {
                $data->whereDate('attendance_date', '<=', $request->to_date);
            } else {
                  $date = Carbon::now()->toDateString();
                  $data->whereDate('attendance_date', '=', $date);
            }

            return DataTables::of($data)
//                ->addColumn('attendance_status_name', function($row) {
//                    if ($row->attendance_status) {
//                        $status = $row->attendance_status->name;
//
//                        // Map attendance status to badge classes
//                        switch ($status) {
//                            case 'Present':
//                                $bgClass = 'bg-success-focus text-success-main';
//                                break;
//                            case 'Late':
//                                $bgClass = 'bg-warning-focus text-warning-main';
//                                break;
//                            case 'Half Day':
//                                $bgClass = 'bg-info-focus text-info-main';
//                                break;
//                            case 'Early Exit':
//                                $bgClass = 'bg-warning-focus text-warning-main';
//                                break;
//                            case 'Absent':
//                                $bgClass = 'bg-danger-focus text-danger-main';
//                                break;
//                            case 'Holiday':
//                                $bgClass = 'bg-info-focus text-info-main';
//                                break;
//                            case 'Weekend':
//                                $bgClass = 'bg-primary-focus text-primary-main';
//                                break;
//                            case 'Leave':
//                                $bgClass = 'bg-warning-focus text-warning-main';
//                                break;
//                            default:
//                                $bgClass = 'bg-neutral-focus text-neutral-main';
//                                break;
//                        }
//
//                        return '<span class="'.$bgClass.' px-24 py-4 rounded-pill fw-medium text-sm">'.$status.'</span>';
//                    }
//                    return '-';
//                })
                ->editColumn('working_hours', function($row) {
                    if ($row->working_hours) {
                        $totalSeconds = $row->working_hours;

                        $hours = floor($totalSeconds / 3600);
                        $minutes = floor(($totalSeconds % 3600) / 60);
                        $seconds = $totalSeconds % 60;

                        $result = [];
                        if ($hours > 0) $result[] = $hours . 'h';
                        if ($minutes > 0) $result[] = $minutes . 'm';
                        if ($seconds > 0 && $hours == 0) $result[] = $seconds . 's';

                        return implode(' ', $result);
                    }
                    return '-';
                })

                ->addColumn('attendance_status_name', function($row) {
                    if ($row->attendance_status) {
                        $status = $row->attendance_status->name;
                        $bgClass = $this->statusColors[$status] ?? 'bg-neutral-focus text-neutral-main';
                        return '<span class="'.$bgClass.' px-24 py-4 rounded-pill fw-medium text-sm">'.$status.'</span>';
                    }
                    return '';
                })
                ->addColumn('employee_name', function ($row) {
                    return $row->employee->full_name ?? '-';
                })
                ->editColumn('check_in', function($row) {
                    return $row->check_in ? $row->check_in : '-';
                })
                ->editColumn('check_out', function($row) {
                    return $row->check_out ? $row->check_out : '-';
                })
                ->filter(function ($query) {
                    if ($search = request('search.value')) {
                        $query->where(function($q) use ($search) {
                            $q->whereHas('attendance_status', function($sub) use ($search) {
                                $sub->where('name', 'like', "%{$search}%");
                            })
                                ->orWhereHas('employee', function($sub) use ($search) {
                                    $sub->where('full_name', 'like', "%{$search}%");
                                });
                        });
                    }
                })
                ->rawColumns(['attendance_status_name'])
                ->make(true);
        }
        $employees = Employee::select('id','full_name')->get();
        return view('admin.user_management.employees.attendance_list',
            compact('employees')
        );
    }

    public function break_list(Request $request)
    {
        if ($request->ajax()) {
            $data = EmployeeBreak::with('employee')
                ->select('hr_employee_breaks.id', 'hr_employee_breaks.employee_id', 'hr_employee_breaks.break_start', 'hr_employee_breaks.break_end', 'hr_employee_breaks.break_duration');

            if($request->employee_ids) {
                $data->whereIn('hr_employee_breaks.employee_id',$request->employee_ids);
            }
            if ($request->from_date && $request->to_date) {
                $from = Carbon::parse($request->from_date)->startOfDay();
                $to   = Carbon::parse($request->to_date)->endOfDay();
                $data->whereBetween('hr_employee_breaks.created_at', [$from, $to]);
            } elseif ($request->from_date) {
                $data->where('hr_employee_breaks.created_at', '>=', Carbon::parse($request->from_date)->startOfDay());
            } elseif ($request->to_date) {
                $data->where('hr_employee_breaks.created_at', '<=', Carbon::parse($request->to_date)->endOfDay());
            } else {
                $data->whereDate('hr_employee_breaks.created_at', Carbon::today());
            }

            return DataTables::of($data)
                ->addColumn('employee_name', function ($row) {
                    return $row->employee->full_name ?? '-';
                })
                ->editColumn('break_duration', function ($row) {
                    if (!$row->break_duration) {
                        return '-';
                    }

                    // break_duration DB me minutes (decimal) hai
                    $minutes = (float) $row->break_duration;

                    // CarbonInterval banake human readable
                    $interval = CarbonInterval::minutes($minutes)->cascade();

                    // Example: "1 hour 12 minutes (72 mins)"
                    return $interval->forHumans();
                })
                ->filter(function ($query) {
                    if ($search = request('search')['value'] ?? false) {
                        $query->whereHas('employee', function($sub) use ($search) {
                            $sub->where('full_name', 'like', "%{$search}%");
                        });
                    }
                })
                ->make(true);
        }
        $employees = Employee::select('id','full_name')->get();
        return view('admin.user_management.employees.break_list',
            compact('employees')
        );
    }

    public function daily_activity_list(Request $request)
    {
        if ($request->ajax()) {

            $data = EmployeeDailyActivity::with('employee')->select(
                'id',
                'employee_id',
                'activity_date',
                'field_name',
                'field_value',
                'field_type'
            );
            if($request->employee_ids) {
                $data->whereIn('employee_id',$request->employee_ids);
            }
            if ($request->from_date && $request->to_date) {
                $data->whereBetween('activity_date', [$request->from_date, $request->to_date]);
            } elseif ($request->from_date) {
                $data->whereDate('activity_date', '>=', $request->from_date);
            } elseif ($request->to_date) {
                $data->whereDate('activity_date', '<=', $request->to_date);
            } else {
                $date = Carbon::now()->toDateString();
                $data->whereDate('activity_date', '=', $date);
            }

            return DataTables::of($data)
//                ->addColumn('action', function ($row) {
//                    return '<div class="d-flex justify-content-center gap-2">
//                            <button type="button"
//                                class="bg-success-focus text-success-600 bg-hover-success-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle edit_btn"
//                                data-id="'.$row->id.'">
//                                <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
//                            </button>
//                        </div>';
//                })
                ->addColumn('employee_name', function ($row) {
                    return $row->employee->full_name ?? '-';
                })
                ->editColumn('field_value', function ($row) {
                    if ($row->field_type === 'file') {
                        return $row->field_value
                            ? '<a href="'.asset($row->field_value).'" target="_blank">View File</a>'
                            : '';
                    }
                    return e($row->field_value);
                })
                ->editColumn('activity_date', fn($row) => $row->activity_date ? date('Y-m-d', strtotime($row->activity_date)) : '')
                ->filter(function ($query) {
                    if (request()->has('search') && request('search')['value'] != '') {
                        $search = request('search')['value'];
                        $query->where('field_name', 'like', "%{$search}%")
                            ->orWhere('field_value', 'like', "%{$search}%");
                    }
                })
                ->rawColumns(['field_value', 'action'])
                ->make(true);
        }
        $employees = Employee::select('id','full_name')->get();
        return view('admin.user_management.employees.daily_activity_list',
            compact('employees')
        );
    }


    public function attach_agent(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|integer|exists:hr_employees,id',
            'agent_id' => 'required|integer', // agar agents users table me hain
        ]);

        try {
            $employee = Employee::findOrFail($request->employee_id);
            $employee->agent_id = $request->agent_id;
            $employee->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Agent attached successfully!',
            ]);

        } catch (\Throwable $th) {
            Log::channel('admin_log')->error([
                'message' => $th->getMessage(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong',
            ], 500);
        }
    }


    public function order_list(Request $request)
    {
        if ($request->ajax()) {
            $today = Carbon::today()->format('Y-m-d');
            $agent_ids = $request->employee_ids
                ? Employee::whereIn('id', $request->employee_ids)->pluck('agent_id')->filter()->values()
                : null;

            // order table: agent linked via order_taker_id -> user.id
            $orders = DB::table('order')
                ->join('hr_employees', 'hr_employees.agent_id', '=', 'order.order_taker_id')
                ->select(
                    'hr_employees.full_name',
                    'order.id',
                    'order.pstatus',
                    'order.created_at',
                    'order.oname',
                    'order.oemail',
                    'order.ophone',
                    'order.origincity',
                    'order.originstate',
                    'order.destinationcity',
                    'order.destinationstate',
                    'order.ymk',
                    'order.paid_amount',
                    'order.deposit_amount',
                    'order.payment_status',
                    'order.payment_method',
                    'order.order_taker_id'
                );

            // Filter by employee agent_ids
            if ($agent_ids && $agent_ids->isNotEmpty()) {
                $orders->whereIn('order.order_taker_id', $agent_ids);
            }

            // Date filtering
            if ($request->from_date && $request->to_date) {
                $orders->whereBetween('order.created_at', [
                    $request->from_date . ' 00:00:00',
                    $request->to_date . ' 23:59:59'
                ]);
            } else {
                $orders->whereBetween('order.created_at', [
                    $today . ' 00:00:00',
                    $today . ' 23:59:59'
                ]);
            }

            return DataTables::of($orders)
                ->addColumn('action', function ($row) {
                    return '<div class="d-flex justify-content-center gap-2">
                        <button class="btn btn-outline-info ms-2 order-history-btn" data-id="' . $row->id . '" style="width:130px;">Order History</button>
                    </div>';
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at ? Carbon::parse($row->created_at)->format('d-M-Y') : '-';
                })
                ->editColumn('pstatus', function ($row) {
                    $statuses = [
                        0  => ['label' => 'New',             'class' => 'bg-secondary'],
                        1  => ['label' => 'Interested',      'class' => 'bg-info'],
                        2  => ['label' => 'Follow More',     'class' => 'bg-primary'],
                        3  => ['label' => 'Asking Low',      'class' => 'bg-warning text-dark'],
                        4  => ['label' => 'Not Interested',  'class' => 'bg-danger'],
                        5  => ['label' => 'No Response',     'class' => 'bg-secondary'],
                        6  => ['label' => 'Time Quote',      'class' => 'bg-info'],
                        7  => ['label' => 'Payment Missing', 'class' => 'bg-warning text-dark'],
                        8  => ['label' => 'Booked',          'class' => 'bg-success'],
                        9  => ['label' => 'Listed',          'class' => 'bg-primary'],
                        10 => ['label' => 'Scheduled',       'class' => 'bg-info'],
                        11 => ['label' => 'Picked Up',       'class' => 'bg-primary'],
                        12 => ['label' => 'Delivered',       'class' => 'bg-success'],
                        13 => ['label' => 'Completed',       'class' => 'bg-success'],
                        14 => ['label' => 'Cancelled',       'class' => 'bg-danger'],
                        15 => ['label' => 'Deleted',         'class' => 'bg-dark'],
                    ];
                    $s = $statuses[$row->pstatus] ?? ['label' => $row->pstatus, 'class' => 'bg-secondary'];
                    return '<span class="badge ' . $s['class'] . ' px-2 py-1">' . $s['label'] . '</span>';
                })
                ->editColumn('payment_status', function ($row) {
                    $status = $row->payment_status ?? 'Unpaid';
                    $class  = $status === 'Paid' ? 'bg-success' : ($status === 'Unpaid' ? 'bg-danger' : 'bg-warning text-dark');
                    return '<span class="badge ' . $class . ' px-2 py-1">' . $status . '</span>';
                })
                ->addColumn('route', function ($row) {
                    $from = $row->origincity ? $row->origincity . ', ' . $row->originstate : '-';
                    $to   = $row->destinationcity ? $row->destinationcity . ', ' . $row->destinationstate : '-';
                    return $from . ' &rarr; ' . $to;
                })
                ->rawColumns(['action', 'pstatus', 'payment_status', 'route'])
                ->make(true);
        }

        $employees = Employee::select('id', 'full_name')->get();
        return view('admin.user_management.employees.order_list', compact('employees'));
    }

    public function order_history($orderId)
    {
        $history = DB::table('order_quote_status')
            ->where('order_id', $orderId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($history);
    }
    public function monthly_tax_list(Request $request)
    {
        if ($request->ajax()) {
            $query = PayrollDetail::query()
                ->join('employees as e', 'e.id', '=', 'hr_payroll_details.employee_id')
                ->select(
                    'e.full_name as employee',
                    DB::raw('MONTH(payroll_details.created_at) as month'),
                    DB::raw('YEAR(payroll_details.created_at) as year'),
                    DB::raw('SUM(payroll_details.tax_amount) as tax_amount')
                )
                ->groupBy(
                    'hr_payroll_details.employee_id',
                    DB::raw('MONTH(payroll_details.created_at)'),
                    DB::raw('YEAR(payroll_details.created_at)')
                );

            // âœ… Filters
            if ($request->filled('employee_ids')) {
                $query->whereIn('hr_payroll_details.employee_id', $request->employee_ids);
            }

            // âœ… Filter by month & current year
            if ($request->filled('month')) {
                $query->whereMonth('hr_payroll_details.created_at', $request->month)
                    ->whereYear('hr_payroll_details.created_at', now()->year);
            }


            // âœ… Safe Total Tax Calculation
            $totalTax = (clone $query)->get()->sum('tax_amount');

            return DataTables::of($query)
                ->editColumn('month', function ($row) {
                    return \Carbon\Carbon::create()->month($row->month)->format('F');
                })
                ->with([
                    'total_tax' => $totalTax
                ])
                ->make(true);
        }

        $employees = Employee::where('employee_status_id', 1)
            ->select('id', 'full_name')
            ->get();

        return view('admin.user_management.employees.monthly_tax_list',
            compact('employees')
        );
    }




}
