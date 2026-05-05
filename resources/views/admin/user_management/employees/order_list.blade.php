@extends('layout.master')

@section('pageName', 'Order list')

@push('cssLinks')
    <style>
        .table-text-center, th, td { text-align: center !important; }
        .dt-input { padding: 10px !important; }
        .dt-length label { margin-left: 10px !important; }
    </style>
@endpush

@section('content')

    @include('partials.alerts')

    <div class="card h-100">
        <div class="card-header border-bottom bg-base py-16 px-24 d-flex flex-wrap align-items-end gap-3">
            <div class="col-md-4 col-6 form-select-2">
                <label class="form-label fw-semibold">Employees</label>
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
                <button type="button" id="search_btn" class="btn btn-primary">
                    <i class="bi bi-search"></i> Search
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table bordered-table sm-table mb-0 table-text-center" id="orderTable">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Agent</th>
                        <th>Order ID</th>
                        <th>Status</th>
                        <th>Order Date</th>
                        <th>Customer</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Route</th>
                        <th>Vehicle</th>
                        <th>Deposit</th>
                        <th>Paid</th>
                        <th>Pay Method</th>
                        <th>Payment Status</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    {{-- Order History Modal --}}
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
$(function () {

    $('#employee_ids').select2({
        placeholder: "-- Select Employee --",
        allowClear: true,
        width: '100%'
    });

    let datatable = $('#orderTable').DataTable({
        processing: true,
        serverSide: true,
        order: [[0, 'desc']],
        searching: true,
        rowId: 'id',
        ajax: {
            url: "{{ route('admin.employees.order_list') }}",
            data: function (d) {
                d.employee_ids = $('#employee_ids').val();
                d.from_date    = $('#from_date').val();
                d.to_date      = $('#to_date').val();
            }
        },
        columns: [
            { data: 'id',             name: 'order.id',             orderable: true },
            { data: 'full_name',      name: 'hr_employees.full_name', orderable: true },
            { data: 'order_taker_id', name: 'order.order_taker_id', orderable: false, visible: false },
            { data: 'pstatus',        name: 'order.pstatus',        orderable: false },
            { data: 'created_at',     name: 'order.created_at',     orderable: true },
            { data: 'oname',          name: 'order.oname',          orderable: false },
            { data: 'oemail',         name: 'order.oemail',         orderable: false },
            { data: 'ophone',         name: 'order.ophone',         orderable: false },
            { data: 'route',          name: 'route',                orderable: false },
            { data: 'ymk',            name: 'order.ymk',            orderable: false },
            { data: 'deposit_amount', name: 'order.deposit_amount', orderable: false },
            { data: 'paid_amount',    name: 'order.paid_amount',    orderable: false },
            { data: 'payment_method', name: 'order.payment_method', orderable: false },
            { data: 'payment_status', name: 'order.payment_status', orderable: false },
            { data: 'action',         name: 'action',               orderable: false },
        ],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
    });

    $('#search_btn').on('click', function () {
        let fromDate = $('#from_date').val();
        let toDate   = $('#to_date').val();

        if (!fromDate || !toDate) {
            Swal.fire({ icon: 'warning', title: 'Missing Dates', text: 'Please select both From Date and To Date.' });
            return false;
        }
        if (fromDate > toDate) {
            Swal.fire({ icon: 'error', title: 'Invalid Range', text: 'From Date cannot be after To Date.' });
            return false;
        }
        datatable.ajax.reload();
    });

    $(document).on('click', '.order-history-btn', function () {
        let orderId = $(this).data('id');
        let modal   = $('#orderHistoryModal');
        let tbody   = modal.find('tbody');

        tbody.html('<tr><td colspan="3" class="text-center">Loading...</td></tr>');

        $.ajax({
            url: '/admin/employees/order/history/' + orderId,
            method: 'GET',
            success: function (data) {
                if (data.length) {
                    let html = '';
                    data.forEach(function (row) {
                        html += `<tr>
                            <td><span class="badge bg-info text-white px-2 py-1">${row.history_status ?? '-'}</span></td>
                            <td>${row.expected_date ?? '-'}</td>
                            <td>${row.history_description ?? '-'}</td>
                        </tr>`;
                    });
                    tbody.html(html);
                } else {
                    tbody.html('<tr><td colspan="3" class="text-center text-muted">No history found</td></tr>');
                }
                modal.modal('show');
            },
            error: function () {
                tbody.html('<tr><td colspan="3" class="text-center text-danger">Failed to load history</td></tr>');
                modal.modal('show');
            }
        });
    });
});
</script>
@endpush
