<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link Expired</title>
    <link rel="icon" type="image/svg+xml"
          href="{{ asset(\App\Support\Brand::current()['logo'] ?? 'assets/images/logo/hello_transport.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/lib/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <style>
        body { background:#f4f6f8; min-height:100vh; display:flex; align-items:center; }
        .sp-card { max-width:460px; margin:0 auto; background:#fff; border-radius:12px;
                   box-shadow:0 4px 24px rgba(0,0,0,.08); padding:38px 32px; text-align:center; }
    </style>
</head>
<body>

<div class="container">
    <div class="sp-card">
        <div style="font-size:44px;line-height:1;margin-bottom:14px;">⏱️</div>
        <h4 style="margin-bottom:12px;">This link has expired</h4>
        <p style="font-size:14px;color:#6c757d;line-height:1.75;">
            Password links are valid for a limited time and can only be used once. If you have
            already set your password, sign in below. Otherwise contact us and we will send you
            a new link.
        </p>
        <a href="{{ route('employee.login') }}" class="btn btn-primary mt-2"
           style="background:#062e39;border:0;padding:11px 28px;border-radius:6px;font-weight:600;">
            Go to Sign In
        </a>
        <p class="mt-3 mb-0" style="font-size:13px;color:#8a9199;">
            <a href="mailto:info@hellotransport.com" style="color:#062e39;">info@hellotransport.com</a>
        </p>
    </div>
</div>

</body>
</html>
