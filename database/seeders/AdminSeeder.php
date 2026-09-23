<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::create([
            'name' => 'Prajwal Ingole',
            'email' => 'prajwal@admin.com',
            'password' => Hash::make('prajwal@admin'), // change password as needed
            'pass' => 'prajwal@admin',
            'status' => 'active',
        ]);
    }
}