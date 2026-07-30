<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Activity extends Model
{
    public const TYPE_VENUE = 'venue_added';

    public const TYPE_SESSION = 'session_logged';

    public const TYPE_TACTIC = 'tactic_shared';

    public const TYPE_CLUB = 'club_added';

    public const TYPE_TACKLE_SHOP = 'tackle_shop_added';

    protected $fillable = [
        'type',
        'subject_type',
        'subject_id',
        'user_id',
        'title',
        'summary',
        'url',
    ];

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_VENUE => 'Venue',
            self::TYPE_SESSION => 'Session',
            self::TYPE_TACTIC => 'Tactic',
            self::TYPE_CLUB => 'Club',
            self::TYPE_TACKLE_SHOP => 'Tackle shop',
            default => 'Update',
        };
    }
}
