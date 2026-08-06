<?php

namespace App\Services;

use App\Mail\ContactMessage;
use App\Mail\MessageReplyNotification;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class MessagingService
{
    public function createFromContact(
        string $name,
        string $email,
        string $subject,
        string $body,
        ?User $user = null,
    ): MessageThread {
        $thread = DB::transaction(function () use ($name, $email, $subject, $body, $user) {
            $linkedUser = $user ?? User::query()->where('email', $email)->first();

            $thread = MessageThread::query()->create([
                'user_id' => $linkedUser?->id,
                'subject' => $subject,
                'contact_name' => $name,
                'contact_email' => $email,
                'source' => 'contact',
                'status' => 'open',
                'last_message_at' => now(),
                'participant_last_read_at' => now(),
                'admin_last_read_at' => null,
            ]);

            $thread->messages()->create([
                'user_id' => $linkedUser?->id,
                'body' => $body,
                'is_from_admin' => false,
            ]);

            return $thread->fresh(['messages']);
        });

        $this->notifyAdminOfContact($thread, $name, $email, $subject, $body);

        return $thread;
    }

    public function startWithUser(
        User $admin,
        User $recipient,
        string $subject,
        string $body,
        bool $queueMail = false,
    ): MessageThread {
        [$thread, $message] = DB::transaction(function () use ($admin, $recipient, $subject, $body) {
            $thread = MessageThread::query()->create([
                'user_id' => $recipient->id,
                'subject' => $subject,
                'contact_name' => $recipient->name,
                'contact_email' => $recipient->email,
                'source' => 'admin',
                'status' => 'open',
                'last_message_at' => now(),
                'admin_last_read_at' => now(),
                'participant_last_read_at' => null,
            ]);

            $message = $thread->messages()->create([
                'user_id' => $admin->id,
                'body' => $body,
                'is_from_admin' => true,
            ]);

            return [$thread->fresh(['messages']), $message];
        });

        $this->notifyParticipant($thread, $message, $queueMail);

        return $thread;
    }

    /**
     * Create an inbox thread (+ email) for every registered user except the sender.
     */
    public function broadcastToAllUsers(User $admin, string $subject, string $body): int
    {
        $recipients = User::query()
            ->whereKeyNot($admin->id)
            ->orderBy('id')
            ->get();

        foreach ($recipients as $recipient) {
            $this->startWithUser($admin, $recipient, $subject, $body, queueMail: true);
        }

        return $recipients->count();
    }

    public function reply(MessageThread $thread, User $sender, string $body, bool $asAdmin): Message
    {
        $message = DB::transaction(function () use ($thread, $sender, $body, $asAdmin) {
            abort_if($thread->isClosed(), 422, 'This conversation is closed.');

            $message = $thread->messages()->create([
                'user_id' => $sender->id,
                'body' => $body,
                'is_from_admin' => $asAdmin,
            ]);

            $updates = [
                'status' => 'open',
                'last_message_at' => now(),
            ];

            if ($asAdmin) {
                $updates['admin_last_read_at'] = now();
            } else {
                $updates['participant_last_read_at'] = now();
                if (! $thread->user_id) {
                    $updates['user_id'] = $sender->id;
                }
            }

            $thread->forceFill($updates)->save();

            return $message;
        });

        $thread = $thread->fresh();

        if ($asAdmin) {
            $this->notifyParticipant($thread, $message);
        } else {
            $this->notifyAdminOfReply($thread, $message);
        }

        return $message;
    }

    public function close(MessageThread $thread): void
    {
        $thread->update(['status' => 'closed']);
    }

    public function reopen(MessageThread $thread): void
    {
        $thread->update(['status' => 'open']);
    }

    private function notifyAdminOfContact(
        MessageThread $thread,
        string $name,
        string $email,
        string $subject,
        string $body,
    ): void {
        $to = config('mail.contact_to');

        if (! filled($to)) {
            return;
        }

        Mail::to($to)->send(new ContactMessage(
            name: $name,
            email: $email,
            subjectLine: $subject,
            messageBody: $body,
            thread: $thread,
        ));
    }

    private function notifyAdminOfReply(MessageThread $thread, Message $message): void
    {
        $to = config('mail.contact_to');

        if (! filled($to)) {
            return;
        }

        Mail::to($to)->send(new MessageReplyNotification(
            thread: $thread,
            message: $message,
            forAdmin: true,
        ));
    }

    private function notifyParticipant(MessageThread $thread, Message $message, bool $queue = false): void
    {
        $mailable = new MessageReplyNotification(
            thread: $thread,
            message: $message,
            forAdmin: false,
        );

        $mail = Mail::to($thread->contact_email, $thread->contact_name);

        if ($queue) {
            $mail->queue($mailable);

            return;
        }

        $mail->send($mailable);
    }
}
