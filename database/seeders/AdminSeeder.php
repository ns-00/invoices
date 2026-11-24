<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@example.com')],
            [
                'name'      => 'مدير النظام',
                'password'  => env('ADMIN_PASSWORD', 'ChangeMe#2026'), // يُشفَّر تلقائياً
                'role'      => 'admin',
                'is_active' => true,
            ]
        );
    }
}
