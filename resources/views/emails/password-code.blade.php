<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>رمز التحقق لتغيير كلمة المرور</title>
</head>
<body style="margin:0;padding:0;background:#edf2f1;font-family:Tahoma,Arial,sans-serif;direction:rtl;text-align:right;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#edf2f1;">
<tr><td align="center" style="padding:28px 12px;">
  <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #d5dfdd;box-shadow:0 4px 14px rgba(0,0,0,0.04);">
    <tr>
      <td style="background:#0f5c63;color:#ffffff;padding:20px 24px;font-size:19px;font-weight:bold;text-align:center;">
        فواتير المشتريات — رمز التحقق
      </td>
    </tr>
    <tr>
      <td style="padding:28px 24px;color:#14262b;font-size:15px;line-height:1.9;">
        مرحباً <b>{{ $user->name }}</b>،<br>
        تلقينا طلباً لتغيير كلمة المرور الخاصة بحسابك. استخدم رمز التحقق التالي لإتمام العملية:

        <div style="text-align:center;margin:24px 0;">
          <div style="display:inline-block;background:#dcebe9;border:2px dashed #0f5c63;border-radius:10px;padding:14px 28px;font-size:32px;font-weight:bold;letter-spacing:8px;color:#0f5c63;font-family:monospace;" dir="ltr">
            {{ $code }}
          </div>
        </div>

        <p style="margin:16px 0 0;color:#5d7479;font-size:13px;text-align:center;">
          ⏱️ هذا الرمز صالح لمدة <b>15 دقيقة فقط</b> للاستخدام مرة واحدة.
        </p>

        <hr style="border:0;border-top:1px solid #edf2f1;margin:24px 0;">

        <p style="margin:0;color:#c93a3a;font-size:12px;line-height:1.7;">
          🔒 إذا لم تكن أنت من طلب هذا الرمز، يرجى تجاهل هذه الرسالة فلن يتم تغيير كلمة المرور بدون إدخال هذا الرمز.
        </p>
      </td>
    </tr>
  </table>
</td></tr>
</table>
</body>
</html>
