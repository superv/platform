<?php namespace SuperV\Platform\Domains\UI\Page\Features;

use SuperV\Platform\Domains\Feature\Feature;
use SuperV\Platform\Domains\UI\Page\Page;
use SuperV\Platform\Domains\UI\Page\PageCollection;
use SuperV\Platform\Traits\RegistersRoutes;

class RegisterPage extends Feature
{
    use RegistersRoutes;

    /**
     * @var Page
     */
    private $page;

    public function __construct(Page $page)
    {
        $this->page = $page;
    }

    public function handle(PageCollection $pages)
    {
        $page = $this->page;
        $handler = $this->page->getHandler();

        $route = [
            'as'   => $page->getRoute(),
            'uses' => is_callable($handler) ? $handler : (str_contains($handler, '@') ? $handler : $handler . '@' . $page->getVerb()),
        ];

        $this->registerRoutes([$page->getUrl() => $route], function ($route) use ($page) {
            array_set($route, 'superv::droplet', $page->getDroplet()->getSlug());
        });
//        $this->dispatch(new RegisterDropletRouteJob($page->getDroplet(), $page->getUrl(), $route));

        $pages->put($page->getRoute(), $page);

        return $route;
    }
}