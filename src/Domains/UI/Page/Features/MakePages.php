<?php

namespace SuperV\Platform\Domains\UI\Page\Features;

use SuperV\Platform\Support\Collection;
use SuperV\Platform\Domains\UI\Page\Page;
use SuperV\Platform\Domains\Droplet\Droplet;
use SuperV\Platform\Domains\Feature\Feature;

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

    public function __construct(Collection $pages)
    {
        $this->pages = $pages;
    }

    public function handle()
    {
        /** @var Page $page */
        foreach ($this->pages as $page) {
            $this->dispatch(new RegisterPage($page));
        }
    }
}
