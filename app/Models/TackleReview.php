<?php

namespace App\Models;

use App\Support\Uploads;
use Database\Factories\TackleReviewFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TackleReview extends Model
{
    /** @use HasFactory<TackleReviewFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'brand',
        'rating',
        'body',
        'purchase_url',
        'is_published',
        'featured_on_home',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_published' => 'boolean',
            'featured_on_home' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(TackleReviewPhoto::class)->orderBy('sort_order')->orderBy('id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->published()->where('featured_on_home', true);
    }

    public function displayName(): string
    {
        return filled($this->brand)
            ? trim($this->brand.' '.$this->title)
            : $this->title;
    }
}
