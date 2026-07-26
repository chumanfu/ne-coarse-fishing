<?php

use App\Models\Species;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Common fish species found in UK rivers, canals, lakes and ponds.
     *
     * @var list<string>
     */
    private array $species = [
        // Carp & cyprinids
        'Common Carp',
        'Mirror Carp',
        'Leather Carp',
        'Ghost Carp',
        'Grass Carp',
        'Crucian Carp',
        'F1',
        'Koi',
        'Goldfish',
        'Tench',
        'Bream',
        'Silver Bream',
        'Roach',
        'Rudd',
        'Chub',
        'Barbel',
        'Ide',
        'Orfe',
        'Dace',
        'Bleak',
        'Gudgeon',
        'Minnow',
        'Nase',

        // Predators
        'Perch',
        'Pike',
        'Zander',
        'Wels Catfish',
        'Bullhead',
        'Ruffe',

        // Eels & lampreys
        'European Eel',
        'Brook Lamprey',
        'River Lamprey',

        // Game / migratory
        'Brown Trout',
        'Rainbow Trout',
        'Sea Trout',
        'Atlantic Salmon',
        'Grayling',
        'Arctic Char',

        // Smaller stillwater / river species
        'Three-spined Stickleback',
        'Nine-spined Stickleback',
        'Stone Loach',
        'Spined Loach',
    ];

    public function up(): void
    {
        foreach ($this->species as $name) {
            Species::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
        }
    }

    public function down(): void
    {
        $slugs = array_map(fn (string $name) => Str::slug($name), $this->species);

        Species::query()->whereIn('slug', $slugs)->delete();
    }
};
