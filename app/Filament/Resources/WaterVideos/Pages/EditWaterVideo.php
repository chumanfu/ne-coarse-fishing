<?php

namespace App\Filament\Resources\WaterVideos\Pages;

use App\Filament\Resources\WaterVideos\WaterVideoResource;
use App\Models\WaterVideo;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditWaterVideo extends EditRecord
{
    protected static string $resource = WaterVideoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $youtubeId = WaterVideo::extractYoutubeId($data['youtube_url'] ?? null);

        if ($youtubeId === null) {
            throw ValidationException::withMessages([
                'data.youtube_url' => 'Enter a valid YouTube video URL.',
            ]);
        }

        $data['youtube_id'] = $youtubeId;

        $wasApproved = (bool) $this->record->is_approved;
        $willBeApproved = ! empty($data['is_approved']);

        if ($willBeApproved && ! $wasApproved) {
            $data['approved_by'] = auth()->id();
            $data['approved_at'] = now();
        } elseif (! $willBeApproved) {
            $data['approved_by'] = null;
            $data['approved_at'] = null;
        }

        return $data;
    }
}
