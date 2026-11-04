@extends('layout.master')

@section('pageName', 'Attendance list')

@push('cssLinks')
    <style>
        .table-text-center, th,td {
            text-align: center!important;
        }
        .dt-input {
            padding:10px!important;
        }
        .dt-length label {
            margin-left: 10px!important;
        }
    </style>
@endpush

@section('content')

    @include('partials.alerts')

    <div class="card h-100">
        <div class="card-header border-bottom bg-base py-16 px-24 d-flex flex-wrap align-items-end gap-3">
            <div class="col-md-4 col-6 form-select-2">
                <label class="form-label fw-semibold">Subcontractors</label>
                <select name="employee_ids[]" id="employee_ids" multiple class="form-select">
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 col-6">
                <label class="form-label fw-semibold">From Date</label>
                <input type="date" name="from_date" id="from_date" class="form-control"
                       max="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
            </div>
            <div class="col-md-3 col-6">
                <label class="form-label fw-semibold">To Date</label>
                <input type="date" name="to_date" id="to_date" class="form-control"
                       max="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
            </div>

            <div class="col-md-1 d-grid">
                <button type="button" id="search_btn" class="btn btn-primary d-flex">
                    <i class="bi bi-search"></i> Search
                </button>
            </div>

            <div class="ms-auto d-grid">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#manualAttendanceModal">
                    <i class="bi bi-calendar-check"></i> Mark Attendance
                </button>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped" id="EmployeeAttendanceTable" style="width: 100%;">
                <thead>
                <tr>
                    <th>Subcontractor Name</th>
                    <th>Attendance Date</th>
                    <th>Check-In</th>
                    <th>Check-Out</th>
                    <th>Working Hours</th>
                    <th>Productive Time</th>
                    <th>Status</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>

    {{-- Admin marks or corrects a day by hand (florida, 4 Nov 2026). Saved through the same shift
         rules as a real check-in/out; the row shows a "Manual" tag. --}}
    <div class="modal fade" id="manualAttendanceModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="manualAttendanceForm" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Mark Attendance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted text-sm mb-12">
                        Status, late deduction and working hours are worked out from the subcontractor's shift,
                        exactly as if they had checked in themselves. Marking a day that already has attendance replaces it.
                    </p>
                    <div class="mb-12">
                        <label class="form-label fw-semibold">Subcontractor <span class="text-danger">*</span></label>
                        <select name="employee_id" id="manual_employee_id" class="form-select" required>
                            <option value="">-- Select Subcontractor --</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-12">
                        <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                        <input type="date" name="attendance_date" class="form-control" required
                               max="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                    </div>
                    <div class="row">
                        <div class="col-6 mb-12">
                            <label class="form-label fw-semibold">Check-In <span class="text-danger">*</span></label>
                            <input type="time" name="check_in" class="form-control" required>
                        </div>
                        <div class="col-6 mb-12">
                            <label class="form-label fw-semibold">Check-Out</label>
                            <input type="time" name="check_out" class="form-control">
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Note</label>
                        <input type="text" name="remarks" class="form-control" maxlength="200" placeholder="e.g. forgot to check in">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="manualAttendanceSave">Save</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')


    <script>
        $(function() {

            $('#employee_ids').select2({
                // dropdownParent: $('#AssignRoleModal .modal-body'), // modal ke andar hi render hoga
                placeholder: "-- Select Subcontractor --",
                allowClear: true,
                width: '100%' // force full width
            });
            let datatable = $('#EmployeeAttendanceTable').DataTable({
                processing: true,
                serverSide: true,
                order: [[0, 'asc']],
                searching: true,
                rowId: 'id',
                ajax: {
                    url: "{{ route('admin.employees.attendance_list') }}",
                    data: function (d) {
                        d.employee_ids = $('#employee_ids').val();
                        d.from_date = $('#from_date').val();
                        d.to_date   = $('#to_date').val();
                    }
                },
                columns: [
                    { data: 'employee_name', name: 'employee_name', orderable: true },
                    { data: 'attendance_date', name: 'attendance_date', orderable: true },
                    { data: 'check_in', name: 'check_in', orderable: false },
                    { data: 'check_out', name: 'check_out', orderable: false },
                    { data: 'working_hours', name: 'working_hours', orderable: false },
                    { data: 'productive', name: 'productive', orderable: false, searchable: false },
                    { data: 'attendance_status_name', name: 'attendance_status_name', orderable: false, searchable: false },
                ],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            });


            $('#search_btn').on('click', function () {
                let fromDate = $('#from_date').val();
                let toDate = $('#to_date').val();

                if (!fromDate || !toDate) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Dates',
                        text: 'Please select both From Date and To Date before searching.',
                    });
                    return false;
                }

                if (fromDate > toDate) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Date Range',
                        text: 'From Date cannot be greater than To Date.',
                    });
                    return false;
                }

                datatable.ajax.reload();
            });

            $('#manual_employee_id').select2({
                dropdownParent: $('#manualAttendanceModal'),
                placeholder: "-- Select Subcontractor --",
                width: '100%'
            });

            $('#manualAttendanceForm').on('submit', function (e) {
                e.preventDefault();
                let $btn = $('#manualAttendanceSave').prop('disabled', true).text('Saving...');
                $.ajax({
                    url: "{{ route('admin.employees.attendance_manual') }}",
                    method: 'POST',
                    data: $(this).serialize(),
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                }).done(function (res) {
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('manualAttendanceModal')).hide();
                    $('#manualAttendanceForm')[0].reset();
                    $('#manual_employee_id').val(null).trigger('change');
                    Swal.fire({ icon: 'success', title: 'Saved', text: res.message });
                    datatable.ajax.reload(null, false);
                }).fail(function (xhr) {
                    let res = xhr.responseJSON || {};
                    let msg = res.message || 'Could not save attendance.';
                    if (res.errors) { msg = Object.values(res.errors).flat().join('\n'); }
                    Swal.fire({ icon: 'error', title: 'Not saved', text: msg });
                }).always(function () {
                    $btn.prop('disabled', false).text('Save');
                });
            });
        });
    </script>
@endpush
