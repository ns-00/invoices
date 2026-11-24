<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Project extends Model
{
    use LogsActivity;

    protected $fillable = ['user_id', 'name'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function months(): HasMany
    {
        return $this->hasMany(Month::class);
    }

    // كل مستخدم (بما في ذلك المدير) يرى مشاريعه الخاصة به فقط
    public function scopeForUser(Builder $q, User $user): Builder
    {
        return $q->where('user_id', $user->id);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => match($eventName) {
                'created' => "تم إنشاء المشروع: {$this->name}",
                'updated' => "تم تعديل المشروع: {$this->name}",
                'deleted' => "تم حذف المشروع: {$this->name}",
                default   => $eventName,
            });
    }
}
