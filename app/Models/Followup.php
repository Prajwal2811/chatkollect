<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Followup extends Model
{
    protected $table = 'rms_followups';

    protected $fillable = [
        'company_id',
        'ledger_id',
        'type',
        'action',
        'frequency',
        'template',
        'status',
        'assigned_to',
        'assigned_by',
        'allocation_date',
        'response_date',
    ];

    protected $casts = [
        'allocation_date' => 'datetime',
        'response_date'   => 'datetime',
    ];

    /**
     * Relationships
     * (apne project ke actual model names ke hisaab se adjust kar lena)
     */
    public function company()
    {
        return $this->belongsTo(TallyCompany::class, 'company_id');
    }

    public function ledger()
    {
        return $this->belongsTo(TallyLedger::class, 'ledger_id');
    }

    public function assignee()
    {
        return $this->belongsTo(Accountant::class, 'assigned_to');
    }

    public function assigner()
    {
        return $this->belongsTo(Owner::class, 'assigned_by');
    }
}