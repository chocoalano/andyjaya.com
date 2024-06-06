<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Role;
use Filament\Models\Contracts\HasAvatar;

class User extends Authenticatable implements HasAvatar
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'departemen_id',
        'position_id',
        'level_id',
        'nik',
        'name',
        'email',
        'password',
        'is_suspended',
        'work_location',
        'saldo_cuti',
        'join_at',
        'loan_limit',
        'total_salary',
        'approval_line',
        'approval_manager',
        'approval_hr',
        'approval_owner',
        'approval_fat',
        'image',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return url("storage/".$this->image);
    }

    public function departement(): HasOne
    {
        return $this->hasOne(\App\Models\Departement::class, 'id', 'departemen_id');
    }
    public function position(): HasOne
    {
        return $this->hasOne(\App\Models\Position::class, 'id', 'position_id');
    }
    public function level(): HasOne
    {
        return $this->hasOne(\App\Models\Level::class, 'id', 'level_id');
    }
    public function rolefind(){
        return $this->belongsToMany(Role::class, 'model_has_roles', 'model_id', 'role_id');
    }
}
