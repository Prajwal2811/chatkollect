<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rms_whatsapp_template_parameters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_id')->index();
            $table->unsignedInteger('num');
            $table->string('label');
            $table->boolean('is_custom')->default(true);
            $table->timestamps();
            $table->unique(['owner_id', 'num']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rms_whatsapp_template_parameters');
    }
};