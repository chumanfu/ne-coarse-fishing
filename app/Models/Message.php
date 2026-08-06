<?php

namespace App\Models;

use Database\Factories\MessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    /** @use HasFactory<MessageFactory> */
    use HasFactory;

    protected $fillable = [
        'message_thread_id',
        'user_id',
        'body',
        'is_from_admin',
    ];

    protected function casts(): array
    {
        return [
            'is_from_admin' => 'boolean',
        ];
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(MessageThread::class, 'message_thread_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function senderLabel(): string
    {
        if ($this->is_from_admin) {
            return $this->user?->name ?: 'Site admin';
        }

        return $this->user?->name
            ?: $this->thread?->contact_name
            ?: 'Angler';
    }
}
