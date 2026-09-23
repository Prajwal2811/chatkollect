<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    protected $table = 'rms_admins';
    
    protected $fillable = [
        'name',
        'email',
        'password',
        'pass',
        'status',
    ];

    protected $hidden = [
        'password',
    ];
}