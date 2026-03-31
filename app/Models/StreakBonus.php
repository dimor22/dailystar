<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StreakBonus extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'title',
        'description',
        'image_path',
        'day_target',
        'bonus_type',
    ];

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }
}
