<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'in',
        'out',
    ];
}
