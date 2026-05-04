@extends('layout.master')

@section('pageName','Gratuity')
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
            <h5 class="mb-0">Gratuity Settings</h5>
            <div class="d-flex gap-2">
                <button class="btn btn-primary btn-sm" id="addNewBtn">
                    Add Gratuity
                </button>
{{--                <button class="btn btn-primary btn-sm" id="assignToRoleBtn">--}}
{{--                    Assign to Role--}}
{{--                </button>--}}
            </div>

        </div>
        <div class="card-body p-24">
            <div class="table-responsive">
                <table class="table bordered-table sm-table mb-0 table-text-center" id="gratuityTable">
                    <thead>
                    <tr>
{{--                        <th scope="col">--}}
{{--                            <div class="d-flex align-items-center gap-10">--}}
{{--                                <div class="form-check style-check d-flex align-items-center">--}}
{{--                                    <input class="form-check-input radius-4 border input-form-dark" type="checkbox" name="checkbox" id="selectAll">--}}
{{--                                </div>--}}
{{--                                ID--}}
{{--                            </div>--}}
{{--                        </th>--}}
                        <th>ID</th>
                        <th>Title</th>
                        <th>Min Service of Years</th>
                        <th>Employee Percentage</th>
                        <th>Company Percentage</th>
{{--                        <th>Amount Type</th>--}}
{{--                        <th>Amount Value</th>--}}
{{--                        <th>Gratuity Days/Year</th>--}}
{{--                        <th>Calculation Base</th>--}}
                        <th>Description</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    {{-- Assign to Role Modal --}}
{{--    <div class="modal fade" id="AssignRoleModal" tabindex="-1" role="dialog">--}}
{{--        <div class="modal-dialog modal-md modal-dialog-centered">--}}
{{--            <div class="modal-content radius-16 bg-base">--}}
{{--                <div class="modal-header">--}}
{{--                    <h5 class="modal-title" >Assign to Role</h5>--}}
{{--                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>--}}
{{--                </div>--}}
{{--                <div class="modal-body">--}}
{{--                    <form action="{{ route('admin.gratuity_settings.assign_roles')}}" method="post" id="AssignRoleForm" class="needs-validation" novalidate>--}}
{{--                        @csrf--}}

