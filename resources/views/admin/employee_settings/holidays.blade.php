@extends('layout.master')

@section('pageName', 'Holiday Management')

@push('cssLinks')
    <style>
        /*.table-text-center, th { text-align: center!important; }*/
        .dt-input { padding:10px!important; }
        .dt-length label { margin-left: 10px!important; }
    </style>
@endpush

@section('content')

    @include('partials.alerts')

    <div class="card h-100">
        <div class="card-header">
            <h4 class="card-title">Holiday Management</h4>
            <button class="btn btn-primary btn-sm float-end" id="add_btn">
                <i class="bi bi-plus"></i> Add New
            </button>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped" id="holidayTable" style="width: 100%;">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Date</th>
                    <th>Recurring</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Action</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="holidayModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content radius-16 bg-base">
                <div class="modal-header">
                    <h5 class="modal-title" id="holidayModalTitle">Add Holiday</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('admin.holidays.store') }}" method="post" id="holidayForm" class="needs-validation" novalidate>
                        @csrf
                        <input type="hidden" name="id" id="holidayId">

                        <div class="row pb-8">
                            <div class="col-4">
                                <label>Name</label>
                                <input type="text" name="name" id="name" class="form-control" required>
                            </div>
                            <div class="col-4">
                                <label>Recurring</label>
                                <select name="is_recurring" id="is_recurring" class="form-select" required>
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </select>
                            </div>
                            <div class="col-4">
                                <label>Status</label>
                                <select name="status" id="status" class="form-select" required>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="row pb-12">
                            <div class="col-6" id="date_container">
                                <label>Date</label>
                                <input type="date" name="holiday_date" id="holiday_date" class="form-control">
                            </div>
                            <div class="col-6 d-none month_day_container">
                                <label>Month</label>
                                <select name="month" id="month" class="form-select">
                                    @for($i=1;$i<=12;$i++)
                                        <option value="{{ $i }}">{{ date("F", mktime(0,0,0,$i,1)) }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-6 d-none month_day_container">
                                <label>Day</label>
                                <input type="number" min="1" max="31" name="day" id="day" class="form-control">
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" id="holidaySubmitBtn" class="btn btn-primary">Save</button>
                            <button type="button" class="btn btn-secondary cancel_btn" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $(function() {
            // validation
            $("#holidayForm").on("submit", function (e) {
                let form = this;
                if (form.checkValidity() === false) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    $(form).addClass("was-validated");
                    return false;
                }
            });

            $('#is_recurring').on('change', function () {
                if ($(this).val() == "1") {
                    $('#date_container').addClass('d-none');
                    $('.month_day_container').removeClass('d-none');
                } else {
                    $('#date_container').removeClass('d-none');
                    $('.month_day_container').addClass('d-none');
                }
            }).trigger('change');


            let datatable = $('#holidayTable').DataTable({
                processing: true,
                serverSide: true,
                order: [[0, 'asc']],
                rowId: 'id',
                ajax: "{{ route('admin.holidays.index') }}",
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'holiday_date', name: 'holiday_date' },
                    { data: 'is_recurring', name: 'is_recurring' },
                    { data: 'status', name: 'status', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ]
            });

            // Add
            $('#add_btn').click(function(){
                $('#holidayForm')[0].reset();
                $('#holidayForm').removeClass('was-validated');
                $('#holidayId').val('');
                $('#holidayModalTitle').text('Add Holiday');
                $('#holidayForm').attr('action', "{{ route('admin.holidays.store') }}");
                $('#holidayForm input[name="_method"]').remove();
                $('#holidaySubmitBtn').text('Save');
                $('#holidayModal').modal('show');
            });

            // Edit
            $('body').on('click', '.edit_btn', function() {
                let id = datatable.row($(this).closest('tr')).data().id;
                $.ajax({
                    url: "{{ route('admin.holidays.edit', ':id') }}".replace(':id', id),
                    type: "GET",
                    success: function(data) {
                        $('#holidayModalTitle').text('Update Holiday');
                        $('#holidayId').val(data.id);
                        $('#name').val(data.name);
                        $('#holiday_date').val(data.holiday_date);
                        $('#is_recurring').val(data.is_recurring);
                        $('#status').val(data.status);

                        $('#holidayForm').attr('action', "{{ route('admin.holidays.update',':id') }}".replace(':id', id));
                        if ($('#holidayForm input[name="_method"]').length === 0) {
                            $('#holidayForm').append('<input type="hidden" name="_method" value="PUT">');
                        }
                        $('#holidaySubmitBtn').text('Update');
                        $('#holidayModal').modal('show');
                    }
                });
            });

            // Cancel
            $('.cancel_btn').click(function () {
                $('#holidayForm')[0].reset();
                $('#holidaySubmitBtn').text('Save');
                $('#holidayModalTitle').text('Add Holiday');
                $('#holidayForm').attr('action', "{{ route('admin.holidays.store') }}");
                $('#holidayForm').attr('method', 'POST');
                $('#holidayForm input[name="_method"]').remove();
            });
        });
    </script>
@endpush
