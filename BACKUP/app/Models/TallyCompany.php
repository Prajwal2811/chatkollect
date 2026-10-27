<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TallyCompany extends Model
{
    protected $table = 'rms_tally_companies';
    protected $fillable = [
        'unique_company_id',
        'owner_id',
        'company_name',
        'last_synced_at',
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    public function ledgers(): HasMany
    {
        return $this->hasMany(TallyLedger::class);
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(TallyVoucher::class);
    }

}