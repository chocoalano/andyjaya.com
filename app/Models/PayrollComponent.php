<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollComponent extends Model
{
    use HasFactory;
    protected $table = 'payroll_components';
    protected $guarded = [];

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class, 'id', 'payroll_id');
    }
}
