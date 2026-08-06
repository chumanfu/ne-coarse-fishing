<?php

namespace App\Http\Controllers;

use App\Models\MessageThread;
use App\Services\MessagingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(Request $request): View
    {
        $threads = MessageThread::query()
            ->forParticipant($request->user())
            ->with('latestMessage')
            ->latest('last_message_at')
            ->paginate(20);

        return view('messages.index', compact('threads'));
    }

    public function show(Request $request, MessageThread $messageThread): View
    {
        $this->authorize('view', $messageThread);

        $messageThread->load(['messages.user', 'user']);
        $messageThread->markReadByParticipant();

        return view('messages.show', ['thread' => $messageThread]);
    }

    public function reply(Request $request, MessageThread $messageThread, MessagingService $messaging): RedirectResponse
    {
        $this->authorize('reply', $messageThread);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $messaging->reply($messageThread, $request->user(), $validated['body'], asAdmin: false);

        return redirect()
            ->route('messages.show', $messageThread)
            ->with('status', 'Reply sent.');
    }
}
