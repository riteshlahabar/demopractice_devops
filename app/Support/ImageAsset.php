<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Builds the URL of an uploaded image, preferring the smaller WebP copy that
 * `images:optimize` (and every new upload) writes beside the original as
 * `<file>.webp`.
 *
 * It is a plain static helper rather than an injected service because it is
 * called from model accessors and Blade, does nothing but look at the file
 * system, and holds no state. The original file is always left in place, so an
 * image whose WebP is missing — or a browser that asked for the original URL
 * directly — still works.
 */
final class ImageAsset
{
    /** Per-request memo: one page can render the same product image many times. */
    private static array $resolved = [];

    public static function url(?string $path): ?string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', 'data:', '//'])) {
            return $path;
        }

        return asset(self::best($path));
    }

    /**
     * The public-relative path actually worth serving: the WebP sibling when it
     * exists, else the path given.
     */
    public static function best(string $path): string
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');

        if (array_key_exists($path, self::$resolved)) {
            return self::$resolved[$path];
        }

        $best = $path;

        if (! Str::endsWith(strtolower($path), '.webp') && ! str_contains($path, '..')) {
            $candidate = $path.'.webp';

            if (is_file(public_path($candidate))) {
                $best = $candidate;
            }
        }

        return self::$resolved[$path] = $best;
    }

    /** Only for tests, which create and delete files between assertions. */
    public static function forget(): void
    {
        self::$resolved = [];
    }
}
