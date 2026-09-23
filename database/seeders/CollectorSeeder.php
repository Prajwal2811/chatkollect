<?php

namespace Database\Seeders;

use App\Models\Collector;
use App\Models\Accountant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CollectorSeeder extends Seeder
{
    public function run(): void
    {
        $accountants = Accountant::pluck('id')->toArray();

        if (count($accountants) == 0) {
            return;
        }

        $collectors = [
            [
                'name' => 'Rahul Sharma',
                'email' => 'collector1@test.com',
                'phone' => '9876543201',
                'address' => 'Nagpur',
            ],
            [
                'name' => 'Amit Verma',
                'email' => 'collector2@test.com',
                'phone' => '9876543202',
                'address' => 'Pune',
            ],
            [
                'name' => 'Suresh Patel',
                'email' => 'collector3@test.com',
                'phone' => '9876543203',
                'address' => 'Mumbai',
            ],
            [
                'name' => 'Vikas Singh',
                'email' => 'collector4@test.com',
                'phone' => '9876543204',
                'address' => 'Delhi',
            ],
            [
                'name' => 'Rohan Joshi',
                'email' => 'collector5@test.com',
                'phone' => '9876543205',
                'address' => 'Hyderabad',
            ],
            [
                'name' => 'Ajay Kumar',
                'email' => 'collector6@test.com',
                'phone' => '9876543206',
                'address' => 'Chennai',
            ],
            [
                'name' => 'Manish Gupta',
                'email' => 'collector7@test.com',
                'phone' => '9876543207',
                'address' => 'Bangalore',
            ],
            [
                'name' => 'Karan Mehta',
                'email' => 'collector8@test.com',
                'phone' => '9876543208',
                'address' => 'Indore',
            ],
            [
                'name' => 'Deepak Yadav',
                'email' => 'collector9@test.com',
                'phone' => '9876543209',
                'address' => 'Jaipur',
            ],
            [
                'name' => 'Ankit Mishra',
                'email' => 'collector10@test.com',
                'phone' => '9876543211',
                'address' => 'Bhopal',
            ],
        ];

        foreach ($collectors as $collector) {
            // 70% chance assigned, 30% chance blank — apne hisaab se ratio adjust kar lena
            $accountantId = (rand(1, 100) <= 70)
                ? $accountants[array_rand($accountants)]
                : null;

            Collector::create([
                'owner_id' => '1',
                'accountant_id' => $accountantId,
                'name' => $collector['name'],
                'email' => $collector['email'],
                'phone' => $collector['phone'],
                'address' => $collector['address'],
                'password' => Hash::make('password123'),
                'pass' => 'password123',
                'status' => 'active',
            ]);
        }
    }
}