<?php namespace SuperV\Platform\Domains\UI\Page\Jobs;

use SuperV\Platform\Domains\Manifest\Manifest;
use SuperV\Platform\Domains\UI\Page\Page;
use SuperV\Platform\Support\Collection;

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

    public function handle(Collection $pages)
    {
        foreach ($this->manifest->getPages() as $page => $pageData) {
            array_set($pageData, 'page', $page);

            $pages->push(
                (new Page())->setManifest($this->manifest)
                            ->setPage(array_get($pageData, 'page'))
                            ->setRoute(array_get($pageData, 'route'))
                            ->setUrl(array_get($pageData, 'url'))
                            ->setHandler(array_get($pageData, 'handler'))
                            ->setTitle(array_get($pageData, 'page_title'))
                            ->setNavigation(array_get($pageData, 'navigation', false))
            );
        }

        $this->manifest->setPages($pages);
    }
}