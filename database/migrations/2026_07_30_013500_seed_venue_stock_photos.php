<?php

use App\Models\Venue;
use App\Models\VenuePhoto;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\File;

return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->slugs() as $slug) {
            $venue = Venue::query()->where('slug', $slug)->first();

            if (! $venue) {
                continue;
            }

            $relativePath = "images/venues/{$slug}.jpg";
            $absolutePath = public_path($relativePath);

            if (! File::exists($absolutePath)) {
                continue;
            }

            VenuePhoto::query()->updateOrCreate(
                [
                    'venue_id' => $venue->id,
                    'image_path' => $relativePath,
                ],
                [
                    'sort_order' => 0,
                ],
            );
        }
    }

    public function down(): void
    {
        VenuePhoto::query()
            ->whereIn('image_path', collect($this->slugs())->map(fn (string $slug) => "images/venues/{$slug}.jpg"))
            ->delete();
    }

    /**
     * @return list<string>
     */
    private function slugs(): array
    {
        return [
            'killingworth-lakes',
            'eden-grange',
            'hebron-lake',
            'qe-ii',
            'angel-lakes',
            'brenkley-pond',
            'horton-grange-lake',
            'milkhope-lake',
            'throckley-reigh',
            'river-tyne-newburn',
            'wydon-burn',
            'fontburn-reservoir',
            'whittle-dene-reservoir',
            'big-waters',
            'stargate',
            'meggies-burn-reservoir',
            'bolam-lake',
            'derwent-reservoir',
            'tunstall-reservoir',
            'aldin-grange',
            'batleys-pond',
        ];
    }
};
