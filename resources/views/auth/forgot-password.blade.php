@extends('layouts.app')
@section('title', 'استعادة كلمة المرور')

@section('content')
<div class="auth">
  <img class="logo" src="{{ asset('images/right.png') }}" alt="Rite Group">
  <div class="card">
    <h2>استعادة كلمة المرور</h2>
    <p class="mu" style="margin-bottom:18px;line-height:1.7;font-size:13px">
      أدخل بريدك الإلكتروني المسجل في النظام، وسنرسل لك <b>رمز تحقق مكوّناً من 6 أرقام</b> لتتمكن من تعيين كلمة مرور جديدة.
    </p>

    <form method="POST" action="{{ route('password.email') }}" class="fg" style="grid-template-columns:1fr">
      @csrf

      <label>البريد الإلكتروني
        <input type="email" name="email" value="{{ old('email') }}" required autofocus dir="ltr" style="text-align:right" placeholder="name@company.com">
      </label>

      <button class="btn pr" style="margin-top:4px">📩 إرسال رمز التحقق</button>
    </form>

    <div style="text-align:center;margin-top:18px">
      <a href="{{ route('login') }}" style="font-size:13px;color:var(--pr);font-weight:600;text-decoration:none">← العودة لتسجيل الدخول</a>
    </div>
  </div>
</div>
@endsection
