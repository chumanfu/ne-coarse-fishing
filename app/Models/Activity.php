<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Activity extends Model
{
    public const TYPE_VENUE = 'venue_added';

    public const TYPE_VENUE_SUBMITTED = 'venue_submitted';

    public const TYPE_SESSION = 'session_logged';

    public const TYPE_TACTIC = 'tactic_shared';

    public const TYPE_CLUB = 'club_added';

    public const TYPE_TACKLE_SHOP = 'tackle_shop_added';

    public const TYPE_USER_REGISTERED = 'user_registered';

    public const TYPE_VENUE_CLAIM = 'venue_claim';

    public const TYPE_VENUE_EDIT_REQUEST = 'venue_edit_request';

    public const TYPE_CLUB_CLAIM = 'club_claim';

    public const TYPE_CLUB_EDIT_REQUEST = 'club_edit_request';

    public const TYPE_SHOP_CLAIM = 'tackle_shop_claim';

    public const TYPE_SHOP_EDIT_REQUEST = 'tackle_shop_edit_request';

    public const TYPE_PEG = 'peg_added';

    public const TYPE_MATCH_REPORT = 'match_report';

    public const TYPE_ANNOUNCEMENT = 'announcement';

    public const TYPE_TACKLE_REVIEW = 'tackle_review';

    public const TYPE_MESSAGE = 'message';

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
        return self::typeOptions()[$this->type] ?? 'Update';
    }

    /**
     * Activities safe to show on the public site (home + /activity).
     * Sign-ups remain available in the Filament admin activity log.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<static>  $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopePublicFeed($query)
    {
        return $query->where('type', '!=', self::TYPE_USER_REGISTERED);
    }

    /**
     * @return array<string, string>
     */
    public static function typeOptions(): array
    {
        return [
            self::TYPE_USER_REGISTERED => 'Sign-up',
            self::TYPE_VENUE_SUBMITTED => 'Venue submitted',
            self::TYPE_VENUE => 'Venue approved',
            self::TYPE_SESSION => 'Session',
            self::TYPE_TACTIC => 'Tactic',
            self::TYPE_PEG => 'Peg',
            self::TYPE_VENUE_CLAIM => 'Venue claim',
            self::TYPE_VENUE_EDIT_REQUEST => 'Venue edit suggestion',
            self::TYPE_CLUB => 'Club',
            self::TYPE_CLUB_CLAIM => 'Club claim',
            self::TYPE_CLUB_EDIT_REQUEST => 'Club edit suggestion',
            self::TYPE_TACKLE_SHOP => 'Tackle shop',
            self::TYPE_SHOP_CLAIM => 'Shop claim',
            self::TYPE_SHOP_EDIT_REQUEST => 'Shop edit suggestion',
            self::TYPE_MATCH_REPORT => 'Match report',
            self::TYPE_ANNOUNCEMENT => 'Announcement',
            self::TYPE_TACKLE_REVIEW => 'Tackle review',
            self::TYPE_MESSAGE => 'Message',
        ];
    }
}
