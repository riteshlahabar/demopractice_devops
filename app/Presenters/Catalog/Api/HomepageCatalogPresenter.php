<?php

namespace App\Presenters\Catalog\Api;

use App\Contracts\Catalog\Api\CatalogTextTranslatorContract;
use App\Contracts\Catalog\Api\Presenters\HomepageCatalogPresenterContract;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductHomepageSectionItem;
use App\Models\Storefront\StorefrontBanner;
use Illuminate\Support\Str;

final class HomepageCatalogPresenter implements HomepageCatalogPresenterContract
{
    public function __construct(private readonly CatalogTextTranslatorContract $translator) {}

    public function item(ProductHomepageSectionItem $item): array
    {
        return [
            'id' => $item->id,
            'slot' => $item->slot,
            'title' => $this->entry($item->title),
            'subtitle' => $this->entry($item->subtitle),
            'description' => $this->entry($item->description),
            'highlight_text' => $this->entry($item->highlight_text),
            'discount_text' => $this->entry($item->discount_text),
            'validity_text' => $this->entry($item->validity_text),
            'coupon_code' => $item->coupon_code,
            'button_text' => $this->button($item->button_text),
            'button_url' => $item->button_url,
            'image_url' => $this->assetUrl($item->image_path),
            'mobile_image_url' => $this->assetUrl($item->mobile_image_path),
            'logo_image_url' => $this->assetUrl($item->logo_image_path),
            'offer_image_url' => $this->assetUrl($item->offer_image_path),
            'background_color' => $item->background_color,
            'text_color' => $item->text_color,
        ];
    }

    /**
     * Mirrors the storefront template's product entry helpers, so the apps
     * show the same banner / offer content as the website.
     */
    public function productEntry(Product $product, bool $nameAsTitle = true): array
    {
        $image = $product->homepage_image_path ?: data_get($product, 'images.0.path');

        return [
            'id' => $product->id,
            'product_id' => $product->id,
            'slot' => 'product',
            'title' => filled($product->homepage_title) ? $this->entry($product->homepage_title) : ($nameAsTitle ? $product->storefront_name : null),
            'subtitle' => $this->entry($product->homepage_subtitle ?: $product->sale_badge_text),
            'description' => $this->entry($product->homepage_description ?: $product->short_description),
            'highlight_text' => $this->entry($product->homepage_highlight_text),
            'discount_text' => $this->entry($product->homepage_discount_text),
            'validity_text' => $this->entry($product->homepage_validity_text),
            'coupon_code' => $product->homepage_coupon_code,
            'button_text' => $this->button($product->homepage_button_text),
            'button_url' => $product->homepage_button_url,
            'image_url' => $this->assetUrl($image),
            'mobile_image_url' => $this->assetUrl($product->homepage_mobile_image_path),
            'logo_image_url' => $this->assetUrl($product->homepage_logo_image_path ?: $image),
            'offer_image_url' => $this->assetUrl($product->homepage_offer_image_path ?: $image),
            'background_color' => $product->homepage_background_color,
            'text_color' => $product->homepage_text_color,
        ];
    }

    public function fallbackBanner(StorefrontBanner $banner): array
    {
        return [
            'id' => $banner->id,
            'slot' => $banner->placement,
            'title' => $this->entry($banner->title),
            'subtitle' => $this->entry($banner->subtitle),
            'description' => $this->entry($banner->description),
            'highlight_text' => null,
            'discount_text' => $this->entry($banner->subtitle),
            'validity_text' => null,
            'coupon_code' => null,
            'button_text' => $this->button($banner->button_text),
            'button_url' => $banner->button_url,
            'image_url' => $this->assetUrl($banner->image_path),
            'mobile_image_url' => null,
            'logo_image_url' => null,
            'offer_image_url' => null,
            'background_color' => null,
            'text_color' => null,
        ];
    }

    public function legacyBanner(StorefrontBanner $banner): array
    {
        return [
            'id' => $banner->id,
            'title' => $this->entry($banner->title),
            'subtitle' => $this->entry($banner->subtitle),
            'description' => $this->entry($banner->description),
            'button_text' => $this->button($banner->button_text),
            'button_url' => $banner->button_url,
            'image_url' => $this->assetUrl($banner->image_path),
        ];
    }

    private function entry(?string $text): ?string
    {
        return $this->translator->text($text, 'homepage_entry');
    }

    private function button(?string $text): ?string
    {
        return $this->translator->text($text, 'homepage_button');
    }

    private function assetUrl(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        return Str::startsWith($path, ['http://', 'https://']) ? $path : asset($path);
    }
}
