<!DOCTYPE html>
<html lang="ar">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>{{ $ar('تقرير فواتير المشتريات — Rite Group') }}</title>
  <style>
    @if(!empty($tajawalRegular) && file_exists(str_replace('/', DIRECTORY_SEPARATOR, $tajawalRegular)))
    @font-face {
      font-family: 'Tajawal';
      font-style: normal;
      font-weight: normal;
      src: url('{{ $tajawalRegular }}') format('truetype');
    }
    @endif
    @if(!empty($tajawalBold) && file_exists(str_replace('/', DIRECTORY_SEPARATOR, $tajawalBold)))
    @font-face {
      font-family: 'Tajawal';
      font-style: normal;
      font-weight: bold;
      src: url('{{ $tajawalBold }}') format('truetype');
    }
    @endif

    @page {
      margin: 16px 20px;
      size: A4 landscape;
    }
    body {
      font-family: 'Tajawal', 'DejaVu Sans', 'Arial', sans-serif;
      direction: ltr; /* ArPHP reshapes and orders Arabic glyphs for standard LTR rendering */
      text-align: right;
      font-size: 10px;
      color: #1c2826;
      background: #ffffff;
      margin: 0;
      padding: 0;
    }
    .top-header {
      width: 100%;
      margin-bottom: 12px;
      border-bottom: 2px solid #0f5c63;
      padding-bottom: 8px;
    }
    .top-header table {
      width: 100%;
      border-collapse: collapse;
    }
    .brand-logo {
      height: 42px;
      width: auto;
      vertical-align: middle;
      margin-left: 8px;
    }
    .brand-title {
      font-size: 18px;
      font-weight: bold;
      color: #0f5c63;
      vertical-align: middle;
    }
    .meta-text {
      font-size: 9.5px;
      color: #5c706d;
      line-height: 1.5;
    }

    /* صناديق الملخص المالي الإحصائي مطابقة لتصميم الموقع */
    .kpi-row {
      width: 100%;
      margin-bottom: 8px;
      border-collapse: separate;
      border-spacing: 6px 0;
    }
    .kpi-box {
      background: #ffffff;
      border: 1px solid #d5dedd;
      border-radius: 6px;
      padding: 7px 10px;
      vertical-align: top;
      text-align: right;
    }
    .kpi-box.pr {
      background: #e6f0f1;
      border-color: #0f5c63;
    }
    .kpi-box.wa {
      background: #fffaf0;
      border-color: #dd6b20;
    }
    .kpi-box.ok {
      background: #f0fff4;
      border-color: #38a169;
    }
    .kpi-title {
      font-size: 9px;
      color: #5c706d;
      margin-bottom: 3px;
    }
    .kpi-val {
      font-size: 13.5px;
      font-weight: bold;
      color: #1c2826;
    }
    .kpi-val.pr {
      color: #0f5c63;
      font-size: 14.5px;
    }
    .kpi-val.ok {
      color: #276749;
    }
    .kpi-val.wa {
      color: #c05621;
    }

    /* أيقونة الريال السعودي sar.png */
    .sar {
      height: 11px;
      width: auto;
      vertical-align: -1px;
      margin-right: 2px;
    }
    .sar-lg {
      height: 13px;
      width: auto;
      vertical-align: -2px;
      margin-right: 3px;
    }

    /* جدول فواتير المشتريات */
    .table-container {
      width: 100%;
      margin-top: 10px;
    }
    .section-head {
      font-size: 12px;
      font-weight: bold;
      color: #0f5c63;
      margin-bottom: 6px;
      text-align: right;
    }
    table.data-table {
      width: 100%;
      border-collapse: collapse;
      border: 1px solid #d5dedd;
      font-size: 9.5px;
    }
    table.data-table th {
      background: #0f5c63;
      color: #ffffff;
      font-weight: bold;
      padding: 6px 4px;
      border: 1px solid #0f5c63;
      font-size: 9.5px;
      white-space: nowrap;
    }
    table.data-table td {
      border: 1px solid #e2e8f0;
      padding: 5px 4px;
      vertical-align: middle;
      font-size: 9px;
    }
    table.data-table tr:nth-child(even) td {
      background: #f8fafc;
    }

    /* شارات حالة السداد */
    .badge {
      display: inline-block;
      padding: 2px 5px;
      border-radius: 4px;
      font-size: 8px;
      font-weight: bold;
      text-align: center;
      white-space: nowrap;
    }
    .badge-op {
      background: #c6f6d5;
      color: #22543d;
    }
    .badge-cl {
      background: #fed7d7;
      color: #742a2a;
    }
    .badge-neutral {
      background: #edf2f7;
      color: #718096;
    }

    .footer {
      margin-top: 12px;
      padding-top: 6px;
      border-top: 1px solid #e2e8f0;
      font-size: 8.5px;
      color: #718096;
      text-align: center;
    }
  </style>
