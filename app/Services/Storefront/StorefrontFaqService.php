<?php

namespace App\Services\Storefront;

use App\Contracts\Storefront\Repositories\StorefrontFaqRepositoryContract;
use App\Contracts\Storefront\StorefrontFaqContract;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Throwable;

final class StorefrontFaqService implements StorefrontFaqContract
{
    public function __construct(
        private readonly StorefrontFaqRepositoryContract $faqs
    ) {}

    public function pageData(Request $request): array
    {
        $search = $this->term($request->query('faq_search'));
        $category = $this->category($request->query('faq_category'));

        try {
            $questions = $this->faqs->published($category, $search);
            $counts = $this->faqs->countsByCategory();
        } catch (Throwable) {
            $questions = collect();
            $counts = [];
        }

        return [
            'faqs' => $questions,
            'faqSearch' => $search,
            'faqCategory' => $category,
            'faqCategories' => $this->categories($counts, $category),
        ];
    }

    /** Search text, trimmed and capped so a long query can never reach the database. */
    private function term(mixed $value): ?string
    {
        $term = trim((string) $value);

        return $term === '' ? null : mb_substr($term, 0, 100);
    }

    /** Only the category keys listed in config are accepted. */
    private function category(mixed $value): ?string
    {
        $key = trim((string) $value);

        return array_key_exists($key, (array) config('storefront.faq_categories', [])) ? $key : null;
    }

    /**
     * @param  array<string, int>  $counts
     * @return array<int, array{key: string, label: string, description: string, image: string, count: int, active: bool}>
     */
    private function categories(array $counts, ?string $selected): array
    {
        return (new Collection((array) config('storefront.faq_categories', [])))
            ->map(fn (array $category, string $key): array => [
                'key' => $key,
                'label' => (string) ($category['label'] ?? $key),
                'description' => (string) ($category['description'] ?? ''),
                'image' => (string) ($category['image'] ?? ''),
                'count' => (int) ($counts[$key] ?? 0),
                'active' => $selected === $key,
            ])
            ->values()
            ->all();
    }
}
