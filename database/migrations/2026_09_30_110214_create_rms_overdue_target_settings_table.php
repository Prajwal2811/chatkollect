<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rms_overdue_target_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_id')->index();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedTinyInteger('bucket_month');              // 1 to 6
            $table->decimal('receivable_target', 5, 2)->nullable();
            $table->decimal('payable_target', 5, 2)->nullable();
            $table->decimal('diff_target', 6, 2)->nullable();         // receivable - payable (negative bhi ho sakta hai)
            $table->timestamps();
            $table->unique(
                ['owner_id', 'company_id', 'bucket_month'],
                'rms_overdue_owner_company_bucket_uq'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rms_overdue_target_settings');
    }
};