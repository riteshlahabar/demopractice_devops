<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProductHomepageSectionItem extends Model
{
    protected $fillable = [
        'section_id', 'slot', 'title', 'subtitle', 'description', 'highlight_text', 'discount_text', 'validity_text',
        'coupon_code', 'button_text', 'button_url', 'image_path', 'mobile_image_path', 'logo_image_path', 'offer_image_path', 'icon_key',
        'video_url', 'video_file_path', 'video_autoplay',
        'background_color', 'text_color', 'sort_order', 'settings', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'video_autoplay' => 'boolean',
        ];
    }

    /**
     * How this item's video should be played: an `embed` (YouTube / Vimeo) or a
     * `file` served by the browser's own player. Null when no video is set.
     * A pasted link always wins over an uploaded file.
     *
     * @return array{mode: string, src: string}|null
     */
    public function videoSource(): ?array
    {
        $url = trim((string) $this->video_url);

        if ($url !== '') {
            $embed = $this->embedUrl($url);

            return $embed !== null ? ['mode' => 'embed', 'src' => $embed] : ['mode' => 'file', 'src' => $url];
        }

        $file = trim((string) $this->video_file_path);

        return $file === '' ? null : ['mode' => 'file', 'src' => Str::startsWith($file, ['http://', 'https://']) ? $file : asset($file)];
    }

    /** Turns a YouTube or Vimeo watch link into its embed address. */
    private function embedUrl(string $url): ?string
    {
        if (preg_match('#(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{6,})#i', $url, $matches) === 1) {
            return 'https://www.youtube.com/embed/'.$matches[1];
        }

        if (preg_match('#vimeo\.com/(?:video/)?(\d+)#i', $url, $matches) === 1) {
            return 'https://player.vimeo.com/video/'.$matches[1];
        }

        return null;
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(ProductHomepageSection::class, 'section_id');
    }
}
