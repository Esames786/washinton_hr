{{-- NDA Blocking Overlay — injected in layout when auth('employee')->user()->nda_required == 1 --}}
@php $signRoute = $signRoute ?? route('employee.nda.sign'); @endphp

<div id="ndaOverlay" style="
    position: fixed; inset: 0; z-index: 99999;
    background: rgba(0,0,0,.72);
    display: flex; align-items: center; justify-content: center;
    padding: 16px;
">
    <div style="
        background: #fff; border-radius: 10px;
        width: 100%; max-width: 1080px; max-height: 96vh;
        display: flex; flex-direction: column;
        box-shadow: 0 20px 60px rgba(0,0,0,.45);
        overflow: hidden;
    ">
        {{-- Header --}}
        <div style="background:#1a4ca0; color:#fff; padding:14px 22px; flex-shrink:0; display:flex; align-items:center; gap:12px;">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>
            </svg>
            <div>
                <div style="font-weight:700; font-size:15px;">Action Required: NDA & Confidentiality Agreement</div>
                <div style="font-size:12px; opacity:.85; margin-top:2px;">Please read the full document and sign before continuing.</div>
            </div>
        </div>

        {{-- Scrollable document --}}
        <div style="flex:1; overflow-y:auto; padding:28px 30px 16px; border-bottom:1px solid #e0e0e0;">

            <div style="text-align:center; margin-bottom:22px;">
                <img src="/Uploads/Settings/logo.png" alt="Crazy Rays Solutions" style="height:64px;" onerror="this.style.display='none'">
            </div>

            <h2 style="text-align:center; color:#1a4ca0; font-size:16px; font-weight:700; text-transform:uppercase; letter-spacing:.4px; margin-bottom:18px; line-height:1.4;">
                Non-Disclosure Agreement (NDA)<br>& Confidentiality Acknowledgment
            </h2>

            <p style="margin-bottom:16px; color:#333; font-size:13.5px;">
                This Non-Disclosure Agreement ("Agreement") is entered into between <strong>Crazy Rays Solutions</strong>
                ("Company") and the undersigned Subcontractor ("Recipient").
            </p>

            @foreach([
                ['1. Confidential Information', 'The Recipient acknowledges that during employment or engagement with Crazy Rays Solutions, they may have access to confidential and proprietary information including but not limited to:', [
                    'Client lists and customer information',
                    'Pricing, rates, commissions, and contracts',
                    'Freight brokerage and dispatching data',
                    'Sales scripts, leads, and marketing strategies',
                    'Company processes, SOPs, and business methods',
                    'Financial information and internal reports',
                    'Subcontractor information and company records',
                ]],
                ['2. Non-Disclosure Obligation', 'The Recipient agrees not to disclose, copy, distribute, sell, share, or use confidential information for any purpose other than performing authorized duties for the Company.', []],
                ['3. Data Security', 'The Recipient shall maintain the confidentiality of all company information and protect all company files, documents, credentials, and systems from unauthorized access.', []],
                ['4. Return of Company Property', 'Upon termination of employment or engagement, the Recipient shall immediately return all company property, documents, files, equipment, passwords, and confidential materials.', []],
                ['5. Non-Solicitation', 'The Recipient agrees not to directly solicit or divert Company clients, customers, carriers, employees, or business opportunities during employment and for a period of 12 months after separation.', []],
                ['6. Breach of Agreement', 'Any breach of this Agreement may result in disciplinary action, termination of employment, legal action, and claims for damages as permitted by applicable law.', []],
                ['7. Termination', 'The confidentiality obligations contained in this Agreement shall survive the termination of employment and remain in effect indefinitely unless otherwise required by law.', []],
                ['8. Acknowledgment', 'I acknowledge that I have read, understood, and agree to comply with the terms of this NDA & Confidentiality Agreement and understand the consequences of violating its provisions.', []],
            ] as [$heading, $body, $items])
            <div style="margin-bottom:14px;">
                <div style="color:#c0392b; font-weight:700; font-size:13px; text-transform:uppercase; margin-bottom:5px;">{{ $heading }}</div>
                <p style="color:#333; font-size:13px; line-height:1.6;">{{ $body }}</p>
                @if(count($items))
                <ul style="margin-top:6px; padding-left:20px;">
                    @foreach($items as $item)
                    <li style="color:#444; font-size:13px; margin-bottom:3px;">{{ $item }}</li>
                    @endforeach
                </ul>
                @endif
            </div>
            @endforeach

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
                        <label style="font-size:12px; font-weight:600; color:#444; display:block; margin-bottom:4px;">Full Name <span style="color:red;">*</span></label>
                        <input type="text" id="ndaFullName" name="employee_name"
                               value="{{ auth('employee')->user()->full_name ?? '' }}"
                               style="width:100%; border:1px solid #ccc; border-radius:5px; padding:7px 10px; font-size:13px;" required>
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#444; display:block; margin-bottom:4px;">CNIC Number <span style="color:red;">*</span></label>
                        <input type="text" id="ndaCnic" name="cnic"
                               value="{{ auth('employee')->user()->cnic ?? '' }}"
                               style="width:100%; border:1px solid #ccc; border-radius:5px; padding:7px 10px; font-size:13px;" required placeholder="e.g. 42101-1234567-1">
                    </div>
                </div>

                <div style="margin-bottom:14px;">
                    <label style="font-size:12px; font-weight:600; color:#444; display:block; margin-bottom:6px;">
                        Draw Your Signature Below <span style="color:red;">*</span>
                        <span style="font-weight:400; color:#888; margin-left:8px; font-size:11px;">(Use mouse or touch)</span>
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
                        I have read the full NDA &amp; Confidentiality Agreement and agree to comply with all its terms.
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
