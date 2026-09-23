<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwnerBankDetail extends Model
{
    protected $table = 'rms_owner_bank_details';

    protected $fillable = [
        'owner_id',
        'account_holder_name',
        'bank_name',
        'account_number',
        'ifsc_code',
        'upi',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }
}