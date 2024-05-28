<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceOut extends Model
{
    use HasFactory;
    protected $table = 'attendances_out';
    protected $fillable = [
        'att_id',
        'user_id',
        'att_group_schedule_id',
        'location',
        'lat',
        'lng',
        'time',
        'difference',
        'photo',
        'status',
    ];

    protected $appends = [
        'location',
    ];

    public function location(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => json_encode([
                'lat' => (float) $attributes['lat'],
                'lng' => (float) $attributes['lng'],
            ]),
            set: fn ($value) => [
                'lat' => $value['lat'],
                'lng' => $value['lng'],
            ],
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }
    public function masuk(): BelongsTo
    {
        return $this->belongsTo(\App\Models\AttendanceIn::class, 'att_id', 'id');
    }
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(\App\Models\UserAttGroupSchedule::class, 'att_group_schedule_id', 'id');
    }
}
