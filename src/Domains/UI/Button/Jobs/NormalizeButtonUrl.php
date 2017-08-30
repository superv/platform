<?php

namespace SuperV\Platform\Domains\UI\Button\Jobs;

use SuperV\Platform\Domains\Entry\EntryModel;
use SuperV\Platform\Domains\UI\Page\Page;

class NormalizeButtonUrl
{
    private $entry;

    private $params;

    private $arguments;

    public function __construct(&$params, &$arguments)
    {
        $this->params = $params;
        $this->arguments = $arguments;
    }

    public function handle()
    {
        if ($href = array_pull($this->params, 'href')) {
            array_set($this->params, 'attributes.href', $href);
        }

        if ($entry = array_get($this->arguments, 'entry')) {
            if ($entry instanceof EntryModel) {
                $verb = array_get($this->params, 'button');
                // If entry model has a page for this verb, use it,
                // otherwise check the entry router, for a (default) route

                /** @var Page $page */
                if ($page = $entry->page($verb)) {
//                    $url = route($page->getRoute(), ['entry' => $entry]);
                    $url = $page->makeRoute();
                    array_set($this->params, 'text', $page->getTitle());
                } else {
                    $url = $entry->route($verb);
                }
                array_set_if($url, $this->params, 'attributes.href', $url);
            }
        }

        if ($route = array_pull($this->params, 'route')) {
            array_set($this->params, 'attributes.href', route($route));
        }

        return $this->params;
    }
}
