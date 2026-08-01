{{-- NDA Blocking Overlay — injected in layout when auth('employee')->user()->nda_required == 1 --}}
@php
    $signRoute = $signRoute ?? route('employee.nda.sign');

    $__emp = auth('employee')->user();
    $__empId = $__emp->id ?? null;

    // The exact NDA the admin prepared for this subcontractor (falls back to the default template
    // from the shared nda_templates table, then to a built-in message).
    $__ndaContent = $__emp->nda_content ?? null;
    if (!$__ndaContent || trim(strip_tags($__ndaContent)) === '') {
        $__tpl = \Illuminate\Support\Facades\DB::table('nda_templates')->where('is_default', 1)->value('content');
        $__ndaContent = $__tpl ? str_ireplace(['{{COMPANY_NAME}}', '{{COMPANY_LEGAL}}'], 'Crazy Rays Solutions', $__tpl) : '';
    }

    // CNIC front/back already on file? (hr_document_settings #10 = Front, #11 = Back).
    $__cnicHave = [];
    if ($__empId) {
        $__cnicHave = \Illuminate\Support\Facades\DB::table('hr_employee_documents')
            ->where('employee_id', $__empId)
            ->whereIn('document_setting_id', [10, 11])
            ->pluck('document_setting_id')->map(fn($v) => (int) $v)->all();
    }
    $__needCnicFront = !in_array(10, $__cnicHave, true);
    $__needCnicBack  = !in_array(11, $__cnicHave, true);

    // Auto-captured signing context + prefill.
    $__signIp  = request()->ip();
    $__signNow = now()->format('d M Y, h:i A');
    $__father  = $__emp->father_name ?? ($__emp->father ?? '');
    $__address = $__emp->address ?? '';
@endphp

<div id="ndaOverlay" style="
    position: fixed; inset: 0; z-index: 99999;
    background: rgba(0,0,0,.72);
    display: flex; align-items: center; justify-content: center;
    padding: 16px;
