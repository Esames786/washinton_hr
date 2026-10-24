{{-- W-9 onboarding form in the HR portal (client, 23 Oct 2026). A copy of the agent portal's
     form (washinton_agent resources/views/w9/modal.blade.php): same fields, wording and checks.
     It posts to employee.w9.submit, which forwards it to the agent portal to be stored. --}}
@php
    // Same keys as App\W9Form::CLASSIFICATIONS in the agent portal, which validates them.
    $__w9Classes = [
        'individual'   => 'Individual / sole proprietor or single-member LLC',
        'c_corp'       => 'C Corporation',
        's_corp'       => 'S Corporation',
        'partnership'  => 'Partnership',
        'trust_estate' => 'Trust / estate',
        'llc'          => 'Limited liability company',
        'other'        => 'Other',
    ];
@endphp

<div id="w9Overlay" style="position:fixed;inset:0;z-index:99998;background:rgba(0,0,0,.72);display:none;align-items:center;justify-content:center;padding:16px;">
    <div style="background:#fff;border-radius:10px;width:100%;max-width:860px;max-height:94vh;box-shadow:0 20px 60px rgba(0,0,0,.45);overflow-y:auto;overflow-x:hidden;">

        <div style="background:#1a4ca0;color:#fff;padding:14px 22px;position:sticky;top:0;z-index:3;display:flex;align-items:center;gap:12px;">
            <div>
                <div style="font-weight:700;font-size:15px;">Form W-9 — Taxpayer Identification &amp; Certification</div>
                <div style="font-size:12px;opacity:.85;margin-top:2px;">Required for US tax reporting. Complete once; it is stored securely.</div>
            </div>
            <button type="button" id="w9Close" style="margin-left:auto;background:none;border:none;color:#fff;font-size:22px;line-height:1;cursor:pointer;opacity:.8;">&times;</button>
        </div>

        <form id="w9Form" style="padding:22px 26px 26px;">
            @csrf
            <div id="w9Error" style="display:none;background:#fde8e8;border:1px solid #f5a0a0;border-radius:6px;padding:10px 14px;color:#c0392b;font-size:13px;margin-bottom:14px;"></div>
            <div id="w9Success" style="display:none;text-align:center;padding:30px 16px;">
                <div style="font-size:2.6rem;">✅</div>
                <h4 style="color:#059669;margin:8px 0;">W-9 submitted</h4>
                <p style="color:#374151;font-size:13.5px;">Thank you — your W-9 is on file. You can close this window.</p>
            </div>

            <div id="w9Fields">
                <div style="font-size:11px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:#9ca3af;margin-bottom:10px;">Taxpayer Information</div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
                    <div>
                        <label class="w9-l">1. Name (as shown on your income tax return) <span style="color:red;">*</span></label>
                        <input type="text" name="legal_name" class="w9-i" value="{{ $employee->full_name ?? '' }}" required>
                        <div class="w9-e" id="w9err_legal_name"></div>
                    </div>
                    <div>
                        <label class="w9-l">2. Business name / disregarded entity <small style="color:#888;">(if different)</small></label>
                        <input type="text" name="business_name" class="w9-i">
                        <div class="w9-e" id="w9err_business_name"></div>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
                    <div>
                        <label class="w9-l">3. Federal tax classification <span style="color:red;">*</span></label>
                        <select name="tax_classification" id="w9Class" class="w9-i" required>
                            @foreach($__w9Classes as $key => $label)
                                <option value="{{ $key }}" @if($key === 'individual') selected @endif>{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="w9-e" id="w9err_tax_classification"></div>
                    </div>
                    <div id="w9LlcWrap" style="display:none;">
                        <label class="w9-l">LLC tax classification <span style="color:red;">*</span></label>
                        <select name="llc_tax_class" class="w9-i">
                            <option value="">-- Select --</option>
                            <option value="C">C — C corporation</option>
                            <option value="S">S — S corporation</option>
                            <option value="P">P — Partnership</option>
                        </select>
                        <div class="w9-e" id="w9err_llc_tax_class"></div>
                    </div>
                    <div id="w9OtherWrap" style="display:none;">
                        <label class="w9-l">Describe "Other" <span style="color:red;">*</span></label>
                        <input type="text" name="other_classification" class="w9-i">
                        <div class="w9-e" id="w9err_other_classification"></div>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
                    <div>
                        <label class="w9-l">4. Exempt payee code <small style="color:#888;">(optional)</small></label>
                        <input type="text" name="exempt_payee_code" class="w9-i" maxlength="10">
                    </div>
                    <div>
                        <label class="w9-l">FATCA reporting exemption code <small style="color:#888;">(optional)</small></label>
                        <input type="text" name="fatca_code" class="w9-i" maxlength="10">
                    </div>
                </div>

                <div style="margin-bottom:14px;">
                    <label class="w9-l">5. Address (number, street, apt or suite) <span style="color:red;">*</span></label>
                    <input type="text" name="address" class="w9-i" required>
                    <div class="w9-e" id="w9err_address"></div>
                </div>

                <div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:14px;margin-bottom:14px;">
                    <div>
                        <label class="w9-l">6. City <span style="color:red;">*</span></label>
                        <input type="text" name="city" class="w9-i" required>
                        <div class="w9-e" id="w9err_city"></div>
                    </div>
                    <div>
                        <label class="w9-l">State <span style="color:red;">*</span></label>
                        <input type="text" name="state" class="w9-i" required>
                        <div class="w9-e" id="w9err_state"></div>
                    </div>
                    <div>
                        <label class="w9-l">ZIP <span style="color:red;">*</span></label>
                        <input type="text" name="zip" class="w9-i" maxlength="10" required>
                        <div class="w9-e" id="w9err_zip"></div>
                    </div>
                </div>

                <div style="margin-bottom:16px;">
                    <label class="w9-l">7. Account number(s) <small style="color:#888;">(optional)</small></label>
                    <input type="text" name="account_numbers" class="w9-i">
                </div>

                <div style="font-size:11px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:#9ca3af;margin:18px 0 10px;">Part I — Taxpayer Identification Number</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:6px;">
                    <div>
                        <label class="w9-l">TIN type <span style="color:red;">*</span></label>
                        <select name="tin_type" id="w9TinType" class="w9-i">
                            <option value="ssn">SSN — Social Security Number</option>
                            <option value="ein">EIN — Employer Identification Number</option>
                        </select>
                    </div>
                    <div>
                        <label class="w9-l"><span id="w9TinLabel">Social Security Number</span> <span style="color:red;">*</span></label>
                        <input type="text" name="tin" id="w9Tin" class="w9-i" placeholder="123-45-6789" maxlength="11" required autocomplete="off">
                        <div class="w9-e" id="w9err_tin"></div>
                    </div>
                </div>
                <p style="font-size:11.5px;color:#6b7280;margin-bottom:16px;">🔒 Your TIN is encrypted before it is stored and is never shown in full to staff.</p>

                <div style="font-size:11px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:#9ca3af;margin:18px 0 10px;">Part II — Certification</div>
                <div style="border:1px solid #e5e7eb;border-radius:6px;padding:12px 14px;background:#fafafa;font-size:12.5px;color:#444;line-height:1.6;margin-bottom:14px;">
                    Under penalties of perjury, I certify that:
                    <ol style="margin:6px 0 0 18px;">
                        <li>The number shown on this form is my correct taxpayer identification number (or I am waiting for a number to be issued to me); and</li>
                        <li>I am not subject to backup withholding because: (a) I am exempt from backup withholding, or (b) I have not been notified by the IRS that I am subject to backup withholding, or (c) the IRS has notified me that I am no longer subject to backup withholding; and</li>
                        <li>I am a U.S. citizen or other U.S. person; and</li>
                        <li>The FATCA code(s) entered on this form (if any) is correct.</li>
                    </ol>
                </div>

                <div style="margin-bottom:14px;">
                    <label class="w9-l">Signature <span style="color:red;">*</span> <small style="color:#888;">(draw with mouse or touch)</small></label>
                    <div style="position:relative;border:2px dashed #aab4cc;border-radius:6px;background:#fff;display:inline-block;">
                        <canvas id="w9Canvas" width="480" height="110" style="display:block;cursor:crosshair;touch-action:none;"></canvas>
                        <button type="button" id="w9ClearSig" style="position:absolute;top:6px;right:6px;font-size:11px;padding:2px 8px;background:#eee;border:1px solid #ccc;border-radius:4px;cursor:pointer;">Clear</button>
                    </div>
                    <input type="hidden" name="signature_data" id="w9SignatureData">
                    <div class="w9-e" id="w9err_signature_data"></div>
                </div>

                <label style="display:flex;align-items:flex-start;gap:8px;margin-bottom:16px;cursor:pointer;">
                    {{-- appearance:auto — the HR theme resets every input to appearance:none, which drew
                         this checkbox as nothing at all (client, 24 Oct 2026: "no check box"). --}}
                    <input type="checkbox" name="certified" id="w9Certified" value="1"
                           style="margin-top:3px;width:16px;height:16px;flex-shrink:0;appearance:auto;-webkit-appearance:checkbox;cursor:pointer;">
                    <span style="font-size:13px;color:#333;">I certify, under penalties of perjury, that the information above is true and correct. <span style="color:red;">*</span></span>
                </label>
                <div class="w9-e" id="w9err_certified" style="margin-top:-10px;margin-bottom:12px;"></div>

                <button type="submit" id="w9Submit" style="background:#1a4ca0;color:#fff;border:none;border-radius:6px;padding:11px 30px;font-size:14px;font-weight:600;cursor:pointer;">
                    Submit W-9
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .w9-l { display:block; font-size:12px; font-weight:600; color:#444; margin-bottom:4px; }
    .w9-i { width:100%; border:1px solid #ccc; border-radius:5px; padding:8px 10px; font-size:13px; background:#fff; }
    select.w9-i { appearance:auto; -webkit-appearance:menulist; }   /* the theme strips select arrows too */
    .w9-i.is-bad { border-color:#e11d48; background:rgba(225,29,72,.04); }
    .w9-e { color:#e11d48; font-size:11.5px; margin-top:3px; display:none; }
</style>

<script>
(function () {
    var overlay = document.getElementById('w9Overlay');
    if (!overlay) return;

    // ── Signature pad ──
    var canvas = document.getElementById('w9Canvas');
    var ctx = canvas.getContext('2d');
    var drawing = false, lastX = 0, lastY = 0, hasDraw = false;
    function pos(e) {
        var r = canvas.getBoundingClientRect(), s = e.touches ? e.touches[0] : e;
        return { x: s.clientX - r.left, y: s.clientY - r.top };
    }
    function start(e) { e.preventDefault(); drawing = true; var p = pos(e); lastX = p.x; lastY = p.y; }
    function stop() { drawing = false; }
    function draw(e) {
        if (!drawing) return;
        e.preventDefault();
        var p = pos(e);
        ctx.strokeStyle = '#1a1a2e'; ctx.lineWidth = 2; ctx.lineCap = 'round'; ctx.lineJoin = 'round';
        ctx.beginPath(); ctx.moveTo(lastX, lastY); ctx.lineTo(p.x, p.y); ctx.stroke();
        lastX = p.x; lastY = p.y; hasDraw = true;
    }
    canvas.addEventListener('mousedown', start);
    canvas.addEventListener('mouseup', stop);
    canvas.addEventListener('mouseleave', stop);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('touchstart', start, { passive: false });
    canvas.addEventListener('touchend', stop);
    canvas.addEventListener('touchmove', draw, { passive: false });
    document.getElementById('w9ClearSig').addEventListener('click', function () {
        ctx.clearRect(0, 0, canvas.width, canvas.height); hasDraw = false;
    });

    // ── Conditional fields ──
    var classSel = document.getElementById('w9Class');
    function syncClass() {
        document.getElementById('w9LlcWrap').style.display   = classSel.value === 'llc'   ? '' : 'none';
        document.getElementById('w9OtherWrap').style.display = classSel.value === 'other' ? '' : 'none';
    }
    classSel.addEventListener('change', syncClass); syncClass();

    var tinType = document.getElementById('w9TinType');
    var tinInput = document.getElementById('w9Tin');
    tinType.addEventListener('change', function () {
        var isEin = this.value === 'ein';
        document.getElementById('w9TinLabel').textContent = isEin ? 'Employer Identification Number' : 'Social Security Number';
        tinInput.placeholder = isEin ? '12-3456789' : '123-45-6789';
    });

    function clearErrors() {
        document.querySelectorAll('#w9Form .w9-e').forEach(function (el) { el.style.display = 'none'; el.textContent = ''; });
        document.querySelectorAll('#w9Form .w9-i.is-bad').forEach(function (el) { el.classList.remove('is-bad'); });
        document.getElementById('w9Error').style.display = 'none';
    }
    function fieldError(name, msg) {
        var e = document.getElementById('w9err_' + name);
        if (e) { e.textContent = msg; e.style.display = 'block'; }
        var i = document.querySelector('#w9Form [name="' + name + '"]');
        if (i && i.classList) i.classList.add('is-bad');
        return i;
    }

    // ── Front-end validation (mirrors the server rules) ──
    function validate() {
        var first = null;
        function bad(n, m) { var el = fieldError(n, m); if (!first) first = el; }
        function v(n) { var el = document.querySelector('#w9Form [name="' + n + '"]'); return el ? el.value.trim() : ''; }

        if (!v('legal_name')) bad('legal_name', 'Name is required.');
        if (classSel.value === 'llc' && !v('llc_tax_class')) bad('llc_tax_class', 'Choose the LLC tax classification.');
        if (classSel.value === 'other' && !v('other_classification')) bad('other_classification', 'Describe the "Other" classification.');
        if (!v('address')) bad('address', 'Address is required.');
        if (!v('city'))    bad('city', 'City is required.');
        if (!v('state'))   bad('state', 'State is required.');
        var zip = v('zip');
        if (!zip) bad('zip', 'ZIP is required.');
        else if (!/^\d{5}(-\d{4})?$/.test(zip)) bad('zip', 'Enter a valid ZIP (12345 or 12345-6789).');

        var digits = v('tin').replace(/\D/g, '');
        if (!digits) bad('tin', 'Your TIN is required.');
        else if (digits.length !== 9) bad('tin', 'A TIN must be exactly 9 digits.');

        if (!hasDraw) bad('signature_data', 'Please draw your signature.');
        if (!document.getElementById('w9Certified').checked) bad('certified', 'You must certify the information.');

        if (first) { try { first.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (e) {} return false; }
        return true;
    }

    document.getElementById('w9Form').addEventListener('submit', function (e) {
        e.preventDefault();
        clearErrors();
        if (!validate()) return;

        document.getElementById('w9SignatureData').value = canvas.toDataURL('image/png');

        var btn = document.getElementById('w9Submit');
        btn.disabled = true; btn.textContent = 'Submitting…';

        fetch('{{ route("employee.w9.submit") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
            body: new FormData(this)
        })
        .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
        .then(function (res) {
            btn.disabled = false; btn.textContent = 'Submit W-9';
            if (res.ok && res.data.success) {
                document.getElementById('w9Fields').style.display = 'none';
                document.getElementById('w9Success').style.display = 'block';
                setTimeout(function () { window.location.reload(); }, 1800);
                return;
            }
            if (res.data && res.data.errors) {
                Object.keys(res.data.errors).forEach(function (f) { fieldError(f, res.data.errors[f][0]); });
                return;
            }
            var box = document.getElementById('w9Error');
            box.textContent = (res.data && res.data.message) || 'Submission failed. Please try again.';
            box.style.display = 'block';
        })
        .catch(function () {
            btn.disabled = false; btn.textContent = 'Submit W-9';
            var box = document.getElementById('w9Error');
            box.textContent = 'Network error. Please try again.';
            box.style.display = 'block';
        });
    });

    document.getElementById('w9Close').addEventListener('click', function () { overlay.style.display = 'none'; });

    // Opened from the W-9 card on the profile page.
    window.openW9Form = function () { overlay.style.display = 'flex'; };
})();
</script>
