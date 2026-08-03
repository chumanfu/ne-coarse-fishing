<?php

use App\Models\Club;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\File;

return new class extends Migration
{
    public function up(): void
    {
        foreach (Club::query()->get() as $club) {
            $path = $this->logoPathForSlug($club->slug);

            if ($path) {
                $club->update(['logo_path' => $path]);
            }
        }
    }

    public function down(): void
    {
        Club::query()->update(['logo_path' => null]);
    }

    private function logoPathForSlug(string $slug): ?string
    {
        foreach (['png', 'jpg', 'jpeg', 'svg', 'webp'] as $extension) {
            $relative = "images/clubs/{$slug}.{$extension}";

            if (File::exists(public_path($relative))) {
                return $relative;
            }
        }

        return null;
    }
};
