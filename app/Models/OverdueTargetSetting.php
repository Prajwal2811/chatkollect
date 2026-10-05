<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OverdueTargetSetting extends Model
{
    use HasFactory;

    protected $table = 'rms_overdue_target_settings';
    
    protected $fillable = [
        'owner_id',
        'company_id',
        'bucket_month',
        'receivable_target',
        'payable_target',
        'diff_target',
    ];

    protected $casts = [
        'bucket_month'      => 'integer',
        'receivable_target' => 'float',
        'payable_target'    => 'float',
    ];
}
