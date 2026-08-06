<?php

namespace App\Filament\Resources\WaterPegs\Pages;

use App\Filament\Resources\WaterPegs\WaterPegResource;
use App\Support\Uploads;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWaterPeg extends EditRecord
{
    protected static string $resource = WaterPegResource::class;

    /** @var list<string> */
    protected array $photoUploads = [];

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
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['photo_uploads'] = $this->record->photos()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('image_path')
            ->all();

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->photoUploads = array_values(array_filter((array) ($data['photo_uploads'] ?? [])));
        unset($data['photo_uploads']);

        return $data;
    }

    protected function afterSave(): void
    {
        $keep = collect($this->photoUploads);

        $this->record->photos()
            ->whereNotIn('image_path', $keep->all())
            ->get()
            ->each(function ($photo): void {
                Uploads::delete($photo->image_path);
                $photo->delete();
            });

        $existing = $this->record->photos()->pluck('image_path')->all();

        foreach ($keep->values() as $index => $path) {
            if (in_array($path, $existing, true)) {
                $this->record->photos()->where('image_path', $path)->update(['sort_order' => $index + 1]);

                continue;
            }

            $this->record->photos()->create([
                'image_path' => $path,
                'sort_order' => $index + 1,
            ]);
        }
    }
}
