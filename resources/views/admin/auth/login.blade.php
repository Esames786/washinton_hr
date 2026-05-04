<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo/hello_transport.png') }}" sizes="16x16">
    <!-- remix icon font css  -->
    <link rel="stylesheet" href="{{ asset('assets/css/remixicon.css') }}">
    <!-- BootStrap css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/bootstrap.min.css') }}">
    <!-- Apex Chart css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/apexcharts.css') }}">
    <!-- Data Table css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/dataTables.min.css') }}">
    <!-- Text Editor css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/editor-katex.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/lib/editor.atom-one-dark.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/lib/editor.quill.snow.css') }}">
    <!-- Date picker css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/flatpickr.min.css') }}">
    <!-- Calendar css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/full-calendar.css') }}">
    <!-- Vector Map css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/jquery-jvectormap-2.0.5.css') }}">
    <!-- Popup css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/magnific-popup.css') }}">
    <!-- Slick Slider css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/slick.css') }}">
    <!-- prism css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/prism.css') }}">
    <!-- file upload css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/file-upload.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/lib/audioplayer.css') }}">
    <!-- main css -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

    <style>
        .validation-error{
            padding-left: 9px;
            padding-top: 8px;
            color: red;
        }
        /* Make the form responsive */
        /*.login-container {*/
        /*    max-width: 464px;*/
        /*}*/

        /*@media (max-width: 576px) {*/
        /*    .login-container {*/
        /*        max-width: 100%;*/
        /*        padding: 0 15px;*/
        /*    }*/
        /*}*/

        /* Icon field styling */
        /*.icon-field {*/
        /*    position: relative;*/
        /*}*/

        /*.icon-field .icon {*/
        /*    position: absolute;*/
        /*    left: 12px;*/
        /*    top: 50%;*/
        /*    transform: translateY(-50%);*/
        /*    color: #888;*/
        /*    font-size: 18px;*/
        /*}*/

        /*.icon-field input {*/
        /*    padding-left: 42px; !* leave space for icon *!*/
        /*}*/

        /*!* Password toggle icon *!*/
        /*.toggle-password {*/
        /*    position: absolute;*/
        /*    right: 12px;*/
        /*    top: 50%;*/
        /*    transform: translateY(-50%);*/
        /*    color: #888;*/
        /*    font-size: 18px;*/
        /*    cursor: pointer;*/
        /*}*/

        /* Error messages below fields */
        /*.error {*/
        /*    display: block;*/
        /*    margin-top: 4px;*/
        /*    font-size: 13px;*/
        /*    color: red;*/
        /*}*/

        /*!* SweetAlert responsive *!*/
        /*.swal-responsive {*/
        /*    width: 90% !important;*/
        /*    max-width: 400px !important;*/
        /*}*/

    </style>

</head>
<body>

<section class="auth bg-base d-flex flex-wrap">
    <div class="auth-left d-lg-block d-none">
        <div class="d-flex align-items-center flex-column h-100 justify-content-center">
            <img src="{{asset('assets/images/auth/33.png')}}" alt="" style="height: 100%">
        </div>
    </div>
    <div class="auth-right py-32 px-24 d-flex flex-column justify-content-center">
        <div class="login-container max-w-464-px mx-auto w-100">
            <div>
                <a href="{{route('admin.login')}}" class="mb-40" style="margin-left: 35%">
                    <img src="{{asset('assets/images/logo/hello_transport.png')}}" alt="" class="logo-width-100">
                </a>
                <h4 class="mb-12 text-center">Admin Sign In</h4>
                <p class="mb-32 text-secondary-light text-lg text-center">Welcome back! please enter your detail</p>
            </div>
            <form id="loginform" action="javascript:void(0);">
                <!-- Email -->
                <div class="position-relative mb-20">
                    <div class="icon-field ">
                        <span class="icon top-50 translate-middle-y">
                            <iconify-icon icon="mage:email"></iconify-icon>
                        </span>
                        <input type="email" name="email" class="form-control h-56-px bg-neutral-50 radius-12" placeholder="Email" required>
                    </div>
                    <div class="validation-error"></div>
                </div>


                <!-- Password -->
                <div class="position-relative mb-20">
                    <div class="icon-field">
                        <span class="icon top-50 translate-middle-y">
                            <iconify-icon icon="solar:lock-password-outline"></iconify-icon>
                        </span>
                        <input type="password" name="password" class="form-control h-56-px bg-neutral-50 radius-12" id="your-password" placeholder="Password" required>
                        <span class="toggle-password ri-eye-line cursor-pointer position-absolute end-0 top-50 translate-middle-y me-16 text-secondary-light" data-toggle="#your-password"></span>
                    </div>
                    <div class="validation-error"></div>
                </div>

                <div class="">
                    <div class="d-flex justify-content-between gap-2">
                        <div class="form-check style-check d-flex align-items-center">
                            <input class="form-check-input border border-neutral-300" type="checkbox" value="" id="remeber">
                            <label class="form-check-label" for="remeber">Remember me </label>
                        </div>
                        <a href="javascript:void(0)" class="text-primary-600 fw-medium">Forgot Password?</a>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 radius-12 mt-3">Sign In</button>
            </form>
        </div>
    </div>
