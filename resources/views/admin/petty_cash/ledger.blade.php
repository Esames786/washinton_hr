@extends('layout.master')

@section('pageName', 'Petty Cash Ledger')

@push('cssLinks')
    <style>
        .table-text-center, th, td {
            text-align: center !important;
        }
    </style>
@endpush

@section('content')
    @include('partials.alerts')

    <div class="card h-100">
        <div class="card-header border-bottom bg-base py-16 px-24 d-flex flex-wrap align-items-end gap-3">
            <div class="col-md-3 col-6 single-form-select2">
                <label class="form-label fw-semibold">Account</label>
                <select name="head_id" id="head_id" class="form-select">
                    <option value="">Select Account</option>
                    @foreach($heads as $head)
                        <option value="{{ $head->id }}">{{ $head->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 col-6">
                <label class="form-label fw-semibold">From Date</label>
                <input type="date" name="from_date" id="from_date" class="form-control">
            </div>
            <div class="col-md-3 col-6">
                <label class="form-label fw-semibold">To Date</label>
                <input type="date" name="to_date" id="to_date" class="form-control">
            </div>
            <div class="col-md-1 d-grid">
                <button type="button" id="search_btn" class="btn btn-primary d-flex">
                    <i class="bi bi-search"></i> Search
                </button>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped" id="LedgerTable" style="width: 100%;">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Particulars</th>
                    <th>Head</th>
                    <th>Debit</th>
                    <th>Credit</th>
                    <th>Balance</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            $('#head_id').select2({
                placeholder: "-- Select Account --",
                allowClear: true,
                width: '100%'
            });

            let datatable;

            $('#search_btn').on('click', function () {
                let from = $('#from_date').val();
                let to   = $('#to_date').val();

                if (!from || !to) {
                    toastr.error("Please select both From Date and To Date");
                    return;
                }
                if (to < from) {
                    toastr.error("To Date must be greater than or equal to From Date");
                    return;
                }

                if ($.fn.DataTable.isDataTable('#LedgerTable')) {
                    datatable.destroy();
                }

                datatable = $('#LedgerTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('admin.petty_cash.ledger.list') }}",
                        data: {
                            head_id: $('#head_id').val(),
                            from_date: from,
                            to_date: to
                        }
                    },
                    columns: [
                        {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                        {data: 'date', name: 'date'},
                        {data: 'particulars', name: 'particulars'},
                        {data: 'head', name: 'head'},
                        {data: 'debit', name: 'debit'},
                        {data: 'credit', name: 'credit'},
                        {data: 'balance', name: 'balance'},
                    ],
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                });
            });
        });
    </script>
@endpush
