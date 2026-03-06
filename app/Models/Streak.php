<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Streak extends Model
{
    use HasFactory;

    const CREATED_AT = null;

    protected $fillable = [
        'kid_id',
        'current_streak',
        'longest_streak',
        'last_completed_date',
        'updated_at',
    ];

    protected $casts = [
        'last_completed_date' => 'date',
        'updated_at' => 'datetime',
    ];

    public function kid()
    {
        return $this->belongsTo(Kid::class);
    }
}
