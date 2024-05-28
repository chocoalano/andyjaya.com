<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserAttGroup extends Model
{
    use HasFactory;
    protected $table = 'user_att_groups';
    protected $fillable = [
        'user_id',
        'name',
    ];

    public function leader(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    public function userTeams(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_att_group_relations', 'user_att_group_id', 'user_id');
    }
}
