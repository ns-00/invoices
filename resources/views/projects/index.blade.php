@extends('layouts.app')
@section('title', 'المشاريع')

@section('content')
<div class="hd">
  <h2>المشاريع</h2>
  <button class="btn pr" onclick="openAddProjectModal()">+ إضافة مشروع</button>
</div>

@if ($projects->isEmpty())
  <div class="empty">
    لا توجد مشاريع بعد.<br>أضف مشروعك الأول وسمّه للبدء.
  </div>
@else
  <div class="grid">
    @foreach ($projects as $project)
      @php
        $invoicesCount = $project->months->sum(fn ($m) => $m->invoices->count());
      @endphp
      <div class="card">
        <h3 onclick="window.location='{{ route('projects.show', $project) }}'">{{ $project->name }}</h3>
        <div class="meta">
          {{ $project->months_count }} ملف شهري · {{ $invoicesCount }} فاتورة
        </div>
        <div class="row">
          <a class="btn pr sm" href="{{ route('projects.show', $project) }}">فتح</a>
          <button class="btn sm" onclick="openEditProjectModal({{ $project->id }}, '{{ addslashes($project->name) }}')">تعديل</button>
          <form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('سيتم حذف المشروع وجميع ملفاته وفواتيره نهائياً.')">
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

function openAddProjectModal() {
  mb.innerHTML = `
    <h3>إضافة مشروع جديد</h3>
    <form method="POST" action="{{ route('projects.store') }}">
      <input type="hidden" name="_token" value="{{ csrf_token() }}">
      <label>اسم المشروع
        <input name="name" required placeholder="اسم المشروع" autofocus>
      </label>
      <div class="act">
        <button class="btn pr" type="submit">حفظ</button>
        <button class="btn" type="button" onclick="closeModal()">إلغاء</button>
      </div>
    </form>
  `;
  ov.classList.add('on');
  setTimeout(() => mb.querySelector('input[name=name]')?.focus(), 50);
}

function openEditProjectModal(id, currentName) {
  mb.innerHTML = `
    <h3>تعديل المشروع</h3>
    <form method="POST" action="{{ url('projects') }}/${id}">
      <input type="hidden" name="_token" value="{{ csrf_token() }}">
      <input type="hidden" name="_method" value="PUT">
      <label>اسم المشروع
        <input name="name" value="${currentName}" required placeholder="اسم المشروع" autofocus>
      </label>
      <div class="act">
        <button class="btn pr" type="submit">حفظ التعديل</button>
        <button class="btn" type="button" onclick="closeModal()">إلغاء</button>
      </div>
    </form>
  `;
  ov.classList.add('on');
  setTimeout(() => mb.querySelector('input[name=name]')?.focus(), 50);
}
</script>
@endpush
@endsection
