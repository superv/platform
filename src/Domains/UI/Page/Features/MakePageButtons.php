<?php

namespace SuperV\Platform\Domains\UI\Page\Features;

use SuperV\Platform\Domains\Feature\Feature;
use SuperV\Platform\Domains\UI\Button\Button;
use SuperV\Platform\Domains\UI\Button\Features\MakeButtons;
use SuperV\Platform\Domains\UI\Page\Page;

class MakePageButtons extends Feature
{
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
            array_map(function (Button $button) {
                $button->setSize('lg');
            }, $buttons);
            $page->setButtons($buttons);
        }
    }
}
