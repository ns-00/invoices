<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class InvoiceItem extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'invoice_id',
        'item_name',
        'quantity',
        'unit_price',
        'total_price',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity'    => 'decimal:2',
            'unit_price'  => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => match($eventName) {
                'created' => "تمت إضافة بند في الفاتورة: {$this->item_name}",
                'updated' => "تم تعديل بند في الفاتورة: {$this->item_name}",
                'deleted' => "تم حذف بند من الفاتورة: {$this->item_name}",
                default   => $eventName,
            });
    }
}
