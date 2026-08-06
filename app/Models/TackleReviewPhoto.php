<?php

namespace App\Models;

use App\Support\Uploads;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TackleReviewPhoto extends Model
{
    protected $fillable = [
        'tackle_review_id',
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
        static::deleting(function (TackleReviewPhoto $photo): void {
            Uploads::delete($photo->image_path);
        });
    }

    public function review(): BelongsTo
    {
        return $this->belongsTo(TackleReview::class, 'tackle_review_id');
    }

    public function url(): string
    {
        return Uploads::url($this->image_path);
    }
}
