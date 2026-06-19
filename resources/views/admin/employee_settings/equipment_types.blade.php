@extends('layout.master')
@section('pageName', 'Equipment Types')

@section('content')
@include('partials.alerts')

<div class="card h-100 p-0 radius-12">
    <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-between">
        <h5 class="mb-0">🔧 Equipment Types</h5>
        <button class="btn btn-primary btn-sm" id="addNewBtn">+ Add Equipment Type</button>
    </div>
    <div class="card-body p-24">
        <div class="table-responsive">
            <table class="table bordered-table sm-table mb-0" id="equipTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Icon</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="equipTableBody">
                    <tr><td colspan="6" class="text-center text-muted py-4">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add/Edit Modal --}}
<div class="modal fade" id="equipModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="equipModalTitle">Add Equipment Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="equipFormError" class="alert alert-danger d-none"></div>
                <input type="hidden" id="equipId">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Icon (emoji) <span class="text-muted fw-normal">(optional)</span></label>
                    <input type="text" id="equipIcon" class="form-control" maxlength="5" placeholder="e.g. 💻">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                    <input type="text" id="equipName" class="form-control" maxlength="100" placeholder="e.g. Laptop">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea id="equipDesc" class="form-control" rows="2" maxlength="255" placeholder="Optional description"></textarea>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="equipActive" checked>
                    <label class="form-check-label" for="equipActive">Active</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="equipSaveBtn">Save</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var CSRF = '{{ csrf_token() }}';
var LIST_URL   = '{{ route("admin.equipment_types.list") }}';
var STORE_URL  = '{{ route("admin.equipment_types.store") }}';
var UPDATE_BASE = '{{ url("admin/equipment_types") }}';

function loadTable() {
    $.getJSON(LIST_URL, function(res) {
        var rows = '';
        if (!res.data.length) {
            rows = '<tr><td colspan="6" class="text-center text-muted py-4">No equipment types yet.</td></tr>';
        } else {
            $.each(res.data, function(i, t) {
                rows += '<tr>' +
                    '<td>' + (i+1) + '</td>' +
                    '<td style="font-size:20px;">' + (t.icon || '') + '</td>' +
                    '<td>' + t.name + '</td>' +
                    '<td>' + (t.description || '<span class="text-muted">—</span>') + '</td>' +
                    '<td>' + (t.is_active
                        ? '<span class="badge bg-success text-white">Active</span>'
                        : '<span class="badge bg-secondary text-white">Inactive</span>') + '</td>' +
                    '<td>' +
                        '<button class="btn btn-xs btn-outline-primary me-1 edit-btn" data-id="'+t.id+'" data-name="'+t.name+'" data-icon="'+escape(t.icon||'')+'" data-desc="'+escape(t.description||'')+'" data-active="'+(t.is_active?1:0)+'">Edit</button>' +
                        '<button class="btn btn-xs btn-outline-danger del-btn" data-id="'+t.id+'">Delete</button>' +
                    '</td>' +
                '</tr>';
            });
        }
        $('#equipTableBody').html(rows);
    });
}

loadTable();

$('#addNewBtn').on('click', function() {
    $('#equipModalTitle').text('Add Equipment Type');
    $('#equipId').val('');
    $('#equipIcon').val('');
    $('#equipName').val('');
    $('#equipDesc').val('');
    $('#equipActive').prop('checked', true);
    $('#equipFormError').addClass('d-none');
    $('#equipModal').modal('show');
});

$(document).on('click', '.edit-btn', function() {
    var $b = $(this);
    $('#equipModalTitle').text('Edit Equipment Type');
    $('#equipId').val($b.data('id'));
    $('#equipIcon').val(unescape($b.data('icon')));
    $('#equipName').val($b.data('name'));
    $('#equipDesc').val(unescape($b.data('desc')));
    $('#equipActive').prop('checked', $b.data('active') == 1);
    $('#equipFormError').addClass('d-none');
    $('#equipModal').modal('show');
});

$('#equipSaveBtn').on('click', function() {
    var id   = $('#equipId').val();
    var url  = id ? UPDATE_BASE + '/' + id : STORE_URL;
    var method = id ? 'PUT' : 'POST';
    $('#equipFormError').addClass('d-none');

    $.ajax({
        url: url, type: method,
        data: {
            _token: CSRF,
            name: $('#equipName').val(),
            icon: $('#equipIcon').val(),
            description: $('#equipDesc').val(),
            is_active: $('#equipActive').is(':checked') ? 1 : 0,
        },
        success: function(res) {
            if (res.success) {
                $('#equipModal').modal('hide');
                loadTable();
            }
        },
        error: function(xhr) {
            var msg = 'An error occurred.';
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            }
            $('#equipFormError').html(msg).removeClass('d-none');
        }
    });
});

$(document).on('click', '.del-btn', function() {
    if (!confirm('Delete this equipment type?')) return;
    var id = $(this).data('id');
    $.ajax({
        url: UPDATE_BASE + '/' + id, type: 'DELETE',
        data: { _token: CSRF },
        success: function(res) { loadTable(); },
        error: function(xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Cannot delete.';
            alert(msg);
        }
    });
});
</script>
@endpush
