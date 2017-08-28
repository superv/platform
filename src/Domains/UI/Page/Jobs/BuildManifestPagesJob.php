<?php namespace SuperV\Platform\Domains\UI\Page\Jobs;

use SuperV\Platform\Domains\Manifest\Manifest;
use SuperV\Platform\Domains\UI\Page\Page;
use SuperV\Platform\Support\Collection;
use SuperV\Platform\Support\Hydrator;

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

            $page = $hydrator->hydrate(superv(Page::class), $data);
            $pages->put($page->getRoute(), $page);
        }

        $this->manifest->setPages($pages);
    }
}