">
    <div style="
        background: #fff; border-radius: 10px;
        width: 100%; max-width: 1080px; max-height: 96vh;
        box-shadow: 0 20px 60px rgba(0,0,0,.45);
        overflow-y: auto; overflow-x: hidden;
    ">
        {{-- Header (sticky so it stays visible while the whole agreement scrolls as one page) --}}
        <div style="background:#1a4ca0; color:#fff; padding:14px 22px; position:sticky; top:0; z-index:3; display:flex; align-items:center; gap:12px;">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>
            </svg>
            <div>
                <div style="font-weight:700; font-size:15px;">Action Required: NDA & Confidentiality Agreement</div>
                <div style="font-size:12px; opacity:.85; margin-top:2px;">Please read the full document and sign before continuing.</div>
            </div>
        </div>

        {{-- Full document — displayed in full (no inner scroll box) so no clause can be missed. --}}
        <div style="padding:28px 30px 16px; border-bottom:1px solid #e0e0e0;">

            <div style="text-align:center; margin-bottom:22px;">
                <img src="/Uploads/Settings/logo.png" alt="Crazy Rays Solutions" style="height:64px;" onerror="this.style.display='none'">
            </div>

            <h2 style="text-align:center; color:#1a4ca0; font-size:16px; font-weight:700; text-transform:uppercase; letter-spacing:.4px; margin-bottom:18px; line-height:1.4;">
                Non-Disclosure Agreement (NDA)<br>& Confidentiality Acknowledgment
            </h2>

            {{-- The exact NDA the admin prepared for this subcontractor (rich text), or the default. --}}
            <div class="nda-doc-body" style="color:#333; font-size:13.5px; line-height:1.6;">
                {!! $__ndaContent !!}
            </div>

            <hr style="border:none; border-top:1px solid #ddd; margin:18px 0;">

            <div style="background:#f8f9ff; border:1px solid #dce3f5; border-radius:6px; padding:16px 18px; font-size:12.5px; color:#444;">
                <strong style="color:#1a4ca0;">CRAZY RAYS SOLUTIONS</strong><br>
                Phone: 0313-8432343 &nbsp;|&nbsp; Email: info@crazyrayssolutions.com.pk &nbsp;|&nbsp;
                Website: <a href="https://crazyrayssolutions.com.pk" target="_blank" style="color:#1a4ca0;">crazyrayssolutions.com.pk</a>
            </div>
        </div>

        {{-- Signing form --}}
        <div style="padding:20px 28px; background:#fafbff; flex-shrink:0;">
            <form id="ndaSignForm">
                @csrf
                <div id="ndaSignError" style="display:none; background:#fde8e8; border:1px solid #f5a0a0; border-radius:6px; padding:10px 14px; color:#c0392b; font-size:13px; margin-bottom:14px;"></div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#444; display:block; margin-bottom:4px;">Agent Full Name <span style="color:red;">*</span></label>
                        <input type="text" id="ndaFullName" name="employee_name"
                               value="{{ auth('employee')->user()->full_name ?? '' }}"
                               style="width:100%; border:1px solid #ccc; border-radius:5px; padding:7px 10px; font-size:13px;" required>
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#444; display:block; margin-bottom:4px;">Father's Name <span style="color:red;">*</span></label>
                        <input type="text" id="ndaFather" name="father_name"
                               value="{{ $__father }}"
                               style="width:100%; border:1px solid #ccc; border-radius:5px; padding:7px 10px; font-size:13px;" required>
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#444; display:block; margin-bottom:4px;">CNIC Number <span style="color:red;">*</span></label>
                        <input type="text" id="ndaCnic" name="cnic"
                               value="{{ auth('employee')->user()->cnic ?? '' }}"
                               style="width:100%; border:1px solid #ccc; border-radius:5px; padding:7px 10px; font-size:13px;" required placeholder="e.g. 42101-1234567-1">
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#444; display:block; margin-bottom:4px;">Address <span style="color:red;">*</span></label>
                        <input type="text" id="ndaAddress" name="address"
                               value="{{ $__address }}"
                               style="width:100%; border:1px solid #ccc; border-radius:5px; padding:7px 10px; font-size:13px;" required placeholder="Your address">
                    </div>
                </div>

                {{-- CNIC front/back — asked only when not already on file (doc settings #10/#11). --}}
                @if($__needCnicFront || $__needCnicBack)
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                    @if($__needCnicFront)
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#444; display:block; margin-bottom:4px;">CNIC Front <span style="color:red;">*</span></label>
                        <input type="file" name="cnic_front" accept="image/*,.pdf" required
                               style="width:100%; border:1px solid #ccc; border-radius:5px; padding:6px 8px; font-size:12px;">
                    </div>
                    @endif
                    @if($__needCnicBack)
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#444; display:block; margin-bottom:4px;">CNIC Back <span style="color:red;">*</span></label>
                        <input type="file" name="cnic_back" accept="image/*,.pdf" required
                               style="width:100%; border:1px solid #ccc; border-radius:5px; padding:6px 8px; font-size:12px;">
                    </div>
                    @endif
                </div>
                @endif

                {{-- Auto-captured — verified server-side against login activity + timestamp. --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#444; display:block; margin-bottom:4px;">Date &amp; Time of Signing</label>
                        <input type="text" value="{{ $__signNow }}" readonly
                               style="width:100%; border:1px solid #e0e0e0; background:#f6f7fb; color:#555; border-radius:5px; padding:7px 10px; font-size:13px;">
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#444; display:block; margin-bottom:4px;">IP Address at Signing</label>
                        <input type="text" value="{{ $__signIp }}" readonly
                               style="width:100%; border:1px solid #e0e0e0; background:#f6f7fb; color:#555; border-radius:5px; padding:7px 10px; font-size:13px;">
                    </div>
                </div>

                <div style="margin-bottom:14px;">
                    <label style="font-size:12px; font-weight:600; color:#444; display:block; margin-bottom:6px;">
                        Signature <span style="color:red;">*</span>
                        <span style="font-weight:400; color:#888; margin-left:8px; font-size:11px;">(Draw with mouse or touch)</span>
                    </label>
                    <div style="position:relative; border:2px dashed #aab4cc; border-radius:6px; background:#fff; display:block; width:100%;">
                        <canvas id="ndaSignatureCanvas" width="900" height="130"
                                style="display:block; cursor:crosshair; touch-action:none; width:100%; height:130px;"></canvas>
                        <button type="button" id="ndaClearSig"
                                style="position:absolute; top:6px; right:6px; font-size:11px; padding:2px 8px; background:#eee; border:1px solid #ccc; border-radius:4px; cursor:pointer;">
                            Clear
                        </button>
                    </div>
                    <input type="hidden" id="ndaSignatureData" name="signature_data">
                </div>

                <div style="margin-bottom:16px; display:flex; align-items:flex-start; gap:10px;">
                    <input type="checkbox" id="ndaAgreeCheck" name="agreed" value="1" required
                           style="appearance:checkbox !important; -webkit-appearance:checkbox !important;
                                  -moz-appearance:checkbox !important;
                                  flex-shrink:0; margin-top:2px;
                                  width:18px !important; height:18px !important;
                                  min-width:18px !important; min-height:18px !important;
                                  max-width:18px !important; max-height:18px !important;
                                  border:2px solid #666 !important; border-radius:3px !important;
                                  accent-color:#1a4ca0; cursor:pointer;
                                  opacity:1 !important; visibility:visible !important;
                                  display:inline-block !important; position:relative !important;">
                    <label for="ndaAgreeCheck" style="font-size:13px; color:#333; cursor:pointer; line-height:1.6;">
                        I have read and understood all 26 clauses of the NDA &amp; Confidentiality Agreement and agree to comply with all its terms.
                        I understand the consequences of violating its provisions.
                    </label>
                </div>

                <div style="display:flex; align-items:center; gap:12px;">
                    <button type="submit" id="ndaSubmitBtn"
                            style="background:#1a4ca0; color:#fff; border:none; border-radius:6px; padding:10px 28px; font-size:14px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:8px;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                        Accept &amp; Sign
                    </button>
                    <span id="ndaSubmitMsg" style="display:none; font-size:13px; color:#27ae60;"></span>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    var canvas  = document.getElementById('ndaSignatureCanvas');
    var ctx     = canvas.getContext('2d');
    var drawing = false;
    var lastX   = 0, lastY = 0;
    var hasDraw = false;

    function getPos(e) {
        var rect = canvas.getBoundingClientRect();
        var src  = e.touches ? e.touches[0] : e;
        return { x: src.clientX - rect.left, y: src.clientY - rect.top };
    }

    function startDraw(e) { e.preventDefault(); drawing = true; var p = getPos(e); lastX = p.x; lastY = p.y; }
    function endDraw()   { drawing = false; }
    function draw(e) {
        if (!drawing) return;
        e.preventDefault();
        var p = getPos(e);
        ctx.strokeStyle = '#1a1a2e';
        ctx.lineWidth   = 2;
        ctx.lineCap     = 'round';
        ctx.lineJoin    = 'round';
        ctx.beginPath();
        ctx.moveTo(lastX, lastY);
        ctx.lineTo(p.x, p.y);
        ctx.stroke();
        lastX = p.x; lastY = p.y;
        hasDraw = true;
    }

    canvas.addEventListener('mousedown',  startDraw);
    canvas.addEventListener('mouseup',    endDraw);
    canvas.addEventListener('mouseleave', endDraw);
    canvas.addEventListener('mousemove',  draw);
    canvas.addEventListener('touchstart', startDraw, { passive: false });
    canvas.addEventListener('touchend',   endDraw);
    canvas.addEventListener('touchmove',  draw, { passive: false });

    document.getElementById('ndaClearSig').addEventListener('click', function () {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        hasDraw = false;
    });

    document.getElementById('ndaSignForm').addEventListener('submit', function (e) {
        e.preventDefault();
        var errEl = document.getElementById('ndaSignError');
        var msgEl = document.getElementById('ndaSubmitMsg');
        var btn   = document.getElementById('ndaSubmitBtn');
        errEl.style.display = 'none';

        if (!hasDraw) {
            errEl.textContent = 'Please draw your signature before submitting.';
            errEl.style.display = 'block';
            return;
        }

        document.getElementById('ndaSignatureData').value = canvas.toDataURL('image/png');

        var fd = new FormData(this);
        btn.disabled = true;
        btn.innerHTML = '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> Saving...';

        fetch('{{ $signRoute }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: fd,
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                msgEl.textContent = 'Signed successfully! Reloading...';
                msgEl.style.display = 'inline';
                document.getElementById('ndaOverlay').style.opacity = '.5';
                setTimeout(function() { window.location.reload(); }, 1200);
            } else {
                errEl.textContent = data.message || 'Something went wrong. Please try again.';
                errEl.style.display = 'block';
                btn.disabled = false;
                btn.innerHTML = '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg> Accept & Sign';
            }
        })
        .catch(function() {
            errEl.textContent = 'Network error. Please try again.';
            errEl.style.display = 'block';
            btn.disabled = false;
            btn.innerHTML = '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg> Accept & Sign';
        });
    });
})();
</script>
