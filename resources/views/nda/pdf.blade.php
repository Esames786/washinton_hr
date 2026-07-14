<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11pt; color: #222; line-height: 1.55; padding: 40px 50px; }
    .header-wrap { text-align: center; margin-bottom: 28px; }
    .header-wrap img { width: 110px; display: block; margin: 0 auto 6px; }
    .company-name { font-size: 13pt; font-weight: bold; color: #1a4ca0; letter-spacing: .3px; }
    .doc-title { text-align: center; margin-bottom: 22px; }
    .doc-title h1 { font-size: 14pt; font-weight: bold; color: #1a4ca0; text-transform: uppercase; letter-spacing: .5px; line-height: 1.4; }
    .intro { margin-bottom: 18px; font-size: 11pt; }
    .section { margin-bottom: 16px; }
    .section-heading { font-size: 11pt; font-weight: bold; color: #c0392b; text-transform: uppercase; margin-bottom: 6px; }
    .section ul { padding-left: 18px; margin-top: 4px; }
    .section ul li { margin-bottom: 3px; font-size: 10.5pt; }
    hr.divider { border: none; border-top: 1px solid #ccc; margin: 22px 0; }
    .sign-section { margin-top: 24px; }
    .sign-block { margin-bottom: 20px; }
    .sign-block .label { font-weight: bold; color: #1a4ca0; font-size: 11pt; text-transform: uppercase; margin-bottom: 10px; }
    .sign-row { display: flex; margin-bottom: 10px; align-items: flex-end; }
    .sign-row .field-label { width: 160px; font-size: 10pt; color: #555; flex-shrink: 0; }
    .sign-row .field-value { border-bottom: 1px solid #333; flex: 1; font-size: 11pt; padding-bottom: 2px; min-height: 18px; }
    .sig-canvas-wrap { margin-top: 4px; }
    .sig-canvas-wrap .field-label { font-size: 10pt; color: #555; margin-bottom: 4px; }
    .sig-img { border: 1px solid #ccc; max-width: 260px; max-height: 80px; }
    .footer { margin-top: 30px; text-align: center; font-size: 9.5pt; color: #666; border-top: 1px solid #ddd; padding-top: 12px; }
</style>
</head>
<body>

<div class="header-wrap">
    @php $logoPath = public_path('Uploads/Settings/logo.png'); @endphp
    @if(file_exists($logoPath))
        <img src="{{ $logoPath }}" alt="">
    @endif
    <div class="company-name">Crazy Rays Solutions</div>
</div>

<div class="doc-title">
    <h1>Non-Disclosure Agreement (NDA)<br>&amp; Confidentiality Acknowledgment</h1>
</div>

<p class="intro">
    This Non-Disclosure Agreement ("Agreement") is entered into between <strong>Crazy Rays Solutions</strong>
    ("Company") and the undersigned Subcontractor ("Recipient").
</p>

<div class="section">
    <div class="section-heading">1. Confidential Information</div>
    <p>The Recipient acknowledges that during employment or engagement with Crazy Rays Solutions, they may have access to confidential and proprietary information including but not limited to:</p>
    <ul>
        <li>Client lists and customer information</li>
        <li>Pricing, rates, commissions, and contracts</li>
        <li>Freight brokerage and dispatching data</li>
        <li>Sales scripts, leads, and marketing strategies</li>
        <li>Company processes, SOPs, and business methods</li>
        <li>Financial information and internal reports</li>
        <li>Subcontractor information and company records</li>
    </ul>
</div>

<div class="section">
    <div class="section-heading">2. Non-Disclosure Obligation</div>
    <p>The Recipient agrees not to disclose, copy, distribute, sell, share, or use confidential information for any purpose other than performing authorized duties for the Company.</p>
</div>

<div class="section">
    <div class="section-heading">3. Data Security</div>
    <p>The Recipient shall maintain the confidentiality of all company information and protect all company files, documents, credentials, and systems from unauthorized access.</p>
</div>

<div class="section">
    <div class="section-heading">4. Return of Company Property</div>
    <p>Upon termination of employment or engagement, the Recipient shall immediately return all company property, documents, files, equipment, passwords, and confidential materials.</p>
</div>

<div class="section">
    <div class="section-heading">5. Non-Solicitation</div>
    <p>The Recipient agrees not to directly solicit or divert Company clients, customers, carriers, employees, or business opportunities during employment and for a period of <strong>12 months</strong> after separation.</p>
</div>

<div class="section">
    <div class="section-heading">6. Breach of Agreement</div>
    <p>Any breach of this Agreement may result in disciplinary action, termination of employment, legal action, and claims for damages as permitted by applicable law.</p>
</div>

<div class="section">
    <div class="section-heading">7. Termination</div>
    <p>The confidentiality obligations contained in this Agreement shall survive the termination of employment and remain in effect indefinitely unless otherwise required by law.</p>
</div>

<div class="section">
    <div class="section-heading">8. Acknowledgment</div>
    <p>I acknowledge that I have read, understood, and agree to comply with the terms of this NDA &amp; Confidentiality Agreement and understand the consequences of violating its provisions.</p>
</div>

<hr class="divider">

<div class="sign-section">
    <div class="sign-block">
        <div class="label">Subcontractor Details</div>
        <div class="sign-row">
            <span class="field-label">Subcontractor Name:</span>
            <span class="field-value">{{ $employeeName }}</span>
        </div>
        <div class="sign-row">
            <span class="field-label">CNIC Number:</span>
            <span class="field-value">{{ $cnic }}</span>
        </div>
        <div class="sign-row">
            <span class="field-label">Date:</span>
            <span class="field-value">{{ $signedDate }}</span>
        </div>
        <div class="sig-canvas-wrap">
            <div class="field-label">Signature:</div>
            @if($signatureData)
                <img class="sig-img" src="{{ $signatureData }}" alt="Subcontractor Signature">
            @endif
        </div>
    </div>

    <div class="sign-block" style="margin-top:20px;">
        <div class="label">Company Representative</div>
        <div class="sign-row">
            <span class="field-label">Authorized Representative:</span>
            <span class="field-value">Crazy Rays Solutions</span>
        </div>
        <div class="sign-row">
            <span class="field-label">Signature:</span>
            <span class="field-value"></span>
        </div>
        <div class="sign-row">
            <span class="field-label">Date:</span>
            <span class="field-value"></span>
        </div>
    </div>
</div>

<div class="footer">
    <strong>CRAZY RAYS SOLUTIONS</strong><br>
    Phone: 0313-8432343 &nbsp;|&nbsp; Email: info@crazyrayssolutions.com.pk &nbsp;|&nbsp; Website: https://crazyrayssolutions.com.pk
</div>

</body>
</html>
