<?php namespace SuperV\Platform\Domains\UI\Page\Features;

use SuperV\Platform\Domains\Droplet\Droplet;
use SuperV\Platform\Domains\Feature\Feature;
use SuperV\Platform\Domains\Manifest\Manifest;
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

    public function __construct(Collection $pages)
    {
        $this->pages = $pages;
    }

    public function handle()
    {
        /** @var Page $page */
        foreach ($this->pages as $page) {

            if ($buttons = $page->getButtons()) {
                $arguments = [];
                foreach($buttons as $button => &$data) {
                    if (is_numeric($button)) {
                        $data = ['button' => $button = $data];
                    }
                    if ($buttonPage = $this->bpage($button)) {
                        array_set($data, 'route', $buttonPage->getRoute());
                        array_set_if_not(array_has($data, 'text'), $data, 'text', $buttonPage->getTitle());
                    }
                }
                $buttons = $this->dispatch(new MakeButtons($buttons, $arguments));
                $page->setButtons($buttons);
            }
        }
    }

    protected function bpage($button) {
        foreach ($this->pages as $page) {
            if ($page->getPage() === $button) {
                return $page;
            }
        }
    }
}