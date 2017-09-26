<?php

namespace SuperV\Platform\Domains\UI\Page\Features;

use SuperV\Platform\Domains\UI\Page\Page;
use SuperV\Platform\Domains\UI\Page\PageCollection;
use SuperV\Platform\Domains\UI\Page\Tab;
use SuperV\Platform\Support\Inflator;
use SuperV\Platform\Support\Parser;

class MakePageTabs
{
    /**
     * @var Page
     */
    private $page;

    public function __construct(Page $page)
    {
        $this->page = $page;
    }

    public function handle(Inflator $inflator, Parser $parser, PageCollection $pages)
    {
        if ($tabs = $this->page->getTabs()) {
            foreach ($tabs as $id => &$tab) {
                array_set($tab, 'tab', $id);
                $url = array_get($tab, 'url');
                $url = $parser->parse($url, ['entry' => $this->page->getEntry()]);
                $tab = $inflator->inflate(app(Tab::class), $tab);
                
                $tab->setUrl($url);
            }
        }

        $this->page->setTabs($tabs);
    }
}