<?php

namespace App\Services\Files;

use App\Contracts\Files\ImageOptimizerContract;
use App\Contracts\Files\PublicUploadContract;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

final class PublicUploadService implements PublicUploadContract
{
    /**
     * Files land under the public web root, so the extension decides whether
     * the server would ever execute or inline-render them. Request validation
     * already restricts uploads; this is the last line that does not depend on
     * a caller remembering the right rule. SVG is excluded on purpose - it can
     * carry script and is served from our own origin.
     */
    private const ALLOWED_EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'webp', 'gif', 'avif',
        'mp4', 'webm',
        'pdf', 'csv', 'xlsx', 'zip',
    ];

    public function __construct(
        private readonly ImageOptimizerContract $optimizer
    ) {}

    public function store(UploadedFile $file, string $directory): string
    {
        $directory = trim(str_replace('\\', '/', $directory), '/');
        if ($directory === '' || str_contains($directory, '..')) {
            throw UnsupportedUploadException::directory($directory);
        }

        $publicDirectory = public_path($directory);
        if (! is_dir($publicDirectory)) {
            mkdir($publicDirectory, 0755, true);
        }

        $filename = now()->format('YmdHis').'-'.Str::random(16).'.'.$this->extension($file);
        $file->move($publicDirectory, $filename);

        // Shrinking here rather than at the call sites means every module that
        // uploads an image gets it, present and future.
        return $this->optimizer->optimize($directory.'/'.$filename);
    }

    private function extension(UploadedFile $file): string
    {
        // Prefer the extension derived from the real mime type; the client
        // supplied one is only a fallback and is checked the same way.
        foreach ([$file->extension(), $file->getClientOriginalExtension()] as $candidate) {
            $candidate = strtolower(trim((string) $candidate));

            if (in_array($candidate, self::ALLOWED_EXTENSIONS, true)) {
                return $candidate;
            }
        }

        throw UnsupportedUploadException::extension(strtolower(trim((string) $file->getClientOriginalExtension())));
    }

    public function delete(?string $path): void
    {
        $path = trim((string) $path);
        if ($path === '' || Str::startsWith($path, ['http://', 'https://']) || str_contains($path, '..')) {
            return;
        }

        $absolutePath = public_path(ltrim(str_replace('\\', '/', $path), '/'));
        $publicRoot = realpath(public_path());
        $realFile = is_file($absolutePath) ? realpath($absolutePath) : false;

        if ($publicRoot && $realFile && Str::startsWith($realFile, $publicRoot)) {
            @unlink($realFile);
        }
    }
}
