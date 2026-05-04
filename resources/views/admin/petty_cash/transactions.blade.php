@extends('layout.master')

@section('pageName','Petty Cash Transactions')

@push('cssLinks')
    <style>
        .table-text-center, th {
            text-align: center!important;
        }
        .dt-input{
            padding:10px!important;
        }
        .dt-length label {
            margin-left: 10px!important;
        }
    </style>
@endpush

@section('content')

    @include('partials.alerts')

    <div class="card h-100 p-0 radius-12">
        <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Petty Cash Transactions</h5>
            <div class="d-flex gap-2">
                <button class="btn btn-primary btn-sm" id="addTxnBtn">
                    Add Transaction
                </button>
            </div>
        </div>
        <div class="card-body p-24">
            <div class="table-responsive">
                <table class="table bordered-table sm-table mb-0 table-text-center" id="txnsTable">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Master</th>
                        <th>Account Head</th>
                        <th>Account Type</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Current Balance</th>
                        <th>Description</th>
                        <th>Image</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
{{--                        <th class="text-center">Action</th>--}}
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="txnModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content radius-16 bg-base">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Transaction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="txnForm" class="needs-validation" novalidate>
                        @csrf
                        <input type="hidden" name="id" id="txnId">

                        <div class="row pb-8">
                            <div class="col-md-6 mb-3">
                                <label>Master</label>
                                <select name="master_id" id="txnMaster" class="form-select" required>
                                    <option value="">-- Select Master --</option>
                                    @foreach(App\Models\PettyCashMaster::all() as $m)
                                        <option value="{{ $m->id }}">{{ $m->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Head</label>
                                <select name="head_id" id="txnHead" class="form-select" required>
                                    <option value="">-- Select Head --</option>
                                    @foreach(App\Models\PettyCashHead::all() as $h)
                                        <option value="{{ $h->id }}">{{ $h->name }} ({{ $h->type }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row pb-8">
                            <div class="col-md-6 mb-3">
                                <label>Date</label>
                                <input type="date" name="date" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Type</label>
                                <select name="entry_type" id="txnType" class="form-select" required>
                                    <option value="debit">Debit</option>
                                    <option value="credit">Credit</option>
                                </select>
                            </div>
                        </div>
                        <div class="row pb-8 d-none" id="payroll_div">
                            <div class="col-md-4 mb-3">
                                <label>Payroll</label>
                                <select name="payroll" id="payroll_id" class="form-select" >

                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Total Deduction</label>
                                <input type="number" step="0.01" id="total_deduction" name="total_deduction" class="form-control" readonly>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Total Net Salary</label>
                                <input type="number" step="0.01" id="total_net_salary" name="total_net_salary" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="row pb-8">
                            <div class="col-md-6 mb-3">
                                <label>Amount</label>
                                <input type="number" step="0.01" name="amount" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Description</label>
                                <textarea name="description" class="form-control"></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Image</label>
                                <input type="file" name="image" class="form-control">
                            </div>
                        </div>


                        <div class="text-end">
                            <button type="submit" class="btn btn-primary" id="submitBtn">Save</button>
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
        $(function () {
            let table = $('#txnsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.petty_cash.transactions.index') }}",
                columns: [
                    { data: 'id' },
                    { data: 'master' },
                    { data: 'head' },
                    { data: 'account_type', orderable: false, searchable: false }, // ✅ New Column
                    { data: 'date' },
                    { data: 'entry_type' },
                    { data: 'amount' },
                    { data: 'balance' },
                    { data: 'description' },
                    { data: 'image', orderable: false, searchable: false }, // 👈 Add this
                    { data: 'status'},
                    { data: 'action',orderable: false, searchable: false }
                    // { data: 'action', orderable: false, searchable: false }
                ],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            });

            // Add Transaction
            $('#addTxnBtn').click(function () {
                $('#txnForm')[0].reset();
                $("#txnForm").removeClass("was-validated");
                $('#txnId').val('');
                $('#modalTitle').text('Add Transaction');
                $('#submitBtn').text('Save');
                $('#txnModal').modal('show');
            });

            // Save / Update Transaction
            {{--$('#txnForm').submit(function (e) {--}}
            {{--    e.preventDefault();--}}
            {{--    let id = $('#txnId').val();--}}
            {{--    let url = id ? "/admin/petty_cash/transactions/" + id : "{{ route('admin.petty_cash.transactions.store') }}";--}}
            {{--    let method = id ? "PUT" : "POST";--}}

            {{--    $.ajax({--}}
            {{--        url, method,--}}
            {{--        data: $(this).serialize(),--}}
            {{--        success: function (res) {--}}
            {{--            $('#txnModal').modal('hide');--}}
            {{--            table.ajax.reload();--}}
            {{--            Swal.fire('Success!', res.message, 'success');--}}
            {{--        },--}}
            {{--        error: function (xhr) {--}}
            {{--            if (xhr.status === 422) {--}}

            {{--                if (xhr.responseJSON?.errors) {--}}
            {{--                    // Validation case (Laravel validator)--}}
            {{--                    let errors = xhr.responseJSON.errors;--}}
            {{--                    let message = Object.values(errors).flat().join("<br>");--}}
            {{--                    Swal.fire('Validation Error!', message, 'error');--}}
            {{--                } else if (xhr.responseJSON?.message) {--}}
            {{--                    // Business logic error (e.g., Insufficient funds)--}}
            {{--                    Swal.fire('Error!', xhr.responseJSON.message, 'error');--}}
            {{--                } else {--}}
            {{--                    Swal.fire('Error!', 'Unprocessable entity (422)', 'error');--}}
            {{--                }--}}
            {{--            } else if (xhr.status === 403) {--}}
            {{--                Swal.fire('Error!', 'Unauthorized action!', 'error');--}}
            {{--            } else if (xhr.status === 404) {--}}
            {{--                Swal.fire('Error!', 'Resource not found!', 'error');--}}
            {{--            } else {--}}
            {{--                Swal.fire('Error!', 'Something went wrong!' , 'error');--}}
            {{--            }--}}
            {{--        }--}}
            {{--    });--}}
            {{--});--}}
            $('#txnForm').submit(function (e) {
                e.preventDefault();
                let id = $('#txnId').val();
                let url = id ? "/admin/petty_cash/transactions/" + id : "{{ route('admin.petty_cash.transactions.store') }}";
                let method = id ? "POST" : "POST"; // PUT ko bhi POST hi rakho agar _method bhejna hai

                let formData = new FormData(this);
                if (id) formData.append('_method', 'PUT');

                $.ajax({
                    url: url,
                    type: method,
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        $('#txnModal').modal('hide');
                        $('#txnForm')[0].reset();
                        $('#txnsTable').DataTable().ajax.reload();
                        Swal.fire('Success!', res.message, 'success');
                    },
                    error: function (xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Something went wrong!', 'error');
                    }
                });
            });

            // Edit Transaction
            $(document).on('click', '.edit_btn', function () {
                let id = $(this).data('id');
                $.get("/admin/petty_cash/transactions/" + id + "/edit", function (data) {
                    $("#txnForm").removeClass("was-validated");
                    $('#modalTitle').text('Update Transaction');
                    $('#txnId').val(data.id);
                    $('#txnMaster').val(data.master_id);
                    $('#txnHead').val(data.head_id);
                    $('[name="date"]').val(data.date);
                    $('#txnType').val(data.entry_type);
                    $('[name="amount"]').val(data.amount);
                    $('[name="description"]').val(data.description);
                    $('#submitBtn').text('Update');
                    $('#txnModal').modal('show');
                });
            });

            // Approve Transaction
            $(document).on('click', '.approve_btn', function() {
                let id = $(this).data('id');
                $.post(`/admin/petty_cash/transactions/${id}/approve`, {_token: '{{ csrf_token() }}'}, function(res) {
                    table.ajax.reload();
                    Swal.fire('Success!', res.message, 'success');
                }).fail(function(xhr) {
                    Swal.fire('Error!', xhr.responseJSON?.message || 'Something went wrong!', 'error');
                });
            });

            // Reject Transaction
            $(document).on('click', '.reject_btn', function() {
                let id = $(this).data('id');
                $.post(`/admin/petty_cash/transactions/${id}/reject`, {_token: '{{ csrf_token() }}'}, function(res) {
                    table.ajax.reload();
                    Swal.fire('Success!', res.message, 'success');
                }).fail(function(xhr) {
                    Swal.fire('Error!', xhr.responseJSON?.message || 'Something went wrong!', 'error');
                });
            });


            $(document).on('change','#txnHead',function () {
                let head_id = $(this).val();

                if(head_id != undefined && head_id == 1) {
                    $.ajax({
                        url: "{{ route('admin.petty_cash.transactions.payroll_list', '') }}/" + head_id,
                        type: 'GET',
                        success: function(res) {
                            if(res.status == 1) {
                                // Select box clear karo pehle
                                $('#payroll_id').empty().append('<option value="">Select Payroll</option>');

                                // Payroll list append karo
                                $.each(res.payroll_list, function(index, payroll) {
                                    $('#payroll_id').append(
                                        `<option value="${payroll.id}" data-deduction="${payroll.total_deduction}" data-net-salary="${payroll.total_net_salary}">${payroll.payroll_month}</option>`
                                    );
                                });

                                // Row ko show karo
                                $('#payroll_div').removeClass('d-none');
                            }
                        }
                    });
                } else {
                    // Agar head_id != 1 hai to row hide kar do
                    $('#payroll_div').addClass('d-none');
                    $('#payroll_id').empty();
                }
            });

            $(document).on('change','#payroll_id',function () {
                let selectedOption = $(this).find(":selected");

                let total_deduction   = selectedOption.data('deduction');   // data-deduction
                let total_net_salary  = selectedOption.data('net-salary'); // data-net-salary

                if(total_deduction > 0) {
                    $("#total_deduction").val(total_deduction);
                } else {
                    $("#total_deduction").val('');
                }

                if(total_net_salary > 0) {
                    $("#total_net_salary").val(total_net_salary);
                } else {
                    $("#total_net_salary").val('');
                }
            });



        });
    </script>
@endpush