</section>

<!-- jQuery core -->
<script src="{{ asset('assets/js/lib/jquery-3.7.1.min.js') }}"></script>

<!-- jQuery Validation Plugin -->
{{--<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>--}}

<!-- Bootstrap and other libraries -->
<script src="{{ asset('assets/js/jquery.validate.min.js') }}"></script>

<script src="{{ asset('assets/js/lib/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/lib/apexcharts.min.js') }}"></script>
<script src="{{ asset('assets/js/lib/dataTables.min.js') }}"></script>
<script src="{{ asset('assets/js/lib/iconify-icon.min.js') }}"></script>
<script src="{{ asset('assets/js/lib/jquery-ui.min.js') }}"></script>
<script src="{{ asset('assets/js/lib/jquery-jvectormap-2.0.5.min.js') }}"></script>
<script src="{{ asset('assets/js/lib/jquery-jvectormap-world-mill-en.js') }}"></script>
<script src="{{ asset('assets/js/lib/magnifc-popup.min.js') }}"></script>
<script src="{{ asset('assets/js/lib/slick.min.js') }}"></script>
<script src="{{ asset('assets/js/lib/prism.js') }}"></script>
<script src="{{ asset('assets/js/lib/file-upload.js') }}"></script>
<script src="{{ asset('assets/js/lib/audioplayer.js') }}"></script>

<!-- Your main app.js -->
<script src="{{ asset('assets/js/app.js') }}"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function () {
        $("#loginform").validate({
            rules: {
                email: { required: true, email: true },
                password: { required: true, minlength: 8 }
            },
            messages: {
                email: {
                    required: "Please enter your email",
                    email: "Please enter a valid email address"
                },
                password: {
                    required: "Please enter your password",
                    minlength: "Password must be at least 8 characters"
                }
            },
            errorPlacement: function(error, element) {
                // Find the .validation-error div in the same parent container and replace its content
                element.closest('.position-relative')
                    .find('.validation-error')
                    .html(error);
            },
            success: function(label, element) {
                // Clear the error when valid
                $(element).closest('.position-relative')
                    .find('.validation-error')
                    .empty();
            },
            submitHandler: function (form, event) {
                event.preventDefault();

                Swal.fire({
                    title: 'Logging in...',
                    customClass: { popup: 'swal-responsive' },
                    timerProgressBar: true,
                    didOpen: () => Swal.showLoading()
                });

                var data = $(form).serialize();
                $.ajax({
                    url: '{{ route("admin.admin_login") }}',
                    method: 'POST',
                    data: data,
                    dataType: 'json',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    success: function (response) {
                        if (response.status === 1) {
                            Swal.fire({
                                title: 'Success!',
                                text: 'Login successful. Redirecting...',
                                icon: 'success',
                                customClass: { popup: 'swal-responsive' },
                                showConfirmButton: false,
                                timer: 2000,
                                willClose: () => window.location.href = '{{ url("admin/dashboard") }}'
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                html: '<ul>' +
                                    (response.errors ? Object.values(response.errors).flat().map(m => `<li>${m}</li>`).join('') : 'User Not Found') +
                                    '</ul>',
                                icon: 'error',
                            });
                        }
                    },
                    error: function (xhr) {
                        Swal.fire({
                            title: 'Error!',
                            text: 'An unexpected error occurred.',
                            icon: 'error',
                            customClass: { popup: 'swal-responsive' }
                        });
                    }
                });
            }
        });

    });

    // ================== Password Show Hide Js Start ==========
    function initializePasswordToggle(toggleSelector) {
        $(toggleSelector).on('click', function() {
            $(this).toggleClass("ri-eye-off-line");
            var input = $($(this).attr("data-toggle"));
            if (input.attr("type") === "password") {
                input.attr("type", "text");
            } else {
                input.attr("type", "password");
            }
        });
    }
    // Call the function
    initializePasswordToggle('.toggle-password');
    // ========================= Password Show Hide Js End ===========================


</script>

</body>
</html>
