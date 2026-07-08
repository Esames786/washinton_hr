<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.css')
    @stack('cssLinks')
    {{-- #4 Responsive (Batch 5): responsive layer for HR portal tables/modals on small screens. --}}
    <style>
        img { max-width: 100%; height: auto; }
        @media (max-width: 991.98px) {
            body { overflow-x: hidden; }
            .table-responsive { overflow-x: auto !important; -webkit-overflow-scrolling: touch; }
            .modal-dialog { max-width: 96% !important; margin: 0.5rem auto !important; }
        }
        @media (max-width: 767.98px) {
            .card-body { padding: 12px !important; }
            .d-flex.justify-content-between { flex-wrap: wrap; gap: 6px; }
        }
    </style>
</head>

<body>
@auth('admin')
    @include('partials.admin-sidebar')
@endauth

@auth('employee')
    @include('partials.employee-sidebar')
@endauth

<main class="dashboard-main">
    @include('partials.navbar-header')

    <div class="dashboard-main-body">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
            <h6 class="fw-semibold mb-0">@yield('pageName')</h6>
            <ul class="d-flex align-items-center gap-2">
                <li class="fw-medium">
                    <a href="index.html" class="d-flex align-items-center gap-1 hover-text-primary">
                        <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                        Dashboard
                    </a>
                </li>
                <li>-</li>
                <li class="fw-medium">AI</li>
            </ul>
        </div>

        @yield('content')
    </div>

    @include('partials.footer')
</main>

@include('partials.scripts')

@auth('employee')
    @if(auth('employee')->user()->nda_required)
        @php $signRoute = route('employee.nda.sign'); @endphp
        @include('nda.modal')
    @endif
    @include('partials.checkin_gate')
    @include('partials.activity_tracker')
@endauth

@stack('scripts')

{{-- ── Live Chat Widget ──────────────────────────────────────────────── --}}
{{-- @auth('admin')
@php $hrChatAgentId = auth('admin')->user()->agent_id ?? 0; @endphp
<div id="hr-chat-container" style="display:none;position:fixed;bottom:90px;right:20px;z-index:99999;">
    <button id="hr-chat-close" style="position:absolute;top:10px;right:10px;z-index:100001;background:#dc3545;color:#fff;border:none;border-radius:50%;width:36px;height:36px;cursor:pointer;">✕</button>
    <iframe id="hr-chat-widget" src="" style="width:500px;height:550px;border:none;border-radius:12px;background:#fff;box-shadow:0 10px 30px rgba(0,0,0,0.25);overflow:hidden;display:block;"></iframe>
</div>
<button id="hr-chat-btn" style="position:fixed;bottom:20px;right:20px;z-index:100000;background:#1a1a2e;color:#d4af37;border:2px solid #d4af37;border-radius:50px;padding:10px 18px;font-weight:600;cursor:pointer;box-shadow:0 8px 20px rgba(0,0,0,0.35);display:flex;align-items:center;gap:8px;">
    <span>💬</span> <span>Live Chat</span>
</button>
<script>
(function () {
    var btn = document.getElementById('hr-chat-btn');
    var container = document.getElementById('hr-chat-container');
    var iframe = document.getElementById('hr-chat-widget');
    var closeBtn = document.getElementById('hr-chat-close');
    var loaded = false;

    btn.addEventListener('click', function () {
        if (!loaded) {
            iframe.src = '{{ rtrim(env("AGENT_PORTAL_URL", "https://hellotransport.com"), "/") }}/chat-widget?user_id={{ $hrChatAgentId }}';
            loaded = true;
        }
        container.style.display = container.style.display === 'none' ? 'block' : 'none';
    });
    closeBtn.addEventListener('click', function () { container.style.display = 'none'; });
})();
</script>
@endauth --}}

{{-- ── Blocking Contract Acceptance Modal (employee portal) ─────────────── --}}
@auth('employee')
@php
    $empPendingContract = null;
    $empAuth = auth('employee')->user();
    if ($empAuth && $empAuth->contract && $empAuth->contract_updated_at && !$empAuth->contract_accepted_at) {
        $empPendingContract = $empAuth->contract;
    }
@endphp
@if($empPendingContract)
<div id="contractBlockOverlay" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.88);z-index:999999;display:flex;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:8px;max-width:820px;width:96%;max-height:90vh;display:flex;flex-direction:column;box-shadow:0 20px 60px rgba(0,0,0,0.6);overflow:hidden;">
        <div style="padding:14px 20px;background:#1a1a2e;color:#d4af37;flex-shrink:0;">
            <h5 style="margin:0;font-weight:700;font-size:16px;">📝 Action Required — Employee Contract</h5>
        </div>
        <div style="padding:12px 20px;background:#fff8e1;border-bottom:1px solid #ffe082;flex-shrink:0;">
            <p style="margin:0;color:#795548;font-size:13px;"><strong>You have a new or updated contract awaiting your acceptance.</strong> Please read the full contract below and click <em>"I Accept"</em> to continue. This dialog cannot be dismissed until you accept.</p>
        </div>
        <div style="flex:1;overflow-y:auto;padding:20px 24px;font-size:14px;line-height:1.7;">
            {!! $empPendingContract !!}
        </div>
        <div style="padding:14px 20px;border-top:1px solid #e0e0e0;background:#f5f5f5;display:flex;align-items:center;justify-content:flex-end;gap:12px;flex-shrink:0;">
            <span id="hrContractAcceptMsg" style="font-size:13px;display:none;"></span>
            <button id="hrContractAcceptBtn"
                    onclick="hrAcceptPendingContract()"
                    style="background:#28a745;color:#fff;border:none;padding:10px 32px;font-size:15px;font-weight:600;border-radius:5px;cursor:pointer;">
                ✓ I Accept this Contract
            </button>
        </div>
    </div>
</div>
<script>
function hrAcceptPendingContract() {
    var btn = document.getElementById('hrContractAcceptBtn');
    var msg = document.getElementById('hrContractAcceptMsg');
    btn.disabled = true;
    btn.textContent = 'Saving...';
    fetch('{{ route("employee.contract.accept") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({})
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) {
            document.getElementById('contractBlockOverlay').style.display = 'none';
        } else {
            btn.disabled = false;
            btn.textContent = '✓ I Accept this Contract';
            msg.style.color = '#dc3545';
            msg.textContent = 'Could not record acceptance. Please try again.';
            msg.style.display = 'inline';
        }
    })
    .catch(function() {
        btn.disabled = false;
        btn.textContent = '✓ I Accept this Contract';
        msg.style.color = '#dc3545';
        msg.textContent = 'Network error. Please try again.';
        msg.style.display = 'inline';
    });
}
</script>
@endif
@endauth

</body>

</html>
