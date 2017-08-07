<?php namespace SuperV\Platform\Domains\UI\Page\Jobs;

use SuperV\Platform\Domains\Manifest\Manifest;
use SuperV\Platform\Domains\UI\Page\Page;

class BuildPageJob
{
    /**
     * @var array
     */
    private $pageData;

    /**
     * @var Manifest
     */
    private $manifest;

    public function __construct(Manifest $manifest, array $pageData)
    {
        $this->pageData = $pageData;
        $this->manifest = $manifest;
    }

    public function handle(Page $page)
    {
        $page->setManifest($this->manifest)
             ->setPage(array_get($this->pageData, 'page'))
             ->setRoute(array_get($this->pageData, 'route'))
             ->setUrl(array_get($this->pageData, 'url'))
             ->setHandler(array_get($this->pageData, 'handler'));

        return $page;
    }
}