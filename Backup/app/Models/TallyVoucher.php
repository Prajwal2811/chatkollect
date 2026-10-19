<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class TallyVoucher extends Model
{
    protected $table = 'rms_tally_vouchers';

    protected $fillable = [
        'unique_voucher_id',
        'owner_id',
        'tally_company_id',
        'ledger_id',
        'date',
        'voucher_number',
        'voucher_type',
        'party_ledger_name',
        'amount',
        'credit_period',
        'master_id',
        'credit_period_source',
        'due_date',
    ];

    protected $casts = [
        'date'         => 'date',
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
     * Cancelled/optional vouchers ko exclude karo (valid vouchers hi lo)
     */

    public function scopeForCompany(Builder $query, string $company): Builder
    {
        return $query->where('company', $company);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('voucher_type', $type);
    }

    public function scopeBetweenDates(Builder $query, $from, $to): Builder
    {
        return $query->whereBetween('date', [$from, $to]);
    }

}