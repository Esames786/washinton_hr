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
                    <th class="text-center">Action</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="gratuityPayoutModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content radius-16 bg-base">
                <div class="modal-header">
                    <h5 class="modal-title" id="payoutModalTitle">Add Gratuity Payout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('admin.gratuity_payouts.store') }}" method="post" id="gratuityPayoutForm" class="needs-validation" novalidate>
                        @csrf
                        <input type="hidden" name="id" id="payoutId">

                        <div class="row pb-8">
                            <div class="col-12 mb-3">
                                <label>Employees</label>
                                <select name="employee_id" id="employee_id" class="form-control">
                                    <option value="">-- Select Employee --</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label>Remarks</label>
                                <textarea name="remarks" id="remarks" class="form-control" rows="4" required></textarea>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" id="payoutSubmitBtn" class="btn btn-primary">Save</button>
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

            // Form Validation
            $("#gratuityPayoutForm").on("submit", function (e) {
                let form = this;
                if (form.checkValidity() === false) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    $(form).addClass("was-validated");
                    return false;
                }
            });

            let datatable = $('#gratuityPayoutTable').DataTable({
                processing: true,
                serverSide: true,
                order: [[0, 'desc']],
                searching: true,
                rowId: 'id',
                ajax: {
                    url: "{{ route('admin.gratuity_payouts.index') }}",
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
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            });

            // Add New
            $('#add_btn').click(function(){
                $('#gratuityPayoutForm')[0].reset();
                $('#gratuityPayoutForm').removeClass('was-validated');
                $('#payoutId').val('');
                $('#payoutModalTitle').text('Add Gratuity Payout');
                $('#gratuityPayoutForm').attr('action', "{{ route('admin.gratuity_payouts.store') }}");
                $('#gratuityPayoutForm input[name="_method"]').remove();
                $('#payoutSubmitBtn').text('Save');
                $('#gratuityPayoutModal').modal('show');
            });

            // Edit
            $('body').on('click', '.edit_btn', function() {
                let id = datatable.row($(this).closest('tr')).data().id;
                $.ajax({
                    url: "{{ route('admin.gratuity_payouts.edit', ':id') }}".replace(':id', id),
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        $('#payoutModalTitle').text('Update Gratuity Payout');
                        $('#payoutId').val(data.id);
                        $('#remarks').val(data.remarks);
                        $("#employee_id").val(data.employee_id)

                        $('#gratuityPayoutForm').attr('action', "{{ route('admin.gratuity_payouts.update',':id') }}".replace(':id', id));
                        if ($('#gratuityPayoutForm input[name="_method"]').length === 0) {
                            $('#gratuityPayoutForm').append('<input type="hidden" name="_method" value="PUT">');
                        }
                        $('#payoutSubmitBtn').text('Update');
                        $('#gratuityPayoutModal').modal('show');
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Record not found!',
                        });
                    }
                });
            });

            // Cancel Button
            $('.cancel_btn').on('click', function () {
                $('#gratuityPayoutForm')[0].reset();
                $('#payoutSubmitBtn').text('Save');
                $('#payoutModalTitle').text('Add Gratuity Payout');
                $('#gratuityPayoutForm').attr('action', "{{ route('admin.gratuity_payouts.store') }}");
                $('#gratuityPayoutForm').attr('method', 'POST');
                $('#gratuityPayoutForm input[name="_method"]').remove();
            });

            $(document).on('click', '.approved_btn', function () {
                let id = datatable.row($(this).closest('tr')).data().id;
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you want to approve this gratuity payout?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Approve it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('admin.gratuity_payouts.approved') }}',
                            type: 'POST',
                            data: {
                                payout_id: id
                            },
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            success: function (response) {
                                if (response.success) {
                                    Swal.fire(
                                        'Approved!',
                                        response.message,
                                        'success'
                                    );
                                    datatable.ajax.reload(); // reload table without page reset
                                }
                            },
                            error: function (xhr) {
                                console.log(xhr.responseText);
                                Swal.fire(
                                    'Error!',
                                    'Something went wrong!',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });

            $(document).on('click', '.paid_btn', function () {
                let id = datatable.row($(this).closest('tr')).data().id;
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you want to paid this gratuity payout?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Paid it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('admin.gratuity_payouts.paid') }}',
                            type: 'POST',
                            data: {
                                payout_id: id
                            },
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            success: function (response) {
                                if (response.success) {
                                    Swal.fire(
                                        'Paid!',
                                        response.message,
                                        'success'
                                    );
                                    datatable.ajax.reload(); // reload table without page reset
                                }
                            },
                            error: function (xhr) {
                                console.log(xhr.responseText);
                                Swal.fire(
                                    'Error!',
                                    'Something went wrong!',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });



        });
    </script>
@endpush
