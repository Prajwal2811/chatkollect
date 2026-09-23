<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rms_whatsapp_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_id')->index();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('followup_type');
            $table->string('name');
            $table->string('tone')->default('Polite');
            $table->string('language')->default('English');
            $table->longText('message');
            $table->timestamps();
            $table->index(['owner_id', 'followup_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rms_whatsapp_templates');
    }
};