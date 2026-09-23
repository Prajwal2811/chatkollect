<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class manualStudent extends Model
{
    protected $table = 'rms_manual_students';

    protected $fillable = ['owner_id', 'name', 'email', 'phone', 'address', 'course', 'section', 'roll_number', 'status'];

    public function owner(): BelongsTo { return $this->belongsTo(Owner::class); }
    public function sales(): HasMany { return $this->hasMany(manualSale::class, 'student_id'); }
    public function receipts(): HasMany { return $this->hasMany(manualReceipt::class, 'student_id'); }
}
