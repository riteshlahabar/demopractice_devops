<?php

namespace App\Services\Files;

use App\Contracts\Files\ImageOptimizerContract;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * GD is used rather than a package: it is already enabled on the cPanel host
 * (GD 2.3.3) and adding an image library to composer would mean another upload
 * of a vendor tree to shared hosting for work that is a dozen calls wide.
 */
final class ImageOptimizerService implements ImageOptimizerContract
{
    /** Formats worth touching. A PDF or an MP4 is passed straight through. */
    private const HANDLED = ['jpg', 'jpeg', 'png', 'webp'];

    /** Below this there is nothing to gain, so the file is left alone. */
    private const SKIP_UNDER_BYTES = 150 * 1024;

    private const WEBP_QUALITY = 82;

    private const JPEG_QUALITY = 82;

    public function optimize(string $publicPath): string
    {
        try {
            $absolute = $this->absolute($publicPath);

            if ($absolute === null || ! extension_loaded('gd')) {
                return $publicPath;
            }

            $extension = strtolower(pathinfo($absolute, PATHINFO_EXTENSION));

            if (! in_array($extension, self::HANDLED, true) || filesize($absolute) < self::SKIP_UNDER_BYTES) {
                return $publicPath;
            }

            $image = $this->read($absolute, $extension);

            if ($image === null) {
                return $publicPath;
            }

            $resized = $this->resize($image);

            $this->writeOriginal($resized, $absolute, $extension);
            $this->writeWebp($resized, $absolute);

            imagedestroy($resized);

            if ($resized !== $image) {
                imagedestroy($image);
            }
        } catch (Throwable $exception) {
            // An image that cannot be processed is still a perfectly good
            // upload; it may never cost the user their save.
            Log::warning('Image not optimized ('.$publicPath.'): '.$exception->getMessage());
        }

        return $publicPath;
    }

    public function variantFor(?string $publicPath): ?string
    {
        $path = trim((string) $publicPath);

        if ($path === '' || Str::startsWith($path, ['http://', 'https://', 'data:'])) {
            return null;
        }

        if (Str::endsWith(strtolower($path), '.webp')) {
            return null;
        }

        $candidate = $path.'.webp';

        return is_file(public_path(ltrim($candidate, '/'))) ? $candidate : null;
    }

    /** Null unless the path really is a file inside the public root. */
    private function absolute(string $publicPath): ?string
    {
        $path = trim($publicPath);

        if ($path === '' || str_contains($path, '..') || Str::startsWith($path, ['http://', 'https://'])) {
            return null;
        }

        $absolute = public_path(ltrim(str_replace('\\', '/', $path), '/'));
        $root = realpath(public_path());
        $real = is_file($absolute) ? realpath($absolute) : false;

        return $root && $real && Str::startsWith($real, $root) ? $real : null;
    }

    private function read(string $absolute, string $extension): ?\GdImage
    {
        $image = match ($extension) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($absolute),
            'png' => @imagecreatefrompng($absolute),
            'webp' => @imagecreatefromwebp($absolute),
            default => false,
        };

        return $image instanceof \GdImage ? $image : null;
    }

    /** Returns the same image untouched when it is already small enough. */
    private function resize(\GdImage $image): \GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $longest = max($width, $height);

        if ($longest <= self::MAX_EDGE) {
            return $image;
        }

        $scale = self::MAX_EDGE / $longest;
        $target = imagecreatetruecolor((int) round($width * $scale), (int) round($height * $scale));

        // Transparency has to be carried over explicitly or a cut-out product
        // photo comes back on a black rectangle.
        imagealphablending($target, false);
        imagesavealpha($target, true);
        imagecopyresampled($target, $image, 0, 0, 0, 0, imagesx($target), imagesy($target), $width, $height);

        return $target;
    }

    /**
     * The original keeps its name and format, so every path already stored in
     * the database and every link already in an app cache still resolves.
     */
    private function writeOriginal(\GdImage $image, string $absolute, string $extension): void
    {
        $temporary = $absolute.'.tmp';

        $written = match ($extension) {
            'jpg', 'jpeg' => imagejpeg($image, $temporary, self::JPEG_QUALITY),
            'png' => imagepng($image, $temporary, 9),
            'webp' => imagewebp($image, $temporary, self::WEBP_QUALITY),
            default => false,
        };

        // Only swap when the rewrite actually came out smaller, so a file that
        // was already well compressed is never made worse.
        if ($written && is_file($temporary) && filesize($temporary) > 0 && filesize($temporary) < filesize($absolute)) {
            @rename($temporary, $absolute);

            return;
        }

        @unlink($temporary);
    }

    private function writeWebp(\GdImage $image, string $absolute): void
    {
        if (! function_exists('imagewebp') || strtolower(pathinfo($absolute, PATHINFO_EXTENSION)) === 'webp') {
            return;
        }

        $target = $absolute.'.webp';

        imagepalettetotruecolor($image);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        if (! @imagewebp($image, $target, self::WEBP_QUALITY)) {
            return;
        }

        // A WebP that is not smaller than the file it stands in for is only a
        // second copy on disk.
        if (! is_file($target) || filesize($target) === 0 || filesize($target) >= filesize($absolute)) {
            @unlink($target);
        }
    }
}
