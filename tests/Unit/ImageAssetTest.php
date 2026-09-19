<?php

namespace Tests\Unit;

use App\Services\Files\ImageOptimizerService;
use App\Support\ImageAsset;
use Tests\TestCase;

class ImageAssetTest extends TestCase
{
    private string $directory = 'uploads/test-image-asset';

    protected function setUp(): void
    {
        parent::setUp();
        ImageAsset::forget();
    }

    protected function tearDown(): void
    {
        $absolute = public_path($this->directory);

        if (is_dir($absolute)) {
            array_map('unlink', glob($absolute.'/*') ?: []);
            rmdir($absolute);
        }

        ImageAsset::forget();
        parent::tearDown();
    }

    private function writeFile(string $name, string $contents = 'x'): string
    {
        $absolute = public_path($this->directory);

        if (! is_dir($absolute)) {
            mkdir($absolute, 0755, true);
        }

        file_put_contents($absolute.'/'.$name, $contents);

        return $this->directory.'/'.$name;
    }

    public function test_the_webp_copy_is_preferred_when_it_exists(): void
    {
        $path = $this->writeFile('photo.png');

        $this->assertSame($path, ImageAsset::best($path));

        $this->writeFile('photo.png.webp');
        ImageAsset::forget();

        $this->assertSame($path.'.webp', ImageAsset::best($path));
        $this->assertStringEndsWith('photo.png.webp', (string) ImageAsset::url($path));
    }

    public function test_an_external_url_is_returned_untouched(): void
    {
        $this->assertSame('https://cdn.example.com/a.png', ImageAsset::url('https://cdn.example.com/a.png'));
        $this->assertSame('data:image/png;base64,AAAA', ImageAsset::url('data:image/png;base64,AAAA'));
    }

    public function test_a_blank_path_has_no_url(): void
    {
        $this->assertNull(ImageAsset::url(null));
        $this->assertNull(ImageAsset::url('   '));
    }

    public function test_a_webp_original_is_never_given_a_second_webp_suffix(): void
    {
        $this->assertSame('uploads/a.webp', ImageAsset::best('uploads/a.webp'));
        $this->assertNull((new ImageOptimizerService)->variantFor('uploads/a.webp'));
        $this->assertNull((new ImageOptimizerService)->variantFor('https://cdn.example.com/a.png'));
    }

    public function test_optimizing_a_path_outside_the_public_root_changes_nothing(): void
    {
        // Never throws, and hands back exactly what it was given, so a save is
        // never lost to an image that could not be processed.
        $service = new ImageOptimizerService;

        $this->assertSame('../../etc/passwd', $service->optimize('../../etc/passwd'));
        $this->assertSame('uploads/does-not-exist.png', $service->optimize('uploads/does-not-exist.png'));
    }
}
