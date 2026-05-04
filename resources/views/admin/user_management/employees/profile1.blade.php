@extends('layout.master')
@section('pageName', 'Employee Profile')

@section('content')
    <div class="dashboard-main-body">
        <div class="row g-4">

            {{-- LEFT COLUMN --}}
            <div class="col-lg-4">
                {{-- Profile Header --}}
                <div class="card border-0 shadow-sm radius-16 mb-4">
                    <div class="card-body text-center p-4">
                        <img src="{{ asset($employee->profile_image ?? 'assets/images/user-grid/user-grid-img14.png') }}"
                             class="rounded-circle border border-3 border-primary shadow mb-3"
                             style="width:120px; height:120px; object-fit:cover;" alt="Profile Image">

                        <h4 class="fw-bold mb-1">{{ $employee->full_name ?? '-' }}</h4>
                        <p class="text-muted small mb-2">{{ $employee->designation ?? '-' }} | {{ $employee->department ?? '-' }}</p>

                        {{-- Contact --}}
                        <div class="d-flex justify-content-center flex-wrap gap-3 text-muted small">
                            <span><i class="bi bi-envelope me-1"></i>{{ $employee->email ?? '-' }}</span>
                            <span><i class="bi bi-telephone me-1"></i>{{ $employee->phone ?? '-' }}</span>
                        </div>

                        {{-- Skills --}}
                        <div class="mt-2">
                            @foreach(explode(',', $employee->skills ?? '') as $skill)
                                @if(!empty(trim($skill)))
                                    <span class="badge bg-light text-dark border fw-normal me-1 mb-1">
                                        {{ trim($skill) }}
                                    </span>
                                @endif
                            @endforeach
                        </div>

                        {{-- Address --}}
                        <p class="mt-2 text-muted small mb-0">
                            <i class="bi bi-geo-alt me-1"></i>
                            {{ $employee->address ?? '-' }}
                            {{ $employee->city ? ', '.$employee->city : '' }}
                            {{ $employee->state ? ', '.$employee->state : '' }}
                            {{ $employee->country ? ', '.$employee->country : '' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN --}}
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light fw-bold">
                        <ul class="nav nav-tabs card-header-tabs" id="profileTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab">
                                    👤 Profile Info
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="bank-tab" data-bs-toggle="tab" data-bs-target="#bank" type="button" role="tab">
                                    🏦 Bank Details
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="docs-tab" data-bs-toggle="tab" data-bs-target="#docs" type="button" role="tab">
                                    📄 Documents
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="days-tab" data-bs-toggle="tab" data-bs-target="#days" type="button" role="tab">
                                    📅 Working Days
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body tab-content" id="profileTabsContent">

                        {{-- Profile Info --}}
                        <div class="tab-pane fade show active" id="info" role="tabpanel">
                            <ul class="list-group list-group-flush small">
                                <li class="list-group-item"><strong>Employee Code:</strong> {{ $employee->employee_code ?? '-' }}</li>
                                <li class="list-group-item"><strong>Joining Date:</strong> {{ $employee->joining_date ?? '-' }}</li>
                                <li class="list-group-item"><strong>Employment Type:</strong> {{ optional($employee->employment_type)->name ?? '-' }}</li>
                                <li class="list-group-item"><strong>Status:</strong> {{ optional($employee->employee_status)->name ?? '-' }}</li>
                                <li class="list-group-item"><strong>CNIC / Passport:</strong> {{ $employee->cnic ?? '-' }}</li>
                                <li class="list-group-item"><strong>Date of Birth:</strong> {{ $employee->dob ?? '-' }}</li>
                                <li class="list-group-item"><strong>Gender:</strong> {{ ucfirst($employee->gender ?? '-') }}</li>
                                <li class="list-group-item"><strong>Salary:</strong> {{ number_format($employee->salary ?? 0) }}</li>
                                <li class="list-group-item"><strong>Reporting To:</strong> {{ optional($employee->manager)->full_name ?? '-' }}</li>
                            </ul>
                        </div>

                        {{-- Bank Details --}}
                        <div class="tab-pane fade" id="bank" role="tabpanel">
                            @if($employee->bankDetail)
                                <ul class="list-group list-group-flush small">
                                    <li class="list-group-item"><strong>Bank:</strong> {{ $employee->bankDetail->bank_name }}</li>
                                    <li class="list-group-item"><strong>Account Title:</strong> {{ $employee->bankDetail->account_title }}</li>
                                    <li class="list-group-item"><strong>Account Number:</strong> {{ $employee->bankDetail->account_number }}</li>
                                    <li class="list-group-item"><strong>IBAN:</strong> {{ $employee->bankDetail->iban }}</li>
                                </ul>
                            @else
                                <p class="text-muted mb-0">No bank details available.</p>
                            @endif
                        </div>

                        {{-- Documents --}}
                        <div class="tab-pane fade" id="docs" role="tabpanel">
                            @if($employee->documents && $employee->documents->count())
                                <div class="row g-3">
                                    @foreach($employee->documents as $doc)
                                        <div class="col-md-4 col-sm-6">
                                            <div class="card border shadow-sm h-100 text-center">
                                                @php $ext = pathinfo($doc->file_path, PATHINFO_EXTENSION); @endphp
                                                <div class="p-2">
                                                    @if(in_array(strtolower($ext), ['jpg','jpeg','png','gif','bmp','webp']))
                                                        <img src="{{ asset($doc->file_path) }}"
                                                             class="img-fluid rounded"
                                                             style="max-height:160px; object-fit:cover; width:100%;"
                                                             alt="{{ $doc->file_name }}">
                                                    @elseif(strtolower($ext) === 'pdf')
                                                        <i class="bi bi-file-earmark-pdf text-danger" style="font-size:60px;"></i>
                                                    @else
                                                        <i class="bi bi-file-earmark" style="font-size:60px;"></i>
                                                    @endif
                                                </div>
                                                <div class="p-2 border-top small">
                                                    <p class="fw-medium text-truncate mb-2">{{ $doc->file_name }}</p>
                                                    <a href="{{ asset($doc->file_path) }}" target="_blank"
                                                       class="btn btn-sm btn-outline-primary w-100">View</a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted mb-0">No documents uploaded.</p>
                            @endif
                        </div>

                        {{-- Working Days --}}
                        <div class="tab-pane fade" id="days" role="tabpanel">
                            @if($employee->working_days && count($employee->working_days))
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($employee->working_days as $day)
                                        <span class="badge {{ $day->is_working ? 'bg-success' : 'bg-danger' }} px-3 py-2">
                                            {{ $day->day_of_week }} - {{ $day->is_working ? 'Working' : 'Off' }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted mb-0">No working days data available.</p>
                            @endif
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
