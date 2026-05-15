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
                <div class="details">
                    <h4 class="fw-bold mb-1">{{ $employee->full_name ?? '-' }}</h4>
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
                        <div class="d-flex align-items-center flex-wrap gap-4">
                            @php
                                $dayNames = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
                            @endphp
                            @foreach($employee->working_days ?? [] as $day)
                                <div class="form-switch d-flex align-items-center gap-2">
                                    <input class="form-check-input" type="checkbox"
                                           role="switch"
                                           id="day_{{ $day->id }}"
                                           {{ $day->is_working ? 'checked' : '' }}
                                           disabled>
                                    <label class="form-check-label fw-medium text-secondary-light" for="day_{{ $day->id }}">
                                        {{ $dayNames[$day->day_of_week] ?? $day->day_of_week }}
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
                    <div class="card-header bg-light fw-bold d-flex justify-content-between align-items-center">
                        <span>📄 Documents</span>
                        @if($employee->employee_status_id == 7)
                            <span class="badge bg-warning text-dark">⚠️ Pending Verification — Please upload required documents</span>
                        @endif
                    </div>
                    <div class="card-body">

                        {{-- Success / Error flash --}}
                        @if(session('success'))
                            <div class="alert alert-success py-2 small">{{ session('success') }}</div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger py-2 small">{{ session('error') }}</div>
                        @endif

                        {{-- Upload Form --}}
                        @if($documentSettings && $documentSettings->count())
                        <div class="mb-4 p-3 border rounded" style="background:#f8f9fa;">
                            <h6 class="fw-bold mb-3">📤 Upload Document</h6>
                            <form method="POST"
                                  action="{{ route('employee.profile.upload_document') }}"
                                  enctype="multipart/form-data"
                                  class="row g-2 align-items-end">
                                @csrf
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Document Type <span class="text-danger">*</span></label>
                                    <select name="document_setting_id" class="form-select form-select-sm" required>
                                        <option value="">-- Select Document --</option>
                                        @foreach($documentSettings as $ds)
                                            <option value="{{ $ds->id }}">
                                                {{ $ds->title }}
                                                @if($ds->is_required) (Required) @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label small fw-semibold">File <span class="text-danger">*</span> <span class="text-muted">(JPG, PNG, PDF — max 5MB)</span></label>
                                    <input type="file" name="file" class="form-control form-control-sm"
                                           accept=".jpg,.jpeg,.png,.pdf" required>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary btn-sm w-100">
                                        <i class="bi bi-upload me-1"></i> Upload
                                    </button>
                                </div>
                            </form>
                        </div>
                        @endif

                        {{-- Uploaded Documents --}}
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

                                            {{-- Verification status --}}
                                            @if($doc->status == 1)
                                                <span class="badge bg-success mb-2">✓ Verified</span>
                                            @else
                                                <span class="badge bg-warning text-dark mb-2">⏳ Pending</span>
                                            @endif

                                            <div class="d-flex gap-1 justify-content-center">
                                                <a href="{{ asset($doc->file_path) }}" target="_blank"
                                                   class="btn btn-sm btn-outline-primary">View</a>
                                                @if($doc->status != 1)
                                                <form method="POST"
                                                      action="{{ route('employee.profile.delete_document', $doc->id) }}"
                                                      onsubmit="return confirm('Remove this document?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                                </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted small">No documents uploaded yet. Please upload the required documents above.</p>
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


            {{-- Contract --}}
            @if($employee->contract)
            <div class="col-md-12">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-light fw-bold d-flex justify-content-between align-items-center">
                        <span>📃 My Contract</span>
                        @if($employee->contract_accepted_at)
                            <span class="badge bg-success text-white">✔ Accepted on {{ \Carbon\Carbon::parse($employee->contract_accepted_at)->format('d M Y H:i') }}</span>
                        @else
                            <span class="badge bg-warning text-dark">⚠ Pending Your Acceptance</span>
                        @endif
                    </div>
                    <div class="card-body p-3">
                        @if(!$employee->contract_accepted_at)
                            <div class="alert alert-warning d-flex align-items-start gap-2 mb-3">
                                <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                                <div>
                                    <strong>A new contract has been added by admin.</strong>
                                    Please read the contract carefully and click <strong>Accept Contract</strong> to confirm.
                                </div>
                            </div>
                        @endif

                        <div class="border rounded p-3 mb-3 bg-light" style="max-height:400px;overflow-y:auto;">
                            {!! $employee->contract !!}
                        </div>

                        @if(!$employee->contract_accepted_at)
                            <button type="button" id="acceptContractBtn" class="btn btn-success">
                                <i class="bi bi-check-circle me-1"></i>Accept Contract
                            </button>
                            <span id="acceptContractMsg" class="small ms-3" style="display:none;"></span>
                        @endif
                    </div>
                </div>
            </div>
            @endif

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

@endsection

@push('scripts')
@if($employee->contract && !$employee->contract_accepted_at)
<script>
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('acceptContractBtn');
    if (!btn) return;
    btn.addEventListener('click', function () {
        btn.disabled = true;
        btn.textContent = 'Accepting...';
        fetch('{{ route("employee.contract.accept") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({})
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res.success) {
                var msg = document.getElementById('acceptContractMsg');
                msg.textContent = '✔ Contract accepted successfully.';
                msg.className = 'small ms-3 text-success';
                msg.style.display = 'inline';
                btn.style.display = 'none';
                var header = btn.closest('.card').querySelector('.card-header .badge');
                if (header) {
                    header.className = 'badge bg-success text-white';
                    header.textContent = '✔ Accepted';
                }
                var banner = btn.closest('.card-body').querySelector('.alert-warning');
                if (banner) banner.remove();
            } else {
                btn.disabled = false;
                btn.textContent = 'Accept Contract';
                alert('Failed to accept contract. Please try again.');
            }
        })
        .catch(function () {
            btn.disabled = false;
            btn.textContent = 'Accept Contract';
            alert('An error occurred. Please try again.');
        });
    });
});
</script>
@endif
@endpush
