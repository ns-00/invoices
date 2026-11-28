<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
<title>@yield('title', 'فواتير المشتريات')</title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
@auth
<header class="top">
  <a class="brand" href="{{ auth()->user()->isAdmin() ? route('dashboard') : route('projects.index') }}">
    <img src="{{ asset('images/right.png') }}" alt="Rite Group"><span>فواتير المشتريات</span>
  </a>
  <nav>
    @if (auth()->user()->isAdmin())
      <a href="{{ route('dashboard') }}" @class(['on' => request()->routeIs('dashboard')])>لوحة التحكم</a>
    @endif
    <a href="{{ route('projects.index') }}" @class(['on' => request()->routeIs('projects.*', 'months.*')])>المشاريع والفواتير</a>
    <a href="{{ route('reports.index') }}" @class(['on' => request()->routeIs('reports.*')])>التقارير</a>
    @if (auth()->user()->isAdmin())
      <a href="{{ route('users.index') }}" @class(['on' => request()->routeIs('users.*')])>المستخدمون</a>
      <a href="{{ route('activity-logs.index') }}" @class(['on' => request()->routeIs('activity-logs.*')])>سجل الأحداث</a>
      <a href="{{ route('backups.index') }}" @class(['on' => request()->routeIs('backups.*')])>النسخ الاحتياطية</a>
    @endif
  </nav>
  <div class="out">
    <a href="{{ route('profile.edit') }}" @class(['btn sm', 'on' => request()->routeIs('profile.*')]) title="تعديل بيانات الحساب" style="text-decoration:none;display:inline-flex;align-items:center;gap:4px">
      👤 <span>{{ auth()->user()->name }}</span>
    </a>
    <form method="POST" action="{{ route('logout') }}" style="margin:0">
      @csrf
      <button class="btn sm">تسجيل الخروج</button>
    </form>
  </div>
</header>
@endauth

<main>
  @if (session('ok'))<div class="alert ok">{{ session('ok') }}</div>@endif
  @if ($errors->any())
    <div class="alert er">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
  @endif
  @yield('content')
</main>

<div id="ov"><div class="mb" id="mb"></div></div>
<div id="mn"></div>
<div id="ts"></div>

@stack('scripts')
</body>
</html>
