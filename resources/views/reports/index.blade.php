@extends('layouts.app')
@section('title', 'تقارير فواتير المشتريات — Rite Group')

@section('content')
@php
  $sar = asset('images/sar.png');
@endphp

{{-- ترويسة خاصة بالطباعة الورقية مطابقة لتقرير الـ PDF والشعار بالجهة اليمنى تماماً --}}
<div class="print-header">
  <table style="width:100%;border-collapse:collapse;margin-bottom:12px;border-bottom:2px solid #0f5c63;padding-bottom:8px">
    <tr>
      {{-- على اليمين في نظام RTL: الشعار وعنوان التقرير تماماً كما في الـ PDF --}}
      <td style="width:65%;text-align:right;vertical-align:middle">
        <img src="{{ asset('images/right.png') }}" alt="Rite Group" style="height:42px;width:auto;vertical-align:middle;margin-left:8px">
        <span style="font-size:18px;font-weight:bold;color:#0f5c63;vertical-align:middle">فواتير المشتريات — تقرير الفواتير</span>
      </td>
      {{-- على اليسار في نظام RTL: تاريخ الاستخراج وبيانات النظام --}}
      <td style="width:35%;text-align:left;vertical-align:middle;font-size:10px;color:#5c706d;direction:ltr">
        تاريخ الاستخراج: {{ now()->format('Y-m-d H:i') }}<br>
        Rite Group Invoices Management
      </td>
    </tr>
  </table>
</div>

<div class="hd" style="flex-wrap:wrap;gap:12px">
  <div>
    <h2>تقرير فواتير المشتريات</h2>
    <div class="mu">تقرير تفصيلي شامل للفواتير مع خيارات التصفية والتصدير والطباعة</div>
  </div>
  <div style="display:flex;gap:8px;flex-wrap:wrap">
    <a class="btn pr sm" href="{{ route('reports.export.excel', request()->query()) }}">📊 تصدير Excel</a>
    <a class="btn sm" href="{{ route('reports.export.pdf', request()->query()) }}" target="_blank">📄 تصدير PDF</a>
    <button class="btn sm" onclick="window.print()">🖨️ طباعة التقرير</button>
  </div>
</div>

{{-- شريط التصفية والبحث --}}
<div class="card report-filters" style="margin-bottom:20px;padding:16px">
  <form method="GET" action="{{ route('reports.index') }}" class="fg" style="grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:12px;align-items:end">
    <label>المشروع
      <select name="project_id" onchange="this.form.submit()">
        <option value="">-- كل المشاريع --</option>
        @foreach($projects as $p)
          <option value="{{ $p->id }}" @selected(request('project_id') == $p->id)>{{ $p->name }}</option>
        @endforeach
      </select>
    </label>

    <label>حالة السداد
      <select name="paid_status">
        <option value="">-- كل الحالات --</option>
        <option value="paid" @selected(request('paid_status') === 'paid')>مستحق مسدد ✓</option>
        <option value="unpaid" @selected(request('paid_status') === 'unpaid')>مستحق غير مسدد ⏳</option>
        <option value="has_due" @selected(request('paid_status') === 'has_due')>فواتير لها مستحق 5%</option>
        <option value="no_due" @selected(request('paid_status') === 'no_due')>غير مستحق</option>
      </select>
    </label>

    <label>من تاريخ
      <input type="date" name="from_date" value="{{ request('from_date') }}">
    </label>

    <label>إلى تاريخ
      <input type="date" name="to_date" value="{{ request('to_date') }}">
    </label>

    <label>بحث برقم الفاتورة أو البيان
      <input type="text" name="search" value="{{ request('search') }}" placeholder="رقم أو وصف...">
    </label>

    <div style="display:flex;gap:6px">
      <button class="btn pr" type="submit" style="flex:1">تصفية</button>
      <a class="btn" href="{{ route('reports.index') }}" title="إعادة ضبط">✕</a>
    </div>
  </form>
</div>

{{-- بطاقات الملخص المالي الإحصائي (مطابقة للـ PDF تماماً في موقع الرقم والأيقونة) --}}
<div class="st">
  <div class="sx">
    <small>المبلغ الخاضع للضريبة</small>
    <b>{{ number_format($metrics['total_amount'], 2) }}</b> <img class="sar" src="{{ $sar }}" alt="ر.س">
  </div>
  <div class="sx">
    <small>ضريبة القيمة المضافة 15%</small>
    <b>{{ number_format($metrics['total_tax'], 2) }}</b> <img class="sar" src="{{ $sar }}" alt="ر.س">
  </div>
  <div class="sx pr">
    <small>الإجمالي الكلي شامل الضريبة</small>
    <b>{{ number_format($grandTotal = $metrics['grand_total'], 2) }}</b> <img class="sar" src="{{ $sar }}" alt="ر.س">
  </div>
