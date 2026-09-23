<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('rms_tally_vouchers', function (Blueprint $table) {

            $table->id();
            $table->string('master_id')->nullable();
            $table->string('unique_voucher_id')->nullable();             // UNIQUE COMPANY ID attribute      
            $table->foreignId('owner_id')->constrained('rms_owners')->cascadeOnDelete();
            $table->foreignId('tally_company_id')->constrained('rms_tally_companies')->cascadeOnDelete();
            $table->foreignId('ledger_id')->constrained('rms_tally_ledgers')->cascadeOnDelete();
            $table->date('date')->nullable();
            $table->string('voucher_number')->nullable();
            $table->string('voucher_type')->nullable();
            $table->string('party_ledger_name')->nullable();
            $table->decimal('amount', 15, 2)->default(0)->nullable();
            $table->string('credit_period')->nullable();
            $table->date('due_date')->nullable();
            $table->string('credit_period_source')->nullable();
            $table->timestamps();

            // Custom short index names
            $table->index(
                ['owner_id', 'tally_company_id', 'voucher_type'],
                'voucher_type_idx'
            );

            $table->index(
                ['owner_id', 'tally_company_id', 'date'],
                'voucher_date_idx'
            );

            $table->index(
                ['owner_id', 'tally_company_id', 'party_ledger_name'],
                'party_ledger_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('rms_tally_vouchers');
    }
};