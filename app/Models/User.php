<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Plan;
use App\Enums\Role;
use App\Services\PlanGate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Billable, HasFactory, Notifiable;

    /**
     * Resolve this user's active plan via PlanGate.
     */
    public function plan(): Plan
    {
        return app(PlanGate::class)->planFor($this);
    }

    /**
     * Convenience: true when this user has an active Pro subscription.
     */
    public function isPro(): bool
    {
        return $this->plan()->isPro();
    }

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
        'status',
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
        'role'     => Role::class,
    ];

    public function isAdmin(): bool
    {
        return $this->role === Role::Admin;
    }

    public function isEarlyAdopter(): bool
    {
        return $this->role === Role::EarlyAdopter;
    }

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
