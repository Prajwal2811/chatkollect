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
        Schema::create('rms_ledger_collectors', function (Blueprint $table) {
            $table->id();
            $table->string('company_id')->nullable();
            $table->string('ledger_id');
            $table->string('ledger_under')->nullable();
            $table->unsignedBigInteger('owner_id');
            $table->unsignedBigInteger('accountant_id');
            $table->unsignedBigInteger('collector_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rms_ledger_collectors');
    }
};
