@extends('layouts.app')
@section('title', 'تعيين كلمة المرور الجديدة')

@section('content')
<div class="auth">
  <img class="logo" src="{{ asset('images/right.png') }}" alt="Rite Group">
  <div class="card">
    <h2>تعيين كلمة المرور</h2>
    <p class="mu" style="margin-bottom:18px;line-height:1.7;font-size:13px">
      أدخل <b>رمز التحقق (6 أرقام)</b> المرسل إلى بريدك الإلكتروني، ثم اكتب كلمة المرور الجديدة.
    </p>

    <form method="POST" action="{{ route('password.update') }}" class="fg" style="grid-template-columns:1fr">
      @csrf

      <label>البريد الإلكتروني
        <input type="email" name="email" value="{{ old('email', $email) }}" required dir="ltr" style="text-align:right">
      </label>

      <label>رمز التحقق (6 أرقام)
        <input 
          name="code" 
          value="{{ old('code') }}" 
          required 
          autofocus 
          placeholder="123456" 
          maxlength="6" 
          dir="ltr" 
          style="text-align:center;font-size:22px;letter-spacing:6px;font-family:monospace;font-weight:bold"
          autocomplete="one-time-code"
        >
        <span class="hint">صلاحية الرمز 15 دقيقة فقط من وقت إرساله.</span>
      </label>

      <label>كلمة المرور الجديدة
        <input type="password" name="password" required placeholder="8 أحرف على الأقل" dir="ltr" style="text-align:right" autocomplete="new-password">
      </label>

      <label>تأكيد كلمة المرور الجديدة
        <input type="password" name="password_confirmation" required placeholder="أعد إدخال كلمة المرور" dir="ltr" style="text-align:right" autocomplete="new-password">
      </label>

      <button class="btn pr" style="margin-top:6px">تحديث كلمة المرور والدخول</button>
    </form>

    <div style="display:flex;justify-content:space-between;margin-top:18px;font-size:13px">
      <a href="{{ route('password.request') }}" style="color:var(--pr);font-weight:600;text-decoration:none">لم يصلك الرمز؟ أعد الإرسال</a>
      <a href="{{ route('login') }}" style="color:var(--pr);font-weight:600;text-decoration:none">← العودة للدخول</a>
    </div>
  </div>
</div>
@endsection
