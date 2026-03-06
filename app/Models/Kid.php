<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Kid extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'name',
        'avatar',
        'color',
        'pin',
        'share_code',
        'points',
    ];

    protected $hidden = [
        'pin',
    ];

    protected static function booted(): void
    {
        static::creating(function (Kid $kid) {
            if (! $kid->share_code) {
                $kid->share_code = self::generateShareCode();
            }
        });
    }

    public static function generateShareCode(): string
    {
        do {
            $code = Str::upper(Str::random(8));
        } while (self::query()->where('share_code', $code)->exists());

        return $code;
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'kid_tasks')
            ->withPivot(['id', 'order', 'active', 'created_at'])
            ->wherePivot('active', true)
            ->orderBy('kid_tasks.order');
    }

    public function taskCompletions()
    {
        return $this->hasMany(TaskCompletion::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function streak()
    {
        return $this->hasOne(Streak::class);
    }
}
