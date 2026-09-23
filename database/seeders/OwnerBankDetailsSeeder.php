<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Owner;
use App\Models\OwnerBankDetail;

class OwnerBankDetailsSeeder extends Seeder
{
    public function run(): void
    {
        $tallyOwner = Owner::where('email', 'tally@test.com')->first();

        if ($tallyOwner) {
            OwnerBankDetail::create([
                'owner_id' => $tallyOwner->id,
                'account_holder_name' => 'Tally Owner',
                'bank_name' => 'HDFC Bank',
                'account_number' => '50100123456789',
                'ifsc_code' => 'HDFC0001234',
                'branch_name' => 'Pune Branch',
                'account_type' => 'Savings',
                'status' => true,
            ]);
        }

        $manualOwner = Owner::where('email', 'manual@test.com')->first();

        if ($manualOwner) {
            OwnerBankDetail::create([
                'owner_id' => $manualOwner->id,
                'account_holder_name' => 'Manual Owner',
                'bank_name' => 'ICICI Bank',
                'account_number' => '123456789012',
                'ifsc_code' => 'ICIC0001234',
                'branch_name' => 'Nagpur Branch',
                'account_type' => 'Current',
                'status' => true,
            ]);
        }
    }
}