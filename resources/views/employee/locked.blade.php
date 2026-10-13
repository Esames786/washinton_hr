<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your application is in progress</title>
    <link rel="icon" type="image/svg+xml"
          href="{{ asset(\App\Support\Brand::current()['logo'] ?? 'assets/images/logo/hello_transport.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/lib/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <style>
        body { background:#f4f6f8; min-height:100vh; display:flex; align-items:center; }
        .lk-card { max-width:620px; margin:0 auto; background:#fff; border-radius:12px;
                   box-shadow:0 4px 24px rgba(0,0,0,.08); overflow:hidden; }
        .lk-head { background:#062e39; padding:30px; color:#fff; text-align:center; }
        .lk-body { padding:30px 34px 34px; }
        .lk-steps { list-style:none; padding:0; margin:0; }
        .lk-steps li { display:flex; gap:12px; align-items:flex-start; padding:11px 0;
                       border-bottom:1px solid #f1f3f5; font-size:14px; }
        .lk-steps li:last-child { border-bottom:0; }
        .lk-dot { width:22px; height:22px; border-radius:50%; flex-shrink:0; font-size:12px;
                  line-height:22px; text-align:center; font-weight:700; }
        .done    { background:#8fc445; color:#062e39; }
        .current { background:#062e39; color:#fff; }
        .todo    { background:#e9ecef; color:#adb5bd; }
    </style>
</head>
<body>

<div class="container">
    <div class="lk-card">
        <div class="lk-head">
            <div style="font-size:40px;line-height:1;margin-bottom:10px;">⏳</div>
            <h4 style="color:#fff;margin-bottom:6px;">{{ $heading }}</h4>
            <p style="color:rgba(255,255,255,.75);margin:0;font-size:14px;">
                {{ $employee->full_name }}
            </p>
        </div>

        <div class="lk-body">
            <p style="font-size:14.5px;line-height:1.8;color:#444;">{{ $message }}</p>

            <p style="font-size:13px;font-weight:700;color:#062e39;margin:22px 0 6px;">
                Where you are
            </p>
            <ul class="lk-steps">
                @foreach($steps as $s)
                    <li>
                        <span class="lk-dot {{ $s['state'] }}">
                            {{ $s['state'] === 'done' ? '✓' : $loop->iteration }}
                        </span>
                        <span style="color:{{ $s['state'] === 'todo' ? '#adb5bd' : '#333' }};
                                     font-weight:{{ $s['state'] === 'current' ? '700' : '400' }};">
                            {{ $s['label'] }}
                        </span>
                    </li>
                @endforeach
            </ul>

            <div style="background:#f8f9fa;border-radius:8px;padding:14px 18px;margin-top:22px;">
                <p style="margin:0;font-size:13.5px;color:#555;line-height:1.7;">
                    You do not need to do anything right now — we will email you as soon as there
                    is an update. If you have a question in the meantime, contact
                    <a href="mailto:info@hellotransport.com" style="color:#062e39;font-weight:600;">info@hellotransport.com</a>.
                </p>
            </div>

            <div class="text-center mt-4">
                <a href="{{ route('employee.logout') }}" class="btn"
                   style="background:#062e39;color:#fff;padding:10px 26px;border-radius:6px;font-weight:600;">
                    Sign Out
                </a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
