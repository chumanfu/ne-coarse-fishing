<?php

use App\Models\TackleShop;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\File;

return new class extends Migration
{
    public function up(): void
    {
        foreach (TackleShop::query()->get() as $shop) {
            $path = $this->logoPathForSlug($shop->slug);

            if ($path) {
                $shop->update(['logo_path' => $path]);
            }
        }
    }

    public function down(): void
    {
        TackleShop::query()->update(['logo_path' => null]);
    }

    private function logoPathForSlug(string $slug): ?string
    {
        foreach (['png', 'jpg', 'jpeg', 'svg', 'webp'] as $extension) {
            $relative = "images/tackle-shops/{$slug}.{$extension}";

            if (File::exists(public_path($relative))) {
                return $relative;
            }
        }

        return null;
    }
};
