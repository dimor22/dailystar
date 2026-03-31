<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StarReward extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'title',
        'description',
        'image_path',
        'active',
        'order_number',
        'stars_needed',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }
}
