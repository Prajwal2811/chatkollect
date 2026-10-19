<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherMapping extends Model
{
    protected $table = 'rms_voucher_mappings';
    
    protected $fillable = [
        'voucher_type',
        'mapped_to',
        'company'
    ];
}
