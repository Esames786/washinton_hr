@extends('layout.master')

@section('pageName', 'Users')

@push('cssLinks')
{{--    <link rel="stylesheet" href="{{ asset('assets/vendor/datatable/css/dataTables.bootstrap5.min.css') }}">--}}
@endpush

@section('content')

    @include('partials.alerts')

    <div class="card h-100">
        <div class="card-header">
            <h4 class="card-title">Users</h4>
            <button class="btn btn-primary btn-sm float-end" id="add_btn">
                <i class="bi bi-plus"></i> Add New
            </button>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped" id="usersTable" style="width: 100%;">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th class="text-center">Action</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="userModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content radius-16 bg-base">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalTitle">Add User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('admin.users.store') }}" method="post" id="userForm" class="needs-validation" novalidate enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="id" id="userId">

                        <div class="row pb-8">
                            <div class="col-6">
                                <label>Name</label>
                                <input type="text" name="name" id="name" class="form-control" required>
                            </div>

                            <div class="col-6">
                                <label>Email</label>
                                <input type="email" name="email" id="email" class="form-control" required>
                            </div>
                        </div>

                        <div class="row pb-8">
                            <div class="col-6">
                                <label>Role</label>
                                <select name="role_id" id="role_id" class="form-select" required>
                                    <option value="">Select Role</option>
                                    @foreach($roles  as $role )
                                        <option value="{{$role->id}}">{{$role->name}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-6" id="passwordRow">
                                <label>Password</label>
                                <input type="password" name="password" id="password" class="form-control" minlength="8"  required>
                            </div>
                        </div>

                        {{-- Profile Upload --}}
                        <div class="row pb-8">
                            <div class="col-12">
                                <label>Profile Picture</label>
                                <div class="upload-image-wrapper d-flex align-items-center gap-3">
                                    <div class="uploaded-img d-none position-relative h-120-px w-120-px border input-form-light radius-8 overflow-hidden border-dashed bg-neutral-50">
                                        <button type="button" class="uploaded-img__remove position-absolute top-0 end-0 z-1 text-2xxl line-height-1 me-8 mt-8 d-flex">
                                            <iconify-icon icon="radix-icons:cross-2" class="text-xl text-danger-600"></iconify-icon>
                                        </button>
                                        <img id="uploaded-img__preview" class="w-100 h-100 object-fit-cover" src="{{ asset('assets/images/user.png') }}" alt="image" >
                                    </div>

                                    <label id="img_label" class="upload-file h-120-px w-120-px border input-form-light radius-8 overflow-hidden border-dashed bg-neutral-50 bg-hover-neutral-200 d-flex align-items-center flex-column justify-content-center gap-1" for="upload-file">
                                        <iconify-icon icon="solar:camera-outline" class="text-xl text-secondary-light"></iconify-icon>
                                        <span class="fw-semibold text-secondary-light">Upload</span>
                                        <input id="upload-file" type="file" name="profile_path" hidden>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" id="userSubmitBtn" class="btn btn-primary">Save</button>
                            <button type="button" class="btn btn-secondary cancel_btn" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


@endsection

@push('scripts')
{{--    <script src="{{ asset('assets/vendor/datatable/js/jquery.dataTables.min.js') }}"></script>--}}
{{--    <script src="{{ asset('assets/vendor/datatable/js/dataTables.bootstrap5.min.js') }}"></script>--}}

    <script>
        $(function() {

            // Form Validation
            $("#userForm").on("submit", function (e) {
                let form = this;
                if (form.checkValidity() === false) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    $(form).addClass("was-validated");
                    return false;
                }
            });

            let datatable = $('#usersTable').DataTable({
                processing: true,
                serverSide: true,
                order: [[0, 'asc']],
                ajax: "{{ route('admin.users.index') }}",
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'email', name: 'email' },
                    { data: 'status', name: 'status', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ]
            });

            // File Upload Preview Logic
            const fileInput = document.getElementById("upload-file");
            const imagePreview = document.getElementById("uploaded-img__preview");
            const uploadedImgContainer = document.querySelector(".uploaded-img");
            const removeButton = document.querySelector(".uploaded-img__remove");
            const imgLabel = document.getElementById("img_label");

            fileInput.addEventListener("change", (e) => {
                if (e.target.files.length) {
                    const src = URL.createObjectURL(e.target.files[0]);
                    imagePreview.src = src;
                    uploadedImgContainer.classList.remove('d-none');
                    imgLabel.classList.add('d-none');
                }
            });
            removeButton.addEventListener("click", () => {
                imagePreview.src = "{{ asset('assets/images/user.png') }}";
                uploadedImgContainer.classList.add('d-none');
                fileInput.value = "";
                imgLabel.classList.remove('d-none')
            });

            // Add
            $('#add_btn').click(function(){
                $('#userForm')[0].reset();
                $('#userId').val('');
                $('#passwordRow').show();
                $('#password').attr('required', true);
                imagePreview.src = "{{ asset('assets/images/user.png') }}";
                uploadedImgContainer.classList.add('d-none');
                $('#userModalTitle').text('Add User');
                $('#userForm').attr('action', "{{ route('admin.users.store') }}");
                $('#userForm input[name="_method"]').remove();
                $('#userSubmitBtn').text('Save');
                $('#userModal').modal('show');
            });

            // Edit
            $('body').on('click', '.edit_btn', function() {
                let id = datatable.row($(this).closest('tr')).data().id;
                $.ajax({
                    url: "{{ route('admin.users.edit', ':id') }}".replace(':id', id),
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        $('#userModalTitle').text('Update User');
                        $('#userId').val(data.id);
                        $('#name').val(data.name);
                        $('#email').val(data.email);
                        $('#role_id').val(data.role_id);
                        $('#passwordRow').hide();
                        $('#password').removeAttr('required');

                        // Profile preview
                        if (data.profile_path) {
                            imagePreview.src = data.profile_path;
                            uploadedImgContainer.classList.remove('d-none');
                        } else {
                            imagePreview.src = "{{ asset('assets/images/user.png') }}";
                            uploadedImgContainer.classList.add('d-none');
                        }

                        $('#userForm').attr('action', "{{ route('admin.users.update',':id') }}".replace(':id', id));
                        if ($('#userForm input[name="_method"]').length === 0) {
                            $('#userForm').append('<input type="hidden" name="_method" value="PUT">');
                        }
                        $('#userSubmitBtn').text('Update');
                        $('#userModal').modal('show');
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

            // Cancel
            $('.cancel_btn').on('click', function () {
                $('#userForm')[0].reset();
                $('#userSubmitBtn').text('Save');
                $('#userModalTitle').text('Add User');
                $('#userForm').attr('action', "{{ route('admin.users.store') }}");
                $('#userForm input[name="_method"]').remove();
                $('#passwordRow').show();
                $('#password').attr('required', true);
            });


        });
    </script>
@endpush