{{--                        <div class="row pb-8">--}}
{{--                            <div class="col-12">--}}
{{--                                <label>Roles</label>--}}
{{--                                <select name="role_id[]" id="role_id" multiple="multiple" class="form-control">--}}
{{--                                    @foreach($roles as $role)--}}
{{--                                        <option value="{{ $role->id }}">{{ $role->name }}</option>--}}
{{--                                    @endforeach--}}
{{--                                </select>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                        <div class="text-end mt-2">--}}
{{--                            <button type="submit" id="submitBtn" class="btn btn-primary">Assign to Role</button>--}}
{{--                            <button type="button" class="btn btn-secondary cancel_btn" data-bs-dismiss="modal">Cancel</button>--}}
{{--                        </div>--}}
{{--                    </form>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}

    {{-- Modal Add Modal --}}
    <div class="modal fade" id="gratuityModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content radius-16 bg-base">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Gratuity Setting</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('admin.gratuity_settings.store')}}" method="post" id="gratuityForm" class="needs-validation" novalidate>
                        @csrf

                        <input type="hidden" name="id" id="settingId">
                        <div class="row pb-8">
                            <div class="col-6">
                                <label>Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="col-6">
                                <label>Is Provident Fund</label>
                                <select name="is_pf" id="is_pf" class="form-control">
                                    <option value="0" selected>No</option>
                                    <option value="1">Yes</option>
                                </select>
                            </div>
                        </div>
                        <div class="row pb-8" id="year_div">
                            <div class="col-12">
                                <label>Minimum Years of Service</label>
                                <input type="number" name="eligibility_years" min="1" class="form-control only-integers" >
                            </div>
                        </div>
                        <div class="row pb-8 d-none" id="pf_div">
                            <div class="col-6">
                                <label>Employee Percentage</label>
                                <input type="number" name="employee_contribution_percentage"  placeholder="Enter % (1 - 100)" class="form-control percent-only" min="1" max="100" >
                            </div>
                            <div class="col-6">
                                <label>Company Percentage</label>
                                <input type="number" name="company_contribution_percentage"  placeholder="Enter % (1 - 100)" class="form-control" min="1" max="100" >
                            </div>
                        </div>
                        <div class="row pb-8">
                            <div class="col-12">
                                <label>Description</label>
                                <textarea name="description" class="form-control"></textarea>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" id="submitBtn" class="btn btn-primary">Save</button>
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
        $(document).ready(function() {


            $(document).on("input", ".only-integers", function () {
                let val = $(this).val();

                // Remove decimal if typed
                if (val.includes('.')) {
                    val = val.split('.')[0];
                }

                val = parseInt(val) || 0;

                // Force minimum 1
                if (val < 1) {
                    val = 1;
                }

                $(this).val(val);
            })
            $(document).on("input", ".percent-only", function () {
                let val = $(this).val();

                // convert to number
                val = parseFloat(val);

                // if decimal entered like 0.1, 0.25 => force to 1
                if (val > 0 && val < 1) {
                    $(this).val(1);
                    return;
                }

                // clamp between 1 and 100, and prevent decimals
                if (!isNaN(val)) {
                    val = Math.floor(val); // remove decimal part
                    if (val < 1) val = 1;
                    if (val > 100) val = 100;
                    $(this).val(val);
                }
            });
            $("#is_pf").on('change', function () {
                let is_pf = $(this).val();

                if (is_pf == 1) {
                    // PF enabled
                    $("#pf_div").removeClass('d-none');
                    $("#year_div").addClass("d-none");

                    // make PF fields required
                    $("input[name='employee_contribution_percentage']").attr("required", true);
                    $("input[name='company_contribution_percentage']").attr("required", true);

                    // remove required from years
                    $("input[name='eligibility_years']").removeAttr("required");

                } else {
                    // Gratuity (not PF)
                    $("#pf_div").addClass("d-none");
                    $("#year_div").removeClass('d-none');

                    // make years required
                    $("input[name='eligibility_years']").attr("required", true);

                    // remove required from PF fields
                    $("input[name='employee_contribution_percentage']").removeAttr("required");
                    $("input[name='company_contribution_percentage']").removeAttr("required");
                }
            });

            $("#is_pf").trigger("change");


            $('#role_id').select2({
                dropdownParent: $('#AssignRoleModal .modal-body'), // modal ke andar hi render hoga
                placeholder: "-- Select Role --",
                allowClear: true,
                width: '100%' // force full width
            });

            $("#gratuityForm").on("submit", function (e) {
                let form = this;

                if (form.checkValidity() === false) {
                    e.preventDefault();      // stop the form
                    e.stopImmediatePropagation(); // stop any other submit handlers
                    $(form).addClass("was-validated");
                    return false; // extra safety
                }else {
                    form.submit();
                }
                // mark valid before native submission
                $(form).addClass("was-validated");
            });


            // 1. Init DataTable
            let datatable = $('#gratuityTable').DataTable({
                processing: true,
                serverSide: true,
                // order: [],
                // [1, 'asc']
                searching: true,
                rowId: 'id',
                ajax: {
                    url: "{{ route('admin.gratuity_settings.index') }}",
                    data: function (d) {
                        if (d.search && d.search.value) {
                            d.columns[1].search.value = d.search.value; // title column
                            d.search.value = ''; // clear global search
                        }
                    }
                },
                columns: [
                    // {
                    //     data: 'id',
                    //     name: 'id',
                    //     orderable: false,
                    //     searchable: false,
                    //     render: function(data, type, row) {
                    //         return `
                    //             <div class="d-flex align-items-center gap-10">
                    //                 <div class="form-check style-check d-flex align-items-center">
                    //                     <input class="form-check-input radius-4 border border-neutral-400 row-checkbox" type="checkbox" name="checkbox" value="${row.id}">
                    //                 </div>
                    //                 ${row.id}
                    //             </div>
                    //         `;
                    //     }
                    // },
                    { data: 'id', name: 'id' },
                    { data: 'title', name: 'title', orderable: false },
                    { data: 'eligibility_years', name: 'eligibility_years' },

                    // { data: 'amount_type', name: 'amount_type' },
                    // { data: 'amount_value', name: 'amount_value' },

                    { data: 'employee_contribution_percentage', name: 'employee_contribution_percentage' },
                    { data: 'company_contribution_percentage', name: 'company_contribution_percentage' },
                    { data: 'description', name: 'description', orderable: false },
                    { data: 'status', name: 'status', orderable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            });

            // 2. Add New
            $('#addNewBtn').click(function(){
                $('#gratuityForm')[0].reset();
                $("#gratuityForm").removeClass("was-validated");
                $('#settingId').val('');
                $('#modalTitle').text('Add Gratuity Setting');
                $('#gratuityForm').attr('action', "{{ route('admin.gratuity_settings.store') }}");
                $('#gratuityForm input[name="_method"]').remove();
                $('#submitBtn').text('Submit');
                $('#gratuityModal').modal('show');

            });

            // 3. Edit
            $('body').on('click', '.edit_btn', function() {
                let id = datatable.row($(this).closest('tr')).data().id;
                $.ajax({
                    url: "{{ route('admin.gratuity_settings.edit', ':id') }}".replace(':id', id),
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        $("#gratuityForm").removeClass("was-validated");
                        $('#modalTitle').text('Update Gratuity Setting');
                        $('#settingId').val(data.id);
                        $('[name="title"]').val(data.title);
                        $('[name="description"]').val(data.description);
                        $('[name="employee_contribution_percentage"]').val(data.employee_contribution_percentage);
                        $('[name="company_contribution_percentage"]').val(data.company_contribution_percentage);
                        $('[name="eligibility_years"]').val(data.eligibility_years);
                        $('[name="status"]').val(data.status);

                        // Change form action & method
                        $('#gratuityForm').attr('action', "{{ route('admin.gratuity_settings.update',':id') }}".replace(':id', id),);
                        // Add hidden _method input for PUT
                        if ($('#gratuityForm input[name="_method"]').length === 0) {
                            $('#gratuityForm').append('<input type="hidden" name="_method" value="PUT">');
                        } else {
                            $('#gratuityForm input[name="_method"]').val('PUT');
                        }

                        // Change submit button text
                        $('#submitBtn').text('Update');
                        $('#gratuityModal').modal('show');
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Record not found!',
                        });
                    }
                });
            });

            $('#cancel_btn').on('click', function () {
                // Reset form fields
                $('#gratuityForm')[0].reset();
                // Change form back to Add mode
                $('#submitBtn').text('Submit');
                // Reset modal title
                $('#modalTitle').text('Add Gratuity Setting');
                $('#gratuityForm').attr('action', "{{ route('admin.gratuity_settings.store') }}");
                $('#gratuityForm').attr('method', 'POST');
                $('#gratuityForm input[name="_method"]').remove();
            });

            $(document).on('click', '#assignToRoleBtn', function () {
                let ids = [];
                $('.row-checkbox:checked').each(function() {
                    ids.push($(this).val());
                });

                if(ids.length === 0){
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Records Selected',
                        text: 'Please select at least one record.'
                    });
                    return;
                }

                // remove old hidden inputs
                $('#AssignRoleForm input[name="ids[]"]').remove();

                // append new hidden inputs for each id
                ids.forEach(function(id){
                    $('#AssignRoleForm').append('<input type="hidden" name="ids[]" value="'+id+'">');
                });

                // open modal
                $('#AssignRoleModal').modal('show');
            });



            // $('body').on('click', '.active_btn', function() {
            //     // Optional: button ya uske data attribute se kuch value lena
            //     let id = $(this).data('id');
            //
            //     $.ajax({
            //         url: '/your/ajax/url',   // yahan apni AJAX request ki URL dein
            //         type: 'POST',            // ya GET, jaisa bhi ho
            //         data: { id: id },        // agar data bhejna ho to
            //         headers: {
            //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')  // agar Laravel CSRF token chahiye ho
            //         },
            //         success: function(response) {
            //             // AJAX request successful hone par kya karna hai
            //             console.log('Success:', response);
            //             // Example: alert ya page update karna
            //             alert('Action completed!');
            //         },
            //         error: function(xhr) {
            //             // Agar error aaye to
            //             console.error('Error:', xhr.responseText);
            //
            //         }
            //     });
            // });

        });

    </script>
@endpush
