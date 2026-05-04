@extends('layout.master')
@section('pageName', 'Employee Profile')

@section('content')
    <div class="row g-4">

        {{-- Employee Information --}}
        <div class="col-12">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">Employee Information</div>
                <div class="card-body row g-3">
                    <div class="col-md-6"><strong>First Name:</strong> {{ $employee->first_name }}</div>
                    <div class="col-md-6"><strong>Last Name:</strong> {{ $employee->last_name }}</div>
                    <div class="col-md-6"><strong>Email:</strong> {{ $employee->email }}</div>
                </div>
            </div>
        </div>

        {{-- Employment Details --}}
        <div class="col-12">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-success text-white">Employment Details</div>
                <div class="card-body row g-3">
                    <div class="col-md-6"><strong>Code:</strong> {{ $employee->employee_code }}</div>
                    <div class="col-md-6"><strong>Department:</strong> {{ $employee->department }}</div>
                    <div class="col-md-6"><strong>Designation:</strong> {{ $employee->designation }}</div>
                    <div class="col-md-6"><strong>Joining Date:</strong> {{ $employee->joining_date }}</div>
                    <div class="col-md-6"><strong>Shift:</strong> {{ optional($employee->shift)->name }}</div>
                    <div class="col-md-6"><strong>Role:</strong> {{ optional($employee->role)->name }}</div>
                    <div class="col-md-6"><strong>Status:</strong> {{ optional($employee->employee_status)->name }}</div>
                    <div class="col-md-6"><strong>Employment Type:</strong> {{ optional($employee->employment_type)->name }}</div>

                    {{-- Working Days --}}
                    <div class="col-12">
                        <strong>Working Days:</strong>
                        @foreach($employee->working_days ?? [] as $day => $isWorking)
                            <span class="badge bg-{{ $isWorking ? 'success' : 'secondary' }}">
                            {{ $day }}
                        </span>
                        @endforeach
                    </div>

                    {{-- Excluded Holidays --}}
                    <div class="col-12">
                        <strong>Excluded Holidays:</strong>
                        @foreach($employee->excluded_holidays ?? [] as $holiday)
                            <span class="badge bg-warning">{{ $holiday->name }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Personal Information --}}
        <div class="col-12">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-info text-white">Personal Information</div>
                <div class="card-body row g-3">
                    <div class="col-md-6"><strong>Father Name:</strong> {{ $employee->father_name }}</div>
                    <div class="col-md-6"><strong>Mother Name:</strong> {{ $employee->mother_name }}</div>
                    <div class="col-md-6"><strong>CNIC:</strong> {{ $employee->cnic }}</div>
                    <div class="col-md-6"><strong>DOB:</strong> {{ $employee->dob }}</div>
                    <div class="col-md-6"><strong>Gender:</strong> {{ ucfirst($employee->gender) }}</div>
                    <div class="col-md-6"><strong>Marital Status:</strong> {{ ucfirst($employee->marital_status) }}</div>
                    <div class="col-md-6"><strong>Kids:</strong> {{ $employee->kids_count }}</div>
                    <div class="col-md-6"><strong>Skills:</strong> {{ $employee->skills }}</div>
                    <div class="col-md-6"><strong>Phone:</strong> {{ $employee->phone }}</div>
                    <div class="col-md-6"><strong>Phone 2:</strong> {{ $employee->phone2 }}</div>
                    <div class="col-md-6"><strong>Contact Person:</strong> {{ $employee->contact_person }}</div>
                    <div class="col-md-6"><strong>Emergency Contact:</strong> {{ $employee->emergency_contact }}</div>
                    <div class="col-md-12"><strong>Address:</strong> {{ $employee->address }}</div>
                    <div class="col-md-4"><strong>City:</strong> {{ $employee->city }}</div>
                    <div class="col-md-4"><strong>State:</strong> {{ $employee->state }}</div>
                    <div class="col-md-4"><strong>Country:</strong> {{ $employee->country }}</div>
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
                                    <div class="card doc-card h-100 text-center">
                                        @php $ext = pathinfo($doc->file_path, PATHINFO_EXTENSION); @endphp
                                        @if(in_array(strtolower($ext), ['jpg','jpeg','png','gif','bmp','webp']))
                                            <img src="{{ asset($doc->file_path) }}"
                                                 class="img-fluid rounded mb-2 doc-img"
                                                 alt="{{ $doc->file_name }}">
                                        @elseif(strtolower($ext) === 'pdf')
                                            <i class="bi bi-file-earmark-pdf text-danger" style="font-size:48px;"></i>
                                        @else
                                            <i class="bi bi-file-earmark" style="font-size:48px;"></i>
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


        {{-- Bank Details --}}
        <div class="col-12">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-dark text-white">Bank Details</div>
                <div class="card-body row g-3">
                    <div class="col-md-6"><strong>Bank Name:</strong> {{ $employee->bankDetail->bank_name ?? '-' }}</div>
                    <div class="col-md-6"><strong>Account Title:</strong> {{ $employee->bankDetail->account_title ?? '-' }}</div>
                    <div class="col-md-6"><strong>Account Number:</strong> {{ $employee->bankDetail->account_number ?? '-' }}</div>
                    <div class="col-md-6"><strong>IBAN:</strong> {{ $employee->bankDetail->iban ?? '-' }}</div>
                </div>
            </div>
        </div>

    </div>
@endsection
