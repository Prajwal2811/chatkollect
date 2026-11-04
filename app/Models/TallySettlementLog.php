<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TallySettlementLog extends Model
{
    protected $table = 'rms_tally_settlement_logs';

    protected $fillable = [
        'voucherId',
        'master_id',
        'unique_voucher_id',
        'owner_id',
        'tally_company_id',
        'ledger_id',
        'date',
        'voucher_number',
        'voucher_type',
        'credit_period',
        'due_date',
        'interestDue',
        'settlement_status',
        'settlement_amount',
    ];

    protected $casts = [
        'settlement_amount' => 'decimal:2',
        'interestDue'       => 'decimal:2',
    ];

    // Relations (logs dikhane me kaam aayenge)
    public function voucher()
    {
        return $this->belongsTo(TallyVoucher::class, 'voucherId');
    }

    public function ledger()
    {
        return $this->belongsTo(TallyLedger::class, 'ledger_id');
    }

    public function company()
    {
        return $this->belongsTo(TallyCompany::class, 'tally_company_id');
    }
}