<?php

namespace App\Filament\Resources\WaterPegs\Pages;

use App\Filament\Resources\WaterPegs\WaterPegResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWaterPeg extends CreateRecord
{
    protected static string $resource = WaterPegResource::class;

    /** @var list<string> */
    protected array $photoUploads = [];

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->photoUploads = array_values(array_filter((array) ($data['photo_uploads'] ?? [])));
        unset($data['photo_uploads']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->syncUploadedPhotos();
    }

    protected function syncUploadedPhotos(): void
    {
        foreach ($this->photoUploads as $index => $path) {
            $this->record->photos()->create([
                'image_path' => $path,
                'sort_order' => $index + 1,
            ]);
        }
    }
}
