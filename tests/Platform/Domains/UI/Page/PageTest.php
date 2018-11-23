<?php

namespace Tests\Platform\Domains\UI\Page;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Domains\UI\Components\PageComponent;
use SuperV\Platform\Domains\UI\Page\Page;
use SuperV\Platform\Domains\UI\Page\PageConfig;
use Tests\Platform\TestCase;

class PageTest extends TestCase
{
    use RefreshDatabase;

    function test__construct()
    {
        $page = Page::make('title');
        $this->assertInstanceOf(Page::class, $page);
        $this->assertNotNull($page->uuid());
    }

    function test__makes_component()
    {
        $page = Page::make('Users Index Page');
        $page->setMeta('subtitle', 'Page Subtitle');
        $page->addBlock('block a');
        $page->addBlock('block b');
        $page->setActions(['action a', 'action b']);

        $component = $page->makeComponent();
        $this->assertInstanceOf(PageComponent::class, $component);

        $this->assertEquals([
            'component' => 'sv-page',
            'uuid'      => $page->uuid(),
            'props'     => [
                'meta'    => [
                    'title'    => 'Users Index Page',
                    'subtitle' => 'Page Subtitle',
                ],
                'actions' => ['action a', 'action b'],
                'blocks'  => ['block a', 'block b'],
            ],
            'class' => ['w-full']
        ], $component->compose());
    }
}