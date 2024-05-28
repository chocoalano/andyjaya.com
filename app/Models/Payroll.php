<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payroll extends Model
{
    use HasFactory;
    protected $table = 'payrolls';
    protected $fillable = [
        'user_id',
        'total_schedule',
        'total_present',
        'total_late',
        'total_unlate',
        'total_early',
        'subtotal_payroll',
        'total_payroll',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function component(): HasMany
    {
        return $this->hasMany(PayrollComponent::class, 'payroll_id', 'id');
    }
}
