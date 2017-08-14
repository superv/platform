<?php namespace SuperV\Platform\Domains\UI\Page\Features;

use SuperV\Platform\Domains\Droplet\Droplet;
use SuperV\Platform\Domains\Feature\Feature;
use SuperV\Platform\Domains\Manifest\Manifest;
use SuperV\Platform\Domains\UI\Page\Page;

class RegisterManifestPages extends Feature
{
    /**
     * @var Manifest
     */
    private $manifest;

    /**
     * @var Droplet
     */
    private $droplet;

    public function __construct(Manifest $manifest, Droplet $droplet)
    {
        $this->manifest = $manifest;
        $this->droplet = $droplet;
    }

    public function handle()
    {
        $pages = $this->manifest->pages();

        /** @var Page $page */
        foreach ($pages as $page) {
            $page->setDroplet($this->droplet);

            $this->dispatch(new RegisterPage($page));
        }
    }
}