</div>

<div class="st">
  <div class="sx wa">
    <small>إجمالي المستحق 5%</small>
    <b>{{ number_format($metrics['total_due'], 2) }}</b> <img class="sar" src="{{ $sar }}" alt="ر.س">
  </div>
  <div class="sx ok">
    <small>المستحق (المستلم / المسدد)</small>
    <b>{{ number_format($metrics['total_paid_due'], 2) }}</b> <img class="sar" src="{{ $sar }}" alt="ر.س">
  </div>
  <div class="sx wa">
    <small>باقي المستحق غير المسدد</small>
    <b>{{ number_format($metrics['total_unpaid_due'], 2) }}</b> <img class="sar" src="{{ $sar }}" alt="ر.س">
  </div>
  <div class="sx">
    <small>إجمالي عدد الفواتير</small>
    <b>{{ $metrics['count_invoices'] }}</b> <span class="mu" style="font-size:12px">فاتورة</span>
  </div>
</div>

{{-- جدول فواتير المشتريات --}}
<div style="margin-bottom:30px">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
    <h3 style="font-size:16px;color:var(--pr)">📋 جدول فواتير المشتريات</h3>
    <span class="mu" style="font-size:12px">العدد: {{ $invoices->count() }} فاتورة</span>
  </div>

  <div class="tw">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>رقم الفاتورة</th>
          <th>المشروع</th>
          <th>الشهر</th>
          <th>تاريخ الفاتورة</th>
          <th>المبلغ الخاضع</th>
          <th>الضريبة 15%</th>
          <th>الإجمالي</th>
          <th>مستحق 5%</th>
          <th>تاريخ الاستحقاق</th>
          <th>حالة السداد</th>
          <th class="col-img">صورة الفاتورة</th>
          <th>البيان والتفاصيل</th>
        </tr>
      </thead>
      <tbody>
        @forelse($invoices as $index => $inv)
        <tr>
          <td>{{ $index + 1 }}</td>
          <td><b>{{ $inv->number }}</b></td>
          <td>
            @if($inv->month?->project)
              <a href="{{ route('projects.show', $inv->month->project) }}" style="color:var(--pr);font-weight:bold;text-decoration:none">
                {{ $inv->month->project->name }}
              </a>
            @else
              -
            @endif
          </td>
          <td>
            @if($inv->month)
              <a href="{{ route('months.show', [$inv->month->project, $inv->month]) }}" style="color:var(--mu);text-decoration:none">
                {{ $inv->month->label }}
              </a>
            @else
              -
            @endif
          </td>
          <td>{{ $inv->invoice_date?->format('Y-m-d') }}</td>
          <td><b>{{ number_format($inv->amount, 2) }}</b> <img class="sar" src="{{ $sar }}" alt="ر.س"></td>
          <td class="mu">{{ number_format($inv->tax, 2) }} <img class="sar" src="{{ $sar }}" alt="ر.س"></td>
          <td style="color:var(--pr);font-weight:bold">{{ number_format($inv->total, 2) }} <img class="sar" src="{{ $sar }}" alt="ر.س"></td>
          <td>
            @if($inv->has_due)
              <span style="font-weight:bold">{{ number_format($inv->due_amount, 2) }}</span> <img class="sar" src="{{ $sar }}" alt="ر.س">
            @else
              <span class="mu">—</span>
            @endif
          </td>
          <td>{{ $inv->has_due && $inv->due_date ? $inv->due_date->format('Y-m-d') : '—' }}</td>
          <td>
            @if($inv->has_due)
              <span class="badge {{ $inv->due_paid ? 'op' : 'cl' }}">
                {{ $inv->due_paid ? '✓ مسدد' : 'غير مسدد' }}
              </span>
            @else
              <span class="badge neutral" style="background:#edf2f7;color:#718096">غير مستحق</span>
            @endif
          </td>
          <td class="col-img">
            @if($inv->image_path)
              <a href="{{ asset('storage/' . $inv->image_path) }}" target="_blank" title="عرض الصورة بالحجم الكامل">
                <img src="{{ asset('storage/' . $inv->image_path) }}" alt="مرفق الفاتورة" style="width:42px;height:42px;object-fit:cover;border-radius:6px;border:1px solid #cbd5e0;vertical-align:middle">
              </a>
            @else
              <span class="mu" style="font-size:11px">لا يوجد</span>
            @endif
          </td>
          <td style="max-width:240px;font-size:12px">{{ $inv->description ?: '—' }}</td>
        </tr>
        @empty
        <tr>
          <td colspan="13" style="text-align:center;padding:24px;color:var(--mu)">لا توجد فواتير مطابقة لخيارات التصفية</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- تذييل الطباعة --}}
