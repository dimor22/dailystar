<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KidTask extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'kid_id',
        'task_id',
        'order',
        'active',
        'created_at',
    ];

    protected $casts = [
        'active' => 'boolean',
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
