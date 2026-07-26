<?php

use App\Models\Species;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Species type and typical UK waters.
     * Habitats: river, canal, lake, pond, reservoir
     *
     * @return array<string, array{type: string, habitats: list<string>}>
     */
    private function speciesData(): array
    {
        return [
            'Common Carp' => ['type' => 'cyprinid', 'habitats' => ['lake', 'pond', 'reservoir', 'canal']],
            'Mirror Carp' => ['type' => 'cyprinid', 'habitats' => ['lake', 'pond', 'reservoir', 'canal']],
            'Leather Carp' => ['type' => 'cyprinid', 'habitats' => ['lake', 'pond', 'reservoir']],
            'Ghost Carp' => ['type' => 'cyprinid', 'habitats' => ['lake', 'pond', 'reservoir']],
            'Grass Carp' => ['type' => 'cyprinid', 'habitats' => ['lake', 'pond', 'reservoir']],
            'Crucian Carp' => ['type' => 'cyprinid', 'habitats' => ['lake', 'pond']],
            'F1' => ['type' => 'cyprinid', 'habitats' => ['lake', 'pond', 'canal']],
            'Koi' => ['type' => 'cyprinid', 'habitats' => ['pond', 'lake']],
            'Goldfish' => ['type' => 'cyprinid', 'habitats' => ['pond', 'lake']],
            'Tench' => ['type' => 'cyprinid', 'habitats' => ['lake', 'pond', 'canal', 'reservoir']],
            'Bream' => ['type' => 'cyprinid', 'habitats' => ['river', 'lake', 'pond', 'reservoir', 'canal']],
            'Silver Bream' => ['type' => 'cyprinid', 'habitats' => ['river', 'lake', 'canal']],
            'Roach' => ['type' => 'cyprinid', 'habitats' => ['river', 'lake', 'pond', 'reservoir', 'canal']],
            'Rudd' => ['type' => 'cyprinid', 'habitats' => ['lake', 'pond', 'canal', 'reservoir']],
            'Chub' => ['type' => 'cyprinid', 'habitats' => ['river', 'canal']],
            'Barbel' => ['type' => 'cyprinid', 'habitats' => ['river']],
            'Ide' => ['type' => 'cyprinid', 'habitats' => ['lake', 'pond', 'river', 'canal']],
            'Orfe' => ['type' => 'cyprinid', 'habitats' => ['lake', 'pond']],
            'Dace' => ['type' => 'cyprinid', 'habitats' => ['river', 'canal']],
            'Bleak' => ['type' => 'cyprinid', 'habitats' => ['river', 'canal']],
            'Gudgeon' => ['type' => 'cyprinid', 'habitats' => ['river', 'canal', 'lake']],
            'Minnow' => ['type' => 'cyprinid', 'habitats' => ['river', 'stream']],
            'Nase' => ['type' => 'cyprinid', 'habitats' => ['river']],

            'Perch' => ['type' => 'predator', 'habitats' => ['river', 'lake', 'pond', 'reservoir', 'canal']],
            'Pike' => ['type' => 'predator', 'habitats' => ['river', 'lake', 'pond', 'reservoir', 'canal']],
            'Zander' => ['type' => 'predator', 'habitats' => ['river', 'canal', 'lake', 'reservoir']],
            'Wels Catfish' => ['type' => 'predator', 'habitats' => ['lake', 'pond', 'reservoir', 'river']],
            'Bullhead' => ['type' => 'predator', 'habitats' => ['river', 'stream']],
            'Ruffe' => ['type' => 'predator', 'habitats' => ['river', 'lake', 'canal']],

            'European Eel' => ['type' => 'eel', 'habitats' => ['river', 'lake', 'pond', 'reservoir', 'canal']],
            'Brook Lamprey' => ['type' => 'eel', 'habitats' => ['river', 'stream']],
            'River Lamprey' => ['type' => 'eel', 'habitats' => ['river']],

            'Brown Trout' => ['type' => 'game', 'habitats' => ['river', 'lake', 'reservoir', 'stream']],
            'Rainbow Trout' => ['type' => 'game', 'habitats' => ['lake', 'reservoir', 'river']],
            'Sea Trout' => ['type' => 'game', 'habitats' => ['river']],
            'Atlantic Salmon' => ['type' => 'game', 'habitats' => ['river']],
            'Grayling' => ['type' => 'game', 'habitats' => ['river']],
            'Arctic Char' => ['type' => 'game', 'habitats' => ['lake', 'reservoir']],

            'Three-spined Stickleback' => ['type' => 'minor', 'habitats' => ['river', 'lake', 'pond', 'canal', 'stream']],
            'Nine-spined Stickleback' => ['type' => 'minor', 'habitats' => ['pond', 'lake', 'stream']],
            'Stone Loach' => ['type' => 'minor', 'habitats' => ['river', 'stream']],
            'Spined Loach' => ['type' => 'minor', 'habitats' => ['river', 'drain', 'canal']],

            // Legacy seeder names (if present)
            'Carp' => ['type' => 'cyprinid', 'habitats' => ['lake', 'pond', 'reservoir', 'canal']],
            'Crucian' => ['type' => 'cyprinid', 'habitats' => ['lake', 'pond']],
        ];
    }

    public function up(): void
    {
        Schema::table('species', function (Blueprint $table) {
            $table->string('type')->nullable()->after('slug');
            $table->json('habitats')->nullable()->after('type');
        });

        foreach ($this->speciesData() as $name => $data) {
            Species::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'type' => $data['type'],
                    'habitats' => $data['habitats'],
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::table('species', function (Blueprint $table) {
            $table->dropColumn(['type', 'habitats']);
        });
    }
};
