<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
@php
    // Never hardcode a company here — fall back to the configured default brand so a Hello
    // subcontractor's NDA can never render CrazyRays details.
    $brand        = (is_array($brand ?? null) && $brand) ? $brand : \App\Support\Brand::byKey(config('brands.default', 'hellotransport'));
    $companyName  = $brand['name']  ?? 'Hello Transport';
    $companyPhone = $brand['phone'] ?? '';
    $companyMail  = $brand['contact_email'] ?? ($brand['email'] ?? '');
    $companySite  = $brand['site']  ?? '';
    $ndaContent   = $ndaContent   ?? '';
    $signedIp     = $signedIp     ?? '';
    $cnicFrontPath = $cnicFrontPath ?? null;
    $cnicBackPath  = $cnicBackPath  ?? null;
@endphp
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11pt; color: #222; line-height: 1.55; padding: 40px 50px; }
    .header-wrap { text-align: center; margin-bottom: 20px; }
    .company-name { font-size: 13pt; font-weight: bold; color: #1a4ca0; letter-spacing: .3px; }
    .doc-title { text-align: center; margin-bottom: 20px; }
    .doc-title h1 { font-size: 14pt; font-weight: bold; color: #1a4ca0; text-transform: uppercase; letter-spacing: .5px; line-height: 1.4; }
    .nda-body { font-size: 10.5pt; }
    .nda-body h1, .nda-body h2, .nda-body h3, .nda-body h4 { color: #c0392b; font-size: 11pt; margin: 12px 0 4px; text-transform: uppercase; }
    .nda-body p { margin-bottom: 8px; }
    .nda-body ul { padding-left: 18px; margin: 4px 0 8px; }
    .nda-body li { margin-bottom: 3px; }
    hr.divider { border: none; border-top: 1px solid #ccc; margin: 22px 0; }
    .sign-block { margin-bottom: 18px; }
    .sign-block .label { font-weight: bold; color: #1a4ca0; font-size: 11pt; text-transform: uppercase; margin-bottom: 8px; }
    .sign-row { margin-bottom: 8px; }
    .sign-row .field-label { color: #555; font-size: 10pt; display: inline-block; width: 150px; }
    .sign-row .field-value { border-bottom: 1px solid #333; font-size: 11pt; }
    .sig-img { border: 1px solid #ccc; max-width: 260px; max-height: 80px; margin-top: 4px; }
    .cnic-imgs { margin-top: 10px; }
    .cnic-imgs .cnic-cell { display: inline-block; width: 47%; vertical-align: top; margin-right: 2%; }
    .cnic-imgs img { border: 1px solid #ccc; max-width: 100%; max-height: 150px; }
    .cnic-imgs .cnic-cap { font-size: 9pt; color: #555; margin-bottom: 3px; }
    .footer { margin-top: 28px; text-align: center; font-size: 9.5pt; color: #666; border-top: 1px solid #ddd; padding-top: 12px; }
</style>
</head>
<body>

<div class="header-wrap">
    <div class="company-name">{{ $companyName }}</div>
</div>

<div class="doc-title">
    <h1>Non-Disclosure Agreement (NDA)<br>&amp; Confidentiality Acknowledgment</h1>
</div>

{{-- The exact NDA copy the subcontractor signed (admin-prepared rich text, or the default). --}}
<div class="nda-body">
    {!! $ndaContent !!}
</div>

<hr class="divider">

<div class="sign-block">
    <div class="label">Subcontractor Acknowledgment &amp; Signature</div>
    <div class="sign-row"><span class="field-label">Agent Full Name:</span> <span class="field-value">{{ $employeeName }}</span></div>
    <div class="sign-row"><span class="field-label">Father's Name:</span> <span class="field-value">{{ $fatherName ?? '' }}</span></div>
    <div class="sign-row"><span class="field-label">Address:</span> <span class="field-value">{{ $address ?? '' }}</span></div>
    <div class="sign-row"><span class="field-label">{{ (($brand['key'] ?? '') !== 'crazyrays') ? 'State ID' : 'CNIC' }} Number:</span> <span class="field-value">{{ $cnic }}</span></div>
    <div class="sign-row"><span class="field-label">Date Signed:</span> <span class="field-value">{{ $signedDate }}</span></div>
    @if($signedIp)
    <div class="sign-row"><span class="field-label">Signed From IP:</span> <span class="field-value">{{ $signedIp }}</span></div>
    @endif
    <div class="sign-row" style="margin-top:10px;">
        <div class="field-label">Signature:</div>
        @if(!empty($signatureData))
            <img class="sig-img" src="{{ $signatureData }}" alt="Signature">
        @endif
    </div>

    @if($cnicFrontPath || $cnicBackPath)
    <div class="cnic-imgs">
        @if($cnicFrontPath && @file_exists($cnicFrontPath))
        <div class="cnic-cell">
            <div class="cnic-cap">{{ (($brand['key'] ?? '') !== 'crazyrays') ? 'State ID' : 'CNIC' }} Front</div>
            <img src="{{ $cnicFrontPath }}" alt="CNIC Front">
        </div>
        @endif
        @if($cnicBackPath && @file_exists($cnicBackPath))
        <div class="cnic-cell">
            <div class="cnic-cap">{{ (($brand['key'] ?? '') !== 'crazyrays') ? 'State ID' : 'CNIC' }} Back</div>
            <img src="{{ $cnicBackPath }}" alt="CNIC Back">
        </div>
        @endif
    </div>
    @endif
</div>

<div class="sign-block" style="margin-top:16px;">
    <div class="label">Company Representative</div>
    <div class="sign-row"><span class="field-label">Authorized Representative:</span> <span class="field-value">{{ $companyName }}</span></div>
</div>

<div class="footer">
    <strong>{{ strtoupper($companyName) }}</strong><br>
    @if($companyPhone) Phone: {{ $companyPhone }} &nbsp;|&nbsp; @endif
    @if($companyMail) Email: {{ $companyMail }} &nbsp;|&nbsp; @endif
    @if($companySite) Website: {{ $companySite }} @endif
</div>

</body>
</html>
