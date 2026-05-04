@extends('layout.master')

@section('pageName', 'Order list')

@push('cssLinks')
    <style>
        /*.table-text-center, th,td {*/
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
        <div class="card-header border-bottom bg-base py-16 px-24 d-flex flex-wrap align-items-end gap-3">
            <div class="col-md-2 col-4">

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
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table  bordered-table sm-table mb-0 table-text-center" id="orderTable">
                    <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Status</th>
                        <th>Order Date</th>
                        <th>Customer</th>
                        <th>Customer Email</th>
                        <th>Customer Phone</th>
                        <th>Customer Address</th>
                        <th>Book Price</th>
                        <th>Deposit Amount</th>
                        <th>Paid Amount</th>
                        <th>Paid Method</th>
                        <th>Received Date</th>
                        <th>Payment Status</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                </table>
            </div>

        </div>
    </div>

    <div class="modal fade" id="orderHistoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-between">
                    <h6 class="text-lg fw-semibold mb-0">Order History</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="card-body p-24">
                    <div class="table-responsive scroll-sm">
                        <table class="table bordered-table mb-0" id="orderHistoryTable">
                            <thead>
                            <tr>
                                <th>Status</th>
                                <th>Expected Date</th>
                                <th>Description</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')


    <script>
        $(function() {

            let datatable = $('#orderTable').DataTable({
                processing: true,
                serverSide: true,
                order: [[0, 'desc']],
                searching: true,
                rowId: 'id',
                ajax: {
                    url: "{{ route('employee.orders.list') }}", // route name
                    data: function (d) {
                        d.from_date = $('#from_date').val();
                        d.to_date   = $('#to_date').val();
                    }
                },
                columns: [
                    { data: 'id', name: 'id', orderable: true },
                    { data: 'Listing_Status', name: 'Listing_Status', orderable: false },
                    { data: 'created_at', name: 'created_at', orderable: false },
                    { data: 'Customer_Name', name: 'Customer_Name', orderable: false },
                    { data: 'Customer_Email', name: 'Customer_Email', orderable: false },
                    { data: 'Customer_Phone', name: 'Customer_Phone', orderable: false },
                    { data: 'Address', name: 'Address', orderable: false },
                    { data: 'Book_Price', name: 'Book_Price', orderable: false },
                    { data: 'Deposit_Amount', name: 'Deposit_Amount', orderable: false },
                    { data: 'Paid_Amount', name: 'Paid_Amount', orderable: false },
                    { data: 'Paid_Method', name: 'Paid_Method', orderable: false },
                    { data: 'Received_Date', name: 'Received_Date', orderable: false },
                    { data: 'payment_status', name: 'payment_status', orderable: false },
                    { data: 'action', name: 'action', orderable: false },

                ],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            });

            $('#search_btn').on('click', function () {
                let fromDate = $('#from_date').val();
                let toDate = $('#to_date').val();

                if (!fromDate) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing From Date',
                        text: 'Please select From Date.',
                    });
                    return false;
                }

                if (toDate && fromDate > toDate) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Date Range',
                        text: 'From Date cannot be greater than To Date.',
                    });
                    return false;
                }

                datatable.ajax.reload();
            });

            $(document).on('click', '.order-history-btn', function() {
                let orderId = $(this).data('id');
                let modal = $('#orderHistoryModal');
                let tbody = modal.find('tbody');

                // Loading placeholder
                tbody.html('<tr><td colspan="3" class="text-center">Loading...</td></tr>');

                $.ajax({
                    url: `/employee/orders/history/${orderId}`,
                    method: 'GET',
                    success: function(data) {
                        if (data.length) {
                            let html = '';
                            data.forEach(function(row){
                                // Generic color for all custom statuses
                                let statusClass = 'bg-info text-white';

                                html += `<tr>
                        <td><span class="${statusClass} px-24 py-4 rounded-pill fw-medium text-sm">${row.history_status}</span></td>
                        <td>${row.expected_date}</td>
                        <td>${row.history_description}</td>
                    </tr>`;
                            });
                            tbody.html(html);
                        } else {
                            tbody.html('<tr><td colspan="3" class="text-center">No history found</td></tr>');
                        }
                        modal.modal('show');
                    }
                });
            });
        });
    </script>
@endpush
