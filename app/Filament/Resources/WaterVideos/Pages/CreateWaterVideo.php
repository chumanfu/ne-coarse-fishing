<?php

namespace App\Filament\Resources\WaterVideos\Pages;

use App\Filament\Resources\WaterVideos\WaterVideoResource;
use App\Models\WaterVideo;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateWaterVideo extends CreateRecord
{
    protected static string $resource = WaterVideoResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $youtubeId = WaterVideo::extractYoutubeId($data['youtube_url'] ?? null);

        if ($youtubeId === null) {
            throw ValidationException::withMessages([
                'data.youtube_url' => 'Enter a valid YouTube video URL.',
            ]);
        }

        $data['youtube_id'] = $youtubeId;
        $data['user_id'] = $data['user_id'] ?? auth()->id();

        if (! empty($data['is_approved'])) {
            $data['approved_by'] = auth()->id();
            $data['approved_at'] = now();
        } else {
            $data['approved_by'] = null;
            $data['approved_at'] = null;
        }

        return $data;
    }
}
