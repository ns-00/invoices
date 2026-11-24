@extends('layouts.app')
@section('title', 'فواتير ' . $month->label . ' — ' . $project->name)

@section('content')
@php
  $sar = asset('images/sar.png');
  $invs = $month->invoices;
  $subtotal = $invs->sum('amount');
  $taxTotal = $invs->sum(fn ($i) => round($i->amount * 0.15, 2));
  $grandTotal = $subtotal + $taxTotal;
  $dueTotal = $invs->where('has_due', true)->sum(fn ($i) => round($i->amount * 0.05, 2));
  $dueReceived = $invs->where('has_due', true)->where('due_paid', true)->sum(fn ($i) => round($i->amount * 0.05, 2));
  $duePending = $dueTotal - $dueReceived;
  $today = now()->startOfDay();
  $canManage = auth()->user()->canManageInvoices();
@endphp

<div class="bc">
  <a href="{{ route('projects.index') }}">المشاريع</a> / 
  <a href="{{ route('projects.show', $project) }}">{{ $project->name }}</a> / 
  {{ $month->label }}
</div>

<div class="hd">
  <h2>
    📁 {{ $month->label }} 
    <span class="badge {{ $month->closed ? 'cl' : 'op' }}">{{ $month->closed ? 'مُقفل' : 'مفتوح' }}</span>
  </h2>
  <div class="row" style="margin:0">
    @if($canManage)
      <button class="btn pr" onclick="openAddInvoiceModal()" {{ $month->closed ? 'disabled' : '' }}>+ إضافة فاتورة</button>
      <form method="POST" action="{{ route('months.toggle', $month) }}" style="margin:0">
        @csrf
        <button class="btn" type="submit">{{ $month->closed ? 'إعادة فتح الشهر' : 'إقفال الشهر' }}</button>
      </form>
    @else
      <span class="badge" style="background:#edf2f7;color:#4a5568">صلاحية مشاهدة فقط</span>
    @endif
  </div>
</div>

@if ($invs->isEmpty())
  <div class="empty">
    لا توجد فواتير في هذا الشهر.<br>
    @if($canManage)
      اضغط «إضافة فاتورة» لتسجيل أول فاتورة.
    @endif
  </div>
@else
  <div class="tw">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>رقم الفاتورة</th>
          <th>التاريخ</th>
          <th>الوصف</th>
          <th>الخاضع</th>
          <th>الضريبة 15%</th>
          <th>الإجمالي</th>
          <th>المستحق 5%</th>
          <th>الاستحقاق</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach ($invs as $i)
          @php
            $isLate = $i->has_due && ! $i->due_paid && $i->due_date && $i->due_date->lt($today);
          @endphp
          <tr id="row-inv-{{ $i->id }}">
            <td>{{ $loop->iteration }}</td>
            <td>
              <b>{{ $i->number }}</b>
              @if ($i->image_path)
                <span title="مرفق صورة (WebP)" style="cursor:pointer;font-size:15px;margin-inline-start:4px" onclick="viewInvoice({{ $i->id }})">📷</span>
              @endif
            </td>
            <td>{{ $i->invoice_date->format('Y-m-d') }}</td>
            <td class="ds" title="{{ $i->description }}">{{ $i->description ?: '—' }}</td>
            <td>{{ number_format($i->amount, 2) }} <img class="sar" src="{{ $sar }}" alt="ر.س"></td>
            <td>{{ number_format($i->tax, 2) }} <img class="sar" src="{{ $sar }}" alt="ر.س"></td>
            <td><b>{{ number_format($i->total, 2) }}</b> <img class="sar" src="{{ $sar }}" alt="ر.س"></td>
            <td>
              @if($i->has_due)
                {{ number_format($i->due_amount, 2) }} <img class="sar" src="{{ $sar }}" alt="ر.س">
              @else
                —
              @endif
            </td>
            <td>
              @if ($i->has_due)
                <div class="{{ $isLate ? 'late' : '' }}">
                  {{ $i->due_date ? $i->due_date->format('Y-m-d') : '—' }}{{ $isLate ? ' (متأخر)' : '' }}
                </div>
                @if($canManage)
                  <button 
                    class="chip {{ $i->due_paid ? 'on' : '' }}" 
                    onclick="togglePaid({{ $i->id }}, this)"
                    type="button"
                  >
                    {{ $i->due_paid ? '✓ تم الاستحقاق' : 'هل تم استحقاق المبلغ؟' }}
                  </button>
                @else
                  <span class="badge {{ $i->due_paid ? 'op' : 'cl' }}">{{ $i->due_paid ? 'مسدد' : 'غير مسدد' }}</span>
                @endif
              @else
                —
              @endif
            </td>
            <td>
              <button class="dots" onclick="openMenu(event, {{ $i->id }})" aria-label="خيارات">⋮</button>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endif

