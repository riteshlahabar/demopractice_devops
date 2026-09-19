<?php

namespace Tests\Unit;

use App\Models\Catalog\ProductHomepageSectionItem;
use Tests\TestCase;

class HomepageVideoItemTest extends TestCase
{
    public function test_youtube_and_vimeo_links_become_embeds(): void
    {
        $cases = [
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
            'https://youtu.be/dQw4w9WgXcQ' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
            'https://www.youtube.com/shorts/dQw4w9WgXcQ' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
            'https://vimeo.com/76979871' => 'https://player.vimeo.com/video/76979871',
        ];

        foreach ($cases as $link => $embed) {
            $source = (new ProductHomepageSectionItem(['video_url' => $link]))->videoSource();

            $this->assertSame('embed', $source['mode'], $link);
            $this->assertSame($embed, $source['src'], $link);
        }
    }

    public function test_a_direct_link_is_played_as_a_file(): void
    {
        $source = (new ProductHomepageSectionItem(['video_url' => 'https://cdn.example.com/clip.mp4']))->videoSource();

        $this->assertSame('file', $source['mode']);
        $this->assertSame('https://cdn.example.com/clip.mp4', $source['src']);
    }

    public function test_an_uploaded_file_is_served_from_the_site(): void
    {
        $source = (new ProductHomepageSectionItem(['video_file_path' => 'uploads/storefront/videos/clip.mp4']))->videoSource();

        $this->assertSame('file', $source['mode']);
        $this->assertSame(asset('uploads/storefront/videos/clip.mp4'), $source['src']);
    }

    public function test_a_link_wins_over_an_uploaded_file_and_no_video_is_null(): void
    {
        $item = new ProductHomepageSectionItem([
            'video_url' => 'https://youtu.be/dQw4w9WgXcQ',
            'video_file_path' => 'uploads/storefront/videos/clip.mp4',
        ]);

        $this->assertSame('https://www.youtube.com/embed/dQw4w9WgXcQ', $item->videoSource()['src']);
        $this->assertNull((new ProductHomepageSectionItem(['title' => 'No video']))->videoSource());
    }
}
