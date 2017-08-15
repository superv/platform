<?php namespace SuperV\Platform\Domains\UI\Page\Features;

use SuperV\Platform\Domains\Droplet\Droplet;
use SuperV\Platform\Domains\Feature\Feature;
use SuperV\Platform\Domains\Manifest\Manifest;
use SuperV\Platform\Domains\UI\Button\Features\MakeButtons;
use SuperV\Platform\Domains\UI\Page\Page;
use SuperV\Platform\Support\Collection;

class MakePages extends Feature
{
    /**
     * @var Droplet
     */
    private $droplet;

    /**
     * @var Collection
     */
    private $pages;

    public function __construct(Collection $pages, Droplet $droplet)
    {
        $this->droplet = $droplet;
        $this->pages = $pages;
    }

    public function handle()
    {
        $pages = $this->pages;

        /** @var Page $page */
        foreach ($pages as $page) {
            $page->setDroplet($this->droplet);

            $this->dispatch(new RegisterPage($page));
        }
    }

}