<?php

namespace SuperV\Platform\Domains\Manifest\Jobs;

use SuperV\Platform\Domains\Manifest\ManifestBuilder;
use SuperV\Platform\Domains\UI\Page\Page;
use SuperV\Platform\Support\Hydrator;

class BuildManifestPages
{
    /**
     * @var ManifestBuilder
     */
    private $builder;

    public function __construct(ManifestBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function handle(Hydrator $hydrator)
    {
        $manifest = $this->builder->getManifest();

        foreach ($this->builder->getPages() as $verb => $data) {
            array_set($data, 'verb', $verb);

            if ($verb == 'create' && ! array_has($data, 'icon')) {
                array_set($data, 'icon', 'plus');
            } elseif ($verb == 'index' && ! array_has($data, 'icon')) {
                array_set($data, 'icon', 'list');
            } elseif ($verb == 'edit' && ! array_has($data, 'icon')) {
                array_set($data, 'icon', 'pencil-square-o');
            }

            /** @var Page $page */
            $page = $hydrator->hydrate(app(Page::class), $data);

            if ($model = $manifest->getModel()) {
                $page->setModel($manifest->getModel())
                     ->setManifest($manifest)
                     ->setPort($manifest->getPort());
            }

            $page->setDroplet($manifest->getDroplet());

            $manifest->addPage($page->getRoute(), $page);
        }
    }
}
