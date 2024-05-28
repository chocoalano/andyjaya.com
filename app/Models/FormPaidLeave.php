<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormPaidLeave extends Model
{
    use HasFactory;
    protected $table = 'form_paid_leaves';
    protected $fillable = [
        'user_id',
        'from_date',
        'to_date',
        'notes',
        'status_line',
        'status_mngr',
        'status_hr',
    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
