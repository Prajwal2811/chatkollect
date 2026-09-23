<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappTemplateParameter extends Model
{
    use HasFactory;

    protected $table = 'rms_whatsapp_template_parameters';
    protected $fillable = [
        'owner_id',
        'num',
        'label',
        'is_custom',
    ];

    protected $casts = [
        'is_custom' => 'boolean',
        'num'       => 'integer',
    ];

    public function scopeForOwner($query, $ownerId)
    {
        return $query->where('owner_id', $ownerId);
    }
}