<div class="print-footer">
  تم استخراج هذا التقرير آلياً عبر نظام إدارة الفواتير — Rite Group © {{ date('Y') }}
</div>

<style>
/* إخفاء عناصر ترويسة وتذييل الطباعة في العرض العادي على الشاشة */
.print-header,
.print-footer {
  display: none;
}

/* تنسيقات الطباعة المطابقة تماماً لملف الـ PDF وتصميم الموقع */
@media print {
  @page {
    size: A4 landscape;
    margin: 10mm 12mm;
  }
  * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    box-sizing: border-box !important;
  }
  /* إخفاء جميع عناصر المتصفح والواجهة غير المخصصة للطباعة */
  header.top,
  .top,
  .hd,
  .report-filters,
  .col-img,
  #ov,
  #mn,
  #ts,
  .alert,
  .btn {
    display: none !important;
  }
  body {
    background: #ffffff !important;
    color: #1c2826 !important;
    font-family: 'Tajawal', Cairo, Tahoma, sans-serif !important;
    font-size: 10px !important;
    margin: 0 !important;
    padding: 0 !important;
  }
  main {
    max-width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
  }
  .print-header {
    display: block !important;
  }
  .print-footer {
    display: block !important;
    margin-top: 14px !important;
    padding-top: 8px !important;
    border-top: 1px solid #d5dfdd !important;
    font-size: 9px !important;
    color: #5c706d !important;
    text-align: center !important;
  }
  .st {
    display: flex !important;
    gap: 6px !important;
    margin-bottom: 8px !important;
  }
  .sx {
    flex: 1 !important;
    background: #ffffff !important;
    border: 1px solid #d5dfdd !important;
    border-radius: 6px !important;
    padding: 7px 10px !important;
  }
  .sx.pr {
    background: #e6f0f1 !important;
    border-color: #0f5c63 !important;
  }
  .sx.wa {
    background: #fffaf0 !important;
    border-color: #dd6b20 !important;
  }
  .sx.ok {
    background: #f0fff4 !important;
    border-color: #38a169 !important;
  }
  .sx small {
    display: block !important;
    font-size: 8.5px !important;
    color: #5c706d !important;
    margin-bottom: 3px !important;
  }
  .sx b {
    font-size: 13px !important;
    color: #1c2826 !important;
  }
  .sx.pr b {
    color: #0f5c63 !important;
  }
  .sx.ok b {
    color: #276749 !important;
  }
  .sx.wa b {
    color: #c05621 !important;
  }
  .tw {
    overflow: visible !important;
    border: none !important;
  }
  table {
    width: 100% !important;
    border-collapse: collapse !important;
    font-size: 9.5px !important;
    border: 1px solid #d5dedd !important;
  }
  thead {
    display: table-header-group !important;
  }
  th {
    background-color: #0f5c63 !important;
    color: #ffffff !important;
    font-weight: bold !important;
    padding: 6px 4px !important;
    font-size: 9.5px !important;
    border: 1px solid #0f5c63 !important;
    white-space: nowrap !important;
  }
  td {
    border: 1px solid #e2e8f0 !important;
    padding: 5px 4px !important;
    font-size: 9px !important;
    vertical-align: middle !important;
  }
  tbody tr:nth-child(even) td {
    background-color: #f8fafc !important;
  }
  tr {
    page-break-inside: avoid !important;
  }
  .badge {
    display: inline-block !important;
    border-radius: 4px !important;
    padding: 2px 5px !important;
    font-size: 8px !important;
    font-weight: bold !important;
    white-space: nowrap !important;
  }
  .badge.op {
    background-color: #c6f6d5 !important;
    color: #22543d !important;
  }
  .badge.cl {
    background-color: #fed7d7 !important;
    color: #742a2a !important;
  }
  .badge.neutral {
    background-color: #edf2f7 !important;
    color: #718096 !important;
  }
  .sar {
    height: 11px !important;
    vertical-align: -1px !important;
    margin-inline-start: 3px !important;
    margin-inline-end: 0 !important;
  }
}
</style>
@endsection
