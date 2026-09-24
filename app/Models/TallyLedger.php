<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class TallyLedger extends Model
{
    protected $table = 'rms_tally_ledgers';
    
    protected $fillable = [
        'master_id',
        'unique_ledger_id',
        'owner_id',
        'tally_company_id',
        'ledger_name',
        'ledger_email',
        'ledger_mobile_number',
        'ledger_mobile_number_source',
        'parent',
        'opening_balance',
        'closing_balance',
        'balance_synced_at',
        'credit_period_source',
        'credit_period',
        'interest_rate_source',
        'interest_rate',
        'interest_style',
        'assigned_collector',
        'balance_limit',
        'overlimit',
        'mark',
        'red_reason',
        'maintain_bill_by_bill',          // ← add if missing
        'activate_interest_calculation',  // ← add if missing
        
    ];

    protected $casts = [
        'opening_balance'   => 'decimal:2',
        'closing_balance'   => 'decimal:2',
        'balance_synced_at' => 'datetime',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    public function tallyCompany(): BelongsTo
    {
        return $this->belongsTo(TallyCompany::class);
    }

    /**
     * Sirf Sundry Debtors ledgers
     */
    public function scopeDebtors(Builder $query): Builder
    {
        return $query->where('parent', 'Sundry Debtors');
    }

    /**
     * Sirf Sundry Creditors ledgers
     */
    public function scopeCreditors(Builder $query): Builder
    {
        return $query->where('parent', 'Sundry Creditors');
    }

    /**
     * Ek specific company ke ledgers
     */
    public function scopeForCompany(Builder $query, string $company): Builder
    {
        return $query->where('company', $company);
    }
}