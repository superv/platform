<?php

namespace SuperV\Platform\Domains\UI\Page;

use SuperV\Platform\Domains\Feature\ServesFeaturesTrait;
use SuperV\Platform\Domains\UI\Page\Features\BuildPage;

class PageBuilder
{
    use ServesFeaturesTrait;

    /**
     * @var Page
     */
    private $page;

    private $manifest;

    private $data;

    public function __construct(Page $page)
    {
        $this->page = $page;
    }

    public function reset()
    {
        return app(PageBuilder::class);
    }

    public function build()
    {
        $this->dispatch(new BuildPage($this));

        return $this;
    }

    /**
     * @return Page
     */
    public function getPage(): Page
    {
        return $this->page;
    }

    /**
     * @return mixed
     */
    public function getManifest()
    {
        return $this->manifest;
    }

    /**
     * @param mixed $manifest
     *
     * @return PageBuilder
     */
    public function setManifest($manifest)
    {
        $this->manifest = $manifest;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     *
     * @return PageBuilder
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }



}