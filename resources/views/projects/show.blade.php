@extends('layouts.app')
@section('title', $project->name)

@section('content')
@php
  $sar = asset('images/sar.png');
@endphp

<div class="bc">
  <a href="{{ route('projects.index') }}">المشاريع</a> / {{ $project->name }}
</div>

<div class="hd">
  <h2>{{ $project->name }}</h2>
  <button class="btn pr" onclick="openAddMonthModal()">+ فتح ملف شهر جديد</button>
</div>

@if ($project->months->isEmpty())
  <div class="empty">
    لا توجد ملفات شهرية.<br>افتح ملف الشهر لتبدأ بتسجيل الفواتير.
  </div>
@else
  <div class="grid">
    @foreach ($project->months as $month)
      @php
        $invs = $month->invoices;
        $totalSum = $invs->sum(fn ($i) => round($i->amount * 1.15, 2));
        $dueSum = $invs->where('has_due', true)->sum(fn ($i) => round($i->amount * 0.05, 2));
        $receivedSum = $invs->where('has_due', true)->where('due_paid', true)->sum(fn ($i) => round($i->amount * 0.05, 2));
        $pendingDue = $dueSum - $receivedSum;
      @endphp
      <div class="card mc {{ $month->closed ? 'cl' : '' }}">
        <h3 onclick="window.location='{{ route('months.show', [$project, $month]) }}'">
          📁 {{ $month->label }} 
          <span class="badge {{ $month->closed ? 'cl' : 'op' }}">{{ $month->closed ? 'مُقفل' : 'مفتوح' }}</span>
        </h3>
        <div class="meta">
          {{ $invs->count() }} فاتورة<br>
          الإجمالي: {{ number_format($totalSum, 2) }} <img class="sar" src="{{ $sar }}" alt="ر.س"><br>
          باقي المستحق: {{ number_format($pendingDue, 2) }} <img class="sar" src="{{ $sar }}" alt="ر.س">
        </div>
        <div class="row">
          <a class="btn pr sm" href="{{ route('months.show', [$project, $month]) }}">فتح</a>
          <form method="POST" action="{{ route('months.toggle', $month) }}">
            @csrf
            <button class="btn sm">{{ $month->closed ? 'إعادة فتح' : 'إقفال' }}</button>
          </form>
          <form method="POST" action="{{ route('months.destroy', $month) }}" onsubmit="return confirm('سيتم حذف ملف الشهر وجميع فواتيره نهائياً.')">
            @csrf
            @method('DELETE')
            <button class="btn dg sm">حذف</button>
          </form>
        </div>
      </div>
    @endforeach
  </div>
@endif

@push('scripts')
<script>
const mb = document.getElementById('mb');
const ov = document.getElementById('ov');
function closeModal() { ov.classList.remove('on'); }
ov.addEventListener('click', e => { if (e.target === ov) closeModal(); });

@php
  $lastYm = $project->months->pluck('ym')->sort()->last();
  if ($lastYm) {
    [$ly, $lm] = explode('-', $lastYm);
    $ly = (int) $ly;
    $lm = (int) $lm;
    $defaultNextYm = ($lm === 12) ? (($ly + 1) . '-01') : sprintf('%04d-%02d', $ly, $lm + 1);
  } else {
    $defaultNextYm = date('Y-m');
  }
@endphp

function openAddMonthModal() {
  mb.innerHTML = `
    <h3>فتح ملف شهر جديد</h3>
    <form method="POST" action="{{ route('projects.months.store', $project) }}">
      <input type="hidden" name="_token" value="{{ csrf_token() }}">
      <label>الشهر
        <input name="ym" type="month" required value="{{ $defaultNextYm }}" autofocus>
      </label>
      <div class="act">
        <button class="btn pr" type="submit">فتح الملف</button>
        <button class="btn" type="button" onclick="closeModal()">إلغاء</button>
      </div>
    </form>
  `;
  ov.classList.add('on');
}
</script>
@endpush
@endsection
