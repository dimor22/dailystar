<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'title',
        'description',
        'image_path',
        'points',
        'category',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function kids()
    {
        return $this->belongsToMany(Kid::class, 'kid_tasks')
            ->withPivot(['id', 'order', 'active', 'days_of_week', 'created_at']);
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }
}
