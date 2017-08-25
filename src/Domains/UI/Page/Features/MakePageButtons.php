<?php namespace SuperV\Platform\Domains\UI\Page\Features;

use SuperV\Platform\Domains\Droplet\Droplet;
use SuperV\Platform\Domains\Feature\Feature;
use SuperV\Platform\Domains\UI\Button\Features\MakeButtons;
use SuperV\Platform\Domains\UI\Page\Page;
use SuperV\Platform\Domains\UI\Page\PageCollection;
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

    public function handle(PageCollection $pages)
    {
        $page = $this->page;

        if ($buttons = $page->getButtons()) {
            $arguments = ['entry' => $page->getEntry()];
            foreach ($buttons as $button => &$data) {
                if (is_numeric($button)) {
                    $data = ['button' => $button = $data];
                }
                if ($buttonPage = $this->findPage($pages, $button)) {
                    array_set($data, 'route', $buttonPage->getRoute());
                    array_set_if_not(array_has($data, 'text'), $data, 'text', $buttonPage->getTitle());
                }
            }
            $buttons = $this->dispatch(new MakeButtons($buttons, $arguments));
            $page->setButtons($buttons);
        }
    }

    protected function findPage(PageCollection $pages, $button)
    {
        foreach ($pages->byDroplet($this->page->getDroplet()->getSlug()) as $page) {
            if ($page->getPage() === $button) {
                return $page;
            }
        }
    }
}