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

<!-- audioplayer css -->
<link rel="stylesheet" href="{{ asset('assets/css/lib/audioplayer.css') }}">

<!-- main css -->
<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

<link rel="stylesheet" href="{{ asset('assets/css/lib/toastr.min.css') }}">

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">



<style>
    .dt-input {
        padding:10px!important;
    }
    .dt-length label {
        margin-left: 10px!important;
    }

    /* Generic Modal Select2 */
    .single-form-select2  .select2-container--default .select2-selection--single {
        height: 38px !important;   /* same as bootstrap input */
        border: 1px solid #ced4da;
        border-radius: 6px;
        display: flex;
        align-items: center;
    }

    .single-form-select2  .select2-container--default .select2-selection--single .select2-selection__clear {
        position: absolute;
        right: 13px;
        top: 47%;
        transform: translateY(-50%);
        font-size: 16px;
        color: #888;
        cursor: pointer
    }

    .single-form-select2   .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        padding-left: 12px;
    }

    .single-form-select2   .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
        right: 8px;
        /*padding-right: 40px;*/
    }


    /* Generic Wizard Select2 */
    .form-select-2 .select2-container {
        width: 100% !important;
    }

    .form-select-2 .select2-container .select2-search--inline .select2-search__field {
        box-sizing: border-box !important;
        border: none !important;
        font-size: 100% !important;
        margin-top: 5px !important;
        margin-left: 5px !important;
        padding: 0 !important;
        max-width: 100% !important;
        resize: none !important;
        height: 30px !important;
        vertical-align: bottom !important;
        font-family: sans-serif !important;
        overflow: hidden !important;
        word-break: keep-all !important;
    }

    .form-select-2 .select2-search--inline {
        margin-left: 12px!important;
    }


    /*!* Dropdown z-index fix *!*/
    /*.select2-dropdown {*/
    /*    z-index: 9999 !important;*/
    /*}*/

    /*!* Single select styling *!*/
    /*.select2-container .select2-selection--single {*/
    /*    height: 38px !important; !* Bootstrap input height *!*/
    /*    border: 1px solid #ced4da !important;*/
    /*    border-radius: 6px !important;*/
    /*    display: flex !important;*/
    /*    align-items: center !important;*/
    /*    padding: 0 8px !important;*/
    /*    background-color: #fff !important;*/
    /*    font-size: 14px !important;*/
    /*}*/

    /*!* Text inside select *!*/
    /*.select2-container--default .select2-selection--single .select2-selection__rendered {*/
    /*    color: #495057 !important;*/
    /*    line-height: 36px !important;*/
    /*    font-size: 14px !important;*/
    /*}*/

    /*!* Arrow alignment *!*/
    /*.select2-container--default .select2-selection--single .select2-selection__arrow {*/
    /*    height: 36px !important;*/
    /*    right: 8px !important;*/
    /*}*/

    /*!* Focus state *!*/
    /*.select2-container--default .select2-selection--single:focus,*/
    /*.select2-container--default .select2-selection--single:active,*/
    /*.select2-container--default .select2-selection--single.select2-selection--focus {*/
    /*    border-color: #86b7fe !important;*/
    /*    outline: 0 !important;*/
    /*    box-shadow: 0 0 0 0.25rem rgba(13,110,253,.25) !important; !* Bootstrap focus ring *!*/
    /*}*/
    .select2-container--default .selection {
        width: 100%!important;
    }


</style>
