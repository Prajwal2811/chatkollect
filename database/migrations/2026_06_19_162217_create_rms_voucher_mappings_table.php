<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rms_voucher_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_type');
            $table->string('mapped_to')->nullable();
            $table->string('company');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rms_voucher_mappings');
    }
};