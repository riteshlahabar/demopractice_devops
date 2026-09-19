<?php

namespace App\Services\Catalog\Product;

use App\Contracts\Catalog\Product\ProductMediaContract;
use App\Contracts\Files\PublicUploadContract;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductMedia;
use Illuminate\Http\UploadedFile;

final class ProductMediaService implements ProductMediaContract
{
    public function __construct(private readonly PublicUploadContract $uploads) {}

    public function sync(Product $product, array $mediaRows): void
    {
        $keptIds = [];
        foreach ($mediaRows as $index => $row) {
            $sourceType = (string) ($row['source_type'] ?? 'upload');
            $mediaId = (int) ($row['id'] ?? 0);
            $media = $mediaId > 0 ? $product->media()->whereKey($mediaId)->firstOrFail() : new ProductMedia(['product_id' => $product->id]);
            $filePath = $mediaId > 0 ? $media->file_path : null;
            $thumbnailPath = $mediaId > 0 ? $media->thumbnail_path : null;

            $file = $row['file'] ?? null;
            $thumbnail = $row['thumbnail'] ?? null;
            if ($file instanceof UploadedFile && $file->isValid()) {
                $filePath = $this->uploads->store($file, 'uploads/products/videos');
            }
            if ($thumbnail instanceof UploadedFile && $thumbnail->isValid()) {
                $thumbnailPath = $this->uploads->store($thumbnail, 'uploads/products/video-thumbnails');
            }

            $youtubeUrl = filled($row['youtube_url'] ?? null) ? trim((string) $row['youtube_url']) : null;
            if (($sourceType === 'upload' && blank($filePath)) || ($sourceType === 'youtube' && blank($youtubeUrl))) {
                continue;
            }

            $media->fill([
                'source_type' => $sourceType,
                'file_path' => $sourceType === 'upload' ? $filePath : null,
                'youtube_url' => $sourceType === 'youtube' ? $youtubeUrl : null,
                'title' => filled($row['title'] ?? null) ? trim((string) $row['title']) : null,
                'thumbnail_path' => $thumbnailPath,
                'language' => filled($row['language'] ?? null) ? trim((string) $row['language']) : null,
                'sort_order' => (int) ($row['sort_order'] ?? $index),
                'is_active' => filter_var($row['is_active'] ?? true, FILTER_VALIDATE_BOOL),
            ]);
            $media->save();
            $keptIds[] = $media->id;
        }

        $product->media()->when($keptIds !== [], fn ($query) => $query->whereNotIn('id', $keptIds))->update(['is_active' => false]);
        if ($keptIds === []) {
            $product->media()->update(['is_active' => false]);
        }
    }

    public function formData(Product $product): array
    {
        $media = $product->relationLoaded('media') ? $product->media : $product->media()->get();

        return $media->where('is_active', true)->map(fn (ProductMedia $item): array => [
            'id' => $item->id,
            'source_type' => $item->source_type,
            'file_path' => $item->file_path,
            'youtube_url' => $item->youtube_url,
            'title' => $item->title,
            'thumbnail_path' => $item->thumbnail_path,
            'language' => $item->language,
            'sort_order' => $item->sort_order,
            'is_active' => $item->is_active,
        ])->values()->all();
    }
}
