<?php

namespace App\Presenters\Catalog\Api;

use App\Contracts\Catalog\Api\CatalogTextTranslatorContract;
use App\Contracts\Catalog\Api\Presenters\ProductCatalogPresenterContract;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductMedia;
use App\Models\Catalog\ProductVariant;
use Illuminate\Support\Str;

final class ProductCatalogPresenter implements ProductCatalogPresenterContract
{
    public function __construct(private readonly CatalogTextTranslatorContract $translator) {}

    public function present(Product $product): array
    {
        $mainVariant = $product->mainVariant();

        return [
            'id' => $product->id,
            'name' => $product->storefront_name,
            'sku' => $product->sku,
            'customer_price' => $product->customer_price,
            'dealer_price' => $product->dealer_price,
            'mrp' => $product->mrp,
            'gst_percent' => $product->gst_percent,
            'product_type' => $product->product_type,
            'description' => $product->storefront_description,
            'short_description' => $product->short_description,
            'category_id' => $product->category_id,
            'category_name' => $product->category?->storefront_name,
            'unit_name' => $this->translator->text($product->unit?->name, 'unit'),
            'image_url' => $product->storefront_image_url,
            'homepage_image_url' => $this->assetUrl($product->homepage_image_path),
            'homepage_mobile_image_url' => $this->assetUrl($product->homepage_mobile_image_path),
            'image_version' => $product->updated_at?->timestamp ?: $product->id,
            'is_featured' => $product->is_featured,
            'is_trending' => $product->is_trending,
            'is_top_selling' => $product->is_top_selling,
            'is_new_arrival' => $product->is_new_arrival,
            'is_offer_product' => $product->is_offer_product,
            'main_variant_id' => $mainVariant?->id,
            'variants' => $product->variants
                ->where('is_active', true)
                ->map(fn (ProductVariant $variant): array => $this->variant($product, $variant))
                ->values()
                ->all(),
            'media' => $product->media
                ->where('is_active', true)
                ->map(fn (ProductMedia $media): array => $this->media($media))
                ->values()
                ->all(),
        ];
    }

    private function variant(Product $product, ProductVariant $variant): array
    {
        $unitsPerCase = max(1.0, (float) $variant->units_per_case);
        $dealerPrice = (float) ($variant->dealer_price ?? $product->dealer_price ?? 0);
        $customerPrice = (float) ($variant->customer_price ?? $product->customer_price ?? 0);
        $mrp = (float) ($variant->mrp ?? $product->mrp ?? 0);
        $availableStock = max(0.0, (float) $variant->available_stock);

        return [
            'id' => $variant->id,
            'name' => $variant->display_name,
            'value' => $variant->value,
            'size_value' => $variant->size_value !== null ? (float) $variant->size_value : null,
            'size_unit' => $variant->size_unit,
            'variant_sku' => $variant->variant_sku,
            'units_per_case' => $unitsPerCase,
            'mrp' => $mrp,
            'dealer_price' => $dealerPrice,
            'dealer_case_price' => round($dealerPrice * $unitsPerCase, 2),
            'customer_price' => $customerPrice,
            'available_stock' => round($availableStock, 3),
            'available_cases' => (int) floor(($availableStock + 0.000001) / $unitsPerCase),
            'is_default' => (bool) $variant->is_default,
            'sort_order' => (int) $variant->sort_order,
        ];
    }

    private function media(ProductMedia $media): array
    {
        return [
            'id' => $media->id,
            'source_type' => $media->source_type,
            'url' => $media->url,
            'youtube_url' => $media->youtube_url,
            'embed_url' => $media->embed_url,
            'thumbnail_url' => $media->thumbnail_url,
            'title' => $media->title,
            'language' => $media->language,
            'sort_order' => (int) $media->sort_order,
        ];
    }

    private function assetUrl(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        return Str::startsWith($path, ['http://', 'https://']) ? $path : asset($path);
    }
}
