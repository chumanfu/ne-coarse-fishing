<?php

namespace App\Models;

use Database\Factories\SessionPhotoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Support\Uploads;

class SessionPhoto extends Model
{
    /** @use HasFactory<SessionPhotoFactory> */
    use HasFactory;

    protected $fillable = [
        'fishing_session_id',
        'image_path',
    ];

    public function fishingSession(): BelongsTo
    {
        return $this->belongsTo(FishingSession::class);
    }

    public function url(): string
    {
        return Uploads::url($this->image_path);
    }
}
