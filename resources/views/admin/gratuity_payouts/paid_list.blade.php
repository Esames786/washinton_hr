@extends('layout.master')

@section('pageName', 'Gratuity Payouts')

@push('cssLinks')
    <style>
        /*.table-text-center, th {*/
        /*    text-align: center!important;*/
        /*}*/
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
        <div class="card-header">
            <h4 class="card-title">Gratuity Payouts</h4>
            <button class="btn btn-primary btn-sm float-end" id="add_btn">
                <i class="bi bi-plus"></i> Add New
            </button>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped" id="gratuityPayoutTable" style="width: 100%;">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Employee</th>
                    <th>Payout Date</th>
                    <th>Total Balance</th>
                    <th>Paid Amount</th>
                    <th >Status</th>
{{--                    <th class="text-center">Action</th>--}}
                </tr>
                </thead>
            </table>
        </div>
    </div>



@endsection

@push('scripts')
    <script>
        $(function() {

            let datatable = $('#gratuityPayoutTable').DataTable({
                processing: true,
                serverSide: true,
                order: [[0, 'desc']],
                searching: true,
                rowId: 'id',
                ajax: {
                    url: "{{ route('admin.gratuity_payouts.paid_list') }}",
                    data: function (d) {
                        if (d.search && d.search.value) {
                            d.columns[1].search.value = d.search.value;
                            d.search.value = '';
                        }
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'employee', name: 'employee', orderable: false },
                    { data: 'payout_date', name: 'payout_date' },
                    { data: 'total_balance', name: 'total_balance' },
                    { data: 'paid_amount', name: 'paid_amount' },
                    { data: 'status_id', name: 'status_id', orderable: false, searchable: false },
                    // { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            });

        });
    </script>
@endpush
