<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تحديث كلمة المرور</title>
</head>
<body style="margin: 0; padding: 0; background-color: #0f172a; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #f8fafc; direction: rtl; text-align: right;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #0f172a; padding: 40px 15px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" style="max-width: 550px; background-color: #1e293b; border-radius: 16px; border: 1px solid #334155; overflow: hidden;">

                <td style="background: linear-gradient(135deg, #d97706, #b45309); padding: 25px; text-align: center;">
                    <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: 800;">🔐 Gym Master</h1>
                    <p style="margin: 6px 0 0 0; color: #fef3c7; font-size: 13px;">تم تحديث كلمة المرور الخاصة بك</p>
                </td>

                <tr>
                    <td style="padding: 32px 24px;">
                        <h2 style="margin: 0 0 16px 0; font-size: 18px; color: #ffffff;">أهلاً بك {{ $user->name }} 👋</h2>
                        <p style="margin: 0 0 20px 0; color: #94a3b8; font-size: 14px;">تم تحديث كلمة المرور الخاصة بحسابك بنجاح من قبل إدارة الجيم.</p>

                        <div style="background-color: #0f172a; border: 1px solid #334155; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
                            <p style="margin: 0 0 8px 0; color: #94a3b8; font-size: 13px;">كلمة المرور الجديدة:</p>
                            <span style="background-color: #1e293b; color: #f59e0b; border: 1px solid #d97706; padding: 6px 12px; border-radius: 6px; font-family: monospace; font-weight: bold; font-size: 16px;">{{ $newPassword }}</span>
                        </div>

                        <div style="text-align: center;">
                            <a href="{{ route('login') }}" target="_blank" style="display: inline-block; background-color: #d97706; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 14px; padding: 12px 28px; border-radius: 10px;">
                                تسجيل الدخول الآن
                            </a>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="background-color: #0f172a; padding: 14px; text-align: center; border-top: 1px solid #334155;">
                        <p style="margin: 0; color: #64748b; font-size: 11px;">&copy; {{ date('Y') }} Gym Master</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
