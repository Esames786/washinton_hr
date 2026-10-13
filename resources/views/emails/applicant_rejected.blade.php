<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your application</title>
</head>
<body style="margin:0;padding:0;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;color:#333;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8;padding:28px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                   style="max-width:580px;background:#ffffff;border-radius:10px;overflow:hidden;
                          box-shadow:0 2px 10px rgba(0,0,0,.06);">

                <tr>
                    <td style="background:#062e39;padding:26px 30px;">
                        <h1 style="margin:0;color:#ffffff;font-size:20px;font-weight:700;">
                            {{ $brand }}
                        </h1>
                        <p style="margin:6px 0 0;color:#8fc445;font-size:13px;font-weight:600;">
                            Recruitment
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:30px;">
                        <p style="margin:0 0 16px;font-size:16px;">Hello {{ $applicantName }},</p>

                        <p style="margin:0 0 16px;font-size:14.5px;line-height:1.75;">
                            Thank you for taking the time to apply to join {{ $brand }}, and for your
                            interest in working with us.
                        </p>

                        <p style="margin:0 0 16px;font-size:14.5px;line-height:1.75;">
                            After reviewing your application, we are not able to take it forward on this
                            occasion.
                        </p>

                        @if(!empty($reason))
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                   style="margin:0 0 20px;background:#f8f9fa;border-left:3px solid #062e39;border-radius:4px;">
                                <tr>
                                    <td style="padding:14px 18px;">
                                        <p style="margin:0 0 6px;font-size:13px;font-weight:700;color:#062e39;">
                                            Feedback
                                        </p>
                                        <p style="margin:0;font-size:14px;line-height:1.7;color:#444;">
                                            {{ $reason }}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        @endif

                        <p style="margin:0 0 16px;font-size:14.5px;line-height:1.75;">
                            We do keep applications on file, and our requirements change as the business
                            grows — you are welcome to apply again in future.
                        </p>

                        <p style="margin:0;font-size:14px;line-height:1.7;">
                            If you would like to discuss this, contact
                            <a href="mailto:{{ $supportEmail }}" style="color:#062e39;">{{ $supportEmail }}</a>.
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="background:#f8f9fa;padding:18px 30px;text-align:center;">
                        <p style="margin:0;font-size:12px;color:#8a9199;">
                            &copy; {{ date('Y') }} {{ $brand }}. All rights reserved.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
