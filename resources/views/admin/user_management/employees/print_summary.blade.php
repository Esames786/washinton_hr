<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php $brand = method_exists($employee, 'brandName') ? $employee->brandName() : 'Hello Transport'; @endphp
    <title>Subcontractor Summary — {{ $employee->full_name }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; color:#1a1a2e; margin:0; padding:24px; font-size:13px; }
        .hdr { display:flex; align-items:center; justify-content:space-between; border-bottom:3px solid #d4a72c; padding-bottom:12px; margin-bottom:18px; }
        .hdr h1 { margin:0; font-size:20px; }
        .hdr .brand { font-size:16px; font-weight:800; color:#b8860b; }
        /* Section titles sit above long embedded documents (Contract / NDA) that carry their own
           large headings — keep these clearly bigger so they still read as section titles. */
        h2 { font-size:19px; font-weight:800; letter-spacing:.2px; margin:22px 0 10px; padding:9px 12px; background:#f3f4f6; border-left:5px solid #d4a72c; }
        /* Keep the embedded agreement's own headings below the section title in the hierarchy. */
        .doc-embed h1 { font-size:17px; margin:6px 0; }
        .doc-embed h2 { font-size:15px; font-weight:700; background:none; border-left:0; padding:0; margin:10px 0 4px; }
        .doc-embed h3, .doc-embed h4 { font-size:14px; margin:8px 0 4px; }
        table { width:100%; border-collapse:collapse; margin-bottom:10px; }
        td, th { border:1px solid #dee2e6; padding:6px 9px; text-align:left; vertical-align:top; }
        th { background:#fafafa; width:32%; font-weight:600; }
        .docs th { width:auto; background:#fafafa; }
        .badge { display:inline-block; padding:2px 8px; border-radius:10px; font-size:11px; color:#fff; }
        .b-ok { background:#16a34a; } .b-pend { background:#9ca3af; } .b-req { background:#e11d48; }
        .contract-box { border:1px solid #dee2e6; padding:12px; border-radius:6px; max-height:none; }
        .muted { color:#6b7280; }
        .actions { text-align:center; margin:22px 0; }
        .actions button { background:#1a73e8; color:#fff; border:none; padding:9px 22px; border-radius:6px; font-weight:700; cursor:pointer; }
        @media print { .actions { display:none; } body { padding:0; } }
        .grid2 { display:grid; grid-template-columns:1fr 1fr; gap:0 18px; }
    </style>
</head>
<body onload="window.print()">

    <div class="hdr">
        <h1>Subcontractor Summary</h1>
        <div class="brand">{{ $brand }} HR</div>
    </div>

    {{-- #5: state at the very top who this agreement is between. --}}
    <p style="text-align:center;font-size:14px;font-weight:700;margin:0 0 16px;color:#1a1a2e;">
        This contract is between <u>{{ $employee->full_name ?? '-' }}</u> and
        <u>{{ \App\Support\Brand::for($employee)['name'] ?? 'Hello Transport' }}</u>.
    </p>

    <h2>Profile Information</h2>
    <div class="grid2">
        <table>
            <tr><th>Full Name</th><td>{{ $employee->full_name ?? '-' }}</td></tr>
            <tr><th>Father's Name</th><td>{{ $employee->father_name ?? '-' }}</td></tr>
            <tr><th>Subcontractor Code</th><td>{{ $employee->employee_code ?? '-' }}</td></tr>
            <tr><th>Email</th><td>{{ $employee->email ?? '-' }}</td></tr>
            <tr><th>Phone</th><td>{{ $employee->phone ?? '-' }}</td></tr>
            <tr><th>CNIC / National ID</th><td>{{ $employee->cnic ?? ($employee->national_id ?? '-') }}</td></tr>
            <tr><th>Date of Birth</th><td>{{ $employee->dob ?? '-' }}</td></tr>
        </table>
        <table>
            <tr><th>Gender</th><td>{{ ucfirst($employee->gender ?? '-') }}</td></tr>
            <tr><th>Marital Status</th><td>{{ ucfirst($employee->marital_status ?? '-') }}</td></tr>
            <tr><th>Address</th><td>{{ $employee->address ?? '-' }}{{ $employee->city ? ', '.$employee->city : '' }}{{ $employee->state ? ', '.$employee->state : '' }}</td></tr>
            <tr><th>Employment</th><td>{{ optional($employee->employment_type)->name ?? '-' }}</td></tr>
            <tr><th>Status</th><td>{{ optional($employee->employee_status)->name ?? '-' }}</td></tr>
            <tr><th>Shift</th><td>{{ optional($employee->shift)->name ?? '-' }}
                @if(optional($employee->shift)->shift_start) ({{ $employee->shift->shift_start }} – {{ $employee->shift->shift_end }}) @endif</td></tr>
            <tr><th>Joining Date</th><td>{{ $employee->joining_date ?? '-' }}</td></tr>
        </table>
    </div>

    <h2>Bank Details</h2>
    @if($employee->bankDetail)
        <table>
            <tr><th>Bank Name</th><td>{{ $employee->bankDetail->bank_name ?? '-' }}</td></tr>
            <tr><th>Account Title</th><td>{{ $employee->bankDetail->account_title ?? '-' }}</td></tr>
            <tr><th>Account Number / IBAN</th><td>{{ $employee->bankDetail->account_number ?? ($employee->bankDetail->iban ?? '-') }}</td></tr>
        </table>
    @else
        <p class="muted">No bank details available.</p>
    @endif

    <h2>Working Days</h2>
    @php
        $dayNames = [0=>'Sun',1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat'];
        $working = [];
        foreach (($employee->working_days ?? []) as $wd) {
            if (($wd->is_working ?? 0)) { $working[] = $dayNames[$wd->day_of_week] ?? $wd->day_of_week; }
        }
    @endphp
    <p>{{ !empty($working) ? implode(', ', $working) : 'Not set' }}</p>

    <h2>Documents</h2>
    <table class="docs">
        <thead><tr><th>Document</th><th>Status</th><th>File</th></tr></thead>
        <tbody>
        @forelse($employee->documents ?? [] as $doc)
            <tr>
                <td>{{ $doc->file_name ?? ('Document #'.$doc->document_setting_id) }}</td>
                <td>@if(($doc->status ?? 0) == 1)<span class="badge b-ok">Verified</span>@else<span class="badge b-pend">Pending</span>@endif</td>
                <td>{{ $doc->file_path ? basename($doc->file_path) : '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="3" class="muted">No documents uploaded.</td></tr>
        @endforelse
        </tbody>
    </table>

    <h2>Contract</h2>
    @if(!empty($employee->contract))
        <p class="muted">
            Status:
            @if($employee->contract_accepted_at)
                <span class="badge b-ok">Accepted</span> on {{ \Carbon\Carbon::parse($employee->contract_accepted_at)->format('d M Y H:i') }}
                @if(!empty($employee->contract_signature))
                    — Signature: <img src="{{ $employee->contract_signature }}" alt="Signature" style="max-height:40px;border:1px solid #ccc;vertical-align:middle;">
                    @if(!empty($employee->contract_signed_ip)) <span class="muted">(IP {{ $employee->contract_signed_ip }})</span>@endif
                @endif
            @else
                <span class="badge b-req">Not accepted yet</span>
            @endif
        </p>
        <div class="contract-box doc-embed">{!! \App\Support\Brand::applyTokens($employee->contract, \App\Support\Brand::for($employee)) !!}</div>
    @else
        <p class="muted">No contract assigned.</p>
    @endif

    <h2>NDA</h2>
    @if($employee->nda_signed_at)
        <p><span class="badge b-ok">Signed</span> on {{ \Carbon\Carbon::parse($employee->nda_signed_at)->format('d M Y H:i') }}
        @if(!empty($employee->nda_document_url))— <a href="{{ $employee->nda_document_url }}" target="_blank">View signed NDA</a>@endif</p>
        @if(!empty($employee->nda_content))
            <div class="doc-embed" style="border:1px solid #ccc;padding:10px;margin-top:6px;font-size:12px;">
                {!! \App\Support\Brand::applyTokens($employee->nda_content, \App\Support\Brand::for($employee)) !!}
                <hr>
                <div><strong>Agent Full Name:</strong> {{ $employee->full_name ?? '-' }} &nbsp; <strong>Father's Name:</strong> {{ $employee->nda_father_name ?? '-' }}</div>
                <div><strong>CNIC:</strong> {{ $employee->cnic ?? '-' }} &nbsp; <strong>Address:</strong> {{ $employee->nda_address ?? '-' }}</div>
                <div><strong>Signed:</strong> {{ \Carbon\Carbon::parse($employee->nda_signed_at)->format('d M Y, h:i A') }} &nbsp; <strong>IP:</strong> {{ $employee->nda_signed_ip ?? '-' }}</div>
                @if(!empty($employee->nda_signature))
                    <div style="margin-top:6px;"><strong>Signature:</strong><br><img src="{{ $employee->nda_signature }}" style="max-height:70px;border:1px solid #ccc;"></div>
                @endif
            </div>
        @endif
    @elseif($employee->nda_required)
        <p><span class="badge b-req">Required — not signed yet</span></p>
    @else
        <p class="muted">NDA not required.</p>
    @endif

    <div class="actions">
        <button onclick="window.print()">🖨️ Print</button>
    </div>

</body>
</html>
