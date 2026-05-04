@extends('layout.master')
@section('pageName', 'Ticket Chat')

@push('cssLink')
<style>
    .chat-message-content {
        background: #f8f9fa; /* halka grey bubble */
        padding: 8px 12px;
        border-radius: 6px;
        max-width: 70%;        /* text bubble width limit */
        display: inline-block;
        word-wrap: break-word; /* long words break ho jayein */
        height: auto;          /* auto adjust height */
    }
</style>
@endpush

@section('content')
    <div class="chat-main card">

        {{-- Header --}}
        <div class="chat-sidebar-single active">
            <div class="img">
                <img src="{{ asset('assets/images/default_images/profile_image.png') }}" alt="image">
            </div>
            <div class="info">
                <h6 class="text-md mb-0">Admin Support</h6>
                <p class="mb-0 text-sm text-muted">
                    Ticket #{{ $ticket->id }} | {{ $ticket->ticket_type?->name ?? 'General' }}
                </p>
            </div>
            <div class="action d-inline-flex align-items-center gap-3">
                <button type="button"
                        class="btn btn-sm btn-danger d-inline-flex align-items-center gap-1 px-3 py-1">
                    <iconify-icon icon="mdi:close"></iconify-icon> Closed
                </button>
            </div>
        </div>

        {{-- Messages --}}
        <div class="chat-message-list" style="max-height:400px; overflow-y:auto;">
            @foreach($ticket->messages as $msg)
                @if($msg->sender_type === 'admin')
                    <div class="chat-single-message left">
                        <img src="{{ asset('assets/images/default_images/profile_image.png') }}" alt="admin" class="avatar-lg object-fit-cover rounded-circle">
                        <div class="chat-message-content">
                            <p class="mb-3">{{ $msg->message }}</p>
                            @if($msg->attachment_path)
                                <a href="{{ asset($msg->attachment_path) }}" target="_blank">📎 Attachment</a>
                            @endif
                            <p class="chat-time mb-0">
                                <span>{{ $msg->created_at->format('h:i A') }}</span>
                            </p>
                        </div>
                    </div>
                @else
                    <div class="chat-single-message right">
                        <div class="chat-message-content">
                            <p class="mb-3">{{ $msg->message }}</p>
                            @if($msg->attachment_path)
                                <a href="{{ asset('storage/'.$msg->attachment_path) }}" target="_blank">📎 Attachment</a>
                            @endif
                            <p class="chat-time mb-0">
                                <span style="float:inline-end">{{ $msg->created_at->format('h:i A') }}</span>
                            </p>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        {{-- Message Box --}}
        <form action="{{ route('employee.tickets.messages.store', $ticket->id) }}" method="POST" enctype="multipart/form-data" class="chat-message-box">
            @csrf
            <input type="text" name="message" placeholder="Write message">
            <div class="chat-message-box-action">
                <label for="attachment" class="text-xl cursor-pointer">
                    <iconify-icon icon="solar:gallery-linear"></iconify-icon>
                </label>
                <input type="file" name="attachment" id="attachment" class="d-none">
                <button type="submit" class="btn btn-sm btn-primary-600 radius-8 d-inline-flex align-items-center gap-1">
                    Send
                    <iconify-icon icon="f7:paperplane"></iconify-icon>
                </button>
            </div>
        </form>
    </div>
@endsection
