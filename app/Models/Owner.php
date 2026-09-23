<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Owner extends Authenticatable
{
    use HasFactory;

    protected $table = 'rms_owners';

    // Business type constants — use these instead of raw strings everywhere
    const TYPE_TALLY  = 'tally';
    const TYPE_manual = 'manual';

    protected $fillable = [
        'owner_name',
        'email',
        'phone',
        'business_name',
        'business_type',
        'address',
        'password',
        'pass',
        'pass',
        'status',
        'is_subscribed',
        'subscription_expiry',
    ];

    protected $hidden = [
        'password',
        'pass',
    ];

    protected $casts = [
        'business_type' => 'string',
    ];


    public function tallyConnections()
    {
        return $this->hasMany(TallyConnection::class);
    }
}