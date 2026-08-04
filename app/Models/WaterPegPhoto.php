<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class WaterPegPhoto extends Model
{
    protected $fillable = [
        'water_peg_id',
        'image_path',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (WaterPegPhoto $photo): void {
            if (str_starts_with($photo->image_path, 'images/')) {
                return;
            }

            Storage::disk('public')->delete($photo->image_path);
        });
    }

    public function waterPeg(): BelongsTo
    {
        return $this->belongsTo(WaterPeg::class);
    }

    public function url(): string
    {
        if (str_starts_with($this->image_path, 'images/')) {
            return asset($this->image_path);
        }

        return Storage::disk('public')->url($this->image_path);
    }
}
