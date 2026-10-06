<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Stores user images on the public disk with random names. When GD is
 * available the image is decoded and re-encoded (strips metadata and any
 * embedded payload, downsizes very large images). Never keeps the original name.
 */
class ImageUploader
{
    private const ALLOWED = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    public static function rules(): array
    {
        $max = (int) config('kustore.uploads.max_kb', 4096);

        return ['image', 'mimes:jpg,jpeg,png,webp', 'mimetypes:image/jpeg,image/png,image/webp', "max:{$max}"];
    }

    public function store(UploadedFile $file, string $directory): string
    {
        $mime = $file->getMimeType();
        if (! isset(self::ALLOWED[$mime]) || @getimagesize($file->getRealPath()) === false) {
            throw new RuntimeException('Unsupported image.');
        }

        $name = trim($directory, '/').'/'.Str::lower(Str::random(40));

        if (function_exists('imagecreatefromstring') && function_exists('imagewebp')) {
            $binary = $this->reencode($file->getRealPath());
            if ($binary !== null) {
                Storage::disk('public')->put($name.'.webp', $binary);

                return $name.'.webp';
            }
        }

        // Fallback: keep bytes but force a safe extension derived from the real mime type.
        $path = $name.'.'.self::ALLOWED[$mime];
        Storage::disk('public')->putFileAs(dirname($path), $file, basename($path));

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    private function reencode(string $path): ?string
    {
        $src = @imagecreatefromstring((string) file_get_contents($path));
        if (! $src) {
            return null;
        }

        $w = imagesx($src);
        $h = imagesy($src);
        $max = (int) config('kustore.uploads.max_dimension', 1600);
        $scale = min(1, $max / max($w, $h));
        $nw = max(1, (int) round($w * $scale));
        $nh = max(1, (int) round($h * $scale));

        $dst = imagecreatetruecolor($nw, $nh);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);

        ob_start();
        imagewebp($dst, null, 82);
        $out = ob_get_clean();
        imagedestroy($src);
        imagedestroy($dst);

        return $out ?: null;
    }
}
