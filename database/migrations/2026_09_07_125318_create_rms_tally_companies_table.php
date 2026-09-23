<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rms_tally_companies', function (Blueprint $table) {
            $table->id();
            $table->string('unique_company_id')->nullable();             // UNIQUE COMPANY ID attribute
            $table->foreignId('owner_id')->constrained('rms_owners')->cascadeOnDelete();
            $table->string('company_name');              // COMPANY NAME attribute
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->unique(['owner_id', 'company_name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rms_tally_companies');
    }
};
