<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskCompletion extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'kid_id',
        'task_id',
        'completed_date',
        'completed_at',
        'created_at',
    ];

    protected $casts = [
        'completed_date' => 'date',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function kid()
    {
        return $this->belongsTo(Kid::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
