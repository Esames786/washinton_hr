@extends('layout.master')
@section('pageName', 'Productivity Rules')

@section('content')
@include('partials.alerts')

<div class="card h-100 p-0 radius-12">
    <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-between">
        <div>
            <h5 class="mb-0">📊 Productivity Rules</h5>
            <small class="text-muted">Maps an agent's daily active (productive) time — as a % of their shift — to an attendance status and salary deduction.</small>
        </div>
        <button class="btn btn-primary btn-sm" id="addNewBtn">+ Add Band</button>
    </div>
    <div class="card-body p-24">
        <div class="table-responsive">
            <table class="table bordered-table sm-table mb-0">
                <thead>
                    <tr>
                        <th>Label</th>
                        <th>Productive % (min–max of shift)</th>
                        <th>Attendance Status</th>
                        <th>Salary Deduction</th>
                        <th>Active</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="prTableBody">
                    <tr><td colspan="6" class="text-center text-muted py-4">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="prModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="prModalTitle">Add Band</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="prFormError" class="alert alert-danger d-none"></div>
                <input type="hidden" id="prId">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Label <span class="text-danger">*</span></label>
                    <input type="text" id="prLabel" class="form-control" maxlength="100" placeholder="e.g. Half Day">
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label fw-semibold">Min % of shift <span class="text-danger">*</span></label>
                        <input type="number" id="prMin" class="form-control" min="0" max="100" step="0.01">
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label fw-semibold">Max % of shift <span class="text-danger">*</span></label>
                        <input type="number" id="prMax" class="form-control" min="0" max="100" step="0.01">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Attendance Status <span class="text-danger">*</span></label>
                    <select id="prStatus" class="form-select">
                        <option value="2">Present (Full Day)</option>
                        <option value="3">Half Day</option>
                        <option value="9">Quarter Day</option>
                        <option value="5">Absent</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Salary Deduction % <span class="text-danger">*</span></label>
                    <input type="number" id="prDeduction" class="form-control" min="0" max="100" step="0.01" placeholder="0 = full pay, 100 = full deduction">
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="prActive" checked>
                    <label class="form-check-label" for="prActive">Active</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="prSaveBtn">Save</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var CSRF = '{{ csrf_token() }}';
var LIST_URL  = '{{ route("admin.productivity_rules.list") }}';
var STORE_URL = '{{ route("admin.productivity_rules.store") }}';
var BASE_URL  = '{{ url("admin/productivity_rules") }}';
var STATUS_LABELS = { 2: 'Present (Full Day)', 3: 'Half Day', 9: 'Quarter Day', 5: 'Absent' };

function loadTable() {
    $.getJSON(LIST_URL, function (res) {
        var rows = '';
        if (!res.data.length) {
            rows = '<tr><td colspan="6" class="text-center text-muted py-4">No bands defined.</td></tr>';
        } else {
            $.each(res.data, function (i, r) {
                rows += '<tr>' +
                    '<td>' + r.label + '</td>' +
                    '<td>' + r.min_percent + '% – ' + r.max_percent + '%</td>' +
                    '<td>' + (STATUS_LABELS[r.attendance_status_id] || r.attendance_status_id) + '</td>' +
                    '<td>' + r.deduction_percent + '%</td>' +
                    '<td>' + (r.status == 1 ? '<span class="badge bg-success text-white">Active</span>' : '<span class="badge bg-secondary text-white">Inactive</span>') + '</td>' +
                    '<td>' +
                        '<button class="btn btn-xs btn-outline-primary me-1 edit-btn" data-r=\'' + JSON.stringify(r) + '\'>Edit</button>' +
                        '<button class="btn btn-xs btn-outline-danger del-btn" data-id="' + r.id + '">Delete</button>' +
                    '</td></tr>';
            });
        }
        $('#prTableBody').html(rows);
    });
}
loadTable();

$('#addNewBtn').on('click', function () {
    $('#prModalTitle').text('Add Band');
    $('#prId,#prLabel,#prMin,#prMax,#prDeduction').val('');
    $('#prStatus').val('2'); $('#prActive').prop('checked', true);
    $('#prFormError').addClass('d-none');
    $('#prModal').modal('show');
});

$(document).on('click', '.edit-btn', function () {
    var r = $(this).data('r');
    $('#prModalTitle').text('Edit Band');
    $('#prId').val(r.id); $('#prLabel').val(r.label);
    $('#prMin').val(r.min_percent); $('#prMax').val(r.max_percent);
    $('#prStatus').val(r.attendance_status_id); $('#prDeduction').val(r.deduction_percent);
    $('#prActive').prop('checked', r.status == 1);
    $('#prFormError').addClass('d-none');
    $('#prModal').modal('show');
});

$('#prSaveBtn').on('click', function () {
    var id = $('#prId').val();
    $('#prFormError').addClass('d-none');
    $.ajax({
        url: id ? BASE_URL + '/' + id : STORE_URL,
        type: id ? 'PUT' : 'POST',
        data: {
            _token: CSRF,
            label: $('#prLabel').val(),
            min_percent: $('#prMin').val(),
            max_percent: $('#prMax').val(),
            attendance_status_id: $('#prStatus').val(),
            deduction_percent: $('#prDeduction').val(),
            status: $('#prActive').is(':checked') ? 1 : 0,
        },
        success: function () { $('#prModal').modal('hide'); loadTable(); },
        error: function (xhr) {
            var msg = 'An error occurred.';
            if (xhr.responseJSON && xhr.responseJSON.errors) msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
            $('#prFormError').html(msg).removeClass('d-none');
        }
    });
});

$(document).on('click', '.del-btn', function () {
    if (!confirm('Delete this band?')) return;
    $.ajax({ url: BASE_URL + '/' + $(this).data('id'), type: 'DELETE', data: { _token: CSRF },
        success: function () { loadTable(); } });
});
</script>
@endpush
