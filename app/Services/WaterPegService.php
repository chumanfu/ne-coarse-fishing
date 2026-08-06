<?php

namespace App\Services;

use App\Models\User;
use App\Models\Water;
use App\Models\WaterPeg;
use App\Models\WaterPegPhoto;
use App\Support\Uploads;
use Illuminate\Http\UploadedFile;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class WaterPegService
{
    /**
     * @param  array{name?: ?string, number?: ?string, latitude: float|string, longitude: float|string}  $data
     * @param  list<UploadedFile|TemporaryUploadedFile>  $photos
     */
    public function createForWater(Water $water, User $user, array $data, bool $forceVerified = false, array $photos = []): WaterPeg
    {
        $venue = $water->venue;
        $verified = $forceVerified || ($venue && $venue->canManagePegs($user));

        $peg = $water->pegs()->create([
            'created_by' => $user->id,
            'name' => filled($data['name'] ?? null) ? trim((string) $data['name']) : null,
            'number' => filled($data['number'] ?? null) ? trim((string) $data['number']) : null,
            'latitude' => round((float) $data['latitude'], 7),
            'longitude' => round((float) $data['longitude'], 7),
            'is_verified' => $verified,
            'verified_by' => $verified ? $user->id : null,
            'verified_at' => $verified ? now() : null,
            'sort_order' => (int) $water->pegs()->max('sort_order') + 1,
        ]);

        $this->storePhotos($peg, $photos);

        return $peg->load('photos');
    }

    /**
     * @param  array{name?: ?string, number?: ?string, latitude: float|string, longitude: float|string}  $data
     * @param  list<UploadedFile|TemporaryUploadedFile>  $photos
     * @param  list<int>  $keepPhotoIds
     */
    public function updateForWater(
        WaterPeg $peg,
        Water $water,
        User $user,
        array $data,
        array $photos = [],
        array $keepPhotoIds = [],
    ): WaterPeg {
        abort_unless($water->venue && $water->venue->canManagePegs($user), 403);

        $peg->update([
            'water_id' => $water->id,
            'name' => filled($data['name'] ?? null) ? trim((string) $data['name']) : null,
            'number' => filled($data['number'] ?? null) ? trim((string) $data['number']) : null,
            'latitude' => round((float) $data['latitude'], 7),
            'longitude' => round((float) $data['longitude'], 7),
        ]);

        if (! $peg->is_verified) {
            $peg->markVerified($user);
        }

        $this->syncPhotos($peg, $keepPhotoIds, $photos);

        return $peg->load('photos');
    }

    /**
     * Sync peg rows from venue wizard water payload.
     *
     * @param  list<array<string, mixed>>  $pegs
     */
    public function syncForWater(Water $water, array $pegs, ?User $actor): void
    {
        $keepIds = [];
        $verified = $actor && $water->venue && $water->venue->canManagePegs($actor);

        foreach ($pegs as $index => $pegData) {
            if (blank($pegData['name'] ?? null) && blank($pegData['number'] ?? null)) {
                continue;
            }

            if (! isset($pegData['latitude'], $pegData['longitude']) || $pegData['latitude'] === '' || $pegData['longitude'] === '') {
                continue;
            }

            $attributes = [
                'name' => filled($pegData['name'] ?? null) ? trim((string) $pegData['name']) : null,
                'number' => filled($pegData['number'] ?? null) ? trim((string) $pegData['number']) : null,
                'latitude' => round((float) $pegData['latitude'], 7),
                'longitude' => round((float) $pegData['longitude'], 7),
                'sort_order' => $index,
            ];

            $peg = null;
            if (! empty($pegData['id'])) {
                $peg = $water->pegs()->whereKey($pegData['id'])->first();
            }

            if ($peg) {
                $peg->update($attributes);
                if ($verified && ! $peg->is_verified) {
                    $peg->markVerified($actor);
                }
            } else {
                $peg = $water->pegs()->create($attributes + [
                    'created_by' => $actor?->id,
                    'is_verified' => (bool) $verified,
                    'verified_by' => $verified ? $actor?->id : null,
                    'verified_at' => $verified ? now() : null,
                ]);
            }

            $existingIds = collect($pegData['existing_photo_ids'] ?? [])
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();

            $this->syncPhotos($peg, $existingIds, $pegData['new_photos'] ?? []);
            $keepIds[] = $peg->id;
        }

        // Only remove verified/official pegs managed here; keep pending pegs created via sessions.
        $water->pegs()
            ->where('is_verified', true)
            ->whereNotIn('id', $keepIds)
            ->each(function (WaterPeg $peg) {
                $peg->fishingSessions()->update(['water_peg_id' => null]);
                $peg->delete();
            });
    }

    /**
     * @param  list<int>  $keepPhotoIds
     * @param  list<UploadedFile|TemporaryUploadedFile|null>  $newPhotos
     */
    public function syncPhotos(WaterPeg $peg, array $keepPhotoIds, array $newPhotos = []): void
    {
        $peg->photos()
            ->whereNotIn('id', $keepPhotoIds)
            ->get()
            ->each(fn (WaterPegPhoto $photo) => $photo->delete());

        $this->storePhotos($peg, $newPhotos, (int) $peg->photos()->max('sort_order'));
    }

    /**
     * @param  list<UploadedFile|TemporaryUploadedFile|null>  $photos
     */
    public function storePhotos(WaterPeg $peg, array $photos, int $startOrder = 0): void
    {
        $order = $startOrder;

        foreach ($photos as $photo) {
            if (! $photo instanceof UploadedFile && ! $photo instanceof TemporaryUploadedFile) {
                continue;
            }

            $path = Uploads::store($photo, 'peg-photos');
            $order++;

            $peg->photos()->create([
                'image_path' => $path,
                'sort_order' => $order,
            ]);
        }
    }
}
