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
        Schema::create('rms_followup_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('followup_id')->index();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('ledger_id')->index();

            $table->string('type', 60)->nullable();
            $table->string('action', 30)->nullable();
            $table->string('frequency', 20)->nullable();
            $table->string('template', 30)->nullable();

            $table->timestamp('allocation_date')->nullable();
            $table->timestamp('response_date')->nullable();

            $table->text('response')->nullable();            // accountant ka response
            $table->text('solution')->nullable();            // accountant ka solution
            $table->text('admin_solution')->nullable();      // "Clear Solution (Admin)" column

            $table->unsignedBigInteger('responded_by')->nullable();
            $table->timestamps();

            $table->foreign('followup_id')->references('id')->on('rms_followups')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rms_followup_logs');
    }
};
