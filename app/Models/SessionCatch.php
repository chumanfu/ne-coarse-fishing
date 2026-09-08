<?php

namespace App\Models;

use App\Support\Weight;
use Database\Factories\SessionCatchFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionCatch extends Model
{
    /** @use HasFactory<SessionCatchFactory> */
    use HasFactory;

    public const TYPE_INDIVIDUAL = 'individual';

    public const TYPE_BAG = 'bag';

    public const TYPES = [self::TYPE_INDIVIDUAL, self::TYPE_BAG];

    protected $fillable = [
        'fishing_session_id',
        'species_id',
        'weight_g',
        'entry_type',
        'bait',
        'quantity',
        'is_notable',
        'entered_unit',
    ];

    protected $attributes = [
        'entry_type' => self::TYPE_INDIVIDUAL,
        'quantity' => 1,
        'is_notable' => false,
        'entered_unit' => Weight::UNIT_LB_OZ,
    ];

    protected function casts(): array
    {
        return [
            'weight_g' => 'integer',
            'quantity' => 'integer',
            'is_notable' => 'boolean',
        ];
    }

    public function fishingSession(): BelongsTo
    {
        return $this->belongsTo(FishingSession::class);
    }

    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }

    public function weight(): ?Weight
    {
        return Weight::fromGrams($this->weight_g);
    }

    public function isBag(): bool
    {
        return $this->entry_type === self::TYPE_BAG;
    }

    /**
     * Weight rendered in the unit it was entered in, unless the viewer asked
     * for a specific one.
     */
    public function weightLabel(?string $unit = null): ?string
    {
        return $this->weight()?->format(Weight::normaliseUnit($unit ?? $this->entered_unit));
    }

    /** "Roach × 40" for a bag, "Common Carp" for a single fish. */
    public function summaryLabel(?string $unit = null): string
    {
        $label = $this->species?->name ?? 'Fish';

        if ($this->isBag() && $this->quantity > 1) {
            $label .= ' × '.$this->quantity;
        }

        if ($weight = $this->weightLabel($unit)) {
            $label .= ' · '.$weight.($this->isBag() && $this->quantity > 1 ? ' total' : '');
        }

        return $label;
    }
}
