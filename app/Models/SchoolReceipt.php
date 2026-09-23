<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class manualReceipt extends Model
{
    protected $table = 'rms_manual_receipts';

    protected $fillable = ['student_id', 'receipt_number', 'amount', 'receipt_date'];
    protected $casts = ['amount' => 'decimal:2', 'receipt_date' => 'date'];
    public function student(): BelongsTo { return $this->belongsTo(manualStudent::class, 'student_id'); }
}
