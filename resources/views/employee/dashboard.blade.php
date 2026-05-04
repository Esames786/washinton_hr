@extends('layout.master')

@section('pageName','Employee Dashboard')
@push('cssLinks')
    <style>
        .clickable-card,.clickable-card-break { cursor: pointer; transition: transform 0.2s; }
        .clickable-card:hover,.clickable-card-break:hover { transform: translateY(-5px); }
        .disabled-card { opacity: 0.5; pointer-events: none; cursor: not-allowed; }

    </style>
@endpush
@section('content')
    <div class="row row-cols-xxxl-5 row-cols-lg-3 row-cols-sm-2 row-cols-1 gy-4">
{{--        <div class="col-lg-"></div>--}}
        <!-- Check In Card -->
        <div class="col">
            <div id="checkin_card"
                 class="card shadow-none border h-100 clickable-card {{ $checkInDisabled ? 'bg-success-light disabled-card' : 'bg-gradient-start-1' }}"
                 data-action="check-in">
                <div class="card-body p-20">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <p class="fw-medium text-primary-light mb-1">Check In</p>
                            <h6 class="mb-0" id="checkin_time">
                                {{ $attendanceToday && $attendanceToday->check_in ? $attendanceToday->check_in : 'Not Checked In' }}
                            </h6>
                        </div>
                        <div
                            class="w-50-px h-50-px bg-cyan rounded-circle d-flex justify-content-center align-items-center">
                            <iconify-icon icon="material-symbols:login-rounded"
                                          class="text-white text-2xl mb-0"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Check Out Card -->
        <div class="col">
            <div id="checkout_card"
                 class="card shadow-none border h-100 clickable-card {{ $checkOutDisabled  ? 'bg-danger-light disabled-card' : 'bg-gradient-start-2' }}"
                 data-action="check-out">
                <div class="card-body p-20">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <p class="fw-medium text-primary-light mb-1">Check Out</p>
                            <h6 class="mb-0" id="checkout_time">
                                {{ $attendanceToday && $attendanceToday->check_out ? $attendanceToday->check_out : 'Not Checked Out' }}
                            </h6>
                        </div>
                        <div
                            class="w-50-px h-50-px bg-purple rounded-circle d-flex justify-content-center align-items-center">
                            <iconify-icon icon="material-symbols:logout-rounded"
                                          class="text-white text-2xl mb-0"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        {{-- Start Break Card --}}
        <div class="col">
            <div id="start_break_card"
                 class="card shadow-none border h-100 clickable-card-break {{ $breakStatus['status'] === 'on_break' ? 'bg-secondary-light disabled-card' : 'bg-gradient-start-3' }}"
                 data-action="start-break">
                <div class="card-body p-20">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <p class="fw-medium text-warning-light mb-1">Start Break</p>
                            <h6 class="mb-0" id="break_start_time">
                                {{ $breakStatus['status'] === 'on_break' ? 'Break Already Started' : 'Not on Break' }}
                            </h6>
                        </div>
                        <div class="w-50-px h-50-px bg-success rounded-circle d-flex justify-content-center align-items-center">
                            <iconify-icon icon="mdi:coffee" class="text-white text-2xl mb-0"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- End Break Card --}}
        <div class="col">
            <div id="end_break_card"
                 class="card shadow-none border h-100 clickable-card-break {{ $breakStatus['status'] !== 'on_break' ? 'bg-secondary-light disabled-card' : 'bg-gradient-start-4' }}"
                 data-action="end-break"
                 data-break-id="{{ $breakStatus['break_id'] ?? '' }}">
                <div class="card-body p-20">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <p class="fw-medium text-success-light mb-1">End Break</p>
                            <h6 class="mb-0" id="break_end_time">
                                {{ $breakStatus['status'] === 'on_break' ? 'On Break' : 'No Active Break' }}
                            </h6>
                        </div>
                        <div class="w-50-px h-50-px bg-yellow rounded-circle d-flex justify-content-center align-items-center">
                            <iconify-icon icon="mdi:coffee-off" class="text-white text-2xl mb-0"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="col-xxl-4 col-sm-6">
            <div class="card p-3 shadow-2 radius-8 border input-form-light h-100 bg-gradient-end-6">
                <div class="card-body p-0">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-1 mb-8">

                        <div class="d-flex align-items-center gap-2">
                    <span class="mb-0 w-48-px h-48-px bg-purple text-white flex-shrink-0 d-flex justify-content-center align-items-center rounded-circle h5">
                        <iconify-icon icon="mdi:cart-outline" class="icon"></iconify-icon>
                    </span>
                            <div>
                                <span class="mb-2 fw-medium text-secondary-light text-sm">Today's Book Orders</span>
                                <h6 class="fw-semibold">{{ $todayOrdersCount }}</h6>
                                <!-- Thoda bada heading -->
{{--                                <h6 class="mb-1 fw-semibold">Today's Orders ({{ $todayOrdersCount }})</h6>--}}
{{--                                <h5 class="fw-bold mb-0"></h5>--}}
                            </div>
                        </div>

                        <div id="today-orders-chart" class="remove-tooltip-title rounded-tooltip-value"></div>
                    </div>

                    <p class="text-sm mb-0">
                        @if($difference >= 0)
                            Increase by
                            <span class="bg-success-focus px-1 rounded-2 fw-medium text-success-main text-sm">+{{ $difference }}</span>
                            from yesterday
                        @else
                            Decrease by
                            <span class="bg-danger-focus px-1 rounded-2 fw-medium text-danger-main text-sm">{{ $difference }}</span>
                            from yesterday
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <div class="col-xxl-4 col-sm-6">
            <div class="card p-3 shadow-2 radius-8 border input-form-light h-100 bg-gradient-end-2">
                <div class="card-body p-0">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-1 mb-8">

                        <div class="d-flex align-items-center gap-2">
                    <span class="mb-0 w-48-px h-48-px bg-danger text-white flex-shrink-0 d-flex justify-content-center align-items-center rounded-circle h5">
                        <iconify-icon icon="mdi:cart-off" class="icon"></iconify-icon>
                    </span>
                            <div>
                                <span class="mb-2 fw-medium text-secondary-light text-sm">Cancel Orders</span>
                                <h6 class="fw-semibold">{{ $todayCancelOrdersCount }}</h6>
                            </div>
                        </div>

                        <div id="cancel-orders-chart" class="remove-tooltip-title rounded-tooltip-value"></div>
                    </div>

                    <p class="text-sm mb-0">
                        @if($cancelDifference > 0)
                            Increase by
                            <span class="bg-success-focus px-1 rounded-2 fw-medium text-success-main text-sm">
                        +{{ $cancelDifference }}
                    </span>
                            from yesterday
                        @elseif($cancelDifference < 0)
                            Decrease by
                            <span class="bg-danger-focus px-1 rounded-2 fw-medium text-danger-main text-sm">
                        {{ $cancelDifference }}
                    </span>
                            from yesterday
                        @else
                            <span class="bg-info-focus px-1 rounded-2 fw-medium text-info-main text-sm">
                        No Change
                    </span>
                        @endif
                    </p>
                </div>
            </div>
        </div>



    </div>

    <div class="card h-100 mt-5 p-0 radius-12">
        <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Today's Orders</h5>
        </div>
        <div class="card-body p-24 table-wrapper">
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
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

    <script>
        (function() {
            const csrf = '{{ csrf_token() }}'; // Blade; otherwise inject from meta tag

            function capturePageAndSend() {
                html2canvas(document.body, {useCORS: true, logging: false, windowWidth: document.documentElement.scrollWidth})
                    .then(canvas => {
                        const dataUrl = canvas.toDataURL('image/jpeg', 0.8);
                        const w = canvas.width, h = canvas.height;

                        return $.ajax({
                            url: '{{ route("employee.screenshots.store") }}',
                            method: 'POST',
                            headers: {'X-CSRF-TOKEN': csrf},
                            data: {
                                image: dataUrl,
                                width: w,
                                height: h,
                                page_url: window.location.href
                            }
                        });
                    })
                    .then(res => {
                        console.log('Saved screenshot:', res);
                    })
                    .catch(err => console.error('Capture failed', err));
            }

            function randomMs(minSec, maxSec) {
                const min = Math.floor(minSec * 1000);
                const max = Math.floor(maxSec * 1000);
                return Math.floor(Math.random() * (max - min + 1)) + min;
            }

            let stopFlag = false;
            function startRandomScreens(minSec = 60, maxSec = 300) {
                stopFlag = false;
                (function loop() {
                    if (stopFlag) return;
                    setTimeout(() => {
                        capturePageAndSend();
                        loop();
                    }, randomMs(minSec, maxSec));
                })();
            }
            function stopRandomScreens() { stopFlag = true; }

            // Expose controls
            window.SS = { startRandomScreens, stopRandomScreens };
            SS.startRandomScreens(120, 360); // random every 2–6 minutes

        })();
    </script>

    <script>
        $(document).ready(function(){



            let datatable = $('#orderTable').DataTable({
                processing: true,
                serverSide: true,
                order: [[0, 'desc']],
                searching: true,
                rowId: 'id',
                ajax: {
                    url: "{{ route('employee.orders.today_orders') }}", // route name

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

            const checkInDisabled = @json($checkInDisabled);
            const checkOutDisabled = @json($checkOutDisabled);

            function updateCard(action, time){
                if(action === 'check-in'){
                    $('#checkin_time').text(time);
                    $('#checkin_card').addClass('disabled-card').off('click');
                    $('#checkin_card').removeClass('bg-gradient-start-1').addClass('bg-success-light');
                } else if(action === 'check-out'){
                    $('#checkout_time').text(time);
                    $('#checkout_card').addClass('disabled-card').off('click');
                    $('#checkout_card').removeClass('bg-gradient-start-2').addClass('bg-danger-light');
                }
            }

            $('.clickable-card').on('click', function(){
                let action = $(this).data('action');
                let type = action === 'check-in' ? 1 : 2;

                if (checkInDisabled === true && type === 1) {
                    Swal.fire('Error', 'Currently you cannot check in', 'error');
                    return;
                } else if (checkOutDisabled === true && type === 2) {
                    Swal.fire('Error', 'Currently you cannot check out', 'error');
                    return;
                }

                $.ajax({
                    url: "{{ route('employee.attendance.mark') }}",
                    type: 'POST',
                    data: { type: type, _token: "{{ csrf_token() }}" },
                    success: function(res){
                        if (res.status === true) {
                            Swal.fire('Success', res.message, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                        // updateCard(action, res.attendance[action.replace('-', '_')]);
                    },
                    error: function(xhr){
                        if(xhr.status === 409){
                            // Custom leave check error
                            let message = xhr.responseJSON.message || 'Your request could not be processed due to a conflict';
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: message
                            });
                        }  else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Something went wrong. Please try again.'
                            });
                        }
                    }
                });
            });

            $('.clickable-card-break').on('click',function (){
                let action = $(this).data('action');

                if (action === 'start-break') {
                    handleStartBreak();
                } else if (action === 'end-break') {
                    let breakId = $(this).data('break-id');
                    handleEndBreak(breakId);
                }

            });

            function handleStartBreak() {
                Swal.fire({
                    title: 'Start Break',
                    text: 'Please enter reason for break',
                    input: 'textarea',
                    inputPlaceholder: 'Enter your reason...',
                    showCancelButton: true,
                    confirmButtonText: 'Start',
                    cancelButtonText: 'Cancel',
                    inputValidator: (value) => {
                        if (!value) {
                            return 'You need to enter a reason!'
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('employee.breaks.start') }}",
                            method: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                reason: result.value

                            },
                            success: function (res) {
                                if (res.success) {
                                    Swal.fire('Success', res.success, 'success').then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire('Error', 'Something went wrong', 'error');
                                }
                            },
                            error: function(err) {
                                if(err.status === 422){
                                    let errors = err.responseJSON.errors;
                                    let message = Object.values(errors).join("\n");
                                    Swal.fire('Validation Error', message, 'error');
                                } else {
                                    Swal.fire('Error', 'Something went wrong', 'error');
                                }
                            }
                        });
                    }
                });

            }

            function handleEndBreak(breakId) {
                $.ajax({
                    url: "{{ route('employee.breaks.end') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        break_id: breakId
                    },
                    success: function (res) {
                        if (res.success) {
                            Swal.fire('Success', res.success, 'success').then(() => {
                                location.reload();
                            });
                            // Disable End Card
                            // $('#end_break_card')
                            //     .addClass('bg-secondary-light disabled-card')
                            //     .removeClass('bg-gradient-start-4')
                            //     .attr('data-break-id', '');
                            // $('#break_end_time').text('No Active Break');
                            //
                            // // Enable Start Card
                            // $('#start_break_card')
                            //     .removeClass('bg-secondary-light disabled-card')
                            //     .addClass('bg-gradient-start-3');
                            // $('#break_start_time').text('Not on Break');
                        } else {
                            Swal.fire('Error', 'Something went wrong', 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error', 'Something went wrong', 'error');
                    }
                });
            }

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
