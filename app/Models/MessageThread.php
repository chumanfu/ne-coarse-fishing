<?php

namespace App\Models;

use Database\Factories\MessageThreadFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MessageThread extends Model
{
    /** @use HasFactory<MessageThreadFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subject',
        'contact_name',
        'contact_email',
        'source',
        'status',
        'last_message_at',
        'admin_last_read_at',
        'participant_last_read_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
            'admin_last_read_at' => 'datetime',
            'participant_last_read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at')->orderBy('id');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }

    public function scopeForParticipant(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $inner) use ($user) {
            $inner->where('user_id', $user->id)
                ->orWhere('contact_email', $user->email);
        });
    }

    public function isUnreadForAdmin(): bool
    {
        if ($this->last_message_at === null) {
            return false;
        }

        return $this->admin_last_read_at === null
            || $this->admin_last_read_at->lt($this->last_message_at);
    }

    public function isUnreadForParticipant(): bool
    {
        if ($this->last_message_at === null) {
            return false;
        }

        return $this->participant_last_read_at === null
            || $this->participant_last_read_at->lt($this->last_message_at);
    }

    public function markReadByAdmin(): void
    {
        $this->update(['admin_last_read_at' => now()]);
    }

    public function markReadByParticipant(): void
    {
        $this->update(['participant_last_read_at' => now()]);
    }

    public function displayName(): string
    {
        return $this->user?->name ?: $this->contact_name;
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }
}
