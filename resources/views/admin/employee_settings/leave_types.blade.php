@extends('layout.master')
@section('pageName', 'Leave Types')

@section('content')
@include('partials.alerts')

<div class="card h-100 p-0 radius-12">
    <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-between">
        <div>
            <h5 class="mb-0">🏖️ Leave Types</h5>
            <small class="text-muted">Define leave types (Sick, Casual, Annual…) and mark each as Paid or Unpaid.</small>
        </div>
        <button class="btn btn-primary btn-sm" id="addNewBtn">+ Add Leave Type</button>
    </div>
    <div class="card-body p-24">
        <div class="table-responsive">
            <table class="table bordered-table sm-table mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Pay</th>
                        <th>Active</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="ltTableBody">
                    <tr><td colspan="5" class="text-center text-muted py-4">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="ltModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ltModalTitle">Add Leave Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="ltFormError" class="alert alert-danger d-none"></div>
                <input type="hidden" id="ltId">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                    <input type="text" id="ltName" class="form-control" maxlength="75" placeholder="e.g. Sick Leave">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea id="ltDesc" class="form-control" rows="2" maxlength="500"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Pay Type</label>
                    <select id="ltPaid" class="form-select">
                        <option value="1">Paid</option>
                        <option value="0">Unpaid</option>
                    </select>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="ltActive" checked>
                    <label class="form-check-label" for="ltActive">Active</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="ltSaveBtn">Save</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var CSRF = '{{ csrf_token() }}';
var LIST_URL  = '{{ route("admin.leave_types.list") }}';
var STORE_URL = '{{ route("admin.leave_types.store") }}';
var BASE_URL  = '{{ url("admin/leave_types") }}';

function loadTable() {
    $.getJSON(LIST_URL, function (res) {
        var rows = '';
        if (!res.data.length) {
            rows = '<tr><td colspan="5" class="text-center text-muted py-4">No leave types.</td></tr>';
        } else {
            $.each(res.data, function (i, t) {
                rows += '<tr>' +
                    '<td>' + t.name + '</td>' +
                    '<td>' + (t.description || '<span class="text-muted">—</span>') + '</td>' +
                    '<td>' + (t.is_paid ? '<span class="badge bg-success text-white">Paid</span>' : '<span class="badge bg-danger text-white">Unpaid</span>') + '</td>' +
                    '<td>' + (t.status == 1 ? '<span class="badge bg-success text-white">Active</span>' : '<span class="badge bg-secondary text-white">Inactive</span>') + '</td>' +
                    '<td>' +
                        '<button class="btn btn-xs btn-outline-primary me-1 edit-btn" data-t=\'' + JSON.stringify(t) + '\'>Edit</button>' +
                        '<button class="btn btn-xs btn-outline-danger del-btn" data-id="' + t.id + '">Delete</button>' +
                    '</td></tr>';
            });
        }
        $('#ltTableBody').html(rows);
    });
}
loadTable();

$('#addNewBtn').on('click', function () {
    $('#ltModalTitle').text('Add Leave Type');
    $('#ltId,#ltName,#ltDesc').val('');
    $('#ltPaid').val('1'); $('#ltActive').prop('checked', true);
    $('#ltFormError').addClass('d-none');
    $('#ltModal').modal('show');
});

$(document).on('click', '.edit-btn', function () {
    var t = $(this).data('t');
    $('#ltModalTitle').text('Edit Leave Type');
    $('#ltId').val(t.id); $('#ltName').val(t.name); $('#ltDesc').val(t.description || '');
    $('#ltPaid').val(t.is_paid ? '1' : '0'); $('#ltActive').prop('checked', t.status == 1);
    $('#ltFormError').addClass('d-none');
    $('#ltModal').modal('show');
});

$('#ltSaveBtn').on('click', function () {
    var id = $('#ltId').val();
    $('#ltFormError').addClass('d-none');
    $.ajax({
        url: id ? BASE_URL + '/' + id : STORE_URL,
        type: id ? 'PUT' : 'POST',
        data: {
            _token: CSRF,
            name: $('#ltName').val(),
            description: $('#ltDesc').val(),
            is_paid: $('#ltPaid').val(),
            status: $('#ltActive').is(':checked') ? 1 : 0,
        },
        success: function () { $('#ltModal').modal('hide'); loadTable(); },
        error: function (xhr) {
            var msg = 'An error occurred.';
            if (xhr.responseJSON && xhr.responseJSON.errors) msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
            else if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
            $('#ltFormError').html(msg).removeClass('d-none');
        }
    });
});

$(document).on('click', '.del-btn', function () {
    if (!confirm('Delete this leave type?')) return;
    $.ajax({ url: BASE_URL + '/' + $(this).data('id'), type: 'DELETE', data: { _token: CSRF },
        success: function () { loadTable(); },
        error: function (xhr) { alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Cannot delete.'); } });
});
</script>
@endpush
