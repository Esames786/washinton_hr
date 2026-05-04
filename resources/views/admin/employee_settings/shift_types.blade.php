@extends('layout.master')
@section('pageName', 'Shift Types')

@section('content')
    @include('partials.alerts')
    <div class="card h-100 p-0 radius-12">
        <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Shift Types</h5>
            <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#shiftModal">Add Shift Type</button>
        </div>
        <div class="card-body p-24">
            <table class="table table-bordered" id="shiftTable">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Shift Start</th>
                    <th>Shift End</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="shiftModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content radius-16 bg-base">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Shift Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('admin.shift_types.store') }}" method="POST" id="shiftForm" class="needs-validation" novalidate>
                        @csrf
                        <input type="hidden" name="id" id="shift_id">

                        <div class="row pb-8">
                            <div class="col-6">
                                <label>Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-3">
                                <label>Shift Start</label>
                                <input type="time" name="shift_start" class="form-control" required>
                            </div>
                            <div class="col-3">
                                <label>Shift End</label>
                                <input type="time" name="shift_end" class="form-control" required>
                            </div>
                        </div>
                        <div class="row pb-8">
                            <div class="col-12">
                                {{-- Attendance Rules --}}
                                <h6 class="mt-3">Attendance Rules</h6>
                                <table class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Time In</th>
                                        <th>Entry Weight (%)</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($attendance_status as $status)
                                        <tr>
                                            <td>
                                                {{ $status->name }}
                                                <input type="hidden" name="attendance_rules[{{ $status->id }}][attendance_status_id]"
                                                       value="{{ $status->id }}" required>
                                            </td>
                                            <td>
                                                <select name="attendance_rules[{{ $status->id }}][entry_time]"
                                                        class="form-control entry-time" data-status-id="{{ $status->id }}" required>
                                                    <option value="">-- Select Time --</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" step="1"
                                                       min="1" max="100"
                                                       name="attendance_rules[{{ $status->id }}][entry_weight]"
                                                       class="form-control entry-weight"
                                                       placeholder="Enter % (1 - 100)" required>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                                <div class="row d-none" id="status_div">
                                    <div class="col-6">
                                        <label>Status</label>
                                        <select name="status" id="status_id" class="form-control">
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <button type="submit" id="submitBtn" class="btn btn-primary">Save</button>
                                    <button type="button" class="btn btn-secondary cancel_btn" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {

            $("#shiftForm").on("submit", function (e) {
                let form = this;

                if (form.checkValidity() === false) {
                    e.preventDefault();      // stop the form
                    e.stopImmediatePropagation(); // stop any other submit handlers
                    $(form).addClass("was-validated");
                    return false; // extra safety
                }else {
                    form.submit();
                }
                // mark valid before native submission
                $(form).addClass("was-validated");
            });

            $(document).on("input", ".entry-weight", function () {
                let val = $(this).val();

                // convert to number
                val = parseFloat(val);

                // if decimal entered like 0.1, 0.25 => force to 1
                if (val > 0 && val < 1) {
                    $(this).val(1);
                    return;
                }

                // clamp between 1 and 100, and prevent decimals
                if (!isNaN(val)) {
                    val = Math.floor(val); // remove decimal part
                    if (val < 1) val = 1;
                    if (val > 100) val = 100;
                    $(this).val(val);
                }
            });
            function toMinutes(time) {
                if(!time) return 0;
                let [h, m] = time.split(":").map(Number);
                return h * 60 + m;
            }

            function toHHMM(minutes) {
                let h = String(Math.floor(minutes / 60)).padStart(2, '0');
                let m = String(minutes % 60).padStart(2, '0');
                return `${h}:${m}`; // always 24-hour format
            }

            function to12Hour(hhmm) {
                let [h, m] = hhmm.split(":").map(Number);
                let suffix = h >= 12 ? "PM" : "AM";
                h = (h % 12) || 12;
                return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')} ${suffix}`;
            }

            const TIME_STEP = 5; // minutes

            function populateTimes() {
                let shiftStart = $("input[name='shift_start']").val();
                let shiftEnd   = $("input[name='shift_end']").val();

                $(".entry-time").each(function() {
                    let $select = $(this);
                    $select.empty().append('<option value="">-- Select Time --</option>');

                    if (!shiftStart || !shiftEnd) return;

                    let startMins = toMinutes(shiftStart);
                    let endMins   = toMinutes(shiftEnd);

                    if (startMins === endMins) {
                        let hhmm = toHHMM(startMins);
                        $select.append(`<option value="${hhmm}">${to12Hour(hhmm)}</option>`);
                        return;
                    }

                    if (startMins < endMins) {
                        for (let t = startMins; t <= endMins; t += TIME_STEP) {
                            let hhmm = toHHMM(t);
                            $select.append(`<option value="${hhmm}">${to12Hour(hhmm)}</option>`);
                        }
                    } else {
                        for (let t = startMins; t < 24*60; t += TIME_STEP) {
                            let hhmm = toHHMM(t);
                            $select.append(`<option value="${hhmm}">${to12Hour(hhmm)}</option>`);
                        }
                        for (let t = 0; t <= endMins; t += TIME_STEP) {
                            let hhmm = toHHMM(t);
                            $select.append(`<option value="${hhmm}">${to12Hour(hhmm)}</option>`);
                        }
                    }
                });
            }

            $("input[name='shift_start'], input[name='shift_end']").on("change", populateTimes);
            // $('#shiftModal').on('shown.bs.modal', populateTimes);


            // init datatable
            let datatable = $('#shiftTable').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                rowId: 'id',
                ajax: {
                    url: "{{ route('admin.shift_types.index') }}",
                    data: function (d) {
                        if (d.search && d.search.value) {
                            d.columns[1].search.value = d.search.value;
                            d.search.value = '';
                        }
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name', orderable: false },
                    { data: 'shift_start', name: 'shift_start', orderable: false },
                    { data: 'shift_end', name: 'shift_end', orderable: false },
                    { data: 'status', name: 'status', orderable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            });

            // add new
            $('#addShiftBtn').click(function(){
                $('#shiftForm')[0].reset();
                $('#shift_id').val('');
                $('#modalTitle').text('Add Shift Type');
                $('#shiftForm').attr('action', "{{ route('admin.shift_types.store') }}");
                $('#shiftForm input[name="_method"]').remove();
                $('#status_div').addClass('d-none');
                $('#submitBtn').text('Submit');
                $('#shiftModal').modal('show');
            });

            // edit
            $('body').on('click', '.edit_btn', function() {
                let id = datatable.row($(this).closest('tr')).data().id;

                $.ajax({
                    url: "{{ route('admin.shift_types.edit', ':id') }}".replace(':id', id),
                    type: "GET",
                    dataType: "json",
                    success: function(response) {
                        let shift = response.shift;
                        let rules = response.rules || {};

                        $('#modalTitle').text('Update Shift Type');
                        $('#shift_id').val(shift.id);
                        $('[name="name"]').val(shift.name);
                        $('[name="shift_start"]').val(shift.shift_start);
                        $('[name="shift_end"]').val(shift.shift_end);
                        $('#status_div').removeClass('d-none');
                        $('#status_id').val(shift.status);

                        $('#status_div').removeClass('d-none');

                        $('#shiftForm').attr('action', "{{ route('admin.shift_types.update', ':id') }}".replace(':id', shift.id));
                        if ($('#shiftForm input[name="_method"]').length === 0) {
                            $('#shiftForm').append('<input type="hidden" name="_method" value="PUT">');
                        } else {
                            $('#shiftForm input[name="_method"]').val('PUT');
                        }

                        $('#submitBtn').text('Update');

                        // regenerate options
                        populateTimes();

                        // set saved rules
                        Object.keys(rules).forEach(function(statusId) {
                            let rule = rules[statusId];
                            let $select = $(`select.entry-time[data-status-id="${statusId}"]`);
                            let weightInput = $(`input[name="attendance_rules[${statusId}][entry_weight]"]`);

                            if (rule.entry_time) {
                                if ($select.find(`option[value="${rule.entry_time}"]`).length === 0) {
                                    $select.append(`<option value="${rule.entry_time}">${to12Hour(rule.entry_time)}</option>`);
                                }
                                $select.val(rule.entry_time);
                            }
                            if (weightInput.length) {
                                weightInput.val(rule.entry_weight ?? '');
                            }
                        });

                        $('#shiftModal').modal('show');
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Record not found!',
                        });
                    }
                });
            });

            // form validation
            $("#shiftForm").on("submit", function (e) {
                let form = this;

                if (form.checkValidity() === false) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    $(form).addClass("was-validated");
                    return false;
                } else {
                    form.submit();
                }
                $(form).addClass("was-validated");
            });

        });
    </script>
@endpush

