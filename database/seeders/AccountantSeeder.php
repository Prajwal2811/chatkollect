<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Accountant;
use Illuminate\Support\Facades\Hash;

class AccountantSeeder extends Seeder
{
    public function run(): void
    {
        $accountants = [
            [
                'name' => 'Admin Accountant',
                'email' => 'accountant1@test.com',
                'phone' => '9876543210',
            ],
            [
                'name' => 'John Doe',
                'email' => 'accountant2@test.com',
                'phone' => '1234567890',
            ],
        ];

        foreach ($accountants as $accountant) {
            Accountant::create([
                'owner_id' => 1,
                'name' => $accountant['name'],
                'email' => $accountant['email'],
                'phone' => $accountant['phone'],
                'address' => 'Nagpur, Maharashtra',
                'password' => Hash::make('12345678'),
                'pass' => '12345678',
                'status' => 'active',
            ]);
        }
    }
}