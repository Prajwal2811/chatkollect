<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OverdueTargetSettingsSeeder extends Seeder
{
    /**
     * Seed the default overdue target settings.
     * created_at and updated_at are intentionally omitted.
     */
    public function run(): void
    {
        DB::table('rms_overdue_target_settings')->insert([
            [
                'owner_id' => 1,
                'company_id' => 1,
                'bucket_month' => 1,
                'receivable_target' => 50.00,
                'payable_target' => 40.00,
                'diff_target' => 10.00,
            ],
            [
                'owner_id' => 1,
                'company_id' => 1,
                'bucket_month' => 2,
                'receivable_target' => 40.00,
                'payable_target' => 30.00,
                'diff_target' => 10.00,
            ],
            [
                'owner_id' => 1,
                'company_id' => 1,
                'bucket_month' => 3,
                'receivable_target' => 30.00,
                'payable_target' => 20.00,
                'diff_target' => 10.00,
            ],
            [
                'owner_id' => 1,
                'company_id' => 1,
                'bucket_month' => 4,
                'receivable_target' => 18.00,
                'payable_target' => 10.00,
                'diff_target' => 8.00,
            ],
            [
                'owner_id' => 1,
                'company_id' => 1,
                'bucket_month' => 5,
                'receivable_target' => 10.00,
                'payable_target' => 5.00,
                'diff_target' => 5.00,
            ],
            [
                'owner_id' => 1,
                'company_id' => 1,
                'bucket_month' => 6,
                'receivable_target' => 0.00,
                'payable_target' => 0.00,
                'diff_target' => 0.00,
            ],
        ]);
    }
}
