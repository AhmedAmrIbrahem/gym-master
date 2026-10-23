<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مرحباً بك في Gym Master</title>
</head>
<body style="margin: 0; padding: 0; background-color: #0f172a; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #f8fafc; direction: rtl; text-align: right;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #0f172a; padding: 40px 15px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" style="max-width: 550px; background-color: #1e293b; border-radius: 16px; border: 1px solid #334155; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);">

                <!-- Header -->
                <tr>
                    <td style="background: linear-gradient(135deg, #2563eb, #1d4ed8); padding: 30px 20px; text-align: center;">
                        <h1 style="margin: 0; color: #ffffff; font-size: 26px; font-weight: 800; letter-spacing: 1px;">🏋️‍♂️ Gym Master</h1>
                        <p style="margin: 8px 0 0 0; color: #93c5fd; font-size: 14px;">منصتك الرياضية والشخصية المتكاملة</p>
                    </td>
                </tr>

                <!-- Body -->
                <tr>
                    <td style="padding: 32px 24px;">
                        <h2 style="margin: 0 0 16px 0; font-size: 20px; color: #ffffff; font-weight: 700;">أهلاً بك {{ $user->name }} 👋</h2>
                        <p style="margin: 0 0 24px 0; color: #94a3b8; font-size: 14px; line-height: 1.6;">
                            تم إنشاء حسابك بنجاح في تطبيق <strong style="color: #38bdf8;">Gym Master</strong>. يمكنك الآن البدء في تتبع تمارينك، وجباتك، وقياساتك البدنية.
                        </p>

                        <!-- Credentials Box -->
                        <div style="background-color: #0f172a; border: 1px solid #334155; border-radius: 12px; padding: 20px; margin-bottom: 28px;">
                            <h3 style="margin: 0 0 14px 0; color: #38bdf8; font-size: 15px; font-weight: 700;">🔑 بيانات تسجيل الدخول:</h3>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="font-size: 14px;">
                                <tr>
                                    <td style="padding: 6px 0; color: #94a3b8; width: 110px;"><strong>البريد الإلكتروني:</strong></td>
                                    <td style="padding: 6px 0; color: #ffffff; font-weight: 600;">{{ $user->email }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 6px 0; color: #94a3b8;"><strong>كلمة المرور:</strong></td>
                                    <td style="padding: 6px 0;">
                                        <span style="background-color: #1e293b; color: #10b981; border: 1px solid #059669; padding: 4px 10px; border-radius: 6px; font-family: monospace; font-weight: bold; font-size: 15px;">{{ $plainPassword }}</span>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- CTA Button -->
                        <div style="text-align: center; margin-bottom: 24px;">
                            <a href="{{ route('login') }}" target="_blank" style="display: inline-block; background-color: #2563eb; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 15px; padding: 12px 32px; border-radius: 10px; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);">
                                🚀 تسجيل الدخول إلى حسابك
                            </a>
                        </div>

                        <p style="margin: 0; color: #64748b; font-size: 12px; text-align: center; line-height: 1.5;">
                            نتمنى لك رحلة رياضية موفقة وتحقيق أفضل النتائج!<br>
                            إدارة الجيم
                        </p>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="background-color: #0f172a; padding: 16px; text-align: center; border-top: 1px solid #334155;">
                        <p style="margin: 0; color: #64748b; font-size: 11px;">
                            &copy; {{ date('Y') }} Gym Master. جميع الحقوق محفوظة.
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>
</body>
</html>
