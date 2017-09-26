<?php

namespace SuperV\Platform\Domains\Manifest\Jobs;

use SuperV\Platform\Domains\Manifest\ManifestBuilder;
use SuperV\Platform\Domains\UI\Page\Page;
use SuperV\Platform\Domains\UI\Page\PageBuilder;

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

    public function handle(PageBuilder $builder)
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

            $page = $builder->reset()
                            ->setData($data)
                            ->setManifest($manifest)
                            ->build()
                            ->getPage();

            $manifest->addPage($page->getRoute(), $page);
        }
    }
}
