@extends('layout.master')
@section('pageName','Currency Conversion')
@push('cssLinks')
    <style>
        .table-text-center, th {
            text-align: center!important;
        }
        .dt-input{
            padding:10px!important;
        }
        .dt-length  label {
            margin-left: 10px!important;
        }
    </style>
@endpush
@section('content')

    @include('partials.alerts')

    <div class="card h-100 p-0 radius-12">
        <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-between">
            <h6 class="mb-0">Currency Conversion</h6>
            <div class="d-flex gap-2">
                <button class="btn btn-primary btn-sm" id="addNewBtn">Add New</button>
            </div>
        </div>

        <div class="card-body  p-24">
            <div class="table-responsive">
                <table class="table bordered-table sm-table mb-0 table-text-center" id="currencyTable">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Conversion Rate</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="currencyModal" tabindex="-1">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Currency Rate</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="currencyForm" method="POST" action="{{ route('admin.currency_settings.store') }}">
                    @csrf
                    <input type="hidden" name="id" id="currencyId">

                    <div class="modal-body">
                        <div class="mb-3">
                            <label>From Currency</label>
                            <input type="text" class="form-control" name="from_currency" value="USD" required readonly>
                        </div>
                        <div class="mb-3">
                            <label>To Currency</label>
                            <input type="text" class="form-control" name="to_currency" value="PKR" required readonly>
                        </div>
                        <div class="mb-3">
                            <label>Conversion Rate</label>
                            <input type="number" step="0.01" class="form-control" name="rate" required>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $(function() {
            let table = $('#currencyTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.currency_settings.index') }}",
                columns: [
                    { data: 'id' },
                    { data: 'from_currency' },
                    { data: 'to_currency' },
                    { data: 'rate' },
                    { data: 'status', orderable: false },
                    { data: 'action', orderable: false }
                ]
            });

            $('#addNewBtn').on('click', function() {
                $('#currencyForm')[0].reset();
                $('#currencyForm').attr('action', "{{ route('admin.currency_settings.store') }}");
                $('#currencyModal').modal('show');
            });

            $('body').on('click', '.edit_btn', function() {
                let id = table.row($(this).closest('tr')).data().id;
                $.get("{{ url('admin/currency_settings') }}/" + id + "/edit", function(data) {
                    $('#currencyId').val(data.id);
                    $('[name="from_currency"]').val(data.from_currency);
                    $('[name="to_currency"]').val(data.to_currency);
                    $('[name="rate"]').val(data.rate);
                    $('#currencyForm').attr('action', "{{ url('admin/currency_settings') }}/" + id);
                    if ($('#currencyForm input[name="_method"]').length === 0) {
                        $('#currencyForm').append('<input type="hidden" name="_method" value="PUT">');
                    }
                    $('#currencyModal').modal('show');
                });
            });
        });

    </script>
@endpush
