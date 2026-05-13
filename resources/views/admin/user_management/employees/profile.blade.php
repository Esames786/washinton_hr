@extends('layout.master')
@section('pageName', 'Employee Profile')

{{-- Extra CSS --}}
@push('cssLinks')

    <style>
        .doc-card {
            border: 1px solid #eee;
            border-radius: 10px;
            background: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: 10px;
        }

        .doc-img {
            width: 180px;      /* fixed width */
            height: 180px;     /* fixed height */
            object-fit: cover; /* maintain aspect ratio */
        }

        .doc-icon {
            font-size: 80px;
            line-height: 1;
        }

        .text-truncate {
            max-width: 100%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
@endpush

@section('content')
    <div class="dashboard-main-body">

        {{-- Profile Header --}}
        <div class="card border-0 shadow-sm radius-16 mb-4">
            <div class="card-body d-flex align-items-center gap-4 p-3">
                {{-- Avatar --}}
                <img src="{{ asset($employee->profile_path ?? 'assets/images/default_images/profile_image.png') }}"
                     class="rounded-circle border border-3 border-primary shadow"
                     style="width:120px; height:120px; object-fit:cover;" alt="Profile Image">

                {{-- Details --}}
                <div class="details flex-grow-1">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
                        <h4 class="fw-bold mb-0">{{ $employee->full_name ?? '-' }}</h4>

                        {{-- Change Status Dropdown --}}
                        @php
                            $allStatuses = \App\Models\EmployeeStatus::all();
                            $statusColors = [1=>'success',2=>'secondary',3=>'danger',4=>'warning',5=>'info',6=>'primary',7=>'warning',8=>'secondary',9=>'info',10=>'primary'];
                            $currentColor = $statusColors[$employee->employee_status_id] ?? 'secondary';
                        @endphp
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-{{ $currentColor }} fs-6 px-3 py-2" id="currentStatusBadge">
                                {{ optional($employee->employee_status)->name ?? '-' }}
                            </span>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    Change Status
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow">
                                    @foreach($allStatuses as $st)
                                        @if($st->id !== $employee->employee_status_id)
                                            <li>
                                                <a class="dropdown-item change-status-btn" href="#"
                                                   data-employee-id="{{ $employee->id }}"
                                                   data-status-id="{{ $st->id }}"
                                                   data-status-name="{{ $st->name }}">
                                                    {{ $st->name }}
                                                </a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                            <a href="{{ route('admin.employees.edit', $employee->id) }}" class="btn btn-sm btn-outline-secondary">
                                Edit
                            </a>
                        </div>
                    </div>
                    <p class="text-muted mb-2">{{ $employee->designation->name ?? '-' }} | {{ $employee->department->name ?? '-' }}</p>

                    {{-- Contact --}}
                    <div class="d-flex flex-wrap gap-3 small text-muted">
                        <span><i class="bi bi-envelope me-1"></i>{{ $employee->email ?? '-' }}</span>
                        <span><i class="bi bi-telephone me-1"></i>{{ $employee->phone ?? '-' }}</span>
                    </div>

                    {{-- Skills --}}
                    <div class="mt-2">
                        @foreach(explode(',', $employee->skills ?? '') as $skill)
                            @if(!empty(trim($skill)))
                                <span class="badge bg-primary-subtle text-primary fw-semibold me-1 mb-1">
                                {{ trim($skill) }}
                            </span>
                            @endif
                        @endforeach
                    </div>

                    {{-- Address --}}
                    <p class="mt-2 text-muted small mb-0">
                        <i class="bi bi-geo-alt me-1"></i>
                        {{ $employee->address ?? '-' }}, {{ $employee->city ?? '' }},
                        {{ $employee->state ?? '' }}, {{ $employee->country ?? '' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Bottom Section --}}
        <div class="row g-4 mt-1">

            {{-- Profile Info --}}
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-light fw-bold">👤 Profile Info</div>
                    <div class="card-body p-3">
                        <div class="row g-1 small">
                            @php
                                $rateSymbol = ($employee?->tax_slab?->type ?? '') == 'percentage' ? '%' : '';
                            @endphp
                            <div class="col-6"><strong>Employee Code:</strong> {{ $employee->employee_code ?? '-' }}</div>
                            <div class="col-6"><strong>Joining Date:</strong> {{ $employee->joining_date ?? '-' }}</div>
                            <div class="col-6"><strong>Employment:</strong> {{ optional($employee->employment_type)->name ?? '-' }}</div>
                            <div class="col-6"><strong>Status:</strong> {{ optional($employee->employee_status)->name ?? '-' }}</div>
                            <div class="col-6"><strong>CNIC / Passport:</strong> {{ $employee->cnic ?? '-' }}</div>
                            <div class="col-6"><strong>DOB:</strong> {{ $employee->dob ?? '-' }}</div>
                            <div class="col-6"><strong>Gender:</strong> {{ ucfirst($employee->gender ?? '-') }}</div>
                            <div class="col-6"><strong>Salary:</strong> {{ number_format($employee->basic_salary ?? 0) }}</div>
                            <div class="col-6"><strong>Tax Slab:</strong> {{ $employee?->tax_slab?->title ?? '-' }}</div>
                            <div class="col-6"><strong>Rate {{ $rateSymbol }}:</strong> {{ $employee?->tax_slab?->rate ?? 0 }}</div>
                            <div class="col-6"><strong>Shift:</strong> {{ $employee?->shift->name ?? '—' }}</div>
                            <div class="col-6">
                                <strong>Shift Start:</strong> {{ $employee?->shift?->shift_start ? \Carbon\Carbon::parse($employee->shift->shift_start)->format('h:i A') : '-' }}
                            </div>
                            <div class="col-6">
                                <strong>Shift End:</strong> {{ $employee?->shift?->shift_end ? \Carbon\Carbon::parse($employee->shift->shift_end)->format('h:i A') : '-' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bank Details --}}
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-light fw-bold">🏦 Bank Details</div>
                    <div class="card-body p-3">
                        <div class="row g-1 small">
                            @if($employee->bankDetail)
                                <div><strong>Bank:</strong> {{ $employee->bankDetail->bank_name }}</div>
                                <div><strong>Account Title:</strong> {{ $employee->bankDetail->account_title }}</div>
                                <div><strong>Account No:</strong> {{ $employee->bankDetail->account_number }}</div>
                                <div><strong>IBAN:</strong> {{ $employee->bankDetail->iban }}</div>
                            @else
                                <p class="text-muted">No bank details available.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Working Days --}}
            <div class="col-md-6" >
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-light fw-bold">📅 Working Days</div>
                    <div class="card-body p-3">
                        @php $dayNames = [0=>'Sun',1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat']; @endphp
                        <div class="d-flex align-items-center flex-wrap gap-4">
                            @foreach($employee->working_days ?? [] as $day)
                                <div class="form-switch d-flex align-items-center gap-2">
                                    <input class="form-check-input" type="checkbox"
                                           role="switch"
                                           id="day_{{ $day->id }}"
                                           {{ $day->is_working ? 'checked' : '' }}
                                           disabled>
                                    <label class="form-check-label fw-medium text-secondary-light" for="day_{{ $day->id }}">
                                        {{ $dayNames[(int)$day->day_of_week] ?? $day->day_of_week }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Gratuity Details --}}
            {{-- Gratuity Details --}}
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-light fw-bold">💰 Gratuity Details</div>
                    <div class="card-body p-3">
                        @if($employee->gratuity)
                            <div class="row g-1 small">
                                <div class="col-12"><strong>Title:</strong> {{ $employee->gratuity->title ?? '-' }}</div>
                                <div class="col-12"><strong>Description:</strong> {{ $employee->gratuity->description ?? '-' }}</div>
                                <div class="col-6"><strong>Employee Contribution:</strong> {{ $employee->gratuity->employee_contribution_percentage }}%</div>
                                <div class="col-6"><strong>Company Contribution:</strong> {{ $employee->gratuity->company_contribution_percentage }}%</div>
                                <div class="col-6"><strong>Eligibility Years:</strong> {{ $employee->gratuity->eligibility_years ?? '-' }}</div>
                                <div class="col-6"><strong>Status:</strong>
                                    @if($employee->gratuity->status)
                                        <span class="badge text-sm fw-semibold text-success-600 bg-success-100 px-20 py-9 radius-4 text-white">Active</span>
                                    @else
                                        <span class="badge text-sm fw-semibold text-warning-600 bg-warning-100 px-20 py-9 radius-4 text-white">Inactive</span>
                                    @endif
                                </div>
                                <div class="col-12" style="margin-top: -3px;"><strong>PF Applicable:</strong> {{ $employee->gratuity->is_pf ? 'Yes' : 'No' }}</div>
                            </div>
                        @else
                            <p class="text-muted">No gratuity settings assigned.</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Documents --}}
            <div class="col-md-12">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-light fw-bold">📄 Documents</div>
                    <div class="card-body">
                        @if($employee->documents && $employee->documents->count())
                            <div class="row g-3">
                                @foreach($employee->documents as $doc)
                                    <div class="col-sm-6 col-md-4 col-lg-3">
                                        <div class="card doc-card h-100 text-center p-3">
                                            @php
                                                $ext = strtolower(pathinfo($doc->file_path, PATHINFO_EXTENSION));
                                            @endphp

                                            @if(in_array($ext, ['jpg','jpeg','png','gif','bmp','webp']))
                                                <img src="{{ asset($doc->file_path) }}"
                                                     class="doc-img rounded mb-2"
                                                     alt="{{ $doc->file_name }}">
                                            @elseif($ext === 'pdf')
                                                <i class="bi bi-file-earmark-pdf text-danger doc-icon mb-2 doc-img"></i>
                                            @else
                                                <i class="bi bi-link-45deg text-primary doc-icon mb-2 doc-img"></i>
                                            @endif

                                            <p class="small fw-medium mb-1 text-truncate">{{ $doc->file_name }}</p>
                                            <a href="{{ asset($doc->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">No documents uploaded.</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Daily Activities Master Info --}}
            <div class="col-md-12">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-light fw-bold">📋 Daily Activity Fields</div>
                    <div class="card-body p-3">
                        @if($employee->role && $employee->role->activityFields->count())
                            <ul class="list-group list-group-flush">
                                @foreach($employee->role->activityFields as $field)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        {{ $field->name }}
                                        <span class="badge bg-{{ $field->is_required ? 'danger' : 'secondary' }} text-white">
                                {{ $field->is_required ? 'Required' : 'Optional' }}
                            </span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted">No daily activity fields assigned for this employee.</p>
                        @endif
                    </div>
                </div>
            </div>


            {{-- Leaves --}}
            <div class="col-md-12">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-light fw-bold">📝 Assigned Leaves</div>
                    <div class="card-body">
                        @if($employee->assignedLeaves && $employee->assignedLeaves->count())
                            <div class="row g-3">
                                @foreach($employee->assignedLeaves as $leave)
                                    <div class="col-sm-6 col-md-4 col-lg-3">
                                        <div class="card h-100 text-center p-3 shadow-sm border rounded">
                                            <h5 class="fw-bold mb-2">{{ $leave->leaveType->name ?? 'Leave' }}</h5>
                                            <p class="small mb-1">Assigned: {{ $leave->assigned_quota }}</p>
                                            <p class="small mb-2 text-success">Used: {{ $leave->used_quota }}</p>
                                            <p class="small mb-2 text-secondary">Remaining: {{ $leave->assigned_quota - $leave->used_quota }}</p>

                                            <div class="progress rounded-pill" style="height: 8px;">
                                                @php
                                                    $usedPercent = $leave->assigned_quota > 0 ? ($leave->used_quota / $leave->assigned_quota) * 100 : 0;
                                                @endphp
                                                <div class="progress-bar bg-primary" role="progressbar"
                                                     style="width: {{ $usedPercent }}%;"
                                                     aria-valuenow="{{ $usedPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>

                                            <small class="d-block mt-2 text-muted">
                                                Valid: {{ \Carbon\Carbon::parse($leave->valid_from)->format('d M Y') }}
                                                - {{ \Carbon\Carbon::parse($leave->valid_to)->format('d M Y') }}
                                            </small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">No leaves assigned.</p>
                        @endif
                    </div>
                </div>
            </div>


        </div>
    </div>


@push('scripts')
<script>
document.querySelectorAll('.change-status-btn').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        var employeeId = this.dataset.employeeId;
        var statusId   = this.dataset.statusId;
        var statusName = this.dataset.statusName;
        if (!confirm('Change status to "' + statusName + '"?')) return;

        fetch('{{ route('admin.employees.change-status') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ employee_id: employeeId, status: statusId })
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                document.getElementById('currentStatusBadge').textContent = statusName;
                // Remove the clicked status from dropdown, add old one back
                location.reload();
            } else {
                alert(data.message || 'Failed to update status.');
            }
        })
        .catch(function() { alert('Request failed. Please try again.'); });
    });
});
</script>
@endpush

@endsection
