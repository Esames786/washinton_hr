@foreach($messages as $msg)
    <div class="chat-single-message {{ $msg->sender_type === 'admin' ? 'left' : 'right' }}">
        @if($msg->sender_type === 'admin' )
            <img src="{{ asset($msg->sender?->profile_path ?? 'assets/images/default_images/profile_image.png') }}"
                 class="avatar-lg object-fit-cover rounded-circle">
        @endif
        <div class="chat-message-content">
            <p class="mb-3">{{ $msg->message }}</p>
            @if($msg->attachment_path)
                <a href="{{ asset('storage/'.$msg->attachment_path) }}" target="_blank">📎 Attachment</a>
            @endif
            <p class="chat-time mb-0" style="{{ $msg->sender_type === 'admin' ? 'float: inline-end;' : '' }}">
                {{ $msg->created_at->format('h:i A') }}
            </p>
        </div>
    </div>
@endforeach
