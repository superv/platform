<?php

namespace SuperV\Platform\Domains\UI\Page\Features;

use SuperV\Platform\Domains\Feature\Feature;
use SuperV\Platform\Domains\UI\Page\Page;
use SuperV\Platform\Domains\UI\Page\PageBuilder;
use SuperV\Platform\Support\Hydrator;

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

    public function handle(Hydrator $hydrator)
    {
        $manifest = $this->builder->getManifest();

        /** @var Page $page */
        $page = $hydrator->hydrate($this->builder->getPage(), $this->builder->getData());

        if ($model = $manifest->getModel()) {
            $page->setModel($manifest->getModel())
                 ->setManifest($manifest)
                 ->setPort($manifest->getPort());
        }

        $page->setDroplet($manifest->getDroplet());
    }
}