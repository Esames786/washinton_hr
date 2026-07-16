@extends('layout.master')
@section('pageName', 'Add Subcontractor')

@push('cssLinks')
<style>
    .custom-validation {
        display: none;         /* default hidden */
        position: static;      /* inline flow me rakho */
        margin-top: 4px;
        font-size: 0.875rem;
        color: #dc3545;        /* bootstrap danger red */
    }
    .custom-validation.show-error {
        display: block;
    }
</style>
@endpush

@section('content')
    @include('partials.alerts')

    <div class="card h-100">
        <div class="card-body">
            <div class="form-wizard">
                {{-- #0 (Batch 6): novalidate so native HTML validation doesn't block Publish on
                     required fields inside hidden wizard steps ("not focusable"). Wizard JS + server
                     validation still enforce required rules. --}}
                <form action="{{ route('admin.employees.store') }}" method="POST" enctype="multipart/form-data" class="form-select-2" novalidate>
                    @csrf

                    <div class="form-wizard-header overflow-x-auto scroll-sm pb-8 my-32">
                        <ul class="list-unstyled form-wizard-list style-two">
                            <li class="form-wizard-list__item active">
                                <div class="form-wizard-list__line">
                                    <span class="count">1</span>
                                </div>
                                <span class="text text-xs fw-semibold">Subcontractor Information</span>
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
                                <span class="text text-xs fw-semibold px-2">Subcontractor Leaves</span>
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
                                <span class="text text-xs fw-semibold">Subcontractor Documents</span>
                            </li>


                            <li class="form-wizard-list__item">
                                <div class="form-wizard-list__line">
                                    <span class="count">6</span>
                                </div>
                                <span class="text text-xs fw-semibold">Bank Details</span>
                            </li>
                            <li class="form-wizard-list__item">
                                <div class="form-wizard-list__line">
                                    <span class="count">7</span>
                                </div>
                                <span class="text text-xs fw-semibold">Review</span>
                            </li>
                            <li class="form-wizard-list__item">
                                <div class="form-wizard-list__line">
                                    <span class="count">8</span>
                                </div>
                                <span class="text text-xs fw-semibold">Completed</span>
                            </li>
                        </ul>
                    </div>

                    {{-- Step 1: Subcontractor Information --}}
                    <fieldset class="wizard-fieldset show">
                        <h6 class="text-md text-neutral-500">Subcontractor Information</h6>
                        <div class="row gy-2">
                            <div class="col-sm-6">
                                <label class="form-label">First Name*</label>
                                <div class="position-relative">
                                    <input type="text" name="first_name" class="form-control wizard-required" value="{{ old('first_name') }}" placeholder="Enter First Name" required>
                                    <div class="wizard-form-error"></div>
                                </div>
                                <span class="custom-validation text-danger small px-2"></span>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Last Name*</label>
                                <div class="position-relative">
                                    <input type="text" name="last_name" class="form-control wizard-required" value="{{ old('last_name') }}" placeholder="Enter Last Name" required>
                                    <div class="wizard-form-error"></div>
                                </div>
                                <span class="custom-validation text-danger small px-2"></span>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Email*</label>
                                <div class="position-relative">
                                    <input type="email" name="email" class="form-control wizard-required" value="{{ old('email') }}" placeholder="Enter Email" required>
                                    <div class="wizard-form-error"></div>
                                </div>
                                <span class="custom-validation text-danger small px-2"></span>
                            </div>
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
                                    <input type="number" name="basic_salary" class="form-control wizard-required" value="{{ old('basic_salary') }}" placeholder="Enter Basic Salary" required>
                                    <div class="wizard-form-error"></div>
                                </div>
                                <span class="custom-validation text-danger small px-2"></span>
                            </div>
                            <div class="col-6 salary-related tax-field">
                                <label class="form-label">Tax Slab's</label>
                                <div class="position-relative single-form-select2">
                                    <select name="tax_slab_setting_id" id="tax_slab_setting_id"  class="form-control">
                                        <option value="">-- Select Slab --</option>
                                        @foreach($tax_slabs as $tax_slab)
                                            <option value="{{ $tax_slab->id }}" data-rate="{{$tax_slab->rate}}" data-title="{{$tax_slab->title}}" data-type="{{$tax_slab->type}}">
                                                {{ rtrim(rtrim(number_format($tax_slab->min_income, 2, '.', ''), '0'), '.') }}
                                                -
                                                {{ $tax_slab->max_income ? rtrim(rtrim(number_format($tax_slab->max_income, 2, '.', ''), '0'), '.') : '∞' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                                <div class="col-6 salary-related tax-field">
                                    <label class="form-label">Slab Title</label>
                                    <div class="position-relative">
                                        <input type="text" id="tax_title" class="form-control wizard-required"  readonly>
                                    </div>
                                </div>
                                <div class="col-6 salary-related tax-field">
                                    <label class="form-label">Rate</label>
                                    <div class="position-relative">
                                        <input type="text" id="tax_rate" class="form-control wizard-required" readonly>
                                    </div>
                                </div>
                            <div class="col-sm-6">
                                <label class="form-label">Password*</label>
                                <div class="position-relative">
                                    <input type="password" name="password" class="form-control wizard-required" placeholder="Enter Password" required>
                                    <div class="wizard-form-error"></div>
                                </div>
                                <span class="custom-validation text-danger small px-2"></span>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Confirm Password*</label>
                                <div class="position-relative">
                                    <input type="password" name="password_confirmation" class="form-control wizard-required" placeholder="Confirm Password" required>
                                    <div class="wizard-form-error"></div>
                                </div>
                                <span class="custom-validation text-danger small px-2"></span>
                            </div>
                            <div class="col-sm-12">
                                <label>Profile Picture</label>
                                <div class="upload-image-wrapper d-flex align-items-center gap-3">
                                    <div class="uploaded-img d-none position-relative h-120-px w-120-px border input-form-light radius-8 overflow-hidden border-dashed bg-neutral-50">
                                        <button type="button" class="uploaded-img__remove position-absolute top-0 end-0 z-1 text-2xxl line-height-1 me-8 mt-8 d-flex">
                                            <iconify-icon icon="radix-icons:cross-2" class="text-xl text-danger-600"></iconify-icon>
                                        </button>
                                        <img id="uploaded-img__preview" class="w-100 h-100 object-fit-cover" src="{{ asset('assets/images/user.png') }}" alt="image" >
                                    </div>

                                    <label id="img_label" class="upload-file h-120-px w-120-px border input-form-light radius-8 overflow-hidden border-dashed bg-neutral-50 bg-hover-neutral-200 d-flex align-items-center flex-column justify-content-center gap-1" for="upload-file">
                                        <iconify-icon icon="solar:camera-outline" class="text-xl text-secondary-light"></iconify-icon>
                                        <span class="fw-semibold text-secondary-light">Upload</span>
                                            <input id="upload-file" type="file" name="profile_path" class="wizard-required" hidden>
                                    </label>
                                </div>
                                <span class="custom-validation text-danger small mt-1 px-2"></span>
                            </div>
                            <div class="form-group text-end">
                                <button type="button" class="form-wizard-next-btn btn btn-primary-600 px-32">Next</button>
                            </div>
                        </div>
                    </fieldset>

                    {{-- Step 2: Employment Details --}}
                    <fieldset class="wizard-fieldset">
                        <h6 class="text-md text-neutral-500">Employment Details</h6>
                        <div class="row gy-2">
                            <div class="col-6">
                                <label class="form-label">Subcontractor Code*</label>
                                <div class="position-relative">
                                    <input type="text" name="employee_code" class="form-control wizard-required" value="{{ old('employee_code') }}" placeholder="Enter Subcontractor Code" required>
                                    <div class="wizard-form-error"></div>
                                </div>
                                <span class="custom-validation text-danger small px-2"></span>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Employment Type*</label>
                                <div class="position-relative">
                                    <select name="employment_type" id="employment_type" class="form-control wizard-required" required>
                                        <option value=""> -- Select Type --</option>
                                        @foreach($employment_types as $employment_type)
                                            <option value="{{ $employment_type->id }}">{{ $employment_type->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="wizard-form-error"></div>
                                </div>
                                <span class="custom-validation text-danger small px-2"></span>
                            </div>
                            {{-- #21: In-house vs Subcontractor. Subcontractors get NO Leaves / Gratuity / Tax. --}}
                            <div class="col-6">
                                <label class="form-label">Worker Type*</label>
                                <div class="position-relative">
                                    <select name="worker_type" id="worker_type" class="form-control wizard-required" required>
                                        <option value="subcontractor" {{ old('worker_type', 'subcontractor') === 'subcontractor' ? 'selected' : '' }}>Subcontractor</option>
                                        <option value="inhouse" {{ old('worker_type') === 'inhouse' ? 'selected' : '' }}>In-house Employee</option>
                                    </select>
                                    <div class="wizard-form-error"></div>
                                </div>
                                <small class="text-neutral-400 px-2">In-house employees get Leaves, Gratuity &amp; Tax; subcontractors do not.</small>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Department*</label>
                                <div class="position-relative single-form-select2">
                                    <select name="department_id" id="department_id"  class="form-control wizard-required" required>
                                        <option value="">-- Select Department --</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="wizard-form-error"></div>
                                </div>
                                <span class="custom-validation text-danger small px-2"></span>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Designation*</label>
                                <div class="position-relative single-form-select2 ">
                                    <select name="designation_id" id="designation_id"  class="form-control wizard-required" required>
                                        <option value="">-- Select Designation --</option>
                                        @foreach($designations as $designation)
                                            <option value="{{ $designation->id }}">{{ $designation->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="wizard-form-error"></div>
                                </div>
                                <span class="custom-validation text-danger small px-2"></span>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Joining Date *</label>
                                <div class="position-relative">
                                    <input type="date" name="joining_date" class="form-control wizard-required" value="{{ old('joining_date') }}" required>
                                    <div class="wizard-form-error"></div>
                                </div>
                                <span class="custom-validation text-danger small px-2"></span>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Shift*</label>
                                <div class="position-relative">
                                    <select name="shift_id" id="shift_id" class="form-control wizard-required shift_type" required>
                                        <option value="" > -- Select Shift --</option>
                                        @foreach($shift_types as $shift_type)
                                            <option value="{{ $shift_type->id }}" data-start="{{$shift_type->shift_start}}" data-end="{{$shift_type->shift_end}}">{{ $shift_type->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="wizard-form-error"></div>
                                </div>
                                <span class="custom-validation text-danger small px-2"></span>
                            </div>

                            <div class="col-6">
                                <label class="form-label">Shift Start</label>
                                <div class="position-relative">
                                    <input type="text" name="shift_start" id="shift_start" class="form-control " readonly>
                                    <div class="wizard-form-error"></div>
                                </div>
                                <span class="custom-validation text-danger small px-2"></span>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Shift End</label>
                                <div class="position-relative">
                                    <input type="text" name="shift_end" id="shift_end" class="form-control"  readonly>
                                    <div class="wizard-form-error"></div>
                                </div>
                            </div>
                            {{-- B6: Gratuity is optional per subcontractor. Unchecking hides + un-requires
                                 the Gratuity fields and submits gratuity_enabled=0 so the server skips them. --}}
                            <div class="col-12 salary-related">
                                <input type="hidden" name="gratuity_enabled" id="gratuity_enabled" value="1">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="gratuity_enabled_toggle" checked>
                                    <label class="form-check-label fw-medium" for="gratuity_enabled_toggle">
                                        Apply Gratuity for this subcontractor
                                    </label>
                                </div>
                            </div>
                            <div class="col-6 salary-related gratuity-field">
                                <label class="form-label">Gratuity*</label>
                                <div class="position-relative single-form-select2">
                                    <select name="gratuity_id" id="gratuity_id"  class="form-control wizard-required" required>
                                        <option value="">-- Select Gratuity --</option>
                                        @foreach($gratuties as $gratuity)
                                            <option value="{{ $gratuity->id }}">{{ $gratuity->title }}</option>
                                        @endforeach
                                    </select>
                                    <div class="wizard-form-error"></div>
                                </div>
                                <span class="custom-validation text-danger small px-2"></span>
                            </div>
                            <div class="col-6 salary-related gratuity-field">
                                <label class="form-label">Valid Gratuity Date*</label>
                                <div class="position-relative ">
                                    <input type="date" name="valid_gratuity_date" class="form-control wizard-required" min="{{ date('Y-m-d') }}" value="{{ old('valid_gratuity_date') }}" required>
                                    <div class="wizard-form-error"></div>
                                </div>
                                <span class="custom-validation text-danger small px-2"></span>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Commission*</label>
                                <div class="position-relative single-form-select2">
                                    <select name="commission_id" id="commission_id"  class="form-control  wizard-required" required>
                                        <option value="">-- Select Commission --</option>
                                        @foreach($commissions as $commission)
                                            <option value="{{ $commission->id }}">{{ $commission->title }}</option>
                                        @endforeach
                                    </select>
                                    <div class="wizard-form-error"></div>
                                </div>
                                <span class="custom-validation text-danger small px-2"></span>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Role*</label>
                                <div class="position-relative">
                                    <select name="role_id" id="role_id" class="form-control wizard-required" required>
                                        <option value=""> -- Select Role -- </option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="wizard-form-error"></div>
                                </div>
                                <span class="custom-validation text-danger small px-2"></span>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Subcontractor Status*</label>
                                <div class="position-relative">
                                    <select name="employee_status_id" id="employee_status_id" class="form-control wizard-required" required>
                                        @foreach($employee_statuses as $employee_status)
                                            <option value="{{ $employee_status->id }}" {{$employee_status->id == 1 ? 'selected' : ''}}>{{ $employee_status->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="wizard-form-error"></div>
                                </div>
                                <span class="custom-validation text-danger small px-2"></span>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Excluded Holidays</label>
                                <select name="excluded_holiday_ids[]" id="excluded_holiday_id" multiple="multiple" class="form-control wizard-required" required>
                                    <option value="" disabled hidden>-- Select Holiday --</option>
                                    @foreach($holidays as $holiday)
                                        <option value="{{ $holiday->id }}">{{ $holiday->name }}</option>
                                    @endforeach
                                </select>
                                <span class="custom-validation text-danger small px-2"></span>
                            </div>
                            <div class="col-6 working-days-block">
                                <label class="form-label">Days</label>
                                <div class=" align-items-center flex-wrap gap-28">
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
                                    @endphp

                                    @foreach($days as $day => $class)
                                        <div class="form-switch {{ $class }} d-flex align-items-center gap-3 mb-3">
                                            <input type="hidden" name="working_days[{{ $day }}]" value="0">
                                            <input class="form-check-input" type="checkbox"
                                                   name="working_days[{{ $day }}]"
                                                   value="1" id="switch_{{ $day }}" checked>
                                            <label class="form-check-label line-height-1 fw-medium text-secondary-light"
                                                   for="switch_{{ $day }}">{{ $day }}</label>
                                        </div>
                                    @endforeach
                                </div>
                                <span class="custom-validation text-danger small px-2"></span>
                            </div>
                            <div class="form-group d-flex align-items-center justify-content-end gap-8">
                                <button type="button" class="form-wizard-previous-btn btn btn-neutral-500 border-neutral-100 px-32">Back</button>
                                <button type="button" class="form-wizard-next-btn btn btn-primary-600 px-32">Next</button>
                            </div>
                        </div>
                    </fieldset>

                    {{-- Step 3: Subcontractor Leaves --}}
                    <fieldset class="wizard-fieldset" id="wfhLeavesStep">
                        <h6 class="text-md text-neutral-500">Leave Assignment</h6>
                        {{-- #7/#8: Work From Home doesn't need leaves --}}
                        <div id="wfhLeavesNote" class="alert alert-info" style="display:none;">Leaves are not applicable for Work From Home subcontractors.</div>
                        {{-- B6: Leaves are optional per subcontractor. Unchecking hides + un-requires + disables
                             the leave inputs (so they don't submit) and sends assign_leaves=0. --}}
                        <input type="hidden" name="assign_leaves" id="assign_leaves" value="1">
                        <div class="form-check form-switch mb-16">
                            <input class="form-check-input" type="checkbox" id="assign_leaves_toggle" checked>
                            <label class="form-check-label fw-medium" for="assign_leaves_toggle">
                                Assign leaves to this subcontractor
                            </label>
                        </div>
                        <div class="row gy-2">

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
                                            <tr>
                                                <td>
                                                    {{ $leave->name }}
                                                    <input type="hidden" name="leaves[{{ $leave->id }}][leave_type_id]" value="{{ $leave->id }}">
                                                </td>
                                                <td>
                                                    {{-- B6: default quota 0 so it never blocks Publish; admin can change,
                                                         or turn off the "Assign leaves" switch to skip leaves entirely. --}}
                                                    <input type="number" name="leaves[{{ $leave->id }}][assigned_quota]"
                                                           class="form-control" min="0" placeholder="Enter days" value="0" required>
                                                </td>
                                                <td>
                                                    <input type="date" name="leaves[{{ $leave->id }}][valid_from]"
                                                           class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                                                </td>
                                                <td>
                                                    <input type="date" name="leaves[{{ $leave->id }}][valid_to]"
                                                           class="form-control" value="{{ now()->endOfYear()->format('Y-m-d') }}" required>
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

                    {{-- Step 4: Personal Information --}}
                    <fieldset class="wizard-fieldset">
                        <h6 class="text-md text-neutral-500">Personal Information</h6>
                        <div class="row gy-2">
                            <div class="col-6">
                                <label class="form-label">Father Name</label>
                                <div class="position-relative">
                                    <input type="text" name="father_name" class="form-control wizard-required" value="{{ old('father_name') }}" required>
                                    <div class="wizard-form-error"></div>
                                </div>
                                <span class="custom-validation text-danger small px-2"></span>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Mother Name</label>
                                <div class="position-relative">
                                    <input type="text" name="mother_name" class="form-control wizard-required" value="{{ old('mother_name') }}" required>
                                    <div class="wizard-form-error"></div>
                                </div>
                                <span class="custom-validation text-danger small px-2"></span>
                            </div>
                            <div class="col-6">
                                <label class="form-label">CNIC</label>
                                <div class="position-relative">
                                    <input type="text" name="cnic" id="cnic" class="form-control wizard-required"
                                           value="{{ old('cnic') }}"
                                           placeholder="XXXXX-XXXXXXX-X"  required>
                                    <div class="wizard-form-error"></div>
                                </div>
                                <span class="custom-validation text-danger small px-2"></span>
                            </div>
                            <div class="col-6">
                                <label class="form-label">DOB</label>
                                <div class="position-relative">
                                    <input type="date" name="dob" class="form-control wizard-required"
                                           value="{{ old('dob') }}"
                                           max="{{ date('Y-m-d') }}" required>
                                    <div class="wizard-form-error"></div>
                                </div>
                                <span class="custom-validation text-danger small px-2"></span>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Gender</label>
                                <div class="position-relative">
                                    <select name="gender" class="form-control wizard-required" required>
                                        <option value="">Select Gender</option>
                                        <option value="male" {{ old('gender')=='male'?'selected':'' }}>Male</option>
                                        <option value="female" {{ old('gender')=='female'?'selected':'' }}>Female</option>
                                        <option value="other" {{ old('gender')=='other'?'selected':'' }}>Other</option>
                                    </select>
                                    <div class="wizard-form-error"></div>
                                </div>
                                <span class="custom-validation text-danger small px-2"></span>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Marital Status</label>
                                <div class="position-relative">
                                    <select name="marital_status" id="marital_status" class="form-control wizard-required" required>
                                        <option value="">Select Marital Status</option>
                                        <option value="single" {{ old('marital_status')=='single'?'selected':'' }}>Single</option>
                                        <option value="married" {{ old('marital_status')=='married'?'selected':'' }}>Married</option>
                                        <option value="divorced" {{ old('marital_status')=='divorced'?'selected':'' }}>Divorced</option>
                                        <option value="widowed" {{ old('marital_status')=='widowed'?'selected':'' }}>Widowed</option>
                                    </select>
                                    <div class="wizard-form-error"></div>
                                </div>
                                <span class="custom-validation text-danger small px-2"></span>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Children's</label>
                                <div class="position-relative">
                                    <input type="number" name="kids_count" id="kids_count" class="form-control" value="{{ old('kids_count') }}">
{{--                                    <div class="wizard-form-error"></div>--}}
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Skills</label>
                                <div class="position-relative">
                                    <textarea name="skills" class="form-control">{{ old('skills') }}</textarea>
                                    <div class="wizard-form-error"></div>
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Phone</label>
                                <div class="position-relative">
                                    <input type="text" name="phone" id="phone" class="form-control wizard-required" value="{{ old('phone') }}" placeholder="e.g. 03XXXXXXXXX" required>
                                    <div class="wizard-form-error"></div>
                                </div>
                                <span class="custom-validation text-danger small px-2"></span>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Phone2</label>
                                <div class="position-relative">
                                    <input type="text" name="phone2" id="phone2" class="form-control wizard-required" value="{{ old('phone2') }}" placeholder="e.g. 03XXXXXXXXX" required>
                                    <div class="wizard-form-error"></div>
                                </div>
                                <span class="custom-validation text-danger small px-2"></span>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Contact Person</label>
                                <div class="position-relative">
                                    <input type="text" name="contact_person" class="form-control wizard-required" value="{{ old('contact_person') }}" required>
                                    <div class="wizard-form-error"></div>
                                </div>
                                <span class="custom-validation text-danger small px-2"></span>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Emergency Contact Phone</label>
                                <div class="position-relative">
                                    <input type="text" name="emergency_contact" id="emergency_contact" class="form-control wizard-required" value="{{ old('emergency_contact') }}" placeholder="e.g. 03XXXXXXXXX" required>
                                    <div class="wizard-form-error"></div>
                                </div>
                                <span class="custom-validation text-danger small px-2"></span>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <div class="position-relative">
                                    <input type="text" name="address" class="form-control wizard-required" value="{{ old('address') }}" required>
                                    <div class="wizard-form-error"></div>
                                </div>
                                <span class="custom-validation text-danger small px-2"></span>
                            </div>
                            <div class="col-4">
                                <label class="form-label">City</label>
                                <div class="position-relative">
                                    <input type="text" name="city" class="form-control wizard-required" value="{{ old('city') }}" required>
                                    <div class="wizard-form-error"></div>
                                </div>
                                <span class="custom-validation text-danger small px-2"></span>
                            </div>
                            <div class="col-4">
                                <label class="form-label">State</label>
                                <div class="position-relative">
                                    <input type="text" name="state" class="form-control wizard-required" value="{{ old('state') }}" required>
                                    <div class="wizard-form-error"></div>
                                </div>
                                <span class="custom-validation text-danger small px-2"></span>
                            </div>
                            <div class="col-4">
                                <label class="form-label">Country</label>
                                <div class="position-relative">
                                    <input type="text" name="country" class="form-control wizard-required" value="{{ old('country') }}" required>
                                    <div class="wizard-form-error"></div>
                                </div>
                                <span class="custom-validation text-danger small px-2"></span>
                            </div>

                            <div class="form-group d-flex align-items-center justify-content-end gap-8">
                                <button type="button" class="form-wizard-previous-btn btn btn-neutral-500 border-neutral-100 px-32">Back</button>
                                <button type="button" class="form-wizard-next-btn btn btn-primary-600 px-32">Next</button>
                            </div>
                        </div>
                    </fieldset>

                    {{-- Step 5: Subcontractor Documents --}}
                    <fieldset class="wizard-fieldset">
                        <h6 class="text-md text-neutral-500">Subcontractor Documents</h6>
                        <div class="row gy-2">
                            @foreach($document_types as $doc)
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
                                                   class="form-control wizard-required"
                                                   @if($doc->is_required) required @endif>
                                        @elseif($doc->input_type === 'text')
                                            <input type="text" name="documents[{{ $doc->id }}]"
                                                   class="form-control wizard-required"
                                                   value="{{ old('documents.'.$doc->id) }}"
                                                   @if($doc->is_required) required @endif>
                                        @endif
                                        <div class="wizard-form-error"></div>
                                    </div>
                                    @if($doc->description)
                                        <small class="text-muted">{{ $doc->description }}</small>
                                    @endif
                                    <span class="custom-validation text-danger small px-2"></span>
                                </div>
                            @endforeach
                                <div class="form-group d-flex align-items-center justify-content-end gap-8">
                                    <button type="button" class="form-wizard-previous-btn btn btn-neutral-500 border-neutral-100 px-32">Back</button>
                                    <button type="button" class="form-wizard-next-btn btn btn-primary-600 px-32">Next</button>
                                </div>
                        </div>
                    </fieldset>

                    {{-- Step 6: Bank Details --}}
                    <fieldset class="wizard-fieldset">
                        <h6 class="text-md text-neutral-500">Bank Details</h6>
                        <div class="row gy-3">
                            <div class="col-6">
                                <label class="form-label">Bank Name</label>
                                <div class="position-relative">
                                    <input type="text" name="bank_name" class="form-control wizard-required" value="{{ old('bank_name') }}" required>
                                    <div class="wizard-form-error"></div>
                                </div>
                                <span class="custom-validation text-danger small px-2"></span>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Account Title</label>
                                <div class="position-relative">
                                    <input type="text" name="account_title" class="form-control wizard-required" value="{{ old('account_title') }}" required>
                                    <div class="wizard-form-error"></div>
                                </div>
                                <span class="custom-validation text-danger small px-2"></span>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Account Number</label>
                                <div class="position-relative">
                                    <input type="text" name="account_number" class="form-control wizard-required" value="{{ old('account_number') }}" required>
                                    <div class="wizard-form-error"></div>
                                </div>
                                <span class="custom-validation text-danger small px-2"></span>
                            </div>
                            <div class="col-6">
                                <label class="form-label">IBAN</label>
                                <div class="position-relative">
                                    <input type="text" name="iban" class="form-control wizard-required" value="{{ old('iban') }}" required>
                                    <div class="wizard-form-error"></div>
                                </div>
                                <span class="custom-validation text-danger small px-2"></span>
                            </div>

                            <div class="form-group d-flex align-items-center justify-content-end gap-8">
                                <button type="button" class="form-wizard-previous-btn btn btn-neutral-500 border-neutral-100 px-32">Back</button>
                                <button type="button" class="form-wizard-next-btn btn btn-primary-600 px-32">Next</button>
                            </div>
                        </div>
                    </fieldset>

                    {{-- Step 7: Review (B6) — read-only summary with jump-back edit buttons --}}
                    <fieldset class="wizard-fieldset" id="reviewStep">
                        <h6 class="text-md text-neutral-500">Review &amp; Confirm</h6>
                        <p class="text-neutral-400 text-sm">Please review the details below before publishing. Use <strong>Edit</strong> to jump back to any section.</p>
                        <div id="reviewSummary" class="row gy-3"></div>
                        <div class="form-group d-flex align-items-center justify-content-end gap-8 mt-24">
                            <button type="button" class="form-wizard-previous-btn btn btn-neutral-500 border-neutral-100 px-32">Back</button>
                            <button type="button" class="form-wizard-next-btn btn btn-primary-600 px-32">Confirm</button>
                        </div>
                    </fieldset>

                    {{-- Step 8: Completed --}}
                    <fieldset class="wizard-fieldset">
                        <div class="text-center mb-40">
                            <img src="assets/images/gif/success-img3.gif" alt="" class="gif-image mb-24">
                            <h6 class="text-md text-neutral-600">Congratulations</h6>
                            <p class="text-neutral-400 text-sm mb-0">Well done! You have successfully completed.</p>
                        </div>
                        <div class="form-group d-flex align-items-center justify-content-end gap-8">
                            <button type="button" class="form-wizard-previous-btn btn btn-neutral-500 border-neutral-100 px-32">Back</button>
                            <button type="submit" class="form-wizard-submit btn btn-primary-600 px-32">Publish</button>
                        </div>
                    </fieldset>
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
                    var thisValue = $(this).val();
                    var errorSpan = $(this).closest('.col-6, .col-sm-6, .col-sm-12').find('.custom-validation');


                    // if( thisValue == "") {
                    //     $(this).siblings(".wizard-form-error").show();
                    //     nextWizardStep = false;
                    // }
                    // For text/select inputs

                    // File input validation
                    if (this.type === "file") {
                        if (this.files.length === 0) {
                            errorSpan.text("Profile picture is required").addClass("show-error");
                            nextWizardStep = false;
                        } else {
                            errorSpan.text("").removeClass("show-error");
                        }
                    }
                    else if ($(this).is("select")) {
                        if (thisValue === "" || thisValue === null || thisValue.includes("Select")) {
                            errorSpan.text("This field is required").addClass("show-error");
                            $(this).siblings(".wizard-form-error").show();
                            nextWizardStep = false;
                        } else {
                            errorSpan.text("").removeClass("show-error");
                            $(this).siblings(".wizard-form-error").hide();
                        }
                    }
                    else if (thisValue === "") {
                        errorSpan.text("This field is required").addClass("show-error");
                        $(this).siblings(".wizard-form-error").show();
                        nextWizardStep = false;
                    }
                    else {
                        errorSpan.text("").removeClass("show-error");
                        $(this).siblings(".wizard-form-error").hide();
                    }

                    // if (this.type == "file") {
                    //     errorSpan.text("Profile picture is required").addClass("show-error");
                    //     if (this.files.length === 0) {
                    //         errorSpan.text("Profile picture is required").addClass("show-error");
                    //         nextWizardStep = false;
                    //     } else {
                    //         errorSpan.text("").removeClass("show-error");
                    //     }
                    // } else if(thisValue == "" ) {
                    //     errorSpan.text("This field is required").addClass("show-error");
                    //     $(this).siblings(".wizard-form-error").show();
                    //     nextWizardStep = false;
                    // } else  {
                    //     $(this).siblings(".wizard-form-error").hide();
                    //     errorSpan.text("").removeClass("show-error");
                    // }

                    if($(this).is("#phone, #phone2, #emergency_contact")) {

                        if(thisValue.length !== 11) {
                            $(this).siblings(".wizard-form-error").show();
                            errorSpan.text("Must be 11 digits").addClass("show-error");
                            nextWizardStep = false;
                        }
                    }

                    if($(this).is("#cnic")) {
                        if(thisValue.length !== 15) {
                            $(this).siblings(".wizard-form-error").show();
                            errorSpan.text("Must be 15 characters").addClass("show-error");
                            nextWizardStep = false;
                        }
                    }
                });
                // ✅ Extra validation for working days (after loop)
                var workingDaysBlock = parentFieldset.find('.working-days-block');

                if (workingDaysBlock.length) { // sirf us fieldset me ho tab check karega
                    var workingDays = workingDaysBlock.find('input[name^="working_days"]:checked');
                    var workingDaysError = workingDaysBlock.find('.custom-validation');

                    if (workingDays.length === 0) {
                        workingDaysError.text("Please select at least one working day").addClass("show-error");
                        nextWizardStep = false;
                    } else {
                        workingDaysError.text("").removeClass("show-error");
                    }
                }

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
                var counter = parseInt($(".wizard-counter").text());
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
                var errorSpan = $(this).closest('.col-sm-6, .col-6, .col-sm-12').find('.custom-validation');

                if(tmpThis == '' ) {
                    $(this).parent().removeClass("focus-input");
                    $(this).siblings(".wizard-form-error").show();
                    errorSpan.text("This field is required").addClass("show-error");
                }
                else if(tmpThis !='' ){
                    $(this).parent().addClass("focus-input");
                    $(this).siblings(".wizard-form-error").hide();
                    errorSpan.text("").removeClass("show-error");
                }
            });

            $(".shift_type").on('change',function (){
                var selectedOption = $(this).find('option:selected');
                var startTime = selectedOption.data('start') || '';
                var endTime = selectedOption.data('end') || '';

                $('#shift_start').val(startTime);
                $('#shift_end').val(endTime);
                applyOnboardingToggles();
            });

            // #7/#8 + B6: Gratuity & Leaves are optional per subcontractor.
            //   - Work From Home (shift 6) forces BOTH off and disables the switches.
            //   - Commission-only accounts (type 2) already hide the salary area, gratuity with it.
            //   - Otherwise the admin can toggle each on/off.
            // Original required/wizard-required is captured once so the enabled state restores exactly.
            function applyOnboardingToggles() {
                var isWfh        = String($('#shift_id').val()) === '6';
                var isSub        = String($('#worker_type').val()) === 'subcontractor';
                var salaryHidden = String($('#account_type_id').val()) === '2';
                // #21: subcontractors (and WFH) get NO gratuity / leaves / tax.
                var benefitsOff  = isSub || isWfh;

                var $gSwitch = $('#gratuity_enabled_toggle');
                var $lSwitch = $('#assign_leaves_toggle');

                // WFH / subcontractor force both switches off + disabled.
                $gSwitch.prop('disabled', benefitsOff);
                $lSwitch.prop('disabled', benefitsOff);
                if (benefitsOff) { $gSwitch.prop('checked', false); $lSwitch.prop('checked', false); }

                var gratuityOn = !benefitsOff && !salaryHidden && $gSwitch.is(':checked');
                var leavesOn   = !benefitsOff && $lSwitch.is(':checked');
                var taxOn      = !benefitsOff && !salaryHidden; // commission-only has no tax either

                // Hidden flags the server reads.
                $('#gratuity_enabled').val(gratuityOn ? '1' : '0');
                $('#assign_leaves').val(leavesOn ? '1' : '0');

                // Tax fields — remove required + clear when off so they never block submit.
                $('#tax_slab_setting_id, #tax_title, #tax_rate').each(function () {
                    var $f = $(this);
                    if ($f.data('origReq') === undefined) {
                        $f.data('origReq', $f.prop('required'));
                        $f.data('origWiz', $f.hasClass('wizard-required'));
                    }
                    if (taxOn) {
                        $f.prop('required', $f.data('origReq'));
                        $f.data('origWiz') ? $f.addClass('wizard-required') : $f.removeClass('wizard-required');
                    } else {
                        $f.prop('required', false).removeClass('wizard-required').val('');
                    }
                });
                if (!taxOn) { $('#tax_slab_setting_id').val('').trigger('change'); }
                $('.tax-field').toggle(taxOn);

                // Gratuity fields.
                $('#gratuity_id, input[name="valid_gratuity_date"]').each(function () {
                    var $f = $(this);
                    if ($f.data('origReq') === undefined) {
                        $f.data('origReq', $f.prop('required'));
                        $f.data('origWiz', $f.hasClass('wizard-required'));
                    }
                    if (gratuityOn) {
                        $f.prop('required', $f.data('origReq'));
                        $f.data('origWiz') ? $f.addClass('wizard-required') : $f.removeClass('wizard-required');
                    } else {
                        $f.prop('required', false).removeClass('wizard-required');
                    }
                });
                $('.gratuity-field').toggle(gratuityOn);

                // Leave fields — DISABLE when off so they don't submit at all.
                $('#wfhLeavesStep input[name^="leaves"]').each(function () {
                    var $f = $(this);
                    if ($f.data('origReq') === undefined) {
                        $f.data('origReq', $f.prop('required'));
                        $f.data('origWiz', $f.hasClass('wizard-required'));
                    }
                    if (leavesOn) {
                        $f.prop('disabled', false).prop('required', $f.data('origReq'));
                        $f.data('origWiz') ? $f.addClass('wizard-required') : $f.removeClass('wizard-required');
                    } else {
                        $f.prop('required', false).removeClass('wizard-required').prop('disabled', true);
                    }
                });
                $('#wfhLeavesStep .table-responsive').toggle(leavesOn);
                $('#wfhLeavesNote')
                    .text(isSub
                        ? 'Leaves, Gratuity & Tax are not applicable for subcontractors.'
                        : 'Leaves are not applicable for Work From Home subcontractors.')
                    .toggle(benefitsOff);
            }
            $('#gratuity_enabled_toggle, #assign_leaves_toggle').on('change', applyOnboardingToggles);
            $('#account_type_id, #worker_type').on('change', applyOnboardingToggles);
            applyOnboardingToggles();

            // ============================ B6 Review step ============================
            function gotoWizardStep(index) {
                var $fs = $('.wizard-fieldset');
                $fs.removeClass('show');
                $fs.eq(index).addClass('show');
                $('.form-wizard-list .form-wizard-list__item').each(function (i) {
                    $(this).removeClass('active activated');
                    if (i < index)      $(this).addClass('activated');
                    else if (i === index) $(this).addClass('active');
                });
                $('html, body').animate({ scrollTop: 0 }, 200);
            }

            function rvTxt(sel) {
                var $e = $(sel);
                if (!$e.length) return '';
                if ($e.is('select')) return $.trim($e.find('option:selected').text());
                return $.trim(($e.val() || '').toString());
            }
            function rvRow(label, value) {
                var safe = (value === '' || value == null)
                    ? '<span class="text-neutral-400">—</span>'
                    : $('<div>').text(value).html();
                return '<div class="col-md-4 col-6"><div class="text-neutral-400 text-xs">' + label +
                    '</div><div class="fw-semibold">' + safe + '</div></div>';
            }
            function rvHead(title, step) {
                return '<div class="col-12 d-flex align-items-center justify-content-between border-bottom pb-4 mt-8">' +
                    '<h6 class="text-sm mb-0">' + title + '</h6>' +
                    '<button type="button" class="btn btn-sm btn-outline-primary-600 px-16 review-edit" data-step="' + step + '">Edit</button></div>';
            }
            function populateReview() {
                var h = '';
                h += rvHead('Subcontractor Information', 0);
                h += rvRow('First Name', rvTxt('input[name=first_name]'));
                h += rvRow('Last Name', rvTxt('input[name=last_name]'));
                h += rvRow('Email', rvTxt('input[name=email]'));
                h += rvRow('Account Type', rvTxt('#account_type_id'));
                h += rvRow('Basic Salary', rvTxt('input[name=basic_salary]'));

                h += rvHead('Employment Details', 1);
                h += rvRow('Subcontractor Code', rvTxt('input[name=employee_code]'));
                h += rvRow('Employment Type', rvTxt('#employment_type'));
                h += rvRow('Worker Type', rvTxt('#worker_type'));
                h += rvRow('Department', rvTxt('#department_id'));
                h += rvRow('Designation', rvTxt('#designation_id'));
                h += rvRow('Joining Date', rvTxt('input[name=joining_date]'));
                h += rvRow('Shift', rvTxt('#shift_id'));
                h += rvRow('Gratuity', $('#gratuity_enabled').val() === '1' ? rvTxt('#gratuity_id') : 'Not applied');
                if ($('#gratuity_enabled').val() === '1') h += rvRow('Valid Gratuity Date', rvTxt('input[name=valid_gratuity_date]'));
                h += rvRow('Commission', rvTxt('#commission_id'));
                h += rvRow('Role', rvTxt('#role_id'));
                h += rvRow('Status', rvTxt('#employee_status_id'));

                h += rvHead('Leave Assignment', 2);
                if ($('#assign_leaves').val() === '1') {
                    $('#wfhLeavesStep tbody tr').each(function () {
                        var name = $.trim($(this).find('td:first').clone().children().remove().end().text());
                        var q = $(this).find('input[name*="[assigned_quota]"]').val();
                        h += rvRow(name, (q || '0') + ' days');
                    });
                } else {
                    h += '<div class="col-12 text-neutral-500">No leaves assigned.</div>';
                }

                h += rvHead('Personal Information', 3);
                h += rvRow('Father Name', rvTxt('input[name=father_name]'));
                h += rvRow('Mother Name', rvTxt('input[name=mother_name]'));
                h += rvRow('CNIC', rvTxt('#cnic'));
                h += rvRow('DOB', rvTxt('input[name=dob]'));
                h += rvRow('Gender', rvTxt('select[name=gender]'));
                h += rvRow('Marital Status', rvTxt('#marital_status'));
                h += rvRow('Phone', rvTxt('#phone'));
                h += rvRow('Address', rvTxt('input[name=address]'));
                h += rvRow('City', rvTxt('input[name=city]'));
                h += rvRow('State', rvTxt('input[name=state]'));
                h += rvRow('Country', rvTxt('input[name=country]'));

                h += rvHead('Documents', 4);
                $('input[name^="documents["]').each(function () {
                    var label = $.trim($(this).closest('.col-6').find('label').text());
                    var v = this.type === 'file' ? (this.files.length ? this.files[0].name : '') : $(this).val();
                    h += rvRow(label, v);
                });

                h += rvHead('Bank Details', 5);
                h += rvRow('Bank Name', rvTxt('input[name=bank_name]'));
                h += rvRow('Account Title', rvTxt('input[name=account_title]'));
                h += rvRow('Account Number', rvTxt('input[name=account_number]'));
                h += rvRow('IBAN', rvTxt('input[name=iban]'));

                $('#reviewSummary').html(h);
            }
            $(document).on('click', '.review-edit', function () {
                gotoWizardStep(parseInt($(this).data('step'), 10));
            });

            // Populate the summary the instant the Review step becomes visible.
            // MutationObserver is timing-independent (doesn't rely on the click order
            // of the wizard's own next-button handler); the click handler is a fallback.
            (function () {
                var reviewEl = document.getElementById('reviewStep');
                if (reviewEl && window.MutationObserver) {
                    new MutationObserver(function () {
                        if (reviewEl.classList.contains('show')) populateReview();
                    }).observe(reviewEl, { attributes: true, attributeFilter: ['class'] });
                }
            })();
            $(document).on('click', '.form-wizard-next-btn, .form-wizard-previous-btn', function () {
                setTimeout(function () {
                    if ($('#reviewStep').hasClass('show')) populateReview();
                }, 80);
            });
        });
        // =============================== Wizard Step Js End ================================

        // File Upload Preview Logic
        const fileInput = document.getElementById("upload-file");
        const imagePreview = document.getElementById("uploaded-img__preview");
        const uploadedImgContainer = document.querySelector(".uploaded-img");
        const removeButton = document.querySelector(".uploaded-img__remove");
        const imgLabel = document.getElementById("img_label");
        const errorSpan = fileInput.closest('.col-sm-12').querySelector('.custom-validation');

        fileInput.addEventListener("change", (e) => {
            if (e.target.files.length) {
                const src = URL.createObjectURL(e.target.files[0]);
                imagePreview.src = src;
                uploadedImgContainer.classList.remove('d-none');
                imgLabel.classList.add('d-none');

                // ✅ clear error if file uploaded
                errorSpan.textContent = "";
                errorSpan.classList.remove("show-error");
            }
        });
        removeButton.addEventListener("click", () => {
            imagePreview.src = "{{ asset('assets/images/user.png') }}";
            uploadedImgContainer.classList.add('d-none');
            fileInput.value = "";
            imgLabel.classList.remove('d-none')

            // ✅ show error again if required
            errorSpan.textContent = "Profile picture is required";
            errorSpan.classList.add("show-error");
        });
    </script>
@endpush
