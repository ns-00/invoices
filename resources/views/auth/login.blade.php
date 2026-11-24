@extends('layouts.app')
@section('title', 'تسجيل الدخول')
@section('content')
<div class="auth">
  <img class="logo" src="{{ asset('images/right.png') }}" alt="Rite Group">
  <div class="card">
    <h2>تسجيل الدخول</h2>
    <form method="POST" action="{{ url('/login') }}" class="fg" style="grid-template-columns:1fr">
      @csrf
      <label>البريد الإلكتروني
        <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" dir="ltr" style="text-align:right">
      </label>
      <label>كلمة المرور
        <input type="password" name="password" required autocomplete="current-password" dir="ltr" style="text-align:right">
      </label>
      <div style="display:flex;justify-content:space-between;align-items:center;margin:4px 0">
        <label class="chk" style="margin:0"><input type="checkbox" name="remember" value="1"> تذكّرني على هذا الجهاز</label>
        <a href="{{ route('password.request') }}" style="font-size:12px;color:var(--pr);font-weight:600;text-decoration:none">نسيت كلمة المرور؟</a>
      </div>
      <button class="btn pr">دخول</button>
    </form>
  </div>
  <p class="mu" style="text-align:center;margin-top:14px">لا يوجد تسجيل حساب جديد — تواصل مع المدير لإنشاء حسابك.</p>
</div>
@endsection
