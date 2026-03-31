<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointsStoreItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'title',
        'description',
        'points',
        'image_path',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }
}
