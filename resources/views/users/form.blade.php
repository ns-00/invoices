@extends('layouts.app')
@section('title', $user->exists ? 'تعديل مستخدم' : 'إضافة مستخدم')
@section('content')
<div class="hd"><h2>{{ $user->exists ? 'تعديل المستخدم' : 'إضافة مستخدم' }}</h2></div>
<div class="card">
  <form method="POST" action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}">
    @csrf
    @if ($user->exists) @method('PUT') @endif
    <div class="fg">
      <label>الاسم
        <input name="name" value="{{ old('name', $user->name) }}" required>
      </label>
      <label>البريد الإلكتروني (يصله تذكير المستحقات)
        <input type="email" name="email" value="{{ old('email', $user->email) }}" required dir="ltr" style="text-align:right">
      </label>
      <label>الصلاحية
        <select name="role">
          <option value="user" @selected(old('role', $user->role) === 'user')>مستخدم (إدخال وتعديل فواتير)</option>
          <option value="viewer" @selected(old('role', $user->role) === 'viewer')>مشاهد فقط (اطلاع وتقارير بدون تعديل)</option>
          <option value="admin" @selected(old('role', $user->role) === 'admin')>مدير النظام</option>
        </select>
      </label>
      <label class="chk" style="align-self:end;padding-bottom:10px">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active))> الحساب نشط
      </label>
      <label>كلمة المرور @if ($user->exists)<span class="hint">(اتركها فارغة بدون تغيير)</span>@endif
        <input type="password" name="password" autocomplete="new-password" dir="ltr" style="text-align:right" @required(! $user->exists)>
      </label>
      <label>تأكيد كلمة المرور
        <input type="password" name="password_confirmation" autocomplete="new-password" dir="ltr" style="text-align:right">
      </label>
    </div>
    <div class="act">
      <button class="btn pr">حفظ</button>
      <a class="btn" href="{{ route('users.index') }}">إلغاء</a>
    </div>
  </form>
</div>
@endsection
