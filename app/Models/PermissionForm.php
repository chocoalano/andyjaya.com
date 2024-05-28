<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermissionForm extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'request_type',
        'from_date',
        'to_date',
        'notes',
        'file',
        'status_hr',
    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
