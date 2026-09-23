<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rms_collectors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('rms_owners')->cascadeOnDelete();
            $table->unsignedBigInteger('accountant_id')->nullable();
            // $table->enum('business_type', ['tally', 'manual']);
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->unique();
            $table->string('address');
            $table->string('password');
            $table->string('pass')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->rememberToken();
            $table->timestamps();
            $table->foreign('accountant_id')->references('id')->on('rms_accountants')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rms_collectors');
    }
};