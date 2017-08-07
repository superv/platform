<?php namespace SuperV\Platform\Domains\UI\Page\Features;

use Illuminate\Routing\Router;
use SuperV\Platform\Domains\Feature\Feature;
use SuperV\Platform\Domains\UI\Page\Page;

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

    public function handle(Router $router)
    {
        $route = [
            'as'   => $this->page->getRoute(),
            'uses' => $this->page->getHandler() . '@' . $this->page->getPage(),
        ];
        $uri = $this->page->getUrl();

        $constraints = [];
        $route = $router->any($uri, $route)->where($constraints);

        return $route;
    }
}