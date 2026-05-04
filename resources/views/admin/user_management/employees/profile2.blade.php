@extends('layout.master')
@section('pageName', 'Employee Profile')

@section('content')
    <div class="dashboard-main-body">

        {{-- Profile Header --}}
        <div class="card border-0 shadow-sm radius-16 mb-4">
            <div class="card-body text-center p-4">
                <img src="{{ asset($employee->profile_image ?? 'assets/images/user-grid/user-grid-img14.png') }}"
                     class="rounded-circle border border-3 border-primary shadow mb-3"
                     style="width:150px; height:150px; object-fit:cover;" alt="Profile Image">

                <h4 class="fw-bold mb-0">{{ $employee->full_name ?? '-' }}</h4>
                <p class="text-muted">{{ $employee->designation ?? '-' }} | {{ $employee->department ?? '-' }}</p>

                {{-- Contact --}}
                <div class="d-flex justify-content-center gap-3 mt-2 flex-wrap">
                    <span><i class="bi bi-envelope me-2"></i>{{ $employee->email ?? '-' }}</span>
                    <span><i class="bi bi-telephone me-2"></i>{{ $employee->phone ?? '-' }}</span>
                </div>

                {{-- Skills --}}
                <div class="mt-3">
                    @foreach(explode(',', $employee->skills ?? '') as $skill)
                        @if(!empty(trim($skill)))
                            <span class="badge bg-primary-subtle text-primary fw-semibold me-1 mb-1">
                            {{ trim($skill) }}
                        </span>
                        @endif
                    @endforeach
                </div>

                {{-- Address --}}
                <p class="mt-3 text-muted small">
                    <i class="bi bi-geo-alt me-2"></i>
                    {{ $employee->address ?? '-' }}, {{ $employee->city ?? '' }},
                    {{ $employee->state ?? '' }}, {{ $employee->country ?? '' }}
                </p>
            </div>
        </div>

        {{-- Bottom Section - Cards --}}
        <div class="row g-4">

            {{-- Profile Info Card --}}
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-light fw-bold">👤 Profile Info</div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
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
                </div>
            </div>

            {{-- Bank Details Card --}}
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-light fw-bold">🏦 Bank Details</div>
                    <div class="card-body">
                        @if($employee->bankDetail)
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><strong>Bank:</strong> {{ $employee->bankDetail->bank_name }}</li>
                                <li class="list-group-item"><strong>Account Title:</strong> {{ $employee->bankDetail->account_title }}</li>
                                <li class="list-group-item"><strong>Account Number:</strong> {{ $employee->bankDetail->account_number }}</li>
                                <li class="list-group-item"><strong>IBAN:</strong> {{ $employee->bankDetail->iban }}</li>
                            </ul>
                        @else
                            <p class="text-muted">No bank details available.</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Documents Card --}}
            <div class="col-md-12">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-light fw-bold">📄 Documents</div>
                    <div class="card-body">
                        @if($employee->documents && $employee->documents->count())
                            <div class="row g-3">
                                @foreach($employee->documents as $doc)
                                    <div class="col-md-3">
                                        <div class="card text-center p-3 shadow-sm h-100">
                                            @php $ext = pathinfo($doc->file_path, PATHINFO_EXTENSION); @endphp
                                            @if(in_array(strtolower($ext), ['jpg','jpeg','png','gif','bmp','webp']))
                                                <img src="{{ asset($doc->file_path) }}" class="img-fluid rounded mb-2" alt="{{ $doc->file_name }}">
                                            @elseif(strtolower($ext) === 'pdf')
                                                <i class="bi bi-file-earmark-pdf text-danger" style="font-size: 64px;"></i>
                                            @else
                                                <i class="bi bi-file-earmark" style="font-size: 64px;"></i>
                                            @endif
                                            <p class="fw-medium mt-2">{{ $doc->file_name }}</p>
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

            {{-- Working Days Card --}}
            <div class="col-md-12">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-light fw-bold">📅 Working Days</div>
                    <div class="card-body">
                        <div class="row g-2">
                            @foreach($employee->working_days ?? [] as $day)
                                <div class="col-md-2 col-6">
                                    <div class="card text-center p-2 shadow-sm">
                                        <h6 class="mb-1">{{ $day->day_of_week }}</h6>
                                        <span class="badge {{ $day->is_working ? 'bg-success' : 'bg-danger' }}">
                                        {{ $day->is_working ? 'Working' : 'Off' }}
                                    </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
