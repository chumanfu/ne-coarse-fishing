<?php

namespace App\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use RuntimeException;

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

    /**
     * Store an uploaded file on the uploads disk, failing loudly on error.
     */
    public static function store(UploadedFile|TemporaryUploadedFile $file, string $directory): string
    {
        $path = $file->store($directory, self::diskName());

        if ($path === false || $path === '') {
            throw new RuntimeException("Unable to store upload in [{$directory}] on disk [".self::diskName().'].');
        }

        return $path;
    }

    /**
     * Copy a local public file onto the uploads disk and return the new path.
     */
    public static function promotePublicPath(string $publicRelativePath, string $destinationPath): string
    {
        $absolute = public_path($publicRelativePath);

        if (! is_file($absolute)) {
            throw new RuntimeException("Public file missing: {$publicRelativePath}");
        }

        $stream = fopen($absolute, 'r');

        if ($stream === false) {
            throw new RuntimeException("Unable to read public file: {$publicRelativePath}");
        }

        try {
            $stored = self::disk()->put($destinationPath, $stream);
        } finally {
            fclose($stream);
        }

        if ($stored === false) {
            throw new RuntimeException("Unable to promote [{$publicRelativePath}] to uploads disk.");
        }

        return $destinationPath;
    }

    public static function isStockPath(?string $path): bool
    {
        return filled($path) && str_starts_with($path, 'images/');
    }
}
