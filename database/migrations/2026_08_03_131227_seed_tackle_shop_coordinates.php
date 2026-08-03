<?php

use App\Models\TackleShop;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->coordinates() as $slug => $coords) {
            TackleShop::query()
                ->where('slug', $slug)
                ->update([
                    'latitude' => $coords[0],
                    'longitude' => $coords[1],
                ]);
        }
    }

    public function down(): void
    {
        TackleShop::query()
            ->whereIn('slug', array_keys($this->coordinates()))
            ->update([
                'latitude' => null,
                'longitude' => null,
            ]);
    }

    /**
     * Approximate map pins for North East (and nearby) shops with a known address.
     *
     * @return array<string, array{0: float, 1: float}>
     */
    private function coordinates(): array
    {
        return [
            'billys-fishing-tackle' => [55.0067246, -1.4472245],
            'rutherfords-angling' => [54.9171658, -1.3736958],
            'north-east-tackle' => [54.8832024, -1.3646266],
            'bagnall-and-kirkwood' => [54.9871096, -1.5677217],
            'angling-direct-stockton' => [54.5763858, -1.2824310],
            'ad-tackle' => [54.9898076, -1.5374485],
            'wallys-fishing-tackle' => [54.5244000, -1.5518000],
            'the-anglers-lodge' => [54.6100000, -1.5800000],
        ];
    }
};
