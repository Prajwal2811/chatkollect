<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FollowupLog extends Model
{
    protected $table = 'rms_followup_logs';

    protected $fillable = [
        'followup_id',
        'company_id',
        'ledger_id',
        'type',
        'action',
        'frequency',
        'template',
        'allocation_date',
        'response_date',
        'response',
        'solution',
        'admin_solution',
        'responded_by',
    ];

    protected $casts = [
        'allocation_date' => 'datetime',
        'response_date'   => 'datetime',
    ];

    /**
     * Relationships
     */
    public function followup()
    {
        return $this->belongsTo(Followup::class, 'followup_id');
    }

    public function company()
    {
        return $this->belongsTo(TallyCompany::class, 'company_id');
    }

    public function ledger()
    {
        return $this->belongsTo(TallyLedger::class, 'ledger_id');
    }

    public function responder()
    {
        return $this->belongsTo(Accountant::class, 'responded_by');
    }
}