<div class="sec">الملخص المالي لشهر {{ $month->label }}</div>
<div class="st">
  <div class="sx">
    <small>المبلغ الخاضع للضريبة</small>
    <b>{{ number_format($subtotal, 2) }}</b> <img class="sar" src="{{ $sar }}" alt="ر.س">
  </div>
  <div class="sx">
    <small>ضريبة القيمة المضافة 15%</small>
    <b>{{ number_format($taxTotal, 2) }}</b> <img class="sar" src="{{ $sar }}" alt="ر.س">
  </div>
  <div class="sx pr">
    <small>الإجمالي الكلي شامل الضريبة</small>
    <b>{{ number_format($grandTotal, 2) }}</b> <img class="sar" src="{{ $sar }}" alt="ر.س">
  </div>
</div>

<div class="st">
  <div class="sx wa">
    <small>إجمالي المستحق 5%</small>
    <b>{{ number_format($dueTotal, 2) }}</b> <img class="sar" src="{{ $sar }}" alt="ر.س">
  </div>
  <div class="sx ok">
    <small>المستحق (المستلم)</small>
    <b>{{ number_format($dueReceived, 2) }}</b> <img class="sar" src="{{ $sar }}" alt="ر.س">
  </div>
  <div class="sx wa">
    <small>باقي المستحق</small>
    <b>{{ number_format($duePending, 2) }}</b> <img class="sar" src="{{ $sar }}" alt="ر.س">
  </div>
</div>

@push('scripts')
<script>
const invoicesData = {!! json_encode($invs->mapWithKeys(function ($inv) {
  return [$inv->id => [
    'id'          => $inv->id,
    'number'      => $inv->number,
    'date'        => $inv->invoice_date->format('Y-m-d'),
    'description' => $inv->description,
    'amount'      => (float) $inv->amount,
    'tax'         => (float) $inv->tax,
    'total'       => (float) $inv->total,
    'has_due'     => (bool) $inv->has_due,
    'due_amount'  => (float) $inv->due_amount,
    'due_date'    => $inv->due_date ? $inv->due_date->format('Y-m-d') : '',
    'due_paid'    => (bool) $inv->due_paid,
    'image_url'   => $inv->image_path ? asset('storage/' . $inv->image_path) : null,
  ]];
})) !!};

const isMonthClosed = {{ $month->closed ? 'true' : 'false' }};
const canManage = {{ $canManage ? 'true' : 'false' }};
const sarImg = '<img class="sar" src="{{ $sar }}" alt="ر.س">';
const mb = document.getElementById('mb');
const ov = document.getElementById('ov');
const mn = document.getElementById('mn');
const ts = document.getElementById('ts');

function closeModal() { ov.classList.remove('on'); }
ov.addEventListener('click', e => { if (e.target === ov) closeModal(); });

function toast(msg) {
  ts.textContent = msg;
  ts.style.opacity = '1';
  clearTimeout(toast.t);
  toast.t = setTimeout(() => ts.style.opacity = '0', 2600);
}

