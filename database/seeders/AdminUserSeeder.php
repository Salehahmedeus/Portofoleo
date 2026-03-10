<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->upsert(
            values: [[
                'name' => config('admin.name'),
                'email' => config('admin.email'),
                'password' => Hash::make((string) config('admin.password')),
                'is_admin' => true,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]],
            uniqueBy: ['email'],
            update: ['name', 'password', 'is_admin', 'email_verified_at', 'updated_at']
        );
    }
}
