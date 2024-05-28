<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAttGroupSchedule extends Model
{
    use HasFactory;
    protected $table = 'att_group_schedule';
    protected $fillable = [
        'user_att_group_id',
        'att_time_id',
        'date_work',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(UserAttGroup::class, 'user_att_group_id', 'id');
    }
    public function time(): BelongsTo
    {
        return $this->belongsTo(AttTime::class, 'att_time_id', 'id');
    }
}
