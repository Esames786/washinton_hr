<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>You have been shortlisted</title>
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
                            Good news — your application has been <strong>shortlisted</strong>. We would
                            like to take you through to the next stage.
                        </p>

                        <p style="margin:0 0 20px;font-size:14.5px;line-height:1.75;">
                            We have created your HR portal account. To get started, set your password
                            using the button below, then sign in to complete your NDA and questionnaire.
                        </p>

                        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 22px;">
                            <tr>
                                <td style="background:#8fc445;border-radius:6px;">
                                    <a href="{{ $setPasswordUrl }}"
                                       style="display:inline-block;padding:13px 30px;color:#062e39;
                                              font-size:15px;font-weight:700;text-decoration:none;">
                                        Set Your Password
                                    </a>
                                </td>
                            </tr>
                        </table>

                        <p style="margin:0 0 20px;font-size:13px;color:#6c757d;line-height:1.7;">
                            This link expires in {{ $expiresInDays }} days. If it stops working, contact us
                            and we will send a new one. For security we never send passwords by email.
                        </p>

                        <hr style="border:0;border-top:1px solid #e9ecef;margin:24px 0;">

                        <p style="margin:0 0 10px;font-size:14px;font-weight:700;">What happens next</p>
                        <ol style="margin:0 0 20px;padding-left:20px;font-size:14px;line-height:1.9;color:#444;">
                            <li>Set your password and sign in to the HR portal</li>
                            <li>Read and sign your NDA</li>
                            <li>Complete the questionnaire</li>
                            <li>Interview and a short practical assessment</li>
                            <li>Submit your documents</li>
                            <li>Contract, then your CRM account and link</li>
                        </ol>

                        <p style="margin:0 0 6px;font-size:13px;color:#6c757d;">Your portal address:</p>
                        <p style="margin:0 0 20px;font-size:13px;">
                            <a href="{{ $portalUrl }}" style="color:#062e39;">{{ $portalUrl }}</a>
                        </p>

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
