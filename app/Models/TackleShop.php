<?php

namespace App\Models;

use App\Mail\TackleShopClaimInvite;
use App\Support\Uploads;
use Database\Factories\TackleShopFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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
        'contact_email',
        'invite_sent_at',
        'location_type',
        'is_featured',
        'sort_order',
        'is_published',
        'manager_id',
        'manager_verified',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'manager_verified' => 'boolean',
            'sort_order' => 'integer',
            'invite_sent_at' => 'datetime',
        ];
    }

    public function markInviteSent(): void
    {
        $this->forceFill(['invite_sent_at' => now()])->save();
    }

    public function sendClaimInvite(): bool
    {
        if (blank($this->contact_email)) {
            return false;
        }

        Mail::to($this->contact_email, $this->name)
            ->send(new TackleShopClaimInvite($this));

        $this->markInviteSent();

        return true;
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

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(TackleShopClaim::class);
    }

    public function editRequests(): HasMany
    {
        return $this->hasMany(TackleShopEditRequest::class);
    }

    public function isManagedBy(User $user): bool
    {
        return $this->manager_id === $user->id;
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

        return Uploads::url($this->logo_path);
    }
}
