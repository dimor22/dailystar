<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'timezone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
    ];

    public function kids()
    {
        return $this->hasMany(Kid::class, 'parent_id');
    }

    public function pointsStoreItems()
    {
        return $this->hasMany(PointsStoreItem::class, 'parent_id');
    }

    public function starRewards()
    {
        return $this->hasMany(StarReward::class, 'parent_id');
    }

    public function streakBonuses()
    {
        return $this->hasMany(StreakBonus::class, 'parent_id');
    }
}
