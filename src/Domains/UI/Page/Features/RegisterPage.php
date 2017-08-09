<?php namespace SuperV\Platform\Domains\UI\Page\Features;

use Illuminate\Routing\Router;
use SuperV\Platform\Domains\Droplet\Jobs\RegisterDropletRouteJob;
use SuperV\Platform\Domains\Feature\Feature;
use SuperV\Platform\Domains\UI\Page\Page;
use SuperV\Platform\Domains\UI\Page\PageCollection;

class RegisterPage extends Feature
{
    /**
     * @var Page
     */
    private $page;

    public function __construct(Page $page)
    {
        $this->page = $page;
    }

    public function handle(Router $router, PageCollection $pages)
    {
        $page = $this->page;
        $handler = $this->page->getHandler();

        $route = [
            'as'   => $page->getRoute(),
            'uses' => is_callable($handler) ? $handler : $handler . '@' . $page->getPage(),
        ];

        $this->dispatch(new RegisterDropletRouteJob($page->getDroplet(), $page->getUrl(), $route));

        $pages->put($page->getRoute(), $page);

        return $route;
    }
}