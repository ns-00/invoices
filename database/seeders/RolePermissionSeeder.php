<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create permissions
        $permissions = [
            'invoices.view'   => 'عرض الفواتير',
            'invoices.create' => 'إضافة فواتير جديدة',
            'invoices.edit'   => 'تعديل الفواتير',
            'invoices.delete' => 'حذف الفواتير',
            'invoices.export' => 'تصدير الفواتير PDF وإكسل',
            'reports.view'    => 'عرض صفحة التقارير',
            'users.manage'    => 'إدارة المستخدمين',
            'backups.manage'  => 'إدارة النسخ الاحتياطية',
            'activity.view'   => 'الاطلاع على سجل الأحداث',
        ];

        foreach ($permissions as $name => $label) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // 2. Roles
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        $viewerRole = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);

        // Admin has all permissions
        $adminRole->syncPermissions(Permission::all());

        // Regular user can view, create, edit, delete, export own invoices and reports
        $userRole->syncPermissions([
            'invoices.view',
            'invoices.create',
            'invoices.edit',
            'invoices.delete',
            'invoices.export',
            'reports.view',
        ]);

        // Viewer can ONLY view and export without modifying or deleting
        $viewerRole->syncPermissions([
            'invoices.view',
            'reports.view',
            'invoices.export',
        ]);

        // 3. Assign roles to existing users in DB
        foreach (User::all() as $user) {
            if ($user->role === 'admin') {
                $user->syncRoles([$adminRole]);
            } elseif ($user->role === 'viewer') {
                $user->syncRoles([$viewerRole]);
            } else {
                $user->syncRoles([$userRole]);
            }
        }
    }
}
