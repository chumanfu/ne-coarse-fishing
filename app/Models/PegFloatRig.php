<?php

namespace App\Models;

use Database\Factories\PegFloatRigFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A shotting pattern saved against a peg. The shot list itself is not stored:
 * the calculator regenerates it from the float, depth and chosen pattern.
 */
class PegFloatRig extends Model
{
    /** @use HasFactory<PegFloatRigFactory> */
    use HasFactory;

    public const FLOAT_TYPES = ['pole', 'dibber', 'waggler', 'pellet_waggler', 'stick', 'avon'];

    public const PATTERN_IDS = [
        'strung', 'bulk_droppers', 'olivette', 'shirt_button', 'waggler_locking', 'waggler_drop', 'pellet_waggler',
    ];

    private const FLOAT_TYPE_LABELS = [
        'pole' => 'Pole',
        'dibber' => 'Dibber',
        'waggler' => 'Waggler',
        'pellet_waggler' => 'Pellet waggler',
        'stick' => 'Stick',
        'avon' => 'Avon',
    ];

    private const PATTERN_LABELS = [
        'strung' => 'Strung out',
        'bulk_droppers' => 'Bulk & droppers',
        'olivette' => 'Olivette',
        'shirt_button' => 'Shirt button',
        'waggler_locking' => 'Locking + droppers',
        'waggler_drop' => 'On the drop',
        'pellet_waggler' => 'Pellet waggler',
    ];

    protected $fillable = [
        'water_peg_id',
        'user_id',
        'float_name',
        'float_size',
        'float_type',
        'float_grams',
        'depth',
        'depth_unit',
        'pattern_id',
        'olivette_grams',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'float_grams' => 'float',
            'depth' => 'float',
            'olivette_grams' => 'float',
        ];
    }

    public function peg(): BelongsTo
    {
        return $this->belongsTo(WaterPeg::class, 'water_peg_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function patternLabel(): string
    {
        return self::PATTERN_LABELS[$this->pattern_id] ?? $this->pattern_id;
    }

    public function floatTypeLabel(): string
    {
        return self::FLOAT_TYPE_LABELS[$this->float_type] ?? $this->float_type;
    }

    public function depthLabel(): string
    {
        return rtrim(rtrim(number_format($this->depth, 2, '.', ''), '0'), '.').$this->depth_unit;
    }
}
