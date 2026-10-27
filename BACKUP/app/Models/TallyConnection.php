<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TallyConnection extends Model
{
    protected $table = "rms_tally_connections";

    protected $fillable = ['owner_id', 'tailscale_ip', 'port', 'status'];


     public function getTallyUrlAttribute(): string
    {
        return "http://{$this->tailscale_ip}:{$this->port}";
    }
    
    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }
}
