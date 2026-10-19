<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Accountant extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'rms_accountants';

    protected $fillable = [
        'owner_id',
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
        'pass',
        'remember_token',
    ];

    public function collectors()
    {
        return $this->hasMany(Collector::class, 'accountant_id');
    }

    public function owner()
    {
        return $this->belongsTo(Owner::class, 'owner_id');
    }
}