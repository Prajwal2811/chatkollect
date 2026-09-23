<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappTemplate extends Model
{
    use HasFactory;

    protected $table = 'rms_whatsapp_templates';
    
    protected $fillable = [
        'owner_id',
        'followup_type',
        'name',
        'tone',
        'language',
        'message',
    ];

    public function scopeForOwner($query, $ownerId)
    {
        return $query->where('owner_id', $ownerId);
    }
}