<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TallyResponse extends Model
{
    use HasFactory;

    protected $table = 'rms_tally_responses';

    protected $fillable = [
        'owner_id',
        'response'
        ];
}
