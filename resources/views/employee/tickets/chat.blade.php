@extends('layout.master')
@section('pageName', 'Ticket Chat')

@push('cssLinks')
    <style>
        /*.chat-message-content {*/
        /*    background: #f8f9fa; !* halka grey bubble *!*/
        /*    padding: 8px 12px;*/
        /*    border-radius: 6px;*/
        /*    max-width: 70%;        !* text bubble width limit *!*/
        /*    display: inline-block;*/
        /*    word-wrap: break-word; !* long words break ho jayein *!*/
        /*    height: auto;          !* auto adjust height *!*/
        /*}*/
        .details-table {
            border: 1px solid #eee;
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .details-table .row-item {
            display: grid;
            grid-template-columns: 40% 60%;
            padding: 8px 12px;
            border-bottom: 1px solid #f5f5f5;
        }
        .details-table .row-item:last-child {
            border-bottom: none;
        }
        .details-table .label {
            font-weight: 600;
            color: #444;
            background: #fafafa;
            padding: 6px 8px;
        }
        .details-table .value {
            padding: 6px 8px;
            color: #222;
        }

        /* Responsive (mobile view stack) */
        @media (max-width: 576px) {
            .details-table .row-item {
                grid-template-columns: 1fr;
            }
            .details-table .label {
                background: #f9f9f9;
            }
        }
    </style>
@endpush

@section('content')

    <div class="chat-wrapper">
        <div class="chat-sidebar card p-3">
            <h6 class="mb-3 d-flex align-items-center gap-2">
                <span style="font-size:20px;">🎫</span>
                Ticket Details
            </h6>

            <div class="details-table">
                <div class="row-item">
                    <div class="label">Type</div>
                    <div class="value">{{ $ticket->ticket_type?->name ?? 'General' }}</div>
                </div>
                <div class="row-item">
                    <div class="label">Subject</div>
                    <div class="value">{{ $ticket->subject ?? '-' }}</div>
                </div>
                <div class="row-item">
                    <div class="label">Description</div>
                    <div class="value">{{ $ticket->description ?? '-' }}</div>
                </div>
                <div class="row-item">
                    <div class="label">Approved By</div>
                    <div class="value">{{ $ticket->approvedByAdmin?->name ?? '—' }}</div>
                </div>
                <div class="row-item">
                    <div class="label">Rejected By</div>
                    <div class="value">{{ $ticket->rejectedByAdmin?->name ?? '—' }}</div>
                </div>
                <div class="row-item">
                    <div class="label">Created At</div>
                    <div class="value">{{ $ticket->created_at->format('d M Y, h:i A') }}</div>
                </div>
                <div class="row-item">
                    <div class="label">Status</div>
                    <div class="value">
                        @php
                            $status      = $ticket->status?->name ?? '-';
                            $statusColors = [
                                'Pending'  => 'text-warning-main',
                                'Approved' => ' text-success-main',
                                'Rejected' => ' text-danger-main',
                                'Closed'   => 'text-secondary-main',
                            ];
                            $bgClass = $statusColors[$status] ?? 'bg-light text-dark';
                        @endphp
                        <span class="{{ $bgClass }} fw-medium text-sm">{{ $status }} </span>
                    </div>
                </div>
            </div>

            {{-- Attendance --}}
            @if($ticket->ticket_type_id == 1 && $ticket->attendanceRequest)
                <h6 class="mt-3">⏱ Attendance Request</h6>
                <div class="details-table">
                    <div class="row-item">
                        <div class="label">Date</div>
                        <div class="value">{{ $ticket->attendanceRequest->attendance_date }}</div>
                    </div>
                    <div class="row-item">
                        <div class="label">Check-in</div>
                        <div class="value">        {{ $ticket->attendanceRequest?->check_in ? \Carbon\Carbon::parse($ticket->attendanceRequest->check_in)->format('h:i A') : '—' }}</div>
                    </div>
                    <div class="row-item">
                        <div class="label">Check-out</div>
                        <div class="value">{{ $ticket->attendanceRequest?->check_out ? \Carbon\Carbon::parse($ticket->attendanceRequest->check_out)->format('h:i A') : '—' }}</div>
                    </div>
                    <div class="row-item">
                        <div class="label">Remarks</div>
                        <div class="value">{{ $ticket->attendanceRequest->remarks ?? '—' }}</div>
                    </div>
                </div>
            @endif

            {{-- Leave --}}
            @if($ticket->ticket_type_id == 2 && $ticket->leaveRequest)
                <h6 class="mt-3">🌿 Leave Request</h6>
                <div class="details-table">
                    <div class="row-item">
                        <div class="label">Leave Type</div>
                        <div class="value">{{ $ticket->leaveRequest->leaveType?->name ?? '-' }}</div>
                    </div>
                    <div class="row-item">
                        <div class="label">From</div>
                        <div class="value">{{ $ticket->leaveRequest->start_date }}</div>
                    </div>
                    <div class="row-item">
                        <div class="label">To</div>
                        <div class="value">{{ $ticket->leaveRequest->end_date }}</div>
                    </div>
                    <div class="row-item">
                        <div class="label">Reason</div>
                        <div class="value">{{ $ticket->leaveRequest->reason ?? '—' }}</div>
                    </div>
                </div>
            @endif
        </div>


        <div class="chat-main card">
            {{-- Header --}}
            <div class="chat-sidebar-single active ">
                <div class="d-flex align-items-center gap-3">
                    <div class="img">
                        <img src="{{ asset('assets/images/default_images/profile_image.png') }}" alt="image">
                    </div>
                    <div class="info">
                        <h6 class="text-md mb-0">Admin Support</h6>
                        <p class="mb-0 text-sm text-muted">
                            Ticket #{{ $ticket->id }} | {{ $ticket->ticket_type?->name ?? 'General' }}
                        </p>
                    </div>
                </div>
                <div class="action">
                    {{--                <button type="button"--}}
                    {{--                        class="btn btn-sm btn-danger rounded-pill d-inline-flex align-items-center gap-1 px-3 py-1">--}}
                    {{--                    <iconify-icon icon="mdi:close"></iconify-icon> Closed--}}
                    {{--                </button>--}}
                </div>
            </div>

            {{-- Messages style="overflow-y:auto;"--}}
            <div class="chat-message-list">
                @include('employee.tickets.partials.messages', ['messages' => $ticket->messages])
            </div>

            @if($ticket->status_id == 1)
                {{-- Message Box --}}
                <form id="chatMessageForm"  action="{{ route('employee.tickets.messages.store', $ticket->id) }}" method="POST" enctype="multipart/form-data" class="chat-message-box mt-5">
                    @csrf
                    <input type="text" name="message" placeholder="Write message">
                    <div class="chat-message-box-action">
                        {{--                <label for="attachment" class="text-xl cursor-pointer">--}}
                        {{--                    <iconify-icon icon="solar:gallery-linear"></iconify-icon>--}}
                        {{--                </label>--}}
                        {{--                <input type="file" name="attachment" id="attachment" class="d-none">--}}
                        <button type="submit" class="btn btn-sm btn-primary-600 radius-8 d-inline-flex align-items-center gap-1">
                            Send
                            <iconify-icon icon="f7:paperplane"></iconify-icon>
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        const ticketStatus = {{ $ticket->status_id }};

        function fetchMessages() {
            $.ajax({
                url: "{{ route('employee.tickets.messages.fetch', $ticket->id) }}",
                type: "GET",
                success: function (data) {
                    $(".chat-message-list").html(data);
                    // auto scroll bottom
                    var chatList = $(".chat-message-list");
                    chatList.scrollTop(chatList[0].scrollHeight);
                }
            });
        }

        // initial load
        fetchMessages();

        if (ticketStatus == 1){
            // har 30 sec me refresh
            setInterval(fetchMessages, 30000);
        }

        // AJAX form submit
        $("#chatMessageForm").on("submit", function(e){
            e.preventDefault(); // prevent default form submit

            var formData = new FormData(this);

            $.ajax({
                url: $(this).attr("action"),
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response){
                    $("#chatMessageForm")[0].reset(); // clear form
                    fetchMessages(); // refresh messages
                },
                error: function(xhr){
                    alert(xhr.responseJSON?.message || "Something went wrong!");
                }
            });
        });
    </script>
@endpush
