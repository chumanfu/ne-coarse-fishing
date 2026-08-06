<?php

namespace App\Http\Controllers;

use App\Services\MessagingService;
use App\Services\UserDataExportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GdprExportController extends Controller
{
    public function __invoke(
        Request $request,
        UserDataExportService $exporter,
        MessagingService $messaging,
    ): StreamedResponse {
        $user = $request->user();
        $payload = $exporter->export($user);
        $filename = $exporter->filename($user);

        if (filled(config('mail.contact_to'))) {
            $messaging->createFromContact(
                name: $user->name,
                email: $user->email,
                subject: 'GDPR data export requested',
                body: $user->name.' downloaded a copy of their stored personal data from the profile page on '.now()->timezone(config('app.timezone'))->format('d M Y H:i').'.',
                user: $user,
            );
        }

        return response()->streamDownload(function () use ($payload): void {
            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }
}
