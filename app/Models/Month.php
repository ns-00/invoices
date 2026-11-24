<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Month extends Model
{
    use LogsActivity;

    protected $fillable = ['project_id', 'ym', 'closed'];

    protected $casts = ['closed' => 'boolean'];

    private const NAMES = ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    // مثال: 2026-11 => نوفمبر 2026
    public function getLabelAttribute(): string
    {
        [$y, $m] = explode('-', $this->ym);

        return self::NAMES[(int) $m - 1].' '.$y;
    }

    public function scopeForUser(Builder $q, User $user): Builder
    {
        return $q->whereHas('project', fn ($p) => $p->where('user_id', $user->id));
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => match($eventName) {
                'created' => "تم فتح ملف شهر جديد: {$this->label}",
                'updated' => "تم تعديل ملف الشهر: {$this->label} (" . ($this->closed ? 'تم إغلاقه' : 'مفتوح') . ")",
                'deleted' => "تم حذف ملف الشهر: {$this->label}",
                default   => $eventName,
            });
    }
}
