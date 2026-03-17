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
        'avatar_image_path',
        'avatar_display_mode',
        'color',
        'pin',
        'public_id',
        'points',
    ];

    protected $hidden = [
        'pin',
    ];

    protected static function booted(): void
    {
        static::creating(function (Kid $kid) {
            if (! $kid->public_id) {
                $kid->public_id = self::generatePublicId();
            }
        });
    }

    public static function generatePublicId(): string
    {
        do {
            $publicId = (string) Str::ulid();
        } while (self::query()->where('public_id', $publicId)->exists());

        return $publicId;
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'kid_tasks')
            ->withPivot(['id', 'order', 'active', 'days_of_week', 'created_at'])
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
