@extends('layouts.app')
@section('title', 'لوحة التحكم')
@section('content')
@php($sar = asset('images/sar.png'))
<div class="hd">
  <div><h2>لوحة التحكم</h2><div class="mu">مرحباً {{ auth()->user()->name }}</div></div>
  <div class="row" style="margin:0">
    <a class="btn pr" href="{{ route('projects.index') }}">المشاريع والفواتير</a>
    @if (auth()->user()->isAdmin())
      <a class="btn" href="{{ route('users.index') }}">إدارة المستخدمين</a>
    @endif
  </div>
</div>

<div class="st">
  @if (! is_null($users))<div class="sx"><small>المستخدمون</small><b>{{ $users }}</b></div>@endif
  <div class="sx"><small>المشاريع</small><b>{{ $projects }}</b></div>
  <div class="sx"><small>الأشهر المفتوحة</small><b>{{ $openMonths }}</b></div>
  <div class="sx"><small>الفواتير</small><b>{{ $invoices }}</b></div>
</div>

<div class="sec">المستحقات 5%</div>
<div class="st">
  <div class="sx wa"><small>إجمالي المستحق</small><b>{{ number_format($totalDue, 2) }}</b> <img class="sar" src="{{ $sar }}" alt="ر.س"></div>
  <div class="sx ok"><small>المستحق (المستلم)</small><b>{{ number_format($received, 2) }}</b> <img class="sar" src="{{ $sar }}" alt="ر.س"></div>
  <div class="sx wa"><small>باقي المستحق</small><b>{{ number_format($pending, 2) }}</b> <img class="sar" src="{{ $sar }}" alt="ر.س"></div>
</div>

<div class="sec">أقرب المستحقات القادمة</div>
@if ($upcoming->isEmpty())
  <div class="card mu">لا توجد مستحقات معلّقة حالياً.</div>
@else
  <div class="tw"><table>
    <thead><tr>
      <th>المشروع</th><th>الشهر</th><th>رقم الفاتورة</th><th>تاريخ الاستحقاق</th>
      <th>المبلغ المستحق</th>
    </tr></thead>
    <tbody>
    @foreach ($upcoming as $inv)
      @php($late = $inv->due_date->isPast() && ! $inv->due_date->isToday())
      <tr>
        <td><a href="{{ route('projects.show', $inv->month->project) }}" style="color:var(--pr);font-weight:600;text-decoration:none">{{ $inv->month->project->name }}</a></td>
        <td><a href="{{ route('months.show', [$inv->month->project, $inv->month]) }}" style="color:var(--pr);text-decoration:none">{{ $inv->month->label }}</a></td>
        <td><a href="{{ route('months.show', [$inv->month->project, $inv->month]) }}" style="color:var(--tx);text-decoration:none"><b>{{ $inv->number }}</b></a></td>
        <td @class(['late' => $late])>{{ $inv->due_date->format('Y-m-d') }}{{ $late ? ' (متأخر)' : '' }}</td>
        <td><b>{{ number_format($inv->due_amount, 2) }}</b> <img class="sar" src="{{ $sar }}" alt="ر.س"></td>
      </tr>
    @endforeach
    </tbody>
  </table></div>
@endif
@endsection
