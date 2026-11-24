<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head>
<body style="margin:0;padding:0;background:#edf2f1;font-family:Tahoma,Arial,sans-serif;direction:rtl;text-align:right;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#edf2f1;">
<tr><td align="center" style="padding:24px 12px;">
  <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #d5dfdd;">
    <tr><td style="background:#0f5c63;color:#ffffff;padding:18px 24px;font-size:19px;font-weight:bold;">
      تذكير بمبلغ مستحق
    </td></tr>
    <tr><td style="padding:24px;color:#14262b;font-size:15px;line-height:1.9;">
      مرحباً {{ $user->name }}،<br>
      لديك <b>{{ $invoices->count() }}</b> من الفواتير يقترب موعد استحقاق مبلغ الـ 5% الخاص بها:

      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:16px;border-collapse:collapse;font-size:14px;">
        <tr style="background:#0f5c63;color:#ffffff;">
          <th style="padding:9px;text-align:right;">المشروع / الشهر</th>
          <th style="padding:9px;text-align:right;">رقم الفاتورة</th>
          <th style="padding:9px;text-align:right;">تاريخ الاستحقاق</th>
          <th style="padding:9px;text-align:right;">المبلغ المستحق (ر.س)</th>
        </tr>
        @foreach ($invoices as $inv)
        <tr style="border-bottom:1px solid #d5dfdd;">
          <td style="padding:9px;">{{ $inv->month->project->name }}<br><span style="color:#5d7479;font-size:12px;">{{ $inv->month->label }}</span></td>
          <td style="padding:9px;">{{ $inv->number }}</td>
          <td style="padding:9px;">
            @if ($inv->due_date->isToday()) <b style="color:#c93a3a;">اليوم</b>
            @elseif ($inv->due_date->isTomorrow()) غداً
            @else {{ $inv->due_date->format('Y-m-d') }}
            @endif
          </td>
          <td style="padding:9px;"><b>{{ number_format($inv->due_amount, 2) }}</b></td>
        </tr>
        @endforeach
        <tr style="background:#fcefd2;">
          <td colspan="3" style="padding:10px;font-weight:bold;">إجمالي المستحق</td>
          <td style="padding:10px;font-weight:bold;">{{ number_format($total, 2) }} ر.س</td>
        </tr>
      </table>

      <p style="margin:22px 0 0;">
        <a href="{{ $url }}" style="display:inline-block;background:#0f5c63;color:#ffffff;text-decoration:none;padding:10px 22px;border-radius:8px;font-weight:bold;">عرض الفواتير والمشاريع</a>
      </p>
      <p style="margin:18px 0 0;color:#5d7479;font-size:12px;">بعد استلام المبلغ، فعّل خيار «تم استحقاق المبلغ» في النظام ليتوقف التذكير ويُخصم من باقي المستحق.</p>
    </td></tr>
  </table>
</td></tr>
</table>
</body>
</html>
