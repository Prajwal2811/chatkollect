<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Collector extends Authenticatable
{
    protected $table = 'rms_collectors';

    protected $fillable = [
        'owner_id',
        'accountant_id',
        'name',
        'email',
        'phone',
        'address',
        'password',
        'pass',
        'status',
        'business_type'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function accountant()
    {
        return $this->belongsTo(Accountant::class, 'accountant_id');
    }

    
}