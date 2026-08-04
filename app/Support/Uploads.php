<?php

namespace App\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

class Uploads
{
    public static function diskName(): string
    {
        return (string) config('filesystems.uploads', 'public');
    }

    public static function disk(): Filesystem
    {
        return Storage::disk(self::diskName());
    }

    public static function url(string $path): string
    {
        if (str_starts_with($path, 'images/')) {
            return asset($path);
        }

        return self::disk()->url($path);
    }

    public static function delete(string $path): bool
    {
        if (str_starts_with($path, 'images/')) {
            return false;
        }

        return self::disk()->delete($path);
    }
}
