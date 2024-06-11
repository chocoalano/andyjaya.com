<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MoneyLoan extends Model
{
    use HasFactory;
    protected $table = 'money_loans';
    protected $fillable = [
        'user_id',
        'status_hr',
        'notes',
        'total_loan',
        'status'
    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
