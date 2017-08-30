<?php

namespace SuperV\Platform\Domains\UI\Page\Features;

use SuperV\Platform\Domains\Droplet\Droplet;
use SuperV\Platform\Domains\Feature\Feature;
use SuperV\Platform\Domains\UI\Button\Features\MakeButtons;
use SuperV\Platform\Domains\UI\Page\Page;
use SuperV\Platform\Support\Collection;

class MakePageButtons extends Feature
{
    /**
     * @var Droplet
     */
    private $droplet;

    /**
     * @var Collection
     */
    private $pages;

    /**
     * @var Page
     */
    private $page;

    public function __construct(Page $page)
    {
        $this->page = $page;
    }

    public function handle()
    {
        $page = $this->page;

        if ($buttons = $page->getButtons()) {
            $arguments = [
                'entry' => $page->getEntry() ?: $page->newEntry(),
            ];

            $buttons = $this->dispatch(new MakeButtons($buttons, $arguments));
            $page->setButtons($buttons);
        }
    }
}
