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
        Schema::create('rms_followups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('ledger_id')->index();

            $table->string('type', 60)->nullable();          // Follow Up-Balances/%Targets, Follow Up-Due ...
            $table->enum('action', [
                'allocate_tele_call','call','whatsapp',
                'physical_visit','escalation','no_action'
            ])->nullable();
            $table->enum('frequency', ['daily','weekly','monthly'])->nullable();
            $table->string('template', 30)->nullable();      // template_1 ... template_5

            $table->enum('status', [
                'Not Assign Yet','Already Assigned','Pending','Responded'
            ])->default('Not Assign Yet');

            $table->unsignedBigInteger('assigned_to')->nullable();   // accountant user id
            $table->unsignedBigInteger('assigned_by')->nullable();   // owner user id
            $table->timestamp('allocation_date')->nullable();
            $table->timestamp('response_date')->nullable();

            $table->timestamps();

            $table->unique(['company_id', 'ledger_id']);     // updateOrCreate ke liye
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rms_followups');
    }
};
