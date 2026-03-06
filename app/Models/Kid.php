<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kid extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'name',
        'avatar',
        'color',
        'pin',
        'points',
    ];

    protected $hidden = [
        'pin',
    ];

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
