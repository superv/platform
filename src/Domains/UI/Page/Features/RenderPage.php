<?php

namespace SuperV\Platform\Domains\UI\Page\Features;

use SuperV\Platform\Domains\Feature\Feature;
use SuperV\Platform\Domains\UI\Page\Page;

class RenderPage extends Feature
{
    /**
     * @var Page
     */
    private $page;

    public function __construct(Page $page)
    {
        $this->page = $page;
    }

    public function handle()
    {
        if (!$handler = $this->page->getHandler()) {
            return null;
        }

        $this->dispatch(new MakePageButtons($this->page));

        $params = [];
        if ($entry = $this->page->getEntry()) {
            $params = ['entry' => $this->page->getEntry()];
        }
        return app()->call($handler, $params);
    }
}