</head>
<body>

  {{-- الترويسة العليا (الشعار والعنوان على اليمين، التواريخ والبيانات على اليسار) --}}
  <div class="top-header">
    <table>
      <tr>
        <td style="width:35%;text-align:left;vertical-align:middle" class="meta-text">
          {{ now()->format('Y-m-d H:i') }} :{{ $ar('تاريخ الاستخراج') }}<br>
          Rite Group Invoices Management
        </td>
        <td style="width:65%;text-align:right;vertical-align:middle">
          <span class="brand-title">{{ $ar('فواتير المشتريات — تقرير الفواتير') }}</span>
          @if(!empty($logoBase64))
            <img class="brand-logo" src="{{ $logoBase64 }}" alt="Rite Group">
          @endif
        </td>
      </tr>
    </table>
  </div>

  {{-- بطاقات الملخص المالي الإحصائي (الصف الأول) --}}
  <table class="kpi-row">
    <tr>
      {{-- على اليسار: الإجمالي الكلي شامل الضريبة --}}
      <td class="kpi-box pr" style="width:33.33%">
        <div class="kpi-title" style="color:#0f5c63;font-weight:bold">{{ $ar('الإجمالي الكلي شامل الضريبة') }}</div>
        <div class="kpi-val pr">
          @if(!empty($sarBase64))
            <img class="sar-lg" src="{{ $sarBase64 }}" alt="ر.س">
          @endif
          {{ number_format($metrics['grand_total'], 2) }}
        </div>
      </td>

      {{-- في المنتصف: ضريبة القيمة المضافة 15% --}}
      <td class="kpi-box" style="width:33.33%">
        <div class="kpi-title">{{ $ar('ضريبة القيمة المضافة 15%') }}</div>
        <div class="kpi-val">
          @if(!empty($sarBase64))
            <img class="sar-lg" src="{{ $sarBase64 }}" alt="ر.س">
          @endif
          {{ number_format($metrics['total_tax'], 2) }}
        </div>
      </td>

      {{-- على اليمين: المبلغ الخاضع للضريبة --}}
      <td class="kpi-box" style="width:33.33%">
        <div class="kpi-title">{{ $ar('المبلغ الخاضع للضريبة') }}</div>
        <div class="kpi-val">
          @if(!empty($sarBase64))
            <img class="sar-lg" src="{{ $sarBase64 }}" alt="ر.س">
          @endif
          {{ number_format($metrics['total_amount'], 2) }}
        </div>
      </td>
    </tr>
  </table>

  {{-- بطاقات الملخص المالي الإحصائي (الصف الثاني) --}}
  <table class="kpi-row">
    <tr>
      {{-- على اليسار: باقي المستحق غير المسدد --}}
      <td class="kpi-box wa" style="width:25%">
        <div class="kpi-title">{{ $ar('باقي المستحق غير المسدد') }}</div>
        <div class="kpi-val wa">
          @if(!empty($sarBase64))
            <img class="sar" src="{{ $sarBase64 }}" alt="ر.س">
          @endif
          {{ number_format($metrics['total_unpaid_due'], 2) }}
        </div>
      </td>

      {{-- المستحق المسدد --}}
      <td class="kpi-box ok" style="width:25%">
        <div class="kpi-title">{{ $ar('المستحق (المسدد / المستلم)') }}</div>
        <div class="kpi-val ok">
          @if(!empty($sarBase64))
            <img class="sar" src="{{ $sarBase64 }}" alt="ر.س">
          @endif
          {{ number_format($metrics['total_paid_due'], 2) }}
        </div>
      </td>

      {{-- إجمالي المستحق 5% --}}
      <td class="kpi-box wa" style="width:25%">
        <div class="kpi-title">{{ $ar('إجمالي المستحق 5%') }}</div>
        <div class="kpi-val wa">
          @if(!empty($sarBase64))
            <img class="sar" src="{{ $sarBase64 }}" alt="ر.س">
          @endif
          {{ number_format($metrics['total_due'], 2) }}
        </div>
      </td>

      {{-- على اليمين: إجمالي عدد الفواتير --}}
      <td class="kpi-box" style="width:25%">
        <div class="kpi-title">{{ $ar('إجمالي عدد الفواتير') }}</div>
        <div class="kpi-val" style="color:#0f5c63">
          {{ $metrics['count_invoices'] }} <small style="font-size:9.5px;color:#5c706d;font-weight:normal">{{ $ar('فاتورة') }}</small>
        </div>
      </td>
    </tr>
  </table>

  {{-- جدول فواتير المشتريات --}}
  {{-- الأعمدة مرتبة من اليسار لليمين برمجياً لكي تظهر على الصفحة من اليمين لليسار طبيعياً --}}
  <div class="table-container">
    <div class="section-head">📋 {{ $ar('جدول فواتير المشتريات') }} ({{ $invoices->count() }} {{ $ar('فاتورة') }})</div>
    <table class="data-table">
      <thead>
        <tr>
          <th style="width:14%;text-align:right">{{ $ar('البيان والتفاصيل') }}</th>
          <th style="width:7%;text-align:center">{{ $ar('حالة السداد') }}</th>
          <th style="width:8%;text-align:center">{{ $ar('الاستحقاق') }}</th>
          <th style="width:8%;text-align:right">{{ $ar('مستحق 5%') }}</th>
          <th style="width:10%;text-align:right">{{ $ar('الإجمالي') }}</th>
          <th style="width:8%;text-align:right">{{ $ar('الضريبة 15%') }}</th>
          <th style="width:10%;text-align:right">{{ $ar('المبلغ الخاضع') }}</th>
          <th style="width:8%;text-align:center">{{ $ar('التاريخ') }}</th>
          <th style="width:9%;text-align:right">{{ $ar('الشهر') }}</th>
          <th style="width:12%;text-align:right">{{ $ar('المشروع') }}</th>
          <th style="width:11%;text-align:center">{{ $ar('رقم الفاتورة') }}</th>
          <th style="width:4%;text-align:center">#</th>
        </tr>
      </thead>
      <tbody>
        @forelse($invoices as $index => $inv)
        <tr>
          {{-- 1. البيان والتفاصيل --}}
          <td style="text-align:right">{{ $ar($inv->description ?: '—') }}</td>

          {{-- 2. حالة السداد --}}
          <td style="text-align:center">
            @if($inv->has_due)
              <span class="badge {{ $inv->due_paid ? 'badge-op' : 'badge-cl' }}">
                {{ $ar($inv->due_paid ? '✓ مسدد' : 'غير مسدد') }}
              </span>
            @else
              <span class="badge badge-neutral">
                {{ $ar('غير مستحق') }}
              </span>
            @endif
          </td>

          {{-- 3. تاريخ الاستحقاق --}}
          <td style="text-align:center">
            {{ $inv->has_due && $inv->due_date ? $inv->due_date->format('Y-m-d') : '—' }}
          </td>

          {{-- 4. مستحق 5% --}}
          <td style="text-align:right;white-space:nowrap">
            @if($inv->has_due)
              @if(!empty($sarBase64))
                <img class="sar" src="{{ $sarBase64 }}" alt="ر.س">
              @endif
              {{ number_format($inv->due_amount, 2) }}
            @else
              <span style="color:#a0aec0">—</span>
            @endif
          </td>

          {{-- 5. الإجمالي شامل الضريبة --}}
          <td style="text-align:right;color:#0f5c63;font-weight:bold;white-space:nowrap">
            @if(!empty($sarBase64))
              <img class="sar" src="{{ $sarBase64 }}" alt="ر.س">
            @endif
            {{ number_format($inv->total, 2) }}
          </td>

          {{-- 6. الضريبة 15% --}}
          <td style="text-align:right;color:#5c706d;white-space:nowrap">
            @if(!empty($sarBase64))
              <img class="sar" src="{{ $sarBase64 }}" alt="ر.س">
            @endif
            {{ number_format($inv->tax, 2) }}
          </td>

          {{-- 7. المبلغ الخاضع --}}
          <td style="text-align:right;white-space:nowrap">
            @if(!empty($sarBase64))
              <img class="sar" src="{{ $sarBase64 }}" alt="ر.س">
            @endif
            <b>{{ number_format($inv->amount, 2) }}</b>
          </td>

          {{-- 8. تاريخ الفاتورة --}}
          <td style="text-align:center">{{ $inv->invoice_date?->format('Y-m-d') }}</td>

          {{-- 9. الشهر --}}
          <td style="text-align:right">{{ $ar($inv->month->label ?? '—') }}</td>

          {{-- 10. المشروع --}}
          <td style="text-align:right"><b>{{ $ar($inv->month->project->name ?? '—') }}</b></td>

          {{-- 11. رقم الفاتورة --}}
          <td style="text-align:center"><b>{{ $inv->number }}</b></td>

          {{-- 12. التسلسل --}}
          <td style="text-align:center;font-weight:bold">{{ $index + 1 }}</td>
        </tr>
        @empty
        <tr>
          <td colspan="12" style="text-align:center;padding:16px;color:#718096">
            {{ $ar('لا توجد فواتير مسجلة') }}
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="footer">
    {{ $ar('تم استخراج هذا التقرير آلياً عبر نظام إدارة الفواتير — Rite Group') }} © {{ date('Y') }}
  </div>

</body>
</html>
