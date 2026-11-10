<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Stores product thumbnails on the public disk.
 */
class Thumbnail
{
    public const DISK = 'public';

    /**
     * Validation rules for an uploaded thumbnail (max 2 MB).
     *
     * @return array<int, string>
     */
    public static function rules(): array
    {
        return ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'];
    }

    /**
     * @throws RuntimeException when the file cannot be written to the disk
     */
    public static function store(UploadedFile $file, string $directory): string
    {
        $path = $file->store($directory, self::DISK);

        if ($path === false) {
            throw new RuntimeException('The thumbnail could not be saved.');
        }

        return $path;
    }

    /**
     * Applies a form's thumbnail change and returns the new path.
     *
     * A new upload replaces the current file, "remove" clears it, otherwise the current path is kept.
     */
    public static function replace(?string $currentPath, ?UploadedFile $upload, bool $remove, string $directory): ?string
    {
        if ($upload === null && ! $remove) {
            return $currentPath;
        }

        static::delete($currentPath);

        return $upload === null ? null : static::store($upload, $directory);
    }

    public static function delete(?string $path): void
    {
        if ($path !== null) {
            Storage::disk(self::DISK)->delete($path);
        }
    }

    public static function url(?string $path): ?string
    {
        return $path === null ? null : Storage::disk(self::DISK)->url($path);
    }
}
