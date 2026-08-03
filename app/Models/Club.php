<?php

namespace App\Models;

use Database\Factories\ClubFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Club extends Model
{
    /** @use HasFactory<ClubFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'url',
        'logo_path',
        'overview',
        'town',
        'address',
        'phone',
        'is_featured',
        'sort_order',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Club $club): void {
            if (blank($club->slug)) {
                $club->slug = static::uniqueSlug($club->name);
            }
        });
    }

    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'club';
        $slug = $base;
        $i = 1;

        while (
            static::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function venues(): BelongsToMany
    {
        return $this->belongsToMany(Venue::class)->withTimestamps();
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function websiteHost(): ?string
    {
        if (blank($this->url)) {
            return null;
        }

        $host = parse_url($this->url, PHP_URL_HOST);

        return $host ? Str::of($host)->after('www.')->toString() : null;
    }

    public function logoUrl(): ?string
    {
        if (blank($this->logo_path)) {
            return null;
        }

        if (str_starts_with($this->logo_path, 'images/')) {
            return asset($this->logo_path);
        }

        return Storage::disk('public')->url($this->logo_path);
    }
}
