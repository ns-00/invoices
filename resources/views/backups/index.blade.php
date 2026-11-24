@extends('layouts.app')
@section('title', 'النسخ الاحتياطية')

@section('content')
<div class="hd" style="flex-wrap:wrap;gap:10px">
  <div>
    <h2>إدارة النسخ الاحتياطية (Backups)</h2>
    <div class="mu">إنشاء وتنزيل النسخ الاحتياطية الشاملة لقاعدة البيانات والملفات المرفوعة</div>
  </div>
  <form method="POST" action="{{ route('backups.create') }}">
    @csrf
    <button class="btn pr" type="submit">💾 إنشاء نسخة احتياطية الآن</button>
  </form>
</div>

{{-- صندوق إرشادات الجدولة في السيرفر --}}
<div class="card" style="margin-bottom:20px;border-inline-start-color:var(--pr);background:var(--pr2)">
  <h4 style="color:var(--pr);margin-bottom:8px">⚙️ جدولة النسخ الاحتياطي التلقائي على السيرفر:</h4>
  <p style="font-size:13px;line-height:1.7;color:var(--pr);margin-bottom:10px">
    المشروع مبرمج داخلياً عبر مجدول لارافيل (Laravel Scheduler) لتنفيذ النسخ الاحتياطي <b>يومياً عند الساعة 02:00 صباحاً</b> تلقائياً مع تنظيف النسخ الأقدم من 14 يوماً.
    لكي يعمل المجدول على السيرفر دون توقف، يكفي تفعيل الأمر التالي لمرة واحدة:
  </p>
  <div style="background:#1a202c;color:#e2e8f0;padding:12px;border-radius:6px;font-family:monospace;font-size:13px;direction:ltr;text-align:left;overflow-x:auto">
    <span style="color:#68d391"># على سيرفر Linux (Cron Job يعمل كل دقيقة):</span><br>
    * * * * * cd {{ base_path() }} && php artisan schedule:run >> /dev/null 2>&1<br><br>
    <span style="color:#68d391"># أو أمر النسخ الاحتياطي المباشر يومياً الساعة 2:00 صباحاً:</span><br>
    0 2 * * * cd {{ base_path() }} && php artisan app:backup-run >> /dev/null 2>&1
  </div>
</div>

<div class="tw">
  <table>
    <thead>
      <tr>
        <th style="width:6%">#</th>
        <th style="width:38%">اسم ملف النسخة الاحتياطية</th>
        <th style="width:16%">حجم الملف</th>
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
          <div class="mu" style="font-size:11px">يشمل: قاعدة البيانات SQL + ملفات الصور المرفوعة</div>
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
