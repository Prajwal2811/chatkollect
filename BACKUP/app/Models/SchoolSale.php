<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class manualSale extends Model
{
    protected $table = 'rms_manual_sales';

    protected $fillable = ['student_id', 'sale_number', 'amount', 'sale_date'];
    protected $casts = ['amount' => 'decimal:2', 'sale_date' => 'date'];
    public function student(): BelongsTo { return $this->belongsTo(manualStudent::class, 'student_id'); }
}
