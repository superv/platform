<?php

namespace SuperV\Platform\Domains\UI\Page\Features;

use Illuminate\Routing\Route;
use SuperV\Platform\Domains\UI\Page\Page;
use SuperV\Platform\Traits\RegistersRoutes;
use SuperV\Platform\Domains\Feature\Feature;
use SuperV\Platform\Domains\UI\Page\PageCollection;

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
            'uses' => is_callable($handler) ? $handler : (str_contains($handler, '@') ? $handler : $handler.'@'.$page->getVerb()),
        ];

        if ($port = $page->getPort()) {
            array_set($route, 'superv::port', "superv.ports.{$port}"); // TODO.ali: generic namespace
        }
        $this->dispersePortRoutes([$page->getUrl() => $route],
            function ($data) use ($page) {
                array_set($data, 'superv::droplet', $page->getDroplet()->getSlug());
            }
        );
        $pages->put($page->getRoute(), $page);

        return $route;
    }
}
