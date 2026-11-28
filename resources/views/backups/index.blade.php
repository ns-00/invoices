@extends('layouts.app')
@section('title', 'النسخ الاحتياطية')

@section('content')
<div class="hd" style="flex-wrap:wrap;gap:10px">
  <div>
    <h2>إدارة النسخ الاحتياطية</h2>
  </div>
  <form method="POST" action="{{ route('backups.create') }}">
    @csrf
    <button class="btn pr" type="submit">💾 إنشاء نسخة احتياطية الآن</button>
  </form>
</div>

{{-- بطاقات إحصائيات حالة النسخ الاحتياطي --}}
<div class="st" style="margin-bottom:20px">
  <div class="sx">
    <small>إجمالي عدد النسخ الاحتياطية</small>
    <b>{{ $stats['total_backups'] ?? count($backups) }}</b> <span class="mu" style="font-size:12px">نسخة</span>
  </div>
  <div class="sx">
    <small>إجمالي المساحة المستخدمة</small>
    <b>{{ $stats['used_mb'] ?? '0' }}</b> <span class="mu" style="font-size:12px">ميجابايت</span>
  </div>
  <div class="sx {{ !empty($stats['is_healthy']) ? 'ok' : 'wa' }}">
    <small>حالة صحة النسخ الاحتياطي</small>
    <b style="font-size:15px">{{ !empty($stats['is_healthy']) ? '✅ سليم وجاهز' : '⚠️ يحتاج فحص' }}</b>
  </div>
  <div class="sx pr">
    <small>تاريخ أحدث نسخة احتياطية</small>
    <b style="font-size:14px" dir="ltr">{{ $stats['newest'] ?? '—' }}</b>
  </div>
</div>

<div class="tw">
  <table>
    <thead>
      <tr>
        <th style="width:5%">#</th>
        <th style="width:40%">اسم ملف النسخة الاحتياطية</th>
        <th style="width:15%">حجم الملف</th>
        <th style="width:20%">تاريخ وتوقيت الإنشاء</th>
        <th style="width:20%">الإجراءات</th>
      </tr>
    </thead>
    <tbody>
      @forelse($backups as $index => $b)
      <tr>
        <td>{{ $index + 1 }}</td>
        <td>
          <b dir="ltr" style="font-family:monospace">{{ $b['filename'] }}</b>
          <div class="mu" style="font-size:11px">يشمل: قاعدة البيانات SQL + ملفات الصور المرفوعة وهيكل النظام</div>
        </td>
        <td><b>{{ $b['size_mb'] }}</b> ميجابايت</td>
        <td dir="ltr" style="text-align:right">{{ $b['created_at'] }}</td>
        <td class="acts">
          <a class="btn sm" href="{{ route('backups.download', $b['filename']) }}">⬇️ تنزيل</a>
          <form method="POST" action="{{ route('backups.destroy', $b['filename']) }}" onsubmit="return confirm('هل أنت متأكد من حذف هذه النسخة الاحتياطية نهائياً؟')">
            @csrf
            @method('DELETE')
            <button class="btn dg sm">حذف</button>
          </form>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="5" style="text-align:center;padding:24px;color:var(--mu)">لا توجد نسخ احتياطية حالياً. اضغط على زر «إنشاء نسخة احتياطية الآن» أعلاه.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
