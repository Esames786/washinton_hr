@extends('layout.master')
@section('pageName', 'Edit Employee')

@push('cssLinks')
    {{--    <link rel="stylesheet" href="{{ asset('assets/vendor/datatable/css/dataTables.bootstrap5.min.css') }}">--}}
@endpush

@section('content')
    @include('partials.alerts')

    <div class="card h-100">
        <div class="card-body">
            <div class="form-wizard">
                <form action="{{ route('admin.employees.update', $employee->id) }}" method="POST" enctype="multipart/form-data" class="form-select-2">
                    @csrf
                    @method('PUT')

                    <div class="form-wizard-header overflow-x-auto scroll-sm pb-8 my-32">
                        <ul class="list-unstyled form-wizard-list style-two">
                            <li class="form-wizard-list__item active">
                                <div class="form-wizard-list__line">
                                    <span class="count">1</span>
                                </div>
                                <span class="text text-xs fw-semibold">Employee Information</span>
                            </li>
                            <li class="form-wizard-list__item">
                                <div class="form-wizard-list__line">
                                    <span class="count">2</span>
                                </div>
                                <span class="text text-xs fw-semibold">Employment Details</span>
                            </li>
                            <li class="form-wizard-list__item">
                                <div class="form-wizard-list__line">
                                    <span class="count">3</span>
                                </div>
                                <span class="text text-xs fw-semibold px-2">Employee
                                    Leaves</span>
                            </li>
                            <li class="form-wizard-list__item">
                                <div class="form-wizard-list__line">
                                    <span class="count">4</span>
                                </div>
                                <span class="text text-xs fw-semibold">Personal Information</span>
                            </li>

                            <li class="form-wizard-list__item">
                                <div class="form-wizard-list__line">
                                    <span class="count">5</span>
                                </div>
                                <span class="text text-xs fw-semibold">Employee Documents</span>
                            </li>


                            <li class="form-wizard-list__item">
                                <div class="form-wizard-list__line">
                                    <span class="count">6</span>
                                </div>
                                <span class="text text-xs fw-semibold">Bank Details</span>
                            </li>
{{--                            <li class="form-wizard-list__item">--}}
{{--                                <div class="form-wizard-list__line">--}}
{{--                                    <span class="count">7</span>--}}
{{--                                </div>--}}
{{--                                <span class="text text-xs fw-semibold">Completed</span>--}}
{{--                            </li>--}}
                        </ul>
                    </div>

                    {{-- Step 1: Employee Information --}}
                    <fieldset class="wizard-fieldset show">
                        <h6 class="text-md text-neutral-500">Employee Information</h6>
                        <div class="row gy-3">
                            <div class="col-sm-6">
                                <label class="form-label">FullName*</label>
                                <div class="position-relative">
                                    <input type="text" name="full_name" class="form-control wizard-required"
                                           value="{{ old('full_name', $employee->full_name ?? '') }}"
                                           placeholder="Enter Full Name" required>
                                    <div class="wizard-form-error"></div>
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Email*</label>
                                <div class="position-relative">
                                    <input type="email" name="email" class="form-control wizard-required"
                                           value="{{ old('email', $employee->email ?? '') }}"
                                           placeholder="Enter Email" required>
                                    <div class="wizard-form-error"></div>
                                </div>
                            </div>
{{--                            <div class="col-sm-6">--}}
{{--                                <label class="form-label">Password</label>--}}
{{--                                <div class="position-relative">--}}
{{--                                    <input type="password" name="password" class="form-control"--}}
{{--                                           placeholder="Enter New Password (leave blank to keep old)">--}}
{{--                                    <div class="wizard-form-error"></div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="col-sm-6">--}}
{{--                                <label class="form-label">Confirm Password</label>--}}
{{--                                <div class="position-relative">--}}
{{--                                    <input type="password" name="password_confirmation" class="form-control"--}}
{{--                                           placeholder="Confirm New Password">--}}
{{--                                    <div class="wizard-form-error"></div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
                            <div class="col-6">
                                <label class="form-label">Account Type*</label>
                                <div class="position-relative">
                                    <select name="account_type_id" id="account_type_id" class="form-control wizard-required" required>
                                        <option> -- Select Type --</option>
                                        @foreach($account_types as $account_type)
                                            <option value="{{ $account_type->id }}"
                                                {{ old('employment_type', $employee->account_type_id ?? '') == $account_type->id ? 'selected' : '' }}>
                                                {{ $account_type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="wizard-form-error"></div>
                                </div>
                            </div>
                            <div class="col-6 salary-related">
                                <label class="form-label">Basic Salary*</label>
                                <div class="position-relative">
                                    <input type="number" name="basic_salary" class="form-control wizard-required" value="{{ old('basic_salary',$employee->basic_salary ?? '') }}" placeholder="Enter Basic Salary" required>
                                    <div class="wizard-form-error"></div>
                                </div>
                                <span class="custom-validation text-danger small px-2"></span>
                            </div>

                            <div class="row salary-related">
                                <div class="col-4">
                                    <label class="form-label">Tax Slab's</label>
                                    <div class="position-relative single-form-select2">
                                        <select name="tax_slab_setting_id" id="tax_slab_setting_id" class="form-control">
                                            <option value="">-- Select Slab --</option>
                                            @foreach($tax_slabs as $tax_slab)
                                                <option value="{{ $tax_slab->id }}" data-rate="{{$tax_slab->rate}}" data-title="{{$tax_slab->title}}" data-type="{{$tax_slab->type}}"
                                                    {{ old('tax_slab_setting_id', $employee->tax_slab_setting_id ?? '') == $tax_slab->id ? 'selected' : '' }}>
                                                    {{ rtrim(rtrim(number_format($tax_slab->min_income, 2, '.', ''), '0'), '.') }}
                                                    -
                                                    {{ $tax_slab->max_income ? rtrim(rtrim(number_format($tax_slab->max_income, 2, '.', ''), '0'), '.') : '∞' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <label class="form-label">Slab Title</label>
                                    <div class="position-relative">
                                        <input type="text" name="title" id="tax_title" class="form-control "  value="{{ old('title', $employee->tax_slab->title ?? '') }}"  readonly>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <label class="form-label">Rate</label>
                                    <div class="position-relative">
                                        <input type="text" name="rate" id="tax_rate" class="form-control"
                                               value="{{ old('rate', isset($employee->tax_slab) ? $employee->tax_slab->rate . ($employee->tax_slab->type === 'percentage' ? '%' : '') : '') }}"
                                               readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <label>Profile Picture</label>
                                <div class="upload-image-wrapper d-flex align-items-center gap-3">
                                    <div class="uploaded-img position-relative h-120-px w-120-px border input-form-light radius-8 overflow-hidden border-dashed bg-neutral-50">
                                        <button type="button" class="uploaded-img__remove position-absolute top-0 end-0 z-1 text-2xxl line-height-1 me-8 mt-8 d-flex">
                                            <iconify-icon icon="radix-icons:cross-2" class="text-xl text-danger-600"></iconify-icon>
                                        </button>
                                        <img id="uploaded-img__preview"
                                             class="w-100 h-100 object-fit-cover"
                                             src="{{ $employee->profile_path ? asset($employee->profile_path) : asset('assets/images/default_images/profile_image.png') }}"
                                             alt="Profile Picture">
                                    </div>

                                    <label id="img_label"
                                           class="upload-file h-120-px w-120-px border input-form-light radius-8 overflow-hidden border-dashed bg-neutral-50 bg-hover-neutral-200 d-flex align-items-center flex-column justify-content-center gap-1"
                                           for="upload-file">
                                        <iconify-icon icon="solar:camera-outline" class="text-xl text-secondary-light"></iconify-icon>
                                        <span class="fw-semibold text-secondary-light">Upload</span>
                                        <input id="upload-file" type="file" name="profile_path" hidden>
                                    </label>
                                </div>
                            </div>


                            <div class="form-group d-flex align-items-center justify-content-end gap-8">
                                <button type="button" class="form-wizard-previous-btn btn btn-neutral-500 border-neutral-100 px-32">Back</button>
                                <button type="button" class="form-wizard-next-btn btn btn-primary-600 px-32">Next</button>
                            </div>
                        </div>
                    </fieldset>


                    {{-- Step 2: Employment Details --}}
                    <fieldset class="wizard-fieldset">
                        <h6 class="text-md text-neutral-500">Employment Details</h6>
                        <div class="row gy-3">
                            <div class="col-6">
                                <label class="form-label">Employee Code*</label>
                                <div class="position-relative">
                                    <input type="text" name="employee_code" class="form-control wizard-required"
                                           value="{{ old('employee_code', $employee->employee_code ?? '') }}"
                                           placeholder="Enter Employee Code" required>
                                    <div class="wizard-form-error"></div>
                                </div>
                            </div>

                            <div class="col-6">
                                <label class="form-label">Employment Type*</label>
                                <div class="position-relative">
                                    <select name="employment_type_id" id="employment_type" class="form-control wizard-required" required>
                                        <option> -- Select Type --</option>
                                        @foreach($employment_types as $employment_type)
                                            <option value="{{ $employment_type->id }}"
                                                {{ old('employment_type', $employee->employment_type_id ?? '') == $employment_type->id ? 'selected' : '' }}>
                                                {{ $employment_type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="wizard-form-error"></div>
                                </div>
                            </div>



                            <div class="col-6">
                                <label class="form-label">Department*</label>
                                <div class="position-relative single-form-select2">
                                    <select name="department_id" id="department_id" class="form-control  wizard-required">
                                        <option value="">-- Select Department --</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}"
                                                {{ old('department_id', $employee->department_id ?? '') == $department->id ? 'selected' : '' }}>
                                                {{ $department->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="wizard-form-error"></div>
                                </div>
                            </div>

                            <div class="col-6">
                                <label class="form-label">Designation*</label>
                                <div class="position-relative single-form-select2">
                                    <select name="designation_id" id="designation_id" class="form-control  wizard-required">
                                        <option value="">-- Select Designation --</option>
                                        @foreach($designations as $designation)
                                            <option value="{{ $designation->id }}"
                                                {{ old('designation_id', $employee->designation_id ?? '') == $designation->id ? 'selected' : '' }}>
                                                {{ $designation->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="wizard-form-error"></div>
                                </div>
                            </div>

                            <div class="col-6">
                                <label class="form-label">Joining Date *</label>
                                <div class="position-relative">
                                    <input type="date" name="joining_date" class="form-control wizard-required"
                                           value="{{ old('joining_date', $employee->joining_date ?? '') }}" required>
                                    <div class="wizard-form-error"></div>
                                </div>
                            </div>

                            <div class="col-6">
                                <label class="form-label">Shift*</label>
                                <div class="position-relative">
                                    <select name="shift_id" id="shift_id" class="form-control wizard-required shift_type" required>
                                        <option value="" disabled> -- Select Shift --</option>
                                        @foreach($shift_types as $shift_type)
                                            <option value="{{ $shift_type->id }}"
                                                    data-start="{{ $shift_type->shift_start }}"
                                                    data-end="{{ $shift_type->shift_end }}"
                                                {{ old('shift_id', $employee->shift_id ?? '') == $shift_type->id ? 'selected' : '' }}>
                                                {{ $shift_type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="wizard-form-error"></div>
                                </div>
                            </div>

                            <div class="col-6">
                                <label class="form-label">Shift Start</label>
                                <div class="position-relative">
                                    <input type="text" name="shift_start" id="shift_start" class="form-control  wizard-required"
                                           value="{{ old('shift_start', $employee->shift->shift_start ?? '') }}" readonly>
                                    <div class="wizard-form-error"></div>
                                </div>
                            </div>

                            <div class="col-6">
                                <label class="form-label">Shift End</label>
                                <div class="position-relative">
                                    <input type="text" name="shift_end" id="shift_end" class="form-control  wizard-required"
                                           value="{{ old('shift_end', $employee->shift->shift_end ?? '') }}" readonly>
                                    <div class="wizard-form-error"></div>
                                </div>
                            </div>

                            <div class="col-6 salary-related">
                                <label class="form-label">Gratuity*</label>
                                <div class="position-relative single-form-select2">
                                    <select name="gratuity_id" id="gratuity_id" class="form-control  wizard-required">
                                        <option value="">-- Select Gratuity --</option>
                                        @foreach($gratuties as $gratuity)
                                            <option value="{{ $gratuity->id }}"
                                                {{ old('gratuity_id', $employee->gratuity_id ?? '') == $gratuity->id ? 'selected' : '' }}>
                                                {{ $gratuity->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="wizard-form-error"></div>
                                </div>
                            </div>
                            <div class="col-6 salary-related">
                                <label class="form-label">Valid Gratuity Date*</label>
                                <div class="position-relative ">
                                    <input type="date" name="valid_gratuity_date" class="form-control wizard-required" value="{{ old('valid_gratuity_date', $employee->valid_gratuity_date ?? '') }}" >
                                    <div class="wizard-form-error"></div>
                                </div>
                                <span class="custom-validation text-danger small px-2"></span>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Commission*</label>
                                <div class="position-relative single-form-select2">
                                    <select name="commission_id" id="commission_id" class="form-control  wizard-required">
                                        <option value="">-- Select Commission --</option>
                                        @foreach($commissions as $commission)
                                            <option value="{{ $commission->id }}"
                                                {{ old('commission_id', $employee->commission_id ?? '') == $commission->id ? 'selected' : '' }}>
                                                {{ $commission->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="wizard-form-error"></div>
                                </div>
                            </div>

                            <div class="col-6">
                                <label class="form-label">Role*</label>
                                <div class="position-relative">
                                    <select name="role_id" id="role_id" class="form-control wizard-required" required>
                                        <option> -- Select Role -- </option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}"
                                                {{ old('role_id', $employee->role_id ?? '') == $role->id ? 'selected' : '' }}>
                                                {{ $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="wizard-form-error"></div>
                                </div>
                            </div>

                            <div class="col-6">
                                <label class="form-label">Employee Status*</label>
                                <div class="position-relative">
                                    <select name="employee_status_id" id="employee_status_id" class="form-control wizard-required" required>
                                        @foreach($employee_statuses as $employee_status)
                                            <option value="{{ $employee_status->id }}"
                                                {{ old('employee_status_id', $employee->employee_status_id ?? 1) == $employee_status->id ? 'selected' : '' }}>
                                                {{ $employee_status->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="wizard-form-error"></div>
                                </div>
                            </div>

                            <div class="col-6">
                                <label class="form-label">Excluded Holidays</label>
                                <select name="excluded_holiday_ids[]" id="excluded_holiday_id" multiple="multiple" class="form-control">
                                    <option value="" disabled hidden>-- Select Holiday --</option>
                                    @foreach($holidays as $holiday)
                                        <option value="{{ $holiday->id }}"
                                            {{ in_array($holiday->id, old('excluded_holiday_ids', $employee->excluded_holiday_ids ?? [])) ? 'selected' : '' }}>
                                            {{ $holiday->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-6">
                                <label class="form-label">Days</label>
                                <div class="align-items-center flex-wrap gap-28">
                                    @php
                                        $days = [
                                            'Monday' => 'switch-primary',
                                            'Tuesday' => 'switch-warning',
                                            'Wednesday' => 'switch-success',
                                            'Thursday' => 'switch-warning',
                                            'Friday' => 'switch-primary',
                                            'Saturday' => 'switch-success',
                                            'Sunday' => 'switch-info',
                                        ];

                                        $working_days = old('working_days', $employee->working_days ?? []);
                                    @endphp

                                    @foreach($days as $day => $class)
                                        <div class="form-switch {{ $class }} d-flex align-items-center gap-3 mb-3">
                                            <input type="hidden" name="working_days[{{ $day }}]" value="0">
                                            <input class="form-check-input" type="checkbox"
                                                   name="working_days[{{ $day }}]"
                                                   value="1" id="switch_{{ $day }}"
                                                {{ isset($working_days[$day]) && $working_days[$day] == 1 ? 'checked' : '' }}>
                                            <label class="form-check-label line-height-1 fw-medium text-secondary-light"
                                                   for="switch_{{ $day }}">{{ $day }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="form-group d-flex align-items-center justify-content-end gap-8">
                                <button type="button" class="form-wizard-previous-btn btn btn-neutral-500 border-neutral-100 px-32">Back</button>
                                <button type="button" class="form-wizard-next-btn btn btn-primary-600 px-32">Next</button>
                            </div>
                        </div>
                    </fieldset>

                    {{-- Step 3: Employee Leaves --}}
                    <fieldset class="wizard-fieldset">
                        <h6 class="text-md text-neutral-500">Leave Assignment</h6>
                        <div class="row gy-3">

                            {{--                            <div class="col-12">--}}
                            {{--                                <p class="fw-semibold mb-3">Assign Leave Quota for this Employee</p>--}}
                            {{--                            </div>--}}

                            <div class="col-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle">
                                        <thead>
                                        <tr>
                                            <th>Leave Type</th>
                                            <th>Allowed Quota (Days)</th>
                                            <th>Valid From</th>
                                            <th>Valid To</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($leave_types as $leave)
                                                @php
                                                    $assigned = $assignedLeaves[$leave->id] ?? null;
                                                @endphp
                                                <tr>
                                                    <td>
                                                        {{ $leave->name }}
                                                        <input type="hidden" name="leaves[{{ $leave->id }}][leave_type_id]" value="{{ $leave->id }}">
                                                    </td>
                                                    <td>
                                                        <input type="number"
                                                               name="leaves[{{ $leave->id }}][assigned_quota]"
                                                               class="form-control" min="0"
                                                               placeholder="Enter days"
                                                               value="{{ old("leaves.$leave->id.assigned_quota", $assigned->assigned_quota ?? '') }}"
                                                               required>
                                                    </td>
                                                    <td>
                                                        <input type="date"
                                                               name="leaves[{{ $leave->id }}][valid_from]"
                                                               class="form-control"
                                                               value="{{ old("leaves.$leave->id.valid_from", $assigned->valid_from ?? now()->format('Y-m-d')) }}"
                                                               required>
                                                    </td>
                                                    <td>
                                                        <input type="date"
                                                               name="leaves[{{ $leave->id }}][valid_to]"
                                                               class="form-control"
                                                               value="{{ old("leaves.$leave->id.valid_to", $assigned->valid_to ?? now()->endOfYear()->format('Y-m-d')) }}"
                                                               required>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="form-group d-flex align-items-center justify-content-end gap-8">
                                <button type="button" class="form-wizard-previous-btn btn btn-neutral-500 border-neutral-100 px-32">
                                    Back
                                </button>
                                <button type="button" class="form-wizard-next-btn btn btn-primary-600 px-32">
                                    Next
                                </button>
                            </div>
                        </div>
                    </fieldset>

                    {{-- Step 4: Personal Information (Edit Mode) --}}
                    <fieldset class="wizard-fieldset">
                        <h6 class="text-md text-neutral-500">Personal Information</h6>
                        <div class="row gy-3">
                            <div class="col-6">
                                <label class="form-label">Father Name</label>
                                <div class="position-relative">
                                    <input type="text" name="father_name" class="form-control  wizard-required"
                                           value="{{ old('father_name', $employee->father_name) }}">
                                    <div class="wizard-form-error"></div>
                                </div>
                            </div>

                            <div class="col-6">
                                <label class="form-label">Mother Name</label>
                                <div class="position-relative">
                                    <input type="text" name="mother_name" class="form-control  wizard-required"
                                           value="{{ old('mother_name', $employee->mother_name) }}">
                                    <div class="wizard-form-error"></div>
                                </div>

                            </div>

                            <div class="col-6">
                                <label class="form-label">CNIC</label>
                                <div class="position-relative">
                                    <input type="text" name="cnic" id="cnic" class="form-control wizard-required"
                                           value="{{ old('cnic', $employee->cnic) }}"
                                           placeholder="XXXXX-XXXXXXX-X" required>
                                    <div class="wizard-form-error"></div>
                                </div>

                            </div>

                            <div class="col-6">
                                <label class="form-label">DOB</label>
                                <div class="position-relative">
                                    <input type="date" name="dob" class="form-control wizard-required"
                                           value="{{ old('dob', $employee->dob) }}"
                                           max="{{ date('Y-m-d') }}" required>
                                    <div class="wizard-form-error"></div>
                                </div>

                            </div>

                            <div class="col-6">
                                <label class="form-label">Gender</label>
                                <div class="position-relative">
                                    <select name="gender" class="form-control wizard-required" required>
                                        <option value="">Select Gender</option>
                                        <option value="male" {{ old('gender', $employee->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender', $employee->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="other" {{ old('gender', $employee->gender) == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    <div class="wizard-form-error"></div>
                                </div>
                            </div>

                            <div class="col-6">
                                <label class="form-label">Marital Status</label>
                                <div class="position-relative">
                                    <select name="marital_status" class="form-control wizard-required" required>
                                        <option value="">Select Marital Status</option>
                                        <option value="single" {{ old('marital_status', $employee->marital_status) == 'single' ? 'selected' : '' }}>Single</option>
                                        <option value="married" {{ old('marital_status', $employee->marital_status) == 'married' ? 'selected' : '' }}>Married</option>
                                        <option value="divorced" {{ old('marital_status', $employee->marital_status) == 'divorced' ? 'selected' : '' }}>Divorced</option>
                                        <option value="widowed" {{ old('marital_status', $employee->marital_status) == 'widowed' ? 'selected' : '' }}>Widowed</option>
                                    </select>
                                    <div class="wizard-form-error"></div>
                                </div>

                            </div>

                            <div class="col-6">
                                <label class="form-label">Children's</label>
{{--                                <div class="position-relative">--}}
                                    <input type="number" name="kids_count" class="form-control"
                                           value="{{ old('kids_count', $employee->kids_count == 0 ? '' : $employee->kids_count ) }}">
{{--                                    <div class="wizard-form-error"></div>--}}
{{--                                </div>--}}

                            </div>

                            <div class="col-6">
                                <label class="form-label">Skills</label>
{{--                                <div class="position-relative">--}}
                                    <textarea name="skills" class="form-control">{{ old('skills', $employee->skills) }}</textarea>
{{--                                    <div class="wizard-form-error"></div>--}}
{{--                                </div>--}}

                            </div>

                            <div class="col-6">
                                <label class="form-label">Phone</label>
                                <div class="position-relative">
                                    <input type="text" name="phone" id="phone" class="form-control wizard-required"
                                           value="{{ old('phone', $employee->phone) }}" placeholder="e.g. 03XXXXXXXXX" required>
                                    <div class="wizard-form-error"></div>
                                </div>
                            </div>

                            <div class="col-6">
                                <label class="form-label">Phone2</label>
{{--                                <div class="position-relative">--}}
                                    <input type="text" name="phone2" id="phone2" class="form-control"
                                           value="{{ old('phone2', $employee->phone2) }}" placeholder="e.g. 03XXXXXXXXX">
{{--                                    <div class="wizard-form-error"></div>--}}
{{--                                </div>--}}
                            </div>

                            <div class="col-6">
                                <label class="form-label">Contact Person</label>
                                <div class="position-relative">
                                    <input type="text" name="contact_person" class="form-control wizard-required"
                                           value="{{ old('contact_person', $employee->contact_person) }}" required>
                                    <div class="wizard-form-error"></div>
                                </div>
                            </div>

                            <div class="col-6">
                                <label class="form-label">Emergency Contact Phone</label>
                                <div class="position-relative">
                                    <input type="text" name="emergency_contact"  id="emergency_contact" class="form-control wizard-required"
                                           value="{{ old('emergency_contact', $employee->emergency_contact) }}"
                                           placeholder="e.g. 03XXXXXXXXX" required>
                                    <div class="wizard-form-error"></div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <div class="position-relative">
                                    <input type="text" name="address" class="form-control wizard-required"
                                           value="{{ old('address', $employee->address) }}" required>
                                    <div class="wizard-form-error"></div>
                                </div>

                            </div>

                            <div class="col-4">
                                <label class="form-label">City</label>
                                <div class="position-relative">
                                    <input type="text" name="city" class="form-control wizard-required"
                                           value="{{ old('city', $employee->city) }}" required>
                                    <div class="wizard-form-error"></div>
                                </div>

                            </div>

                            <div class="col-4">
                                <label class="form-label">State</label>
                                <div class="position-relative">
                                    <input type="text" name="state" class="form-control wizard-required"
                                           value="{{ old('state', $employee->state) }}" required>
                                    <div class="wizard-form-error"></div>
                                </div>

                            </div>

                            <div class="col-4">
                                <label class="form-label">Country</label>
                                <div class="position-relative">
                                    <input type="text" name="country" class="form-control wizard-required"
                                           value="{{ old('country', $employee->country) }}" required>
                                    <div class="wizard-form-error"></div>
                                </div>

                            </div>

                            <div class="form-group d-flex align-items-center justify-content-end gap-8">
                                <button type="button" class="form-wizard-previous-btn btn btn-neutral-500 border-neutral-100 px-32">Back</button>
                                <button type="button" class="form-wizard-next-btn btn btn-primary-600 px-32">Next</button>
                            </div>
                        </div>
                    </fieldset>


                    {{-- Step 5: Employee Documents --}}
                    <fieldset class="wizard-fieldset">
                        <h6 class="text-md text-neutral-500">Employee Documents</h6>
                        <div class="row gy-3">
                            @foreach($document_types as $doc)
                                @php
                                    // Check if the employee has this document uploaded, safely
                                    $existingDoc = $employee->documents->firstWhere('document_setting_id', $doc->id) ?? null;
                                @endphp
                                <div class="col-6">
                                    <label class="form-label">
                                        {{ $doc->title }}
                                        @if($doc->is_required)
                                            <span class="text-danger">*</span>
                                        @endif
                                    </label>
                                    <div class="position-relative">
                                        @if($doc->input_type === 'file')
                                            <input type="file" name="documents[{{ $doc->id }}]"
                                                   class="form-control"
                                                   @if($doc->is_required && !$existingDoc) required @endif>
                                        @elseif($doc->input_type === 'text')
                                            <input type="text" name="documents[{{ $doc->id }}]"
                                                   class="form-control"
                                                   value="{{ old('documents.'.$doc->id, $existingDoc->file_name ?? '') }}"
                                                   @if($doc->is_required && !$existingDoc) required @endif>>
                                        @endif
                                        <div class="wizard-form-error"></div>
                                    </div>
                                    @if($doc->description)
                                        <small class="text-muted">{{ $doc->description }}</small>
                                    @endif
                                </div>
                            @endforeach
                            <div class="form-group d-flex align-items-center justify-content-end gap-8">
                                <button type="button" class="form-wizard-previous-btn btn btn-neutral-500 border-neutral-100 px-32">Back</button>
                                <button type="button" class="form-wizard-next-btn btn btn-primary-600 px-32">Next</button>
                            </div>
                        </div>
                    </fieldset>

                    {{-- Step 6: Bank Details (Edit Mode) --}}
                    <fieldset class="wizard-fieldset">
                        <h6 class="text-md text-neutral-500">Bank Details</h6>
                        <div class="row gy-3">
                            <div class="col-6">
                                <label class="form-label">Bank Name</label>
                                <div class="position-relative">
                                    <input type="text" name="bank_name" class="form-control"
                                           value="{{ old('bank_name', optional($employee->bankDetail)->bank_name) }}">
                                    <div class="wizard-form-error"></div>
                                </div>
                            </div>

                            <div class="col-6">
                                <label class="form-label">Account Title</label>
                                <div class="position-relative">
                                    <input type="text" name="account_title" class="form-control"
                                           value="{{ old('account_title', optional($employee->bankDetail)->account_title) }}">
                                    <div class="wizard-form-error"></div>
                                </div>
                            </div>

                            <div class="col-6">
                                <label class="form-label">Account Number</label>
                                <div class="position-relative">
                                    <input type="text" name="account_number" class="form-control"
                                           value="{{ old('account_number', optional($employee->bankDetail)->account_number) }}">
                                    <div class="wizard-form-error"></div>
                                </div>
                            </div>

                            <div class="col-6">
                                <label class="form-label">IBAN</label>
                                <div class="position-relative">
                                    <input type="text" name="iban" class="form-control"
                                           value="{{ old('iban', optional($employee->bankDetail)->iban) }}">
                                    <div class="wizard-form-error"></div>
                                </div>
                            </div>

                            <div class="form-group d-flex align-items-center justify-content-end gap-8">
                                <button type="button" class="form-wizard-previous-btn btn btn-neutral-500 border-neutral-100 px-32">Back</button>
                                <button type="submit" class="btn btn-success-600 px-32">Update Employee</button>
                            </div>
                        </div>
                    </fieldset>

{{--                    --}}{{-- Step 7: Completed --}}
{{--                    <fieldset class="wizard-fieldset">--}}
{{--                        <div class="text-center mb-40">--}}
{{--                            <img src="assets/images/gif/success-img3.gif" alt="" class="gif-image mb-24">--}}
{{--                            <h6 class="text-md text-neutral-600">Congratulations</h6>--}}
{{--                            <p class="text-neutral-400 text-sm mb-0">Well done! You have successfully completed.</p>--}}
{{--                        </div>--}}
{{--                        <div class="form-group d-flex align-items-center justify-content-end gap-8">--}}
{{--                            <button type="button" class="form-wizard-previous-btn btn btn-neutral-500 border-neutral-100 px-32">Back</button>--}}
{{--                            <button type="submit" class="form-wizard-submit btn btn-primary-600 px-32">Publish</button>--}}
{{--                        </div>--}}
{{--                    </fieldset>--}}
                </form>
            </div>


        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/vendor/datatable/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatable/js/dataTables.bootstrap5.min.js') }}"></script>

    <script>

        $(document).ready(function() {


            function toggleSalaryFields() {
                var selectedType = parseInt($('#account_type_id').val());

                if (selectedType === 2) {
                    // Hide and remove required
                    $('.salary-related').hide();
                    $('.salary-related').find('input, select').each(function() {
                        $(this).removeAttr('required').removeClass('wizard-required');
                    });

                    // Set Basic Salary = 1
                    $('#basic_salary').val(1);
                } else {
                    // Show and restore required
                    $('.salary-related').show();
                    $('.salary-related').find('input, select').each(function() {
                        $(this).attr('required', 'required').addClass('wizard-required');
                    });
                }
            }

            // Run on page load (edit case)
            toggleSalaryFields();

            $('#account_type_id').on('change', function() {
                toggleSalaryFields();
            });

            // $("form").on("submit", function(e){
            //     if(this.checkValidity() === false){
            //         e.preventDefault();
            //         e.stopImmediatePropagation();
            //         $(this).addClass("was-validated");
            //         return false;
            //     }
            // });

            function toggleKids() {
                if ($("#marital_status").val() === "single") {
                    $("#kids_count").prop("disabled", true).val('');
                } else {
                    $("#kids_count").prop("disabled", false);
                }
            }
            toggleKids(); // page load par bhi check kare


            $("#marital_status").on("change", toggleKids);

            function digitMask(selector) {
                $(selector).on("input", function () {
                    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);
                });
            }
            digitMask("#phone");
            digitMask("#phone2");
            digitMask("#emergency_contact");

            function cnicMask(selector) {
                $(selector).on("input", function () {
                    let value = this.value.replace(/[^0-9]/g, ''); // sirf digits allow
                    if (value.length > 13) value = value.slice(0, 13); // max 13 digits

                    // formatted as 5-7-1 pattern
                    if (value.length > 5 && value.length <= 12) {
                        value = value.slice(0, 5) + '-' + value.slice(5, 12) + (value.length > 12 ? '-' + value.slice(12) : '');
                    } else if (value.length > 5) {
                        value = value.slice(0, 5) + '-' + value.slice(5, 12) + '-' + value.slice(12);
                    }
                    this.value = value;
                });
            }

            cnicMask("#cnic");


            $('#excluded_holiday_id').select2({
                placeholder: "-- Select Holiday --",
                allowClear: true,
                width: '100%' // force full width
            });

            // Add "Select All" option dynamically if not exists
            if ($('#excluded_holiday_id option[value="select_all"]').length === 0) {
                $('#excluded_holiday_id').prepend('<option value="select_all">Select All</option>');
            }

            // Select All logic
            $('#excluded_holiday_id').on('select2:select', function(e) {
                if (e.params.data.id === 'select_all') {
                    let allValues = [];
                    $('#excluded_holiday_id option').each(function() {
                        let val = $(this).val();
                        if (val !== 'select_all' && val !== '') {
                            allValues.push(val);
                        }
                    });
                    $('#excluded_holiday_id').val(allValues).trigger('change');
                }
            });

            $('#department_id').select2({
                placeholder: "-- Select Department --",
                allowClear: true,
                width: '100%' // force full width
            });

            $('#tax_slab_setting_id').select2({
                placeholder: "-- Select Slab --",
                allowClear: true,
                width: '100%' // force full width
            }).bind('change',function (){
                var selectedOption = $(this).find('option:selected');
                if(selectedOption.val() === '') {
                    $("#tax_title").val('');
                    $("#tax_rate").val('');
                    return;
                }

                var rate  = selectedOption.data('rate');
                var title = selectedOption.data('title');
                var percent = selectedOption.data('type');
                if (percent === 'percentage') {
                    $("#tax_rate").val(rate + '%');
                } else {
                    $("#tax_rate").val(rate);
                }
                $("#tax_title").val(title);


            });

            $('#designation_id').select2({
                placeholder: "-- Select Designation --",
                allowClear: true,
                width: '100%' // force full width
            });
            $('#gratuity_id').select2({
                placeholder: "-- Select Gratuity --",
                allowClear: true,
                width: '100%' // force full width
            });
            $('#commission_id').select2({
                placeholder: "-- Select Commission --",
                allowClear: true,
                width: '100%' // force full width
            });

            $("#account_type_id").on('change', function () {
                let accountType = $(this).val();

                // Reset commission selection
                $('#commission_id').val(null).trigger('change');

                if (accountType == 2 || accountType == 3) {
                    // Commission Only or Salary + Commission → enable & wizard-required
                    $('#commission_id').prop('disabled', false).addClass('wizard-required');
                } else {
                    // Salary Only → disable & remove wizard-required
                    $('#commission_id').prop('disabled', true).removeClass('wizard-required');
                }
            });


            // click on next button
            $('.form-wizard-next-btn').on("click", function() {
                var parentFieldset = $(this).parents('.wizard-fieldset');
                var currentActiveStep = $(this).parents('.form-wizard').find('.form-wizard-list .active');
                var next = $(this);
                var nextWizardStep = true;
                parentFieldset.find('.wizard-required').each(function(){
                    // debugger
                    var thisValue = $(this).val();
                    if( thisValue == "") {
                        $(this).siblings(".wizard-form-error").show();
                        nextWizardStep = false;
                    }
                    else  {
                        $(this).siblings(".wizard-form-error").hide();
                    }
                    if($(this).is("#phone,#emergency_contact")) {

                        if(thisValue.length !== 11) {
                            $(this).siblings(".wizard-form-error").show();
                            // $(this).siblings(".wizard-form-error").text("Must be 13 digits").show();
                            nextWizardStep = false;
                        }
                    }

                    if($(this).is("#cnic")) {
                        if(thisValue.length !== 15) {
                            $(this).siblings(".wizard-form-error").show();
                            nextWizardStep = false;
                        }
                    }
                });
                if( nextWizardStep) {
                    next.parents('.wizard-fieldset').removeClass("show","400");
                    currentActiveStep.removeClass('active').addClass('activated').next().addClass('active',"400");
                    next.parents('.wizard-fieldset').next('.wizard-fieldset').addClass("show","400");
                    $(document).find('.wizard-fieldset').each(function(){
                        if($(this).hasClass('show')){
                            var formAtrr = $(this).attr('data-tab-content');
                            $(document).find('.form-wizard-list .form-wizard-step-item').each(function(){
                                if($(this).attr('data-attr') == formAtrr){
                                    $(this).addClass('active');
                                    var innerWidth = $(this).innerWidth();
                                    var position = $(this).position();
                                    $(document).find('.form-wizard-step-move').css({"left": position.left, "width": innerWidth});
                                }else{
                                    $(this).removeClass('active');
                                }
                            });
                        }
                    });
                }
            });
            //click on previous button
            $('.form-wizard-previous-btn').on("click",function() {
                var counter = parseInt($(".wizard-counter").text());;
                var prev =$(this);
                var currentActiveStep = $(this).parents('.form-wizard').find('.form-wizard-list .active');
                prev.parents('.wizard-fieldset').removeClass("show","400");
                prev.parents('.wizard-fieldset').prev('.wizard-fieldset').addClass("show","400");
                currentActiveStep.removeClass('active').prev().removeClass('activated').addClass('active',"400");
                $(document).find('.wizard-fieldset').each(function(){
                    if($(this).hasClass('show')){
                        var formAtrr = $(this).attr('data-tab-content');
                        $(document).find('.form-wizard-list .form-wizard-step-item').each(function(){
                            if($(this).attr('data-attr') == formAtrr){
                                $(this).addClass('active');
                                var innerWidth = $(this).innerWidth();
                                var position = $(this).position();
                                $(document).find('.form-wizard-step-move').css({"left": position.left, "width": innerWidth});
                            }else{
                                $(this).removeClass('active');
                            }
                        });
                    }
                });
            });
            //click on form submit button
            $(document).on("click",".form-wizard .form-wizard-submit" , function(){
                var parentFieldset = $(this).parents('.wizard-fieldset');
                var currentActiveStep = $(this).parents('.form-wizard').find('.form-wizard-list .active');
                parentFieldset.find('.wizard-required').each(function() {
                    var thisValue = $(this).val();
                    if( thisValue == "" ) {
                        $(this).siblings(".wizard-form-error").show();
                    }
                    else {
                        $(this).siblings(".wizard-form-error").hide();
                    }
                });
            });
            // focus on input field check empty or not
            $(".form-control").on('focus', function(){
                var tmpThis = $(this).val();
                if(tmpThis == '' ) {
                    $(this).parent().addClass("focus-input");
                }
                else if(tmpThis !='' ){
                    $(this).parent().addClass("focus-input");
                }
            }).on('blur', function(){
                var tmpThis = $(this).val();
                if(tmpThis == '' ) {
                    $(this).parent().removeClass("focus-input");
                    $(this).siblings(".wizard-form-error").show();
                }
                else if(tmpThis !='' ){
                    $(this).parent().addClass("focus-input");
                    $(this).siblings(".wizard-form-error").hide();
                }
            });

            $(".shift_type").on('change',function (){
                var selectedOption = $(this).find('option:selected');
                var startTime = selectedOption.data('start') || '';
                var endTime = selectedOption.data('end') || '';

                $('#shift_start').val(startTime);
                $('#shift_end').val(endTime);
            });
        });
        // =============================== Wizard Step Js End ================================

        // File Upload Preview Logic
        const fileInput = document.getElementById("upload-file");
        const imagePreview = document.getElementById("uploaded-img__preview");
        const uploadedImgContainer = document.querySelector(".uploaded-img");
        const removeButton = document.querySelector(".uploaded-img__remove");
        const imgLabel = document.getElementById("img_label");

        fileInput.addEventListener("change", (e) => {
            if (e.target.files.length) {
                const src = URL.createObjectURL(e.target.files[0]);
                imagePreview.src = src;
                uploadedImgContainer.classList.remove('d-none');
                imgLabel.classList.add('d-none');
            }
        });
        removeButton.addEventListener("click", () => {
            imagePreview.src = "{{ asset('assets/images/user.png') }}";
            uploadedImgContainer.classList.add('d-none');
            fileInput.value = "";
            imgLabel.classList.remove('d-none')
        });

        // ── Tab Error Highlighting ─────────────────────────────────────────
        function navigateToFieldsetIndex(idx) {
            var $fieldsets = $('.wizard-fieldset');
            var $steps     = $('.form-wizard-list__item');
            $fieldsets.removeClass('show');
            $fieldsets.eq(idx).addClass('show');
            $steps.each(function(i) {
                $(this).removeClass('active activated');
                if (i < idx)      $(this).addClass('activated');
                else if (i === idx) $(this).addClass('active');
            });
        }

        function markTabError(idx) {
            var $count = $('.form-wizard-list__item').eq(idx).find('.count');
            $count.css({ background: '#dc3545', color: '#fff', 'border-color': '#dc3545' });
        }

        // On form submit: find invalid required fields across ALL fieldsets,
        // highlight the tab and navigate to the first one so the browser can focus.
        $('form.form-select-2').on('submit', function(e) {
            var $fieldsets     = $(this).find('.wizard-fieldset');
            var firstInvalidIdx = -1;

            // Reset any previous error coloring
            $('.form-wizard-list__item .count').css({ background: '', color: '', 'border-color': '' });

            $fieldsets.each(function(idx) {
                var hasInvalid = false;
                $(this).find('[required]').each(function() {
                    if (!this.validity.valid) { hasInvalid = true; return false; }
                });
                if (hasInvalid) {
                    markTabError(idx);
                    if (firstInvalidIdx === -1) firstInvalidIdx = idx;
                }
            });

            // If the first invalid fieldset is NOT the currently visible one, navigate there
            if (firstInvalidIdx !== -1 && !$fieldsets.eq(firstInvalidIdx).hasClass('show')) {
                e.preventDefault();
                navigateToFieldsetIndex(firstInvalidIdx);
                // Give the DOM a tick to show the fieldset, then let the browser validate
                var $inv = $fieldsets.eq(firstInvalidIdx).find('[required]').filter(function() {
                    return !this.validity.valid;
                }).first()[0];
                if ($inv) setTimeout(function() { $inv.reportValidity(); }, 60);
            }
        });

        // On page load: if Laravel returned validation errors, highlight the
        // affected tabs and jump to the first one with errors.
        @if($errors->any())
        (function() {
            var errorKeys  = @json($errors->keys()); // e.g. ["leaves.4.assigned_quota", "full_name"]
            var $fieldsets = $('.wizard-fieldset');
            var firstErrIdx = -1;

            $fieldsets.each(function(idx) {
                var hasErr = false;
                $(this).find('input, select, textarea').each(function() {
                    var name = $(this).attr('name');
                    if (!name) return;
                    // Normalise bracket notation → dot notation for comparison
                    var normalised = name.replace(/\[(\w+)\]/g, '.$1').replace(/^\.|\.$/g, '');
                    for (var k = 0; k < errorKeys.length; k++) {
                        if (normalised === errorKeys[k] || normalised.indexOf(errorKeys[k]) === 0 || errorKeys[k].indexOf(normalised) === 0) {
                            hasErr = true;
                            $(this).addClass('is-invalid');
                            return false;
                        }
                    }
                });
                if (hasErr) {
                    markTabError(idx);
                    if (firstErrIdx === -1) firstErrIdx = idx;
                }
            });

            if (firstErrIdx !== -1) navigateToFieldsetIndex(firstErrIdx);
        })();
        @endif
    </script>
@endpush
