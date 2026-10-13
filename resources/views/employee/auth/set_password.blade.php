<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Your Password</title>
    <link rel="icon" type="image/svg+xml"
          href="{{ asset(\App\Support\Brand::current()['logo'] ?? 'assets/images/logo/hello_transport.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/remixicon.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/lib/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <style>
        body { background:#f4f6f8; min-height:100vh; display:flex; align-items:center; }
        .sp-card { max-width:460px; margin:0 auto; background:#fff; border-radius:12px;
                   box-shadow:0 4px 24px rgba(0,0,0,.08); overflow:hidden; }
        .sp-head { background:#062e39; padding:26px 30px; color:#fff; }
        .sp-body { padding:28px 30px 32px; }
        .sp-rules { font-size:12.5px; color:#6c757d; margin-top:6px; }
        .btn-sp { background:#8fc445; color:#062e39; font-weight:700; border:0;
                  border-radius:6px; padding:12px; width:100%; font-size:15px; }
        .btn-sp:hover { background:#7fb23a; }
    </style>
</head>
<body>

<div class="container">
    <div class="sp-card">
        <div class="sp-head">
            <h4 class="mb-1" style="color:#fff;">Set Your Password</h4>
            <p class="mb-0" style="color:rgba(255,255,255,.7);font-size:13.5px;">
                Welcome, {{ $employee->full_name }}
            </p>
        </div>

        <div class="sp-body">
            @if($errors->any())
                <div class="alert alert-danger py-2" style="font-size:13.5px;">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <p style="font-size:14px;color:#555;line-height:1.7;">
                Choose a password for your HR portal account. You will use it with
                <strong>{{ $employee->email }}</strong> to sign in.
            </p>

            <form method="POST" action="{{ route('employee.set_password.store') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="mb-3">
                    <label class="form-label" style="font-weight:600;font-size:13.5px;">New Password</label>
                    <input type="password" name="password" class="form-control" required
                           minlength="8" placeholder="At least 8 characters" autocomplete="new-password">
                    <div class="sp-rules">Use at least 8 characters.</div>
                </div>

                <div class="mb-4">
                    <label class="form-label" style="font-weight:600;font-size:13.5px;">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required
                           minlength="8" placeholder="Repeat your password" autocomplete="new-password">
                </div>

                <button type="submit" class="btn-sp">Set Password &amp; Continue</button>
            </form>

            <p class="text-center mt-3 mb-0" style="font-size:12.5px;color:#8a9199;">
                This link can only be used once.
            </p>
        </div>
    </div>
</div>

</body>
</html>
