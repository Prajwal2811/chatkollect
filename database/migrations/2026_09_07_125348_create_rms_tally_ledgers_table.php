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
        Schema::create('rms_tally_ledgers', function (Blueprint $table) {
            $table->id();
            $table->string('master_id')->nullable();
            $table->string('unique_ledger_id')->nullable();             // UNIQUE COMPANY ID attribute
            $table->foreignId('owner_id')->constrained('rms_owners')->cascadeOnDelete();
            $table->foreignId('tally_company_id')->constrained('rms_tally_companies')->cascadeOnDelete();
            $table->string('ledger_name');                 // LEDGER NAME attribute
            $table->string('ledger_email')->nullable();                 // LEDGER NAME attribute
            $table->string('ledger_mobile_number')->nullable();                 // LEDGER NAME attribute
            $table->string('parent')->nullable();         // PARENT (under: Sundry Debtors/Creditors etc)
            $table->decimal('opening_balance', 15, 2)->default(0); // OPENINGBALANCE
            $table->decimal('closing_balance', 15, 2)->default(0); // CLOSINGBALANCE
            $table->string('credit_period')->nullable();
            $table->string('credit_period_source')->nullable();
            $table->string('interest_rate')->nullable();
            $table->string('interest_style')->nullable();
            $table->string('interest_rate_source')->nullable();
            $table->string('maintain_bill_by_bill')->nullable();
            $table->string('activate_interest_calculation')->nullable();
            $table->foreignId('assigned_collector')->nullable();
            $table->timestamp('balance_synced_at')->nullable();    // last time balance refresh hua
            $table->decimal('balance_limit', 15, 2)->nullable();
            $table->string('overlimit')->nullable();
            $table->enum('mark', ['red', 'green', 'unmarked'])->default('unmarked');
            $table->string('red_reason')->nullable();
            $table->timestamps();
            $table->index(['owner_id', 'tally_company_id', 'parent']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rms_tally_ledgers');
    }
};
