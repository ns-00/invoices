<table>
  {{-- الترويسة الرئيسية --}}
  <thead>
    <tr>
      <th colspan="12" style="background-color:#0f5c63;color:#ffffff;font-size:15pt;font-weight:bold;text-align:center;height:42px;">
        فواتير المشتريات — تقرير الفواتير الشامل (Rite Group)
      </th>
    </tr>
    <tr>
      <th colspan="12" style="background-color:#e6f0f1;color:#0f5c63;font-size:10pt;text-align:left;height:24px;">
        تاريخ استخراج التقرير: {{ now()->format('Y-m-d H:i') }} | إجمالي عدد الفواتير: {{ $metrics['count_invoices'] }}
      </th>
    </tr>

    {{-- بطاقات الملخص المالي الإحصائي (6 كروت متناسقة على 12 عمود بنفس تصميم الموقع والـ PDF) --}}
    <tr>
      <th colspan="2" style="background-color:#edf2f7;color:#4a5568;font-weight:bold;text-align:center;border:1px solid #cbd5e0;">المبلغ الخاضع للضريبة</th>
      <th colspan="2" style="background-color:#edf2f7;color:#4a5568;font-weight:bold;text-align:center;border:1px solid #cbd5e0;">ضريبة القيمة المضافة 15%</th>
      <th colspan="2" style="background-color:#0f5c63;color:#ffffff;font-weight:bold;text-align:center;border:1px solid #0f5c63;">الإجمالي الكلي شامل الضريبة</th>
      <th colspan="2" style="background-color:#fffaf0;color:#c05621;font-weight:bold;text-align:center;border:1px solid #cbd5e0;">إجمالي المستحق 5%</th>
      <th colspan="2" style="background-color:#f0fff4;color:#276749;font-weight:bold;text-align:center;border:1px solid #cbd5e0;">المستحق المسدد</th>
      <th colspan="2" style="background-color:#fffaf0;color:#c05621;font-weight:bold;text-align:center;border:1px solid #cbd5e0;">باقي المستحق غير المسدد</th>
    </tr>
    <tr>
      <th colspan="2" style="font-size:11pt;font-weight:bold;text-align:center;border:1px solid #cbd5e0;">{{ number_format($metrics['total_amount'], 2) }} ر.س</th>
      <th colspan="2" style="font-size:11pt;font-weight:bold;text-align:center;border:1px solid #cbd5e0;">{{ number_format($metrics['total_tax'], 2) }} ر.س</th>
      <th colspan="2" style="font-size:11pt;font-weight:bold;text-align:center;background-color:#e6f0f1;color:#0f5c63;border:1px solid #0f5c63;">{{ number_format($metrics['grand_total'], 2) }} ر.س</th>
      <th colspan="2" style="font-size:11pt;font-weight:bold;text-align:center;color:#c05621;border:1px solid #cbd5e0;">{{ number_format($metrics['total_due'], 2) }} ر.س</th>
      <th colspan="2" style="font-size:11pt;font-weight:bold;text-align:center;color:#276749;border:1px solid #cbd5e0;">{{ number_format($metrics['total_paid_due'], 2) }} ر.س</th>
      <th colspan="2" style="font-size:11pt;font-weight:bold;text-align:center;color:#c05621;border:1px solid #cbd5e0;">{{ number_format($metrics['total_unpaid_due'], 2) }} ر.س</th>
    </tr>

    <tr>
      <th colspan="12" style="height:14px;"></th>
    </tr>

    {{-- رؤوس جدول الفواتير (12 عمود مطابقة لجدول الموقع والـ PDF) --}}
    <tr style="height:32px;">
      <th style="background-color:#0f5c63;color:#ffffff;font-weight:bold;text-align:center;border:1px solid #0f5c63;">#</th>
      <th style="background-color:#0f5c63;color:#ffffff;font-weight:bold;text-align:center;border:1px solid #0f5c63;">رقم الفاتورة</th>
      <th style="background-color:#0f5c63;color:#ffffff;font-weight:bold;text-align:center;border:1px solid #0f5c63;">المشروع</th>
      <th style="background-color:#0f5c63;color:#ffffff;font-weight:bold;text-align:center;border:1px solid #0f5c63;">الشهر</th>
      <th style="background-color:#0f5c63;color:#ffffff;font-weight:bold;text-align:center;border:1px solid #0f5c63;">تاريخ الفاتورة</th>
      <th style="background-color:#0f5c63;color:#ffffff;font-weight:bold;text-align:center;border:1px solid #0f5c63;">المبلغ الخاضع</th>
      <th style="background-color:#0f5c63;color:#ffffff;font-weight:bold;text-align:center;border:1px solid #0f5c63;">الضريبة 15%</th>
      <th style="background-color:#0f5c63;color:#ffffff;font-weight:bold;text-align:center;border:1px solid #0f5c63;">الإجمالي شامل الضريبة</th>
      <th style="background-color:#0f5c63;color:#ffffff;font-weight:bold;text-align:center;border:1px solid #0f5c63;">مستحق 5%</th>
      <th style="background-color:#0f5c63;color:#ffffff;font-weight:bold;text-align:center;border:1px solid #0f5c63;">تاريخ الاستحقاق</th>
      <th style="background-color:#0f5c63;color:#ffffff;font-weight:bold;text-align:center;border:1px solid #0f5c63;">حالة السداد</th>
      <th style="background-color:#0f5c63;color:#ffffff;font-weight:bold;text-align:center;border:1px solid #0f5c63;">البيان والتفاصيل</th>
    </tr>
  </thead>
  <tbody>
    @forelse($invoices as $index => $inv)
      <tr style="{{ $loop->even ? 'background-color:#f8fafc;' : 'background-color:#ffffff;' }}">
        <td style="text-align:center;border:1px solid #e2e8f0;">{{ $index + 1 }}</td>
        <td style="font-weight:bold;text-align:center;border:1px solid #e2e8f0;">{{ $inv->number }}</td>
        <td style="border:1px solid #e2e8f0;">{{ $inv->month->project->name ?? '—' }}</td>
        <td style="text-align:center;border:1px solid #e2e8f0;">{{ $inv->month->label ?? '—' }}</td>
        <td style="text-align:center;border:1px solid #e2e8f0;">{{ $inv->invoice_date?->format('Y-m-d') }}</td>
        <td style="text-align:right;font-weight:bold;border:1px solid #e2e8f0;">{{ number_format($inv->amount, 2) }} ر.س</td>
        <td style="text-align:right;color:#5c706d;border:1px solid #e2e8f0;">{{ number_format($inv->tax, 2) }} ر.س</td>
        <td style="text-align:right;font-weight:bold;color:#0f5c63;border:1px solid #e2e8f0;">{{ number_format($inv->total, 2) }} ر.س</td>
        <td style="text-align:right;border:1px solid #e2e8f0;">{{ $inv->has_due ? number_format($inv->due_amount, 2) . ' ر.س' : '—' }}</td>
        <td style="text-align:center;border:1px solid #e2e8f0;">{{ $inv->has_due && $inv->due_date ? $inv->due_date->format('Y-m-d') : '—' }}</td>
        <td style="text-align:center;border:1px solid #e2e8f0;font-weight:bold;{{ $inv->has_due ? ($inv->due_paid ? 'color:#276749;background-color:#f0fff4;' : 'color:#9b2c2c;background-color:#fff5f5;') : 'color:#718096;background-color:#edf2f7;' }}">
          {{ $inv->has_due ? ($inv->due_paid ? '✓ مسدد' : 'غير مسدد') : 'غير مستحق' }}
        </td>
        <td style="border:1px solid #e2e8f0;">{{ $inv->description ?: '—' }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="12" style="text-align:center;padding:15px;color:#718096;border:1px solid #e2e8f0;">لا توجد فواتير مسجلة</td>
      </tr>
    @endforelse
  </tbody>
</table>
