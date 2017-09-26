<?php

namespace SuperV\Platform\Domains\UI\Page\Features;

use SuperV\Platform\Domains\Feature\Feature;
use SuperV\Platform\Domains\UI\Page\Page;
use SuperV\Platform\Domains\UI\Page\PageBuilder;
use SuperV\Platform\Support\Inflator;

class BuildPage extends Feature
{
    /**
     * @var PageBuilder
     */
    private $builder;

    public function __construct(PageBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function handle(Inflator $inflator)
    {
        $manifest = $this->builder->getManifest();

        /** @var Page $page */
        $page = $inflator->inflate($this->builder->getPage(), $this->builder->getData());

        if ($model = $manifest->getModel()) {
            $page->setModel($manifest->getModel())
                 ->setManifest($manifest)
                 ->setPort($manifest->getPort());
        }

        $page->setDroplet($manifest->getDroplet());
    }
}