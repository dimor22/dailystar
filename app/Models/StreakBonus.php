<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StreakBonus extends Model
{
    use HasFactory;

    public const TYPE_NO_BONUS = 0;

    public const TYPE_10_PERCENT_BONUS = 1;

    public const TYPE_20_PERCENT_BONUS = 2;

    public const TYPE_30_PERCENT_BONUS = 3;

    protected $fillable = [
        'parent_id',
        'title',
        'description',
        'image_path',
        'day_target',
        'bonus_type',
    ];

    protected $casts = [
        'day_target' => 'integer',
        'bonus_type' => 'integer',
    ];

    public static function percentageForType(int $bonusType): int
    {
        return match ($bonusType) {
            self::TYPE_10_PERCENT_BONUS => 10,
            self::TYPE_20_PERCENT_BONUS => 20,
            self::TYPE_30_PERCENT_BONUS => 30,
            default => 0,
        };
    }

    public static function keyForType(int $bonusType): string
    {
        return match ($bonusType) {
            self::TYPE_10_PERCENT_BONUS => '10_percent_bonus',
            self::TYPE_20_PERCENT_BONUS => '20_percent_bonus',
            self::TYPE_30_PERCENT_BONUS => '30_percent_bonus',
            default => 'no_bonus',
        };
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }
}
