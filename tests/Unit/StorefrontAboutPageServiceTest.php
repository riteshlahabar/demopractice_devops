<?php

namespace Tests\Unit;

use App\Contracts\Storefront\Repositories\StorefrontAboutRepositoryContract;
use App\Models\Storefront\StorefrontAboutItem;
use App\Models\Storefront\StorefrontAboutPage;
use App\Models\Storefront\StorefrontTeamMember;
use App\Services\Storefront\StorefrontAboutPageService;
use Illuminate\Support\Collection;
use RuntimeException;
use Tests\TestCase;

class StorefrontAboutPageServiceTest extends TestCase
{
    public function test_page_content_is_split_into_its_blocks(): void
    {
        $data = (new StorefrontAboutPageService($this->repository()))->pageData();

        $this->assertSame('About Us', $data['aboutPage']->intro_label);
        $this->assertCount(1, $data['aboutHighlights']);
        $this->assertSame(StorefrontAboutItem::BLOCK_HIGHLIGHT, $data['aboutHighlights']->first()->block);
        $this->assertCount(1, $data['aboutStats']);
        $this->assertSame('10+', $data['aboutStats']->first()->value);
        $this->assertCount(1, $data['aboutTeam']);
    }

    public function test_intro_text_is_split_into_paragraphs(): void
    {
        $page = new StorefrontAboutPage(['intro_text' => "First.\n\n  Second.  \n\n\nThird."]);

        $this->assertSame(['First.', 'Second.', 'Third.'], $page->introParagraphs());
        $this->assertSame([], (new StorefrontAboutPage(['intro_text' => ' ']))->introParagraphs());
    }

    public function test_only_filled_social_links_are_shown_with_their_icon(): void
    {
        $member = new StorefrontTeamMember(['name' => 'A', 'facebook_url' => 'https://facebook.com/x', 'linkedin_url' => 'https://linkedin.com/in/x']);
        $links = $member->socialLinks();

        $this->assertCount(2, $links);
        $this->assertSame('fb-bg', $links[0]['class']);
        $this->assertSame('linkedin-bg', $links[1]['class']);
        $this->assertSame([], (new StorefrontTeamMember(['name' => 'B']))->socialLinks());
    }

    public function test_a_database_failure_hides_every_block_instead_of_erroring(): void
    {
        $data = (new StorefrontAboutPageService($this->failingRepository()))->pageData();

        $this->assertNull($data['aboutPage']);
        $this->assertTrue($data['aboutHighlights']->isEmpty());
        $this->assertTrue($data['aboutStats']->isEmpty());
        $this->assertTrue($data['aboutTeam']->isEmpty());
    }

    private function repository(): StorefrontAboutRepositoryContract
    {
        return new class implements StorefrontAboutRepositoryContract
        {
            public function page(): ?StorefrontAboutPage
            {
                return new StorefrontAboutPage(['intro_label' => 'About Us', 'intro_heading' => 'Heading', 'intro_text' => 'One.']);
            }

            public function items(string $block): Collection
            {
                return new Collection([new StorefrontAboutItem([
                    'block' => $block,
                    'title' => $block === StorefrontAboutItem::BLOCK_STAT ? 'Years in Business' : 'GST invoice with every order',
                    'value' => $block === StorefrontAboutItem::BLOCK_STAT ? '10+' : null,
                ])]);
            }

            public function teamMembers(): Collection
            {
                return new Collection([new StorefrontTeamMember(['name' => 'Ritesh', 'role' => 'Sales Head'])]);
            }
        };
    }

    private function failingRepository(): StorefrontAboutRepositoryContract
    {
        return new class implements StorefrontAboutRepositoryContract
        {
            public function page(): ?StorefrontAboutPage
            {
                throw new RuntimeException('database down');
            }

            public function items(string $block): Collection
            {
                throw new RuntimeException('database down');
            }

            public function teamMembers(): Collection
            {
                throw new RuntimeException('database down');
            }
        };
    }
}
