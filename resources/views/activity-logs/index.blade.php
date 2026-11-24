@extends('layouts.app')
@section('title', 'سجل الأحداث والنشاطات')

@section('content')
<div class="hd">
  <div>
    <h2>سجل الأحداث والنشاطات (Audit Log)</h2>
    <div class="mu">تتبع كل الإجراءات والتعديلات والحذف والإضافات في النظام بالتفصيل والوقت والمسؤول</div>
  </div>
</div>

<div class="tw">
  <table>
    <thead>
      <tr>
        <th style="width:6%">#</th>
        <th style="width:16%">المستخدم المسؤول</th>
        <th style="width:14%">نوع الحدث</th>
        <th style="width:36%">تفاصيل الحدث والإجراء</th>
        <th style="width:14%">الكيان / السجل</th>
        <th style="width:14%">التاريخ والوقت</th>
      </tr>
    </thead>
    <tbody>
      @forelse($activities as $index => $act)
      <tr>
        <td>{{ $index + 1 }}</td>
        <td>
          @if($act->causer)
            <b>{{ $act->causer->name }}</b>
            <div class="mu" style="font-size:11px" dir="ltr">{{ $act->causer->email }}</div>
          @else
            <span class="mu">النظام التلقائي (System)</span>
          @endif
        </td>
        <td>
          @php
            $eventClass = match($act->event) {
              'created' => 'op',
              'deleted' => 'cl',
              default   => 'mu',
            };
            $eventLabel = match($act->event) {
              'created' => 'إنشاء +',
              'updated' => 'تعديل ✎',
              'deleted' => 'حذف ✕',
              default   => $act->event ?: 'إجراء',
            };
          @endphp
          <span class="badge {{ $eventClass }}">{{ $eventLabel }}</span>
        </td>
        <td style="font-weight:500">{{ $act->description }}</td>
        <td>
          @if($act->subject_type)
            <span style="font-size:12px;font-family:monospace;color:var(--pr)">
              {{ class_basename($act->subject_type) }} #{{ $act->subject_id }}
            </span>
          @else
            <span class="mu">-</span>
          @endif
        </td>
        <td style="font-size:12px;color:var(--mu)" dir="ltr">
          {{ $act->created_at->format('Y-m-d H:i:s') }}
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="6" style="text-align:center;padding:24px;color:var(--mu)">لا توجد أحداث مسجلة حتى الآن</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
