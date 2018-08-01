<?php

namespace SuperV\Platform\Domains\Nucleo\Resource;

use SuperV\Platform\Domains\Table\TableBuilder;

class ResourceIndex
{
    /**
     * @var \SuperV\Platform\Domains\Nucleo\Resource\Resource
     */
    protected $resource;

    /**
     * @var \SuperV\Platform\Domains\Table\TableBuilder
     */
    protected $builder;

    protected $columns = [];

    public function __construct(Resource $resource, TableBuilder $builder)
    {
        $this->resource = $resource;

        $this->builder = $builder;
    }

    public function render()
    {
        $this->build();

        return $this->builder->render();
    }

    public function build()
    {
        $this->builder->setColumns($this->getColumns());
        $this->builder->setModel($this->resource->getModel());
    }

    public function getColumns()
    {
        $columns = [];
        foreach ($this->columns as $slug) {
            $columns[$slug] = [
                'attr'    => $slug,
                'heading' => ucwords(str_replace(['_', '.'], ' ', $slug)),
            ];
        }

        return $columns;
    }
}