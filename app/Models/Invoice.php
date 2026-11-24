<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Invoice extends Model
{
    use LogsActivity;

    protected $fillable = [
        'month_id', 'number', 'description', 'invoice_date', 'amount',
        'has_due', 'due_date', 'due_paid', 'due_paid_at', 'image_path', 'last_reminded_on',
    ];

    protected $casts = [
        'invoice_date'     => 'date',
        'due_date'         => 'date',
        'amount'           => 'decimal:2',
        'has_due'          => 'boolean',
        'due_paid'         => 'boolean',
        'due_paid_at'      => 'datetime',
        'last_reminded_on' => 'date',
    ];

    public function month(): BelongsTo
    {
        return $this->belongsTo(Month::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    // الضريبة 15% (تلقائي)
    protected function tax(): Attribute
    {
        return Attribute::get(fn () => round($this->amount * config('invoices.tax_rate'), 2));
    }

    // الإجمالي = الخاضع + الضريبة (تلقائي)
    protected function total(): Attribute
    {
        return Attribute::get(fn () => round($this->amount + $this->tax, 2));
    }

    // المستحق 5% من المبلغ الخاضع فقط (وليس من الإجمالي)
    protected function dueAmount(): Attribute
    {
        return Attribute::get(fn () => $this->has_due ? round($this->amount * config('invoices.due_rate'), 2) : 0.0);
    }

    public function scopeForUser(Builder $q, User $user): Builder
    {
        return $q->whereHas('month.project', fn ($p) => $p->where('user_id', $user->id));
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => match($eventName) {
                'created' => "تم إنشاء فاتورة جديدة برقم: {$this->number}",
                'updated' => "تم تعديل الفاتورة رقم: {$this->number}",
                'deleted' => "تم حذف الفاتورة رقم: {$this->number}",
                default   => $eventName,
            });
    }
}
