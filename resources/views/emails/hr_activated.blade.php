<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HR Account Active — Hello Transport</title>
</head>
<body style="margin:0;padding:0;background:#f4f6f9;font-family:'Segoe UI',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f9;padding:32px 0;">
  <tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.08);">

      <!-- Header -->
      <tr>
        <td style="background:linear-gradient(135deg,#062e39 0%,#0d5c70 100%);padding:32px 40px;text-align:center;">
          <h1 style="margin:0;color:#fff;font-size:26px;font-weight:800;letter-spacing:1px;text-transform:uppercase;">
            🚛 Hello Transport HR
          </h1>
          <p style="margin:6px 0 0;color:rgba(255,255,255,.7);font-size:13px;">Employee Portal</p>
        </td>
      </tr>

      <!-- Green success banner -->
      <tr>
        <td style="background:#0d9488;padding:16px 40px;text-align:center;">
          <p style="margin:0;color:#fff;font-size:16px;font-weight:700;">
            ✅ Your HR Account is Now Active!
          </p>
        </td>
      </tr>

      <!-- Body -->
      <tr>
        <td style="padding:36px 40px;">
          <h2 style="margin:0 0 10px;color:#062e39;font-size:20px;font-weight:700;">
            Hi {{ $employeeName }},
          </h2>
          <p style="margin:0 0 20px;color:#555;font-size:14px;line-height:1.7;">
            Your <strong>Hello Transport HR Portal</strong> account has been activated. You can now log in to view your payslips, submit leave requests, track attendance, and manage your employee profile.
          </p>

          <!-- Login Box -->
          <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
            <tr>
              <td style="background:#f0fdfa;border:1px solid #99f6e4;border-radius:8px;padding:20px 24px;">
                <p style="margin:0 0 6px;color:#134e4a;font-size:13px;font-weight:600;">🔗 Login URL</p>
                <p style="margin:0 0 16px;color:#555;font-size:13px;">
                  <a href="https://hellohragent.daydispatch.com/employee/login" style="color:#062e39;word-break:break-all;">
                    https://hellohragent.daydispatch.com/employee/login
                  </a>
                </p>
                <p style="margin:0 0 4px;color:#134e4a;font-size:13px;font-weight:600;">📧 Your Email</p>
                <p style="margin:0;color:#555;font-size:13px;">{{ $employeeEmail }}</p>
              </td>
            </tr>
          </table>

          <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
            <tr>
              <td align="center">
                <a href="https://hellohragent.daydispatch.com/employee/login"
                   style="display:inline-block;background:#0d9488;color:#fff;text-decoration:none;font-weight:700;font-size:14px;padding:13px 40px;border-radius:8px;letter-spacing:.5px;text-transform:uppercase;">
                  Login to HR Portal →
                </a>
              </td>
            </tr>
          </table>

          <!-- What you can do -->
          <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
              <td style="background:#f8f9fa;border-radius:8px;padding:16px 20px;">
                <p style="margin:0 0 10px;color:#062e39;font-size:13px;font-weight:700;">What you can do in the HR portal:</p>
                <p style="margin:3px 0;color:#555;font-size:13px;">📄 View and download your payslips</p>
                <p style="margin:3px 0;color:#555;font-size:13px;">📅 Submit leave requests</p>
                <p style="margin:3px 0;color:#555;font-size:13px;">🕒 Track your attendance</p>
                <p style="margin:3px 0;color:#555;font-size:13px;">📋 Manage your employee profile &amp; documents</p>
              </td>
            </tr>
          </table>
        </td>
      </tr>

      <!-- Divider -->
      <tr><td style="padding:0 40px;"><hr style="border:none;border-top:1px solid #e9ecef;margin:0;"></td></tr>

      <!-- Footer -->
      <tr>
        <td style="background:#f8f9fa;padding:20px 40px;text-align:center;border-top:1px solid #e9ecef;">
          <p style="margin:0 0 6px;color:#6c757d;font-size:12px;">
            If you did not register for a Hello Transport account, please contact us at
            <a href="mailto:info@hellotransport.com" style="color:#062e39;">info@hellotransport.com</a>
          </p>
          <p style="margin:0;color:#adb5bd;font-size:12px;">
            © {{ date('Y') }} Hello Transport HR. All Rights Reserved.<br>
            This email was sent to <strong>{{ $employeeEmail }}</strong>
          </p>
        </td>
      </tr>

    </table>
  </td></tr>
</table>
</body>
</html>
