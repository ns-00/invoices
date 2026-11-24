@extends('layouts.app')
@section('title', 'تعديل البيانات الشخصية')

@section('content')
<div class="hd">
  <div>
    <h2>بيانات الحساب الشخصي</h2>
    <div class="mu">إدارة بيانات حسابك وتغيير كلمة المرور بأمان</div>
  </div>
  <a class="btn" href="{{ auth()->user()->isAdmin() ? route('dashboard') : route('projects.index') }}">← عودة</a>
</div>

<div style="display:flex;flex-direction:column;gap:20px;max-width:680px;margin:0 auto">

  {{-- بطاقة المعلومات الأساسية --}}
  <div class="card">
    <h3 style="font-size:17px;margin-bottom:14px;color:var(--pr)">المعلومات الشخصية</h3>
    <form method="POST" action="{{ route('profile.update') }}">
      @csrf
      @method('PUT')

      <div class="fg">
        <label class="w">الاسم الكامل
          <input name="name" value="{{ old('name', $user->name) }}" required autofocus>
        </label>

        <label class="w">البريد الإلكتروني
          <input name="email" type="email" value="{{ old('email', $user->email) }}" required dir="ltr" style="text-align:right">
          <span class="hint">يُستخدم هذا البريد لتلقي رموز التحقق وتذكيرات الفواتير.</span>
        </label>
      </div>

      <div class="act">
        <button class="btn pr" type="submit">حفظ التعديلات</button>
      </div>
    </form>
  </div>

  {{-- بطاقة تغيير كلمة المرور عبر رمز التحقق --}}
  <div class="card" style="border-inline-start-color:var(--ac)">
    <h3 style="font-size:17px;margin-bottom:6px;color:var(--pr)">تغيير كلمة المرور</h3>
    <p class="mu" style="margin-bottom:16px;line-height:1.7">
      لحماية حسابك، <b>لا يمكن تغيير كلمة المرور إلا برمز تحقق</b> يُرسل إلى بريدك الإلكتروني ({{ $user->email }}).
    </p>

    {{-- زر إرسال الرمز --}}
    <div style="background:var(--pr2);border-radius:8px;padding:12px 16px;margin-bottom:18px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
      <span style="font-size:13px;color:var(--pr);font-weight:600">اضغط لطلب رمز التحقق عبر الإيميل:</span>
      <form method="POST" action="{{ route('profile.send-code') }}" id="sendCodeForm" style="margin:0">
        @csrf
        <button class="btn pr sm" type="button" id="sendCodeBtn" onclick="requestVerificationCode()">📩 إرسال رمز التحقق</button>
      </form>
    </div>

    {{-- نموذج إدخال الرمز وكلمة المرور الجديدة --}}
    <form method="POST" action="{{ route('profile.password.update') }}">
      @csrf
      @method('PUT')

      <div class="fg">
        <label class="w">رمز التحقق المرسل إلى الإيميل (6 أرقام)
          <input 
            name="code" 
            id="otpCode" 
            required 
            placeholder="123456" 
            maxlength="6" 
            dir="ltr" 
            style="text-align:center;font-size:20px;letter-spacing:6px;font-family:monospace;font-weight:bold"
            autocomplete="one-time-code"
          >
          <span class="hint">الرمز مكوّن من 6 أرقام وصالح لمدة 15 دقيقة من وقت الإرسال.</span>
        </label>

        <label>كلمة المرور الجديدة
          <input name="password" type="password" required placeholder="8 أحرف على الأقل" dir="ltr" style="text-align:right" autocomplete="new-password">
        </label>

        <label>تأكيد كلمة المرور الجديدة
          <input name="password_confirmation" type="password" required placeholder="أعد إدخال كلمة المرور" dir="ltr" style="text-align:right" autocomplete="new-password">
        </label>
      </div>

      <div class="act">
        <button class="btn pr" type="submit">تأكيد وتغيير كلمة المرور</button>
      </div>
    </form>
  </div>

</div>

@push('scripts')
<script>
function requestVerificationCode() {
  const btn = document.getElementById('sendCodeBtn');
  btn.disabled = true;
  btn.textContent = 'جاري الإرسال...';

  fetch('{{ route("profile.send-code") }}', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': '{{ csrf_token() }}',
      'Accept': 'application/json'
    }
  })
  .then(res => res.json())
  .then(data => {
    btn.textContent = 'تم الإرسال ✓';
    const ts = document.getElementById('ts');
    if (ts) {
      ts.textContent = data.message || 'تم إرسال رمز التحقق إلى بريدك الإلكتروني بنجاح.';
      ts.style.opacity = '1';
      setTimeout(() => ts.style.opacity = '0', 3500);
    } else {
      alert(data.message || 'تم إرسال رمز التحقق إلى بريدك الإلكتروني.');
    }
    document.getElementById('otpCode')?.focus();

    // عداد 60 ثانية قبل إعادة المحاولة
    let sec = 60;
    const timer = setInterval(() => {
      sec--;
      if (sec > 0) {
        btn.textContent = `إعادة الإرسال بعد (${sec}) ثانية`;
      } else {
        clearInterval(timer);
        btn.disabled = false;
        btn.textContent = '📩 إعادة إرسال الرمز';
      }
    }, 1000);
  })
  .catch(() => {
    btn.disabled = false;
    btn.textContent = '📩 إرسال رمز التحقق';
    alert('حدث خطأ أثناء محاولة إرسال الرمز، يرجى المحاولة لاحقاً.');
  });
}
</script>
@endpush
@endsection
