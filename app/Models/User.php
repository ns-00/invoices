<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, LogsActivity, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'is_active'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->hasRole('admin');
    }

    public function isViewer(): bool
    {
        return $this->role === 'viewer' || $this->hasRole('viewer');
    }

    public function canManageInvoices(): bool
    {
        if ($this->isViewer()) {
            return false;
        }

        if (in_array($this->role, ['admin', 'user'])) {
            return true;
        }

        return $this->can('invoices.create') || $this->hasAnyRole(['admin', 'user']);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'role', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => match($eventName) {
                'created' => "تم إنشاء مستخدم جديد: {$this->name} ({$this->email})",
                'updated' => "تم تعديل بيانات المستخدم: {$this->name} ({$this->email})",
                'deleted' => "تم حذف المستخدم: {$this->name} ({$this->email})",
                default   => $eventName,
            });
    }
}
