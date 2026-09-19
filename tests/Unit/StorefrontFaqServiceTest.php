<?php

namespace Tests\Unit;

use App\Contracts\Storefront\Repositories\StorefrontFaqRepositoryContract;
use App\Models\Storefront\StorefrontFaq;
use App\Services\Storefront\StorefrontFaqService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use RuntimeException;
use Tests\TestCase;

class StorefrontFaqServiceTest extends TestCase
{
    public function test_search_term_and_known_category_reach_the_repository(): void
    {
        $repository = $this->repository();
        $data = (new StorefrontFaqService($repository))->pageData(
            Request::create('/faq', 'GET', ['faq_search' => '  delivery  ', 'faq_category' => 'orders-delivery'])
        );

        $this->assertSame('delivery', $repository->search);
        $this->assertSame('orders-delivery', $repository->category);
        $this->assertSame('delivery', $data['faqSearch']);
        $this->assertSame('orders-delivery', $data['faqCategory']);
        $this->assertCount(1, $data['faqs']);
    }

    public function test_unknown_category_is_ignored_and_blank_search_is_null(): void
    {
        $repository = $this->repository();
        $data = (new StorefrontFaqService($repository))->pageData(
            Request::create('/faq', 'GET', ['faq_search' => '   ', 'faq_category' => 'nonsense'])
        );

        $this->assertNull($repository->search);
        $this->assertNull($repository->category);
        $this->assertNull($data['faqSearch']);
        $this->assertNull($data['faqCategory']);
    }

    public function test_long_search_is_capped(): void
    {
        $repository = $this->repository();
        (new StorefrontFaqService($repository))->pageData(
            Request::create('/faq', 'GET', ['faq_search' => str_repeat('a', 300)])
        );

        $this->assertSame(100, mb_strlen((string) $repository->search));
    }

    public function test_cards_carry_counts_and_mark_the_selected_category(): void
    {
        $data = (new StorefrontFaqService($this->repository()))->pageData(
            Request::create('/faq', 'GET', ['faq_category' => 'support'])
        );

        $keys = array_column($data['faqCategories'], 'key');
        $this->assertSame(array_keys((array) config('storefront.faq_categories')), $keys);

        $cards = collect($data['faqCategories'])->keyBy('key');
        $this->assertTrue($cards['support']['active']);
        $this->assertFalse($cards['getting-started']['active']);
        $this->assertSame(3, $cards['support']['count']);
        $this->assertSame(0, $cards['pricing-payment']['count']);
        $this->assertSame('Support & Returns', $cards['support']['label']);
    }

    public function test_a_database_failure_shows_an_empty_page_instead_of_an_error(): void
    {
        $data = (new StorefrontFaqService($this->failingRepository()))->pageData(Request::create('/faq'));

        $this->assertTrue($data['faqs']->isEmpty());
        $this->assertSame(0, $data['faqCategories'][0]['count']);
    }

    public function test_answer_is_split_into_paragraphs(): void
    {
        $faq = new StorefrontFaq(['answer' => "First line.\n\n  Second line.  \n\n\nThird line."]);

        $this->assertSame(['First line.', 'Second line.', 'Third line.'], $faq->answerParagraphs());
        $this->assertSame([], (new StorefrontFaq(['answer' => "  \n "]))->answerParagraphs());
    }

    private function repository(): StorefrontFaqRepositoryContract
    {
        return new class implements StorefrontFaqRepositoryContract
        {
            public ?string $category = null;

            public ?string $search = null;

            public function published(?string $category = null, ?string $search = null): Collection
            {
                $this->category = $category;
                $this->search = $search;

                return new Collection([new StorefrontFaq(['question' => 'How long does delivery take?', 'answer' => 'It depends on your district.'])]);
            }

            public function countsByCategory(): array
            {
                return ['support' => 3, 'orders-delivery' => 2];
            }
        };
    }

    private function failingRepository(): StorefrontFaqRepositoryContract
    {
        return new class implements StorefrontFaqRepositoryContract
        {
            public function published(?string $category = null, ?string $search = null): Collection
            {
                throw new RuntimeException('database down');
            }

            public function countsByCategory(): array
            {
                throw new RuntimeException('database down');
            }
        };
    }
}
