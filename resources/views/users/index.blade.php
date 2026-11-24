@extends('layouts.app')
@section('title', 'المستخدمون')
@section('content')
<div class="hd">
  <h2>إدارة المستخدمين</h2>
  <a class="btn pr" href="{{ route('users.create') }}">+ إضافة مستخدم</a>
</div>
<div class="tw"><table>
  <thead><tr><th>الاسم</th><th>البريد الإلكتروني</th><th>الصلاحية</th><th>الحالة</th><th>تاريخ الإضافة</th><th></th></tr></thead>
  <tbody>
  @foreach ($users as $u)
    <tr>
      <td><b>{{ $u->name }}</b> @if ($u->is(auth()->user()))<small class="mu">(أنت)</small>@endif</td>
      <td dir="ltr" style="text-align:right">{{ $u->email }}</td>
      <td>{{ $u->isAdmin() ? 'مدير' : ($u->role === 'viewer' ? 'مشاهد فقط' : 'مستخدم') }}</td>
      <td><span class="badge {{ $u->is_active ? 'op' : 'cl' }}">{{ $u->is_active ? 'نشط' : 'معطّل' }}</span></td>
      <td>{{ $u->created_at->format('Y-m-d') }}</td>
      <td class="acts">
        <a class="btn sm" href="{{ route('users.edit', $u) }}">تعديل</a>
        <form method="POST" action="{{ route('users.destroy', $u) }}" onsubmit="return confirm('هل تريد حذف هذا المستخدم؟')">
          @csrf @method('DELETE')
          <button class="btn dg sm">حذف</button>
        </form>
      </td>
    </tr>
  @endforeach
  </tbody>
</table></div>
@endsection
