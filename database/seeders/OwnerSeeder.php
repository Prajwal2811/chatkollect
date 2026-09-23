<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Owner;
use Illuminate\Support\Facades\Hash;

class OwnerSeeder extends Seeder
{
    public function run(): void
    {
        Owner::create([
            'owner_name' => 'Tally Owner',
            'email' => 'tally@test.com',
            'phone' => '9999999999',
            'business_name' => 'Test Tally Business',
            'business_type' => Owner::TYPE_TALLY, // 'tally'
            'address' => 'Pune',
            'password' => Hash::make('12345678'),
            'pass' =>  '12345678',
            'status' => 'active',
            'is_subscribed' => 'false',
            'subscription_expiry' => null,
        ]);

        Owner::create([
            'owner_name' => 'manual Owner',
            'email' => 'manual@test.com',
            'phone' => '8888888888',
            'business_name' => 'Test manual Business',
            'business_type' => Owner::TYPE_manual, // 'manual'
            'address' => 'Nagpur',
            'password' => Hash::make('12345678'),
            'pass' =>  '12345678',
            'status' => 'active',
            'is_subscribed' => 'false',
            'subscription_expiry' => null,
        ]);
    }
}