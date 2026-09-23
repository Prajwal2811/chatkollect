<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LedgerCollector extends Model
{
    use HasFactory;


    protected $table = 'rms_ledger_collectors';

    protected $fillable = [
        'company_id',
        'ledger_id',
        'ledger_under',
        'owner_id',
        'accountant_id',
        'collector_id'
    ];

    public function collector()
    {
        return $this->belongsTo(Collector::class, 'collector_id');
    }

    public function owner()
    {
        return $this->belongsTo(Owner::class, 'owner_id');
    }

     public function accountant()
    {
        return $this->belongsTo(Accountant::class, 'accountant_id');
    }
}
