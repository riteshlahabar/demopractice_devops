<?php

namespace App\Contracts\Files;

/**
 * Turns an uploaded image into a web-sized one.
 *
 * Product photographs are uploaded straight off a camera or a designer's
 * export, so a single card image was routinely 2 MB. Nothing on the site or in
 * the apps displays one larger than about 1200 px, so the pixels past that are
 * pure download time.
 */
interface ImageOptimizerContract
{
    /** Longest edge kept, in pixels. */
    public const MAX_EDGE = 1600;

    /**
     * Shrink the image at this public-relative path in place, and write a WebP
     * copy beside it as `<path>.webp`.
     *
     * Returns the path to use from now on — the original path, since the file
     * keeps its name. Implementations must never throw: an image that cannot
     * be processed is left exactly as it was uploaded.
     */
    public function optimize(string $publicPath): string;

    /**
     * The public-relative path of the WebP copy when one exists, else null.
     * Used at render time so images uploaded before this existed still get the
     * smaller file once the backfill command has run.
     */
    public function variantFor(?string $publicPath): ?string;
}
