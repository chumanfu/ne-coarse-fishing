<?php

namespace App\Models;

use Database\Factories\TackleShopFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Support\Uploads;

class TackleShop extends Model
{
    /** @use HasFactory<TackleShopFactory> */
    use HasFactory;

    public const LOCATION_TYPES = [
        'local' => 'North East shop',
        'online' => 'Online retailer',
        'hybrid' => 'Shop & online',
    ];

    protected $fillable = [
        'name',
        'slug',
        'url',
        'logo_path',
        'overview',
        'town',
        'address',
        'latitude',
        'longitude',
        'phone',
        'location_type',
        'is_featured',
        'sort_order',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeMappable(Builder $query): Builder
    {
        return $query
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');
    }

    public function hasMapCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    protected static function booted(): void
    {
        static::creating(function (TackleShop $shop): void {
            if (blank($shop->slug)) {
                $shop->slug = static::uniqueSlug($shop->name);
            }
        });
    }

    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'tackle-shop';
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

    public function locationTypeLabel(): string
    {
        return self::LOCATION_TYPES[$this->location_type] ?? ucfirst((string) $this->location_type);
    }

    public function websiteHost(): ?string
    {
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

        return Uploads::url($this->logo_path);
    }
}
