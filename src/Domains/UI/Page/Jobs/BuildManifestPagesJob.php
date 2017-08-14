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
        foreach ($this->manifest->getPages() as $page => $data) {
            array_set($data, 'page', $page);

            $pages->push(
                $hydrator->hydrate(superv(Page::class), $data)
            );
        }

        $this->manifest->setPages($pages);
    }
}