function f(num) {
  return (+num || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function updateCalculations() {
  const fam = document.getElementById('fam');
  const ftx = document.getElementById('ftx');
  const fto = document.getElementById('fto');
  const fdv = document.getElementById('fdv');
  const dueBox = document.getElementById('dueBox');
  const dueRadio = document.querySelector('input[name="has_due"]:checked');

  const val = parseFloat(fam?.value) || 0;
  if (ftx) ftx.value = f(val * 0.15);
  if (fto) fto.value = f(val * 1.15);

  const hasDue = dueRadio && dueRadio.value === '1';
  if (dueBox) dueBox.style.display = hasDue ? 'grid' : 'none';
  if (fdv) fdv.value = f(hasDue ? (val * 0.05) : 0);
}

function previewLocalImage(input) {
  const pv = document.getElementById('pv');
  if (input.files && input.files[0]) {
    const file = input.files[0];
    if (file.size > 5 * 1024 * 1024) {
      alert('حجم الصورة أكبر من 5 ميجابايت! الرجاء اختيار صورة أصغر من 5MB.');
      input.value = '';
      if (pv) pv.innerHTML = '';
      return;
    }
    const reader = new FileReader();
    reader.onload = e => {
      pv.innerHTML = `
        <img src="${e.target.result}" alt="معاينة الصورة" style="max-width:100%;max-height:200px;border-radius:8px;border:1px solid var(--bd);margin-top:6px"><br>
        <small style="color:var(--ok)">✓ سيتم تحويل الصورة تلقائياً لصيغة WebP لتوفير المساحة.</small><br>
        <button type="button" class="btn dg sm" style="margin-top:6px" onclick="clearSelectedImage()">إزالة الصورة</button>
      `;
    };
    reader.readAsDataURL(file);
  }
}

function clearSelectedImage() {
  const fim = document.getElementById('fim');
  if (fim) fim.value = '';
  const pv = document.getElementById('pv');
  if (pv) pv.innerHTML = '';
  const rem = document.getElementById('remove_image_input');
  if (rem) rem.value = '1';
}

function openAddInvoiceModal() {
  if (isMonthClosed) {
    return toast('الشهر مُقفل — أعد فتحه لإضافة فواتير');
  }

  const todayStr = '{{ now()->format("Y-m-d") }}';
  const monthPrefix = '{{ $month->ym }}';
  const defaultDate = todayStr.startsWith(monthPrefix) ? todayStr : `${monthPrefix}-01`;

  mb.style.maxWidth = '580px';
  mb.innerHTML = `
    <h3>إضافة فاتورة جديدة</h3>
    <form method="POST" action="{{ route('months.invoices.store', $month) }}" enctype="multipart/form-data">
      <input type="hidden" name="_token" value="{{ csrf_token() }}">
      <div class="fg">
        <label>رقم الفاتورة
          <input name="number" id="fno" required autofocus placeholder="مثال: INV-1001">
        </label>
        <label>تاريخ الفاتورة
          <input name="invoice_date" id="fdt" type="date" required value="${defaultDate}">
        </label>
        <label class="w">الوصف / البيان
          <textarea name="description" id="fds" rows="3" placeholder="بيان مشتريات الفاتورة..."></textarea>
        </label>
        <label>المبلغ الخاضع للضريبة
          <input name="amount" id="fam" type="number" step="0.01" min="0.01" required inputmode="decimal" placeholder="0.00" oninput="updateCalculations()">
        </label>
        <label>الضريبة 15% (تلقائي)
          <input id="ftx" readonly value="0.00">
        </label>
        <label class="w">الإجمالي مع الضريبة
          <input id="fto" readonly value="0.00">
        </label>
        <label class="w">إرفاق صورة الفاتورة (أقصى حجم 5MB — تحويل إلى WebP)
          <input type="file" name="image" id="fim" accept="image/jpeg,image/png,image/jpg,image/webp,image/gif" onchange="previewLocalImage(this)">
        </label>
        <div class="w pv" id="pv"></div>
        <div class="w">
          <span style="font-size:13px;color:var(--mu)">هل يوجد مستحق 5%؟</span>
          <div class="rd">
            <label><input type="radio" name="has_due" value="1" onchange="updateCalculations()"> نعم</label>
            <label><input type="radio" name="has_due" value="0" checked onchange="updateCalculations()"> لا</label>
          </div>
        </div>
        <div class="w fg" id="dueBox" style="display:none">
          <label>قيمة المستحق 5% (من الخاضع فقط)
            <input id="fdv" readonly value="0.00">
          </label>
          <label>تاريخ الاستحقاق
            <input name="due_date" id="fdd" type="date">
          </label>
        </div>
      </div>
      <div class="act">
        <button class="btn pr" type="submit">حفظ الفاتورة</button>
        <button class="btn" type="button" onclick="closeModal()">إلغاء</button>
      </div>
    </form>
  `;
  ov.classList.add('on');
  setTimeout(() => mb.querySelector('input[name=number]')?.focus(), 50);
}

function openEditInvoiceModal(id) {
  if (isMonthClosed) {
    return toast('الشهر مُقفل — أعد فتحه للتعديل');
  }
  const inv = invoicesData[id];
  if (!inv) return;

  mb.style.maxWidth = '580px';
  mb.innerHTML = `
    <h3>تعديل فاتورة رقم ${inv.number}</h3>
    <form method="POST" action="{{ url('invoices') }}/${id}" enctype="multipart/form-data">
      <input type="hidden" name="_token" value="{{ csrf_token() }}">
      <input type="hidden" name="_method" value="PUT">
      <input type="hidden" name="remove_image" id="remove_image_input" value="0">
      <div class="fg">
        <label>رقم الفاتورة
          <input name="number" id="fno" required value="${inv.number}">
        </label>
        <label>تاريخ الفاتورة
          <input name="invoice_date" id="fdt" type="date" required value="${inv.date}">
        </label>
        <label class="w">الوصف / البيان
          <textarea name="description" id="fds" rows="3">${inv.description || ''}</textarea>
        </label>
        <label>المبلغ الخاضع للضريبة
          <input name="amount" id="fam" type="number" step="0.01" min="0.01" required inputmode="decimal" value="${inv.amount}" oninput="updateCalculations()">
        </label>
        <label>الضريبة 15% (تلقائي)
          <input id="ftx" readonly value="${f(inv.tax)}">
        </label>
        <label class="w">الإجمالي مع الضريبة
          <input id="fto" readonly value="${f(inv.total)}">
        </label>
        <label class="w">تغيير صورة الفاتورة (أقصى حجم 5MB — تحويل إلى WebP)
          <input type="file" name="image" id="fim" accept="image/jpeg,image/png,image/jpg,image/webp,image/gif" onchange="previewLocalImage(this)">
        </label>
        <div class="w pv" id="pv">
          ${inv.image_url ? `
            <img src="${inv.image_url}" alt="الصورة الحالية" style="max-width:100%;max-height:200px;border-radius:8px;border:1px solid var(--bd);margin-top:6px"><br>
            <button type="button" class="btn dg sm" style="margin-top:6px" onclick="clearSelectedImage()">إزالة الصورة</button>
          ` : ''}
        </div>
        <div class="w">
          <span style="font-size:13px;color:var(--mu)">هل يوجد مستحق 5%؟</span>
          <div class="rd">
            <label><input type="radio" name="has_due" value="1" ${inv.has_due ? 'checked' : ''} onchange="updateCalculations()"> نعم</label>
            <label><input type="radio" name="has_due" value="0" ${!inv.has_due ? 'checked' : ''} onchange="updateCalculations()"> لا</label>
          </div>
        </div>
        <div class="w fg" id="dueBox" style="display:${inv.has_due ? 'grid' : 'none'}">
          <label>قيمة المستحق 5% (من الخاضع فقط)
            <input id="fdv" readonly value="${f(inv.due_amount)}">
          </label>
          <label>تاريخ الاستحقاق
            <input name="due_date" id="fdd" type="date" value="${inv.due_date || ''}">
          </label>
        </div>
      </div>
      <div class="act">
        <button class="btn pr" type="submit">حفظ التعديلات</button>
        <button class="btn" type="button" onclick="closeModal()">إلغاء</button>
      </div>
    </form>
  `;
  ov.classList.add('on');
  updateCalculations();
}

function viewInvoice(id) {
  const inv = invoicesData[id];
  if (!inv) return;

  const dueDetails = inv.has_due 
    ? `${f(inv.due_amount)} ${sarImg} — يستحق في ${inv.due_date || '—'} ${inv.due_paid ? '(تم الاستحقاق ✓)' : '(معلّق)'}` 
    : 'لا يوجد مستحق';

  mb.style.maxWidth = '580px';
  mb.innerHTML = `
    <div class="vw">
      <h3>فاتورة رقم ${inv.number}</h3>
      <dl class="dl" style="margin-top:14px">
        <dt>التاريخ</dt><dd>${inv.date}</dd>
        <dt>الوصف</dt><dd>${inv.description || '—'}</dd>
        <dt>المبلغ الخاضع</dt><dd>${f(inv.amount)} ${sarImg}</dd>
        <dt>الضريبة 15%</dt><dd>${f(inv.tax)} ${sarImg}</dd>
        <dt>الإجمالي</dt><dd><b>${f(inv.total)} ${sarImg}</b></dd>
        <dt>المستحق 5%</dt><dd>${dueDetails}</dd>
        ${inv.image_url ? `
          <dt>صورة الفاتورة</dt>
          <dd class="pv">
            <a href="${inv.image_url}" target="_blank">
              <img src="${inv.image_url}" alt="صورة الفاتورة" style="max-width:100%;max-height:280px;border-radius:8px">
            </a>
          </dd>
        ` : ''}
      </dl>
      <div class="act" style="margin-top:20px">
        <button class="btn" type="button" onclick="closeModal()">إغلاق</button>
      </div>
    </div>
  `;
  ov.classList.add('on');
}

function openMenu(e, id) {
  e.stopPropagation();
  mn.innerHTML = `
    <button type="button" onclick="closeMenu();viewInvoice(${id})">👁 عرض الفاتورة</button>
    ${canManage ? `
      <button type="button" onclick="closeMenu();openEditInvoiceModal(${id})" ${isMonthClosed ? 'disabled style="opacity:0.5"' : ''}>✎ تعديل</button>
      <form method="POST" action="{{ url('invoices') }}/${id}" onsubmit="return confirm('هل تريد حذف هذه الفاتورة؟')" style="margin:0">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="_method" value="DELETE">
        <button type="submit" style="color:var(--er)" ${isMonthClosed ? 'disabled style="opacity:0.5"' : ''}>🗑 حذف</button>
      </form>
    ` : ''}
  `;
  mn.style.display = 'block';
  const r = e.target.getBoundingClientRect();
  mn.style.left = Math.max(8, Math.min(r.left, window.innerWidth - 150)) + 'px';
  mn.style.top = Math.min(r.bottom + 4, window.innerHeight - 150) + 'px';
}

function closeMenu() {
  mn.style.display = 'none';
}
document.addEventListener('click', closeMenu);
window.addEventListener('scroll', closeMenu, true);

function togglePaid(id, btn) {
  if (!canManage) return;

  fetch(`{{ url('invoices') }}/${id}/toggle-paid`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': '{{ csrf_token() }}',
      'Accept': 'application/json'
    }
  })
  .then(res => res.json())
  .then(data => {
    if (data.ok) {
      if (invoicesData[id]) invoicesData[id].due_paid = data.due_paid;
      if (data.due_paid) {
        btn.classList.add('on');
        btn.textContent = '✓ تم الاستحقاق';
        toast('تم تأكيد استحقاق المبلغ بنجاح');
      } else {
        btn.classList.remove('on');
        btn.textContent = 'هل تم استحقاق المبلغ؟';
        toast('تم تعديل حالة الاستحقاق إلى معلّق');
      }
      setTimeout(() => window.location.reload(), 600);
    }
  })
  .catch(() => {
    toast('تعذر تحديث حالة الاستحقاق');
  });
}
</script>
@endpush
@endsection
