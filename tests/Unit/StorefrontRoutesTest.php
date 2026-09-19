<?php

namespace Tests\Unit;

use Illuminate\Routing\Route;
use Tests\TestCase;

class StorefrontRoutesTest extends TestCase
{
    public function test_existing_storefront_route_names_and_uris_are_preserved(): void
    {
        $expected = [
            'store.home' => '/',
            'store.invoice-preview' => 'invoice-preview/{template}',
            'store.language' => 'language/{locale}',
            'store.email-preview' => 'email-preview/{template}',
            'store.category' => 'category/{category}',
            'store.product' => 'product/{product}',
            'store.page' => '{page}',
        ];

        $routes = collect($this->app['router']->getRoutes()->getRoutes())
            ->filter(fn (Route $route): bool => array_key_exists((string) $route->getName(), $expected))
            ->keyBy(fn (Route $route): string => (string) $route->getName());

        foreach ($expected as $name => $uri) {
            $this->assertSame($uri, $routes->get($name)?->uri());
            $this->assertContains('GET', $routes->get($name)?->methods() ?? []);
        }

        $this->assertSame('slug', $routes->get('store.category')?->bindingFieldFor('category'));
    }
}
