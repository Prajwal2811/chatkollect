<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rms_tally_settlement_logs', function (Blueprint $table) {
            $table->id();

            // Tally voucher identifiers
            $table->string('voucherId')->nullable();
            $table->string('master_id')->nullable();
            $table->string('unique_voucher_id')->nullable();

            // Ownership / relations (add ->constrained() once you confirm the table names)
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->unsignedBigInteger('tally_company_id')->nullable();
            $table->unsignedBigInteger('ledger_id')->nullable();

            // Voucher details
            $table->date('date')->nullable();
            $table->string('voucher_number')->nullable();
            $table->string('voucher_type')->nullable();

            // Credit / interest
            $table->string('credit_period')->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('interestDue', 15, 2)->default(0);

            // Settlement
            $table->string('settlement_status')->default('pending'); // pending | settled
            $table->decimal('settlement_amount', 15, 2)->default(0);

            $table->timestamps(); // created_at, updated_at

            // Indexes for common lookups
            $table->index('voucherId');
            $table->index('unique_voucher_id');
            $table->index(['owner_id', 'tally_company_id', 'ledger_id'], 'settlement_logs_owner_company_ledger_index');
            $table->index('settlement_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rms_tally_settlement_logs');
    }
};