<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rms_manual_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('rms_manual_students')->cascadeOnDelete();
            $table->string('receipt_number');
            $table->decimal('amount', 12, 2);
            $table->date('receipt_date');
            $table->timestamps();
            $table->unique(['student_id', 'receipt_number']);
        });
    }
    public function down(): void { Schema::dropIfExists('rms_manual_receipts'); }
};
