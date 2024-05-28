<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AttArea extends Model
{
    use HasFactory;
    protected $table = 'att_areas';
    protected $fillable = [
        'name',
        'address',
        'location',
        'lat',
        'lng',
        'radius',
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
    public function user(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_att_areas', 'att_area_id', 'user_id');
    }
}
