<?php

namespace App\Console\Commands;

use App\Contracts\Files\ImageOptimizerContract;
use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Backfill for everything uploaded before the optimizer existed. Run it once on
 * the server after deploying; it is safe to run again, because a file that is
 * already small enough is skipped and a rewrite that would not be smaller is
 * discarded.
 */
class OptimizeUploadedImages extends Command
{
    protected $signature = 'images:optimize
        {--dir=uploads : Folder under public/ to walk}
        {--min-kb=150 : Skip files smaller than this}
        {--limit=0 : Stop after this many files (0 = no limit)}
        {--dry-run : Report what would change without touching anything}';

    protected $description = 'Shrink oversized images already uploaded under public/ and write WebP copies beside them.';

    public function handle(ImageOptimizerContract $optimizer): int
    {
        $directory = public_path(trim((string) $this->option('dir'), '/'));

        if (! is_dir($directory)) {
            $this->error('Not a folder: '.$directory);

            return self::FAILURE;
        }

        if (! extension_loaded('gd')) {
            $this->error('GD is not enabled for this PHP binary, so no image can be resized.');

            return self::FAILURE;
        }

        $minimum = max(0, (int) $this->option('min-kb')) * 1024;
        $limit = max(0, (int) $this->option('limit'));
        $dryRun = (bool) $this->option('dry-run');

        $done = 0;
        $before = 0;
        $after = 0;

        foreach ($this->images($directory) as $file) {
            $size = $file->getSize();

            if ($size < $minimum) {
                continue;
            }

            $relative = str_replace('\\', '/', substr($file->getPathname(), strlen(public_path()) + 1));

            if ($dryRun) {
                $this->line(sprintf('%8.1f KB  %s', $size / 1024, $relative));
                $done++;

                continue;
            }

            $optimizer->optimize($relative);
            clearstatcache(true, $file->getPathname());

            $newSize = is_file($file->getPathname()) ? filesize($file->getPathname()) : $size;
            $webp = public_path($relative.'.webp');
            $served = is_file($webp) ? filesize($webp) : $newSize;

            $before += $size;
            $after += $served;
            $done++;

            $this->line(sprintf('%8.1f KB -> %8.1f KB  %s', $size / 1024, $served / 1024, $relative));

            if ($limit > 0 && $done >= $limit) {
                break;
            }
        }

        if ($dryRun) {
            $this->info($done.' file(s) would be processed.');

            return self::SUCCESS;
        }

        $this->info(sprintf(
            '%d file(s) processed: %.1f MB of images now served as %.1f MB.',
            $done,
            $before / 1048576,
            $after / 1048576,
        ));

        return self::SUCCESS;
    }

    /**
     * @return iterable<SplFileInfo>
     */
    private function images(string $directory): iterable
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (! $file instanceof SplFileInfo || ! $file->isFile()) {
                continue;
            }

            $extension = strtolower($file->getExtension());

            // A previously written `.webp` sibling is output, not input.
            if (in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
                yield $file;
            }
        }
    }
}
