{{-- Check-in gate: an active employee must check in before using the portal.
     Only applies to active employees (status 1) who haven't checked in today.
     Onboarding (status 7) is handled by the document/NDA flow instead. --}}
@auth('employee')
@php
    $__needCheckin = false;
    try {
        $__emp = auth('employee')->user();
        if ($__emp && (int) ($__emp->employee_status_id ?? 0) === 1 && !($__emp->nda_required ?? 0)) {
            $__att = \App\Models\EmployeeAttendance::where('employee_id', $__emp->id)
                ->whereDate('attendance_date', date('Y-m-d'))->first();
            $__needCheckin = !($__att && $__att->check_in);
        }
    } catch (\Throwable $e) {}
@endphp
@if($__needCheckin)
<style>
    body.checkin-gate-active .dashboard-main-body,
    body.checkin-gate-active .sidebar,
    body.checkin-gate-active .navbar-header { filter: blur(6px) !important; pointer-events:none !important; }
</style>
<div id="checkinGate" style="position:fixed;inset:0;z-index:99980;background:rgba(15,23,42,.62);display:flex;align-items:center;justify-content:center;padding:16px;">
    <div style="background:#fff;border-radius:14px;max-width:460px;width:100%;box-shadow:0 24px 70px rgba(0,0,0,.45);text-align:center;padding:32px 28px;font-family:'Segoe UI',Arial,sans-serif;">
        <div style="font-size:42px;line-height:1;margin-bottom:10px;">🕒</div>
        <h3 style="margin:0 0 8px;font-weight:800;color:#0f172a;font-size:20px;">Please Check In</h3>
        <p style="color:#555;font-size:14px;margin:0 0 20px;">You need to check in to start your shift before using the portal.</p>
        <button id="checkinGateBtn" style="background:#1a73e8;color:#fff;border:none;border-radius:8px;padding:12px 30px;font-size:15px;font-weight:700;cursor:pointer;">Check In Now</button>
        <div id="checkinGateMsg" style="color:#c0392b;font-size:13px;margin-top:12px;"></div>
    </div>
</div>
<script>
(function () {
    document.body.classList.add('checkin-gate-active');
    var btn = document.getElementById('checkinGateBtn');
    var msg = document.getElementById('checkinGateMsg');
    btn.addEventListener('click', function () {
        btn.disabled = true; btn.textContent = 'Checking in…';
        fetch("{{ route('employee.attendance.mark') }}", {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'type=1'
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res && res.status === true) { location.reload(); }
            else { msg.textContent = (res && res.message) ? res.message : 'Could not check in. Please try again.'; btn.disabled = false; btn.textContent = 'Check In Now'; }
        })
        .catch(function () { msg.textContent = 'Network error. Please try again.'; btn.disabled = false; btn.textContent = 'Check In Now'; });
    });
})();
</script>
@endif
@endauth
