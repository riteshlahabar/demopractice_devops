<?php

namespace Tests\Unit;

use App\Contracts\Catalog\Product\ProductImageContract;
use App\Contracts\Catalog\Product\ProductMediaContract;
use App\Contracts\Catalog\Product\ProductRepositoryContract;
use App\Contracts\Catalog\Product\ProductSkuContract;
use App\Contracts\Catalog\Product\ProductStockContract;
use App\Contracts\Catalog\Product\ProductTranslationContract;
use App\Contracts\Catalog\Product\ProductVariantContract;
use App\Contracts\Files\PublicUploadContract;
use App\Data\Catalog\ProductSaveData;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductImage;
use App\Services\Catalog\Product\ProductWorkflowService;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\TestCase;

/**
 * DB-free: every collaborator is a hand-written stub, and Product models are
 * constructed in memory rather than persisted, matching the pattern already
 * used by ProductVariantFormDataTest on this machine (no working local DB).
 */
class ProductWorkflowImageCleanupTest extends TestCase
{
    public function test_replacing_an_image_field_deletes_the_old_file(): void
    {
        $uploads = new RecordingUploads;
        $service = $this->service($uploads);
        $product = $this->product(['detail_banner_image' => 'uploads/products/detail-banners/old.png']);

        $service->save($this->data(['detail_banner_image' => 'uploads/products/detail-banners/new.png']), $product);

        $this->assertSame(['uploads/products/detail-banners/old.png'], $uploads->deleted);
    }

    public function test_untouched_fields_are_left_alone(): void
    {
        $uploads = new RecordingUploads;
        $service = $this->service($uploads);
        $product = $this->product(['detail_banner_image' => 'uploads/products/detail-banners/old.png']);

        // No new upload for this field, so ModuleInput would never include it.
        $service->save($this->data([]), $product);

        $this->assertSame([], $uploads->deleted);
    }

    public function test_creating_a_product_never_deletes_anything(): void
    {
        $uploads = new RecordingUploads;
        $service = $this->service($uploads);

        $service->save($this->data(['detail_banner_image' => 'uploads/products/detail-banners/new.png']), null);

        $this->assertSame([], $uploads->deleted);
    }

    private function service(PublicUploadContract $uploads): ProductWorkflowService
    {
        return new ProductWorkflowService(
            new class implements ProductRepositoryContract
            {
                public function save(array $data, ?Product $product = null): Product
                {
                    $product ??= new Product;
                    $product->fill($data);
                    $product->exists = true;

                    return $product;
                }

                public function fresh(Product $product, array $relations = []): Product
                {
                    return $product;
                }
            },
            new class implements ProductImageContract
            {
                public function sync(Product $product, ?string $primaryImagePath, array $galleryPaths, array $removeGalleryIds): void {}

                public function destroyGalleryImage(Product $product, ProductImage $image): void {}

                public function destroyFieldImage(Product $product, string $field): void {}

                public function formData(Product $product): array
                {
                    return [];
                }
            },
            new class implements ProductStockContract
            {
                public function createOpeningStock(Product $product, ?array $stock): void {}

                public function syncVariantOpeningStock(Product $product, $variant, array $row): void {}
            },
            new class implements ProductVariantContract
            {
                public function sync(Product $product, array $variants): void {}
            },
            new class implements ProductMediaContract
            {
                public function sync(Product $product, array $media): void {}

                public function formData(Product $product): array
                {
                    return [];
                }
            },
            new class implements ProductTranslationContract
            {
                public function translatePayload(string $name, ?string $description): array
                {
                    return [];
                }

                public function extract(array &$data): array
                {
                    return [];
                }

                public function sync(Product $product, array $translations): void {}

                public function formData(Product $product): array
                {
                    return [];
                }
            },
            new class implements ProductSkuContract
            {
                public function generate(?string $productName = null): string
                {
                    return 'SKU-1';
                }
            },
            $uploads,
        );
    }

    private function product(array $attributes): Product
    {
        $product = new Product($attributes);
        $product->exists = true;
        $product->setRelation('images', collect([]));
        $product->setRelation('media', collect([]));

        return $product;
    }

    private function data(array $product): ProductSaveData
    {
        return new ProductSaveData($product, null, [], [], null, [], [], []);
    }
}

class RecordingUploads implements PublicUploadContract
{
    public array $deleted = [];

    public function store(UploadedFile $file, string $directory): string
    {
        return 'uploads/stub.png';
    }

    public function delete(?string $path): void
    {
        if ($path) {
            $this->deleted[] = $path;
        }
    }
}
