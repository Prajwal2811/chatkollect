<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TallySolution extends Model
{
    use HasFactory;

    protected $table = 'rms_tally_solutions';

    protected $fillable = [
        'owner_id',
        'solution'
        ];
}
