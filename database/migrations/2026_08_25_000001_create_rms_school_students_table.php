<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rms_manual_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('rms_owners')->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('course')->nullable();
            $table->string('section')->nullable();
            $table->string('roll_number')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->unique(['owner_id', 'roll_number']);
        });
    }
    public function down(): void { Schema::dropIfExists('rms_manual_students'); }
};
