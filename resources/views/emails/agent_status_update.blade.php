<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $heading }}</title>
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
                        <h1 style="margin:0;color:#ffffff;font-size:20px;font-weight:700;">{{ $brand }}</h1>
                        <p style="margin:6px 0 0;color:#8fc445;font-size:13px;font-weight:600;">Onboarding</p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:30px;">
                        <p style="margin:0 0 16px;font-size:16px;">Hello {{ $agentName }},</p>

                        <h2 style="margin:0 0 14px;font-size:18px;color:#062e39;">{{ $heading }}</h2>

                        <p style="margin:0 0 20px;font-size:14.5px;line-height:1.75;">{{ $body }}</p>

                        @if($actionUrl && $actionLabel)
                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 22px;">
                                <tr>
                                    <td style="background:#8fc445;border-radius:6px;">
                                        <a href="{{ $actionUrl }}"
                                           style="display:inline-block;padding:13px 30px;color:#062e39;
                                                  font-size:15px;font-weight:700;text-decoration:none;">
                                            {{ $actionLabel }}
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        @endif

                        @if($note)
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                   style="margin:0 0 20px;background:#f8f9fa;border-left:3px solid #062e39;border-radius:4px;">
                                <tr>
                                    <td style="padding:14px 18px;font-size:13.5px;line-height:1.7;color:#555;">
                                        {{ $note }}
                                    </td>
                                </tr>
                            </table>
                        @endif

                        <p style="margin:0;font-size:14px;line-height:1.7;">
                            Any questions, reply to this email or contact
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
