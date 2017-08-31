<?php

namespace SuperV\Platform\Domains\UI\Page\Jobs;

use SuperV\Platform\Support\Hydrator;
use SuperV\Platform\Support\Collection;
use SuperV\Platform\Domains\UI\Page\Page;
use SuperV\Platform\Domains\Manifest\Manifest;

class BuildManifestPagesJob
{
    /**
     * @var Manifest
     */
    private $manifest;

    public function __construct(Manifest $manifest)
    {
        $this->manifest = $manifest;
    }

    public function handle(Collection $pages, Hydrator $hydrator)
    {
        foreach ($this->manifest->getPages() as $verb => $data) {
            array_set($data, 'verb', $verb);

            if ($verb == 'create' && ! array_has($data, 'icon')) {
                array_set($data, 'icon', 'plus');
            } elseif ($verb == 'index' && ! array_has($data, 'icon')) {
                array_set($data, 'icon', 'list');
            } elseif ($verb == 'edit' && ! array_has($data, 'icon')) {
                array_set($data, 'icon', 'pencil-square-o');
            }

            $page = $hydrator->hydrate(app(Page::class), $data);
            $pages->put($page->getRoute(), $page);
        }

        $this->manifest->setPages($pages);
    }
}
