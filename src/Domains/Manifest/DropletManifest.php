<?php

namespace SuperV\Platform\Domains\Manifest;

class DropletManifest extends Manifest
{
    protected $title = '';

    protected $link;

    protected $navigation = false;

    protected $manifests = [];

    /**
     * @return array
     */
    public function getManifests(): array
    {
        return $this->manifests;
    }

    /**
     * @return mixed
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return bool
     */
    public function isNavigation(): bool
    {
        return $this->navigation;
    }
}
