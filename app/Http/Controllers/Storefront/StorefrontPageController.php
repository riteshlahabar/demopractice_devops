<?php

namespace App\Http\Controllers\Storefront;

use App\Contracts\Storefront\StorefrontAboutPageContract;
use App\Contracts\Storefront\StorefrontFaqContract;
use App\Contracts\Storefront\StorefrontPageRendererContract;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class StorefrontPageController extends Controller
{
    public function __construct(
        private readonly StorefrontPageRendererContract $pages,
        private readonly StorefrontFaqContract $faqs,
        private readonly StorefrontAboutPageContract $about
    ) {}

    public function home(Request $request): View
    {
        return $this->pages->render($request, 'index-5');
    }

    public function show(Request $request, string $page): View
    {
        abort_unless(in_array($page, config('storefront.pages', []), true), 404);

        return $this->pages->render($request, $page, $this->pageData($request, $page));
    }

    /** Content only one page needs; every other page is rendered from the shared data. */
    private function pageData(Request $request, string $page): array
    {
        return match ($page) {
            'faq' => $this->faqs->pageData($request),
            'about-us' => $this->about->pageData(),
            default => [],
        };
    }
}
