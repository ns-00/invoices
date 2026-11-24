<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageService
{
    /**
     * Maximum allowed image size in kilobytes (5 MB = 5120 KB)
     */
    public const MAX_SIZE_KB = 5120;

    /**
     * Upload an image file, convert it to .webp format, and store it.
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param int $quality (0 - 100)
     * @return string Relative storage path (e.g. invoices/xyz.webp)
     */
    public function uploadAndConvertToWebp(UploadedFile $file, string $directory = 'invoices', int $quality = 80): string
    {
        // 1. Ensure storage directory exists
        Storage::disk('public')->makeDirectory($directory);

        $filename = Str::random(40) . '.webp';
        $destinationPath = Storage::disk('public')->path($directory . '/' . $filename);

        // 2. Convert to WebP using GD if supported
        if (function_exists('imagewebp')) {
            $converted = $this->convertFileToWebp($file->getRealPath(), $destinationPath, $quality);
            if ($converted && file_exists($destinationPath)) {
                return $directory . '/' . $filename;
            }
        }

        // Fallback: standard Laravel storage if GD conversion fails
        return $file->store($directory, 'public');
    }

    /**
     * Convert an image file on disk to WebP.
     */
    protected function convertFileToWebp(string $sourcePath, string $destinationPath, int $quality): bool
    {
        $info = @getimagesize($sourcePath);
        if (! $info) {
            return false;
        }

        $mime = $info['mime'] ?? '';
        $image = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($sourcePath),
            'image/png'  => @imagecreatefrompng($sourcePath),
            'image/gif'  => @imagecreatefromgif($sourcePath),
            'image/webp' => @imagecreatefromwebp($sourcePath),
            'image/bmp'  => function_exists('imagecreatefrombmp') ? @imagecreatefrombmp($sourcePath) : false,
            default      => @imagecreatefromstring((string) file_get_contents($sourcePath)),
        };

        if (! $image) {
            return false;
        }

        // Handle palette transparency & true color
        if (function_exists('imagepalettetotruecolor')) {
            imagepalettetotruecolor($image);
        }
        imagealphablending($image, true);
        imagesavealpha($image, true);

        // Save as WebP
        $success = imagewebp($image, $destinationPath, $quality);
        imagedestroy($image);

        return $success;
    }

    /**
     * Delete an existing image from public storage.
     */
    public function deleteImage(?string $path): bool
    {
        if (! empty($path) && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }

        return false;